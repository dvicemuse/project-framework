<?php

	class Scaffold extends Framework
	{
		// Internal variables
		private $scaffold_config;
		private $results;
		private $total_results;
		private $where_parts;


		// Starting up
		public function __construct()
		{
			// Load modules
			$this->load_helper('Db');

			// Set the default values
			$this->scaffold_config->table_name				= '';
			$this->scaffold_config->table_names				= array();
			$this->scaffold_config->items_per_page			= 15;

			$this->scaffold_config->join_tables				= array();

			$this->scaffold_config->show_columns			= array();

			$this->scaffold_config->criteria->sort_order	= 'DESC';
			$this->scaffold_config->criteria->sort_column	= '';
			$this->scaffold_config->criteria->page			= 1;
			$this->scaffold_config->criteria->search_term	= '';
		}


		public function set_column_display($column_array)
		{
			// Make sure input is an array
			if(is_array($column_array))
			{
				foreach($column_array as $database_name => $friendly_name)
				{
					// Save the column info
					$this->scaffold_config->show_columns[$database_name] = $friendly_name;
				}
				return TRUE;
			}else{
				return FALSE;
			}
		}


		// Beta function... will need to be cleaned up
		public function set_join_table($table_1, $column_1, $table_2, $column_2)
		{
			// Check the column names
			if($this->Db->column_exists($table_1, $column_1) && $this->Db->column_exists($table_2, $column_2))
			{
				// Save the join
				$this->scaffold_config->join_tables[] = array(
					'table_1' => $table_1,
					'table_2' => $table_2,
					'column_1' => $column_1,
					'column_2' => $column_2,
				);

				if(!in_array($table_1, $this->scaffold_config->table_names))
				{
					$this->scaffold_config->table_names[] = $table_1;
				}
				if(!in_array($table_2, $this->scaffold_config->table_names))
				{
					$this->scaffold_config->table_names[] = $table_2;
				}
			}
		}


		// Set the items per page
		public function set_items_per_page($rows)
		{
			if(!preg_match('/[^0-9]/', $rows) && $rows > 0)
			{
				$this->scaffold_config->items_per_page = $rows;
				return TRUE;
			}else{
				return FALSE;
			}
		}


		// Set the current page
		public function set_page_number($page)
		{
			if(!preg_match('/[^0-9]/', $page) && $page > 0)
			{
				$this->scaffold_config->criteria->page = $page;
				return TRUE;
			}else{
				return FALSE;
			}
		}


		// Set the column sort order
		public function set_sort_order($order)
		{
			if(strtoupper($order) == 'DESC' || strtoupper($order) == 'ASC')
			{
				$this->scaffold_config->criteria->sort_order = $order;
				return TRUE;
			}else{
				return FALSE;
			}
		}


		// Set the sort column
		public function set_sort_column($column_name)
		{
			$table = $this->Db->check_column_exists($this->scaffold_config->table_names, $column_name);
			if($table !== FALSE)
			{
				$this->scaffold_config->show_columns[$column_name]['table'] = $table;
				$this->scaffold_config->criteria->sort_column = $column_name;
				return TRUE;
			}else{
				return FALSE;
			}
		}


		// Set the table name
		public function set_table_name($table_name)
		{
			$table_name = strtolower($table_name);
			if($this->Db->get_table_info($table_name))
			{
				$this->scaffold_config->table_name = $table_name;
				$this->scaffold_config->table_names[] = $table_name; # Used for joins
				return $this;
			}
		}


		public function set_search_term($search_term)
		{
			if(trim($search_term) != '')
			{
				$this->scaffold_config->criteria->search_term = $search_term;
				$this->where_parts[] = trim($search_term);
			}
		}


		public function search()
		{
			$primary_key = $this->Db->get_primary_key($this->scaffold_config->table_name);

			// Set the sort order to the primary key if not defined
			if(empty($this->scaffold_config->criteria->sort_column))
			{
				$this->scaffold_config->criteria->sort_column = $this->Db->get_primary_key($this->scaffold_config->table_name);
			}


			$total_results = $this->Db->get_row("
				SELECT
					count(`{$primary_key}`) as `count`
				FROM
					`{$this->scaffold_config->table_name}`
					{$this->join()}
				WHERE
					{$this->where()}
			");
			$this->total_results = $total_results['count'];
			$sql = "
				SELECT
					*
				FROM
					`{$this->scaffold_config->table_name}`
					{$this->join()}
				WHERE
					{$this->where()}
				ORDER BY
					{$this->sort_column()}
				{$this->scaffold_config->criteria->sort_order}
				LIMIT {$this->offset()}, {$this->scaffold_config->items_per_page}

			";
			#pr($sql);
			$this->results = $this->Db->get_rows($sql);

			// Format data columns
			$this->column_format();
			
			return $this;
		}



		private function column_format()
		{
			// No results loaded, return
			if(!is_array($this->results))
			{
				return TRUE;
			}
			// No columns defined
			if(!is_array($this->scaffold_config->show_columns))
			{
				return TRUE;
			}
			// Loop through results
			foreach($this->results as $result_key => $result_data)
			{
				foreach($this->scaffold_config->show_columns as $column_name => $column_options)
				{
					if(!empty($column_options['format']))
					{
						$f = $this->results[$result_key][$column_name];
						eval('$f='.$column_options['format'].';');
						$this->results[$result_key][$column_name] = $f;
					}
				}
			}
		}


		private function sort_column()
		{
			$table = '';
			if(!empty($this->scaffold_config->show_columns[$this->scaffold_config->criteria->sort_column]['table']))
			{
				$table = "`{$this->scaffold_config->show_columns[$this->scaffold_config->criteria->sort_column]['table']}`.";
			}
			return "{$table}`{$this->scaffold_config->criteria->sort_column}`";
		}



		// Return the where clause for the current search query
		private function where()
		{
			$q = '';
			if(!empty($this->where_parts))
			{
				foreach($this->scaffold_config->table_names as $table)
				{
					$columns = $this->Db->get_column_names($table);
					foreach($columns as $t)
					{
						foreach($this->where_parts as $where)
						{
							$where = $this->Db->escape($where);
							$q .= " `{$table}`.`{$t}` LIKE '%{$where}%' | ";
						}
					}
				}
				$q = trim($q, ' |');
				$q = str_replace('|', ' OR ', $q);
				$q = " AND ({$q}) ";
			}
			// Start with something that is always true and then add
			$q = ' 1=1 '.$q;

			return $q;
		}



		private function join()
		{
			$join = "";
			if(is_array($this->scaffold_config->join_tables))
			{
				foreach($this->scaffold_config->join_tables as $k => $i)
				{
					$join .= " JOIN `{$i['table_1']}` ON `{$i['table_1']}`.`{$i['column_1']}` = `{$i['table_2']}`.`{$i['column_2']}`";
				}
			}
			return $join;
		}



		// Return the offset for paging (used in $this->search())
		private function offset()
		{
			return ($this->scaffold_config->criteria->page-1)*$this->scaffold_config->items_per_page;
		}







		// Make a column sort link
		public function sort_column_link($column_name, $debug = FALSE)
		{
			// Not an array, end here
			if(empty($column_name))
			{
				return '';
			}

			if(trim($column_name) == trim($this->scaffold_config->criteria->sort_column))
			{
				$conf->sort_column = $column_name;
				// Switch sort order
				if($this->scaffold_config->criteria->sort_order == 'ASC')
				{
					$conf->sort_order = 'DESC';
				}else{
					$conf->sort_order = 'ASC';
				}
			}else{
				// Set column to sort on default order
				$conf->sort_column = $column_name;
				$conf->sort_order = 'ASC';
			}

			unset($conf->page);

			// Make the url for this sort
			foreach($conf as $k=>$v)
			{
				$url .= "/{$k}:{$v}";
			}

			if(!empty($this->scaffold_config->criteria->search_term))
			{
				$url .= "/search:{$this->scaffold_config->criteria->search_term}";
			}

			return trim($url, '/').'/';
		}


		public function column_link_class($column)
		{
			if($_GET['sort_column'] != $column)
			{
				$ret = 'none';
			}else if(strtoupper($this->scaffold_config->criteria->sort_order) == 'ASC'){
				$ret = 'ascending';
			}else{
				$ret = 'descending';
			}
			return $ret;
		}



		public function column_display()
		{
			return $this->scaffold_config->show_columns;
		}


		public function paging_links($current_page_url)
		{
			$this->load_helper('Paginate');
			$options = array(
				'current_page' 		=> $this->scaffold_config->criteria->page,
				'total_results' 	=> $this->total_results,
				'results_per_page' 	=> $this->scaffold_config->items_per_page,
				'maximum_links'		=> '8',
				'url_format' 		=> $this->paging_link_format($current_page_url),
			);
			return $this->Paginate->show_page_links($options);
		}



		// Make a column sort link
		private function paging_link_format($current_page_url)
		{
			// Copy the config array
			$conf = $this->scaffold_config->criteria;
			$conf->page = '[x]';

			// Make the url for this sort
			foreach($conf as $k=>$v)
			{
				if(!empty($v))
				{
					$url .= "/{$k}:{$v}";
				}
			}
			$current_page_url = rtrim($current_page_url, '/');
			return $current_page_url.'/'.trim($url, '/')."/";
		}



		public function config()
		{
			return $this->scaffold_config;
		}


		public function results()
		{
			return $this->results;
		}

	}


?>