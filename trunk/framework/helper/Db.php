<?php

	class Db extends Framework
	{
		public $conn;
		public $q;
		public $num_rows;
		public $inserted_columns;
		private $table_info;
		private $error;
		static private $_instance = null; // Singleton instance tracker

		// Singleton creator
		public static function & Instance()
		{
			if(is_null(self::$_instance))
			{
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		function __construct()
		{
			// Put the connection into $this->conn
			$this->conn = @mysql_connect($this->config['database_host'], $this->config['database_user'], $this->config['database_pass']);
			if($this->conn)
			{
				mysql_select_db($this->config['database_name'], $this->conn);
			}else{
				die('Database connection error.');
			}
		}

		function query($query)
		{
			$this->q = NULL;
			$this->num_rows = NULL;
			$this->q = mysql_query($query, $this->conn);
			if($this->q)
			{
				$ret = TRUE;
			}else{
				$this->error = mysql_error();
				$ret = FALSE;
			}
			return $ret;
		}
		
		function get_row($query)
		{
			// Perform the query
			if(!$this->query($query))
			{
				return FALSE;
			}
			// Get results from query
			if(mysql_num_rows($this->q) == 0)
			{
				return FALSE;
			}else{
				return $this->stripslashes_deep(mysql_fetch_assoc($this->q));
			}
		}

		
		function get_rows($query)
		{
			// Perform the query
			if(!$this->query($query))
			{
				return FALSE;
			}
			// Get results from query
			if(mysql_num_rows($this->q) == 0)
			{
				return FALSE;
			}else{
				while($r = mysql_fetch_assoc($this->q))
				{
					$ret[] = $this->stripslashes_deep($r);
				}
				return $ret;
			}
		}



		public function check_column_exists($table_names, $column_name)
		{
			if(!is_array($table_names))
			{
				$t = $table_names;
				$table_names = array();
				$table_names[] = $t;	
			}
			foreach($table_names as $table_name)
			{
				// Load table information
				if($this->get_table_info($table_name))
				{
					// Loop through the columns
					foreach($this->table_info[$table_name] as $column => $d)
					{
						// Check the key field for the PRI attribute
						if($column == $column_name)
						{
							// Found it
							return $table_name;
						}
					}
				}
			}
			return FALSE;
		}


		public function get_column_names($table_name)
		{
			// Load table information
			if($this->get_table_info($table_name))
			{
				// Loop through the columns
				foreach($this->table_info[$table_name] as $column => $d)
				{
					$ret[] = $column;
				}
				return $ret;
			}
			return FALSE;
		}


		/*
		 * Return array column information for a table
		 */
		public function get_table_info($table_name)
		{
			// Check if the table info is already loaded
			if(is_array($this->table_info[$table_name]))
			{
				// Return saved data
				return $this->table_info[$table_name];
			}else{
				// Pull table info from the database
				if($this->query("SHOW COLUMNS FROM `{$table_name}`") === TRUE && mysql_num_rows($this->q) > 0)
				{
					while($r = mysql_fetch_assoc($this->q))
					{
						$this->table_info[$table_name][$r['Field']] = array(
							'type' => $r['Type'],
							'key' => $r['Key'],
						);
					}
					// Return table info
					return $this->table_info[$table_name];
				}
			}
			// Made it to the end, fail
			return FALSE;
		}




		public function get_primary_key($table_name)
		{
			// Load the table information
			if($this->get_table_info($table_name) !== FALSE)
			{
				// Loop through the columns
				foreach($this->table_info[$table_name] as $column_name => $d)
				{
					// Check the key field for the PRI attribute
					if(strtoupper($d['key']) == 'PRI')
					{
						// Found it, return column name string
						return $column_name;
					}
				}
				// Add an error, there is no primary key for this table
				$this->add_error("Primary key not defined in table `{$table_name}`. Db->get_primary_key()");
				return FALSE;
			}
			// Made it to the end, fail
			$this->add_error("Table information not available for table `{$table_name}`. Db->get_primary_key()");
			return FALSE;
		}




		public function load($table_name, $load_id, $field_name = '')
		{
			$load_id = $this->escape($load_id);
			$field_name = $this->escape($field_name);
			$table_name = $this->escape($table_name);

			// Which field to load from
			if(empty($field_name))
			{
				// Load from primary key
				$field_name = $this->get_primary_key($table_name);
				if($field_name === FALSE)
				{
					return FALSE;
				}
			}else{
				// Load from field_name param
				if($this->check_column_exists($table_name, $field_name) === FALSE)
				{
					$this->add_error("Non existant column name passed to Db->load()");
					return FALSE;
				}
			}
			
			// We have a field name, now make the query
			$sql = "SELECT * FROM `{$table_name}` WHERE `{$field_name}` = '{$load_id}' LIMIT 1 ";
			return $this->get_row($sql);
		}


		
		
		
		function insert($table, $data)
		{
			$this->inserted_columns = array();
			// Perform the query
			if(!$this->query("SHOW COLUMNS FROM `{$table}`"))
			{
				return FALSE;
			}
			// Get results from query
			if(mysql_num_rows($this->q) == 0)
			{
				return FALSE;
			}else{
				while($r = mysql_fetch_assoc($this->q))
				{
					$fields[$r['Field']] = array(
						'type' => $r['Type'],
						'key' => $r['Key'],
					);
				}
				$data = $this->stripslashes_deep($data);
				foreach($data as $k => $v)
				{
					if(is_array($fields[$k]) && $fields[$k]['key'] != 'PRI')
					{
						$this->inserted_columns[$k] = $k;
						$v = $this->escape($v);
						$query .= " `{$k}` = '{$v}', ";
					}
				}
				$query = trim($query, ' ,');
				$complete = "INSERT INTO `{$table}` SET {$query}";
				if($this->query($complete))
				{
					return mysql_insert_id($this->conn);
				}else{
					return false;
				}
			}
		}

	
		function update($table, $data, $where)
		{
			if(!strstr(' '.$where, '='))
			{
				//$this->add_error('No where clause specified. Exiting update.');
				return FALSE;
			}
			// Perform the query
			if(!$this->query("SHOW COLUMNS FROM `{$table}`"))
			{
				return FALSE;
			}
			// Get results from query
			if(mysql_num_rows($this->q) == 0)
			{
				return FALSE;
			}else{
				while($r = mysql_fetch_assoc($this->q))
				{
					$fields[$r['Field']] = array(
						'type' => $r['Type'],
						'key' => $r['Key'],
					);
				}
				$data = $this->stripslashes_deep($data);
				foreach($data as $k => $v)
				{
					if(is_array($fields[$k]) && $fields[$k]['key'] != 'PRI')
					{
						$v = $this->escape($v);
						$query .= " `{$k}` = '{$v}', ";
					}
				}
				$query = trim($query, ' ,');
				$complete = "UPDATE `{$table}` SET {$query} WHERE {$where}";
				return $this->query($complete);
			}
		}

		function num_rows()
		{
			return mysql_num_rows($this->q);
		}



		/**
		 * Escape a string
		 *
		 * @param string $value
		 * @return string results
		 */
		function escape($value)
		{
			if(is_array($value))
			{
				return mysql_real_escape_string(serialize($value), $this->conn);
			}
			return mysql_real_escape_string(trim($value), $this->conn);
		}



		/**
		 * Recursively remove slash characters from an array or string
		 *
		 * @param array/string $value
		 * @return array/string results
		 */
		function stripslashes_deep($value)
		{
			if(is_array($value))
			{
				foreach($value as $k=>$v)
				{
					$value[$k] = $this->stripslashes_deep($v);
				}
				return $value;
			}else{
				return stripslashes($value);
			}
		}



		/**
		 * Will pull a single result from table, joining
		 * in any table with the appropriate naming prefix.
		 * If there is a field in table named address_id, it
		 * will look for a table named address, and then
		 * join in the row with the value stored in that field.
		 *
		 * @param string $table
		 * @param string $where
		 * @return array results
		 */
		function fetch_complete($table, $where)
		{
			// Make sure there is a where clause
			if(!strstr(' '.$where, '='))
			{
				//$this->add_error('No where clause specified. Exiting fetch_complete.');
				return FALSE;
			}
			// Fetch table names for later
			if(!$this->get_table_names())
			{
				return FALSE;
			}
			// Perform the query
			if(!$this->query("SHOW COLUMNS FROM `{$table}`"))
			{
				return FALSE;
			}
			// Get results from query
			if(mysql_num_rows($this->q) == 0)
			{
				return FALSE;
			}else{
				while($r = mysql_fetch_assoc($this->q))
				{
					$fields[$r['Field']] = array(
						'type' => $r['Type'],
						'key' => $r['Key'],
					);
				}

				$complete = "SELECT * FROM {$table} WHERE {$where}";
				$return = $this->get_row($complete);				

				if($return !== FALSE)
				{
					$final_return = $return;
					foreach($return as $key => $val)
					{
						if(preg_match('/([a-z_0-9]+)_id/i', $key, $matches))
						{
							if($matches[1] != $table && array_search($matches[1], $this->tables) !== FALSE)
							{
								$get_join = $this->get_row("SELECT * FROM {$matches[1]} WHERE {$key} = '{$val}' ");
								if($get_join !== FALSE)
								{
									$final_return = array_merge($final_return, $get_join);
								}
							}
						}
					}
				}
				return $final_return;

			}
		}



		/**
		 * Get all of the table names for the current database,
		 * store the resulting array in $this->tables
		 *
		 * @return boolean success
		 */
		function get_table_names()
		{
			// Get all of the table names
			if(!$this->query("SHOW TABLES"))
			{
				//$this->add_error("Could not get table names.");
				return FALSE;
			}
			// Get results from query
			if(mysql_num_rows($this->q) == 0)
			{
				$this->add_error("Could not get table names.");
				return FALSE;
			}else{
				while($r = mysql_fetch_array($this->q))
				{
					$this->tables[] = $r[0];
				}
				return TRUE;
			}
		}


		// Get valid enum values from a column
		// Borrowed from php.net
		function get_enum($table, $field, $ucfirst_values = TRUE)
		{
			$result = $this->query("show columns from {$table}");
			$types = array();
			while($tuple=mysql_fetch_assoc($this->q))
			{
				if($tuple['Field'] == $field)
				{
					$types=$tuple['Type'];
					$beginStr=strpos($types,"(")+1;
					$endStr=strpos($types,")");
					$types=substr($types,$beginStr,$endStr-$beginStr);
					$types=str_replace("'","",$types);
					$types=split(',',$types);
					if($sorted)
					{
						sort($types);
					}
				}
			}
			foreach($types as $v)
			{
				if($ucfirst_values)
				{
					$ret[$v] = ucfirst($v);
				}else{
					$ret[$v] = $v;	
				}
			}
			return $ret;
		}


		// Check for error, false if there are no errors
		public function error()
		{
			if(!empty($this->error))
			{
				return $this->error;
			}
			return FALSE;
		}

	}





?>