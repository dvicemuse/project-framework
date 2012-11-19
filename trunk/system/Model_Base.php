<?php

	abstract class Model_Base extends ORM_Base
	{
		private $_where;
		private $_order;
		private $_limit;



		/**
		 * Construct function
		 */
		public function __construct()
		{
			parent::__construct();
		}



		/**
		 * Set the result order
		 * @param int $column
		 * @param int $offset
		 * @return self
		 */
		public function limit($columns, $offset = FALSE)
		{
			if($this->is_id($columns) &&  $columns > 0)
			{
				if($offset === FALSE || $this->is_id($offset))
				{
					$this->_limit = " LIMIT ".trim(" {$offset},{$columns} ", ' ,');
					return $this;
				}
				throw new Exception("Column offset must be an integer.");
			}
			throw new Exception("Column limit must be an integer greater than 0.");
		}



		/**
		 * Set the result order
		 * @param $column
		 * @param $order
		 * @return self
		 */
		public function order($column, $value)
		{
			$value = strtoupper($value);
			if($this->load_helper('Db')->column_exists($this->model_name(), $column) === TRUE)
			{
				if($value == 'ASC' || $value == 'DESC')
				{
					$this->_order[] = " `{$column}` {$value}, ";
					return $this;
				}
				throw new Exception("Sort order is invalid.");
			}
			throw new Exception("Order column does not exist.");
		}



		/**
		 * Add an AND to the where clause
		 * @param string $column
		 * @param string $value
		 * @return self
		 */
		public function where($column, $value)
		{
			if($this->load_helper('Db')->column_exists($this->model_name(), $column))
			{
				$this->_where[] = " AND (`{$column}` = '{$this->Db->escape($value)}') ";
				return $this;
			}
			throw new Exception("Column does not exist.");
		}



		/**
		 * Add an AND LIKE to the where clause
		 * @param string $column
		 * @param string $value
		 * @return self
		 */
		public function like($column, $value)
		{
			if($this->load_helper('Db')->column_exists($this->model_name(), $column))
			{
				$this->_where[] = " AND (`{$column}` LIKE '{$this->Db->escape($value)}') ";
				return $this;
			}
			throw new Exception("Column does not exist.");
		}



		/**
		 *
		 * @param int $key_id
		 * @return object
		 */
		public function get($key_id = FALSE)
		{
			$this->load_helper('Db');

			// Parameter is an integer (primary key)
			if($key_id != FALSE)
			{
				if(strlen($key_id) == strlen(intval($key_id)))
				{
					// Reset where, and add primary key limit
					$this->_where = array(0 => " AND `{$this->Db->get_primary_key($this->model_name())}` = '{$key_id}' ");
				}else{
					return new Db_Wrapper;
				}
			}

			$clause = '';
			if(is_array($this->_where))
			{
				foreach($this->_where as $where)
				{
					$clause .= $where;
				}
			}

			$order = '';
			if(is_array($this->_order) && count($this->_order) > 0)
			{
				$order = " ORDER BY ";
				foreach($this->_order as $sort)
				{
					$order .= $sort;
				}
				$order = trim($order, ' ,');
			}

			// Generate query
			$sql =  "SELECT * FROM {$this->model_name()} WHERE 1=1 {$clause} {$order} {$this->_limit}";

			// Reset where
			$this->_where = array();
			$this->_order = array();
			$this->_limit = '';

			// Create result object
			$result = new Db_Wrapper;
			return $result->set($this->Db->get_rows($sql));
		}



		/**
		 * Check if a primary key exists
		 * @param int $key_id
		 * @return bool
		 */
		public function exists($key_id)
		{
			// Primary key
			if(ctype_digit($key_id))
			{
				$get = $this->get(intval($key_id));
				if($get->count() > 0)
				{
					return TRUE;
				}else{
					return FALSE;
				}
			}
			throw new Exception("Key must be numeric.");
		}

		
		
		public function dropdown($field_display_name, $blank_first = TRUE)
		{
			$return = array();
			
			if($blank_first)
			{
				$return[''] = '&nbsp;';
			}
			
			$get = $this->load_helper('Db')->get_rows("SELECT * FROM `{$this->model_name()}` ");
			if($get !== FALSE)
			{
				// Does the display field exist
				if(!isset($get[0][$field_display_name]))
				{
					throw new Exception('Field display name not found in result.');
				}
				
				// Add results to retun
				foreach($get as $r)
				{
					$return[$r[$this->model_name()."_id"]] = $r[$field_display_name];
				}
			}
			
			return $return;
		}
		
		
		

	}

?>