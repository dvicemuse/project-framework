<?php

	class Db extends Framework
	{
		public $conn;
		public $q;
		public $num_rows;
		public $query_count = 0;
		static protected $_instance = NULL; // Singleton instance tracker
		protected $table_info = array();
		protected $error;



		/**
		 * Db class is a singleton
		 * @return object
		 */
		public static function & Instance()
		{
			if(is_null(self::$_instance))
			{
				self::$_instance = new self();
			}
			return self::$_instance;
		}



		/*
		 * Connect to the database
		 */
		function __construct()
		{
			parent::__construct();
		}



		/**
		 * Connect MySQL
		 */
		private function _connect()
		{
			// Put the connection into $this->conn
			$this->conn = @mysql_connect($this->config()->db->host, $this->config()->db->username, $this->config()->db->password);
			if($this->conn)
			{
				@mysql_select_db($this->config()->db->database, $this->conn);
				return;
			}else{
				throw new Exception('Invalid MySQL connection parameters.');
			}
			throw new Exception("Unable to select database.");	
		}



		/**
		 * Execute a query
		 * @param string $query
		 * @return bool
		 */
		function query($query)
		{
			// Start database connection
			if(empty($this->conn))
			{
				$this->_connect();
			}

			$this->query_count++;
			$this->q = NULL;
			$this->num_rows = NULL;
			$this->q = mysql_query($query, $this->conn);
			if($this->q)
			{
				return TRUE;
			}
			throw new Exception('SQL query failed.');
		}



		/**
		 * Fetch a single row from the database. Returns array on success, FALSE on no result.
		 * @param string $query
		 * @return array|bool
		 */
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



		/**
		 * Fetch multiple rows from the database. Returns array on success, FALSE on no result.
		 * @param string $query
		 * @return array|bool
		 */
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



		/**
		 * Get the column names for a table.
		 * @param string $table_name
		 * @return mixed
		 */
		public function column_names($table_name)
		{
			// Load table information
			if($this->table_info($table_name))
			{
				// Loop through the columns
				foreach($this->table_info[$table_name] as $column => $d)
				{
					$ret[] = $column;
				}
				return $ret;
			}
			// Exception will be thrown by query() if table does not exist
		}



		/**
		 * Check if a column exists in a table.
		 * @param string $table_name
		 * @param string $column_name
		 * @return bool
		 */
		public function column_exists($table_name, $column_name)
		{
			// Load table information
			$columns = $this->column_names($table_name);
			if($columns)
			{
				// Loop through the columns
				foreach($columns as $d => $column)
				{
					if($column_name == $column)
					{
						return TRUE;
					}
				}
			}
			// Exception will be thrown by query() if table does not exist
		}



		/**
		 * Get an array of table info.
		 * @param string $table_name
		 * @return array
		 */
		public function table_info($table_name, $force_table_info_update = FALSE)
		{
			// Force an overwrite of the table data
			if($force_table_info_update === TRUE)
			{
				unset($this->table_info[$table_name]);
			}
			
			// Check if the table info is already loaded
			if(isset($this->table_info[$table_name]) && is_array($this->table_info[$table_name]))
			{
				// Return saved data
				return $this->table_info[$table_name];
			}else{
				// Pull table info from the database
				$info = $this->get_rows("SHOW COLUMNS FROM `{$table_name}`");
				if($info !== FALSE)
				{
					foreach($info as $r)
					{
						$this->table_info[$table_name][$r['Field']] = array(
							'type' => $r['Type'],
							'key' => $r['Key'],
							'null' => ($r['Null'] == 'YES'),
						);
					}
					// Return table info
					return $this->table_info[$table_name];
				}
			}
		}



		/**
		 * Get the primary key column name for a table.
		 * @param string $table_name
		 * @return mixed
		 */
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
				return FALSE;
			}
			// Made it to the end, fail
			return FALSE;
		}



		/**
		 * Insert a record with a keyed array of values. Returns the integer
		 * @param string $table
		 * @param array $data
		 * @return mixed.
		 */
		function insert($table, $data)
		{
			// Array data expected
			if(is_array($data) === FALSE)
			{
				throw new Exception('Array $data expected');
			}
			
			// Remove slashes
			$data = $this->stripslashes_deep($data);
			
			// Table info
			$table_info = $this->table_info($table);
			
			// Initialize query
			$query = '';

			// Loop through data array
			foreach($data as $k => $v)
			{
				// Field exists in table, and is not primary key
				if(isset($table_info[$k]) && $table_info[$k]['key'] != 'PRI')
				{
					if(($v == '' || $v === NULL) && $table_info[$k]['null'] === TRUE)
					{
						// Field is empty, and column allows NULL value
						$query .= " `{$k}` = NULL, ";
					}else if($table_info[$k]['type'] == 'date'){
						// Date field conversion
						$date = strtotime($v);
						if($date !== FALSE)
						{
							$v = date('Y-m-d', $date);
							$query .= " `{$k}` = '{$v}', ";
						}else{
							if($table_info[$k]['null'] === TRUE)
							{
								$query .= " `{$k}` = NULL, ";
							}else{
								$query .= " `{$k}` = '', ";
							}
						}
					}else{
						// Regular field assignment
						$v = $this->escape($v);
						$query .= " `{$k}` = '{$v}', ";
					}
				}
			}
			$query = trim($query, ' ,');
			$complete = "INSERT INTO `{$table}` SET {$query}";
			
			$this->query($complete);
			return mysql_insert_id($this->conn);
		}



		/**
		 * Update a record with a keyed array of values.
		 * @param string $table
		 * @param array $data
		 * @param string $where
		 * @return bool
		 */
		function update($table, $data, $where)
		{
			// Array data expected
			if(is_array($data) === FALSE)
			{
				throw new Exception('Array $data expected');
			}
			
			// Require some sort of limit in the where clause
			if(!strstr(' '.$where, '='))
			{
				throw new Exception('Assignment expected in $where');
			}
			
			// Remove slashes
			$data = $this->stripslashes_deep($data);
			
			// Table info
			$table_info = $this->table_info($table);
			
			// Initialize query
			$query = '';

			// Loop through data array
			foreach($data as $k => $v)
			{
				// Field exists in table, and is not primary key
				if(isset($table_info[$k]) && $table_info[$k]['key'] != 'PRI')
				{
					if(($v == '' || $v === NULL) && $table_info[$k]['null'] === TRUE)
					{
						// Field is empty, and column allows NULL value
						$query .= " `{$k}` = NULL, ";
					}else if($table_info[$k]['type'] == 'date'){
						// Date field conversion
						$date = strtotime($v);
						if($date !== FALSE)
						{
							$v = date('Y-m-d', $date);
							$query .= " `{$k}` = '{$v}', ";
						}else{
							if($table_info[$k]['null'] === TRUE)
							{
								$query .= " `{$k}` = NULL, ";
							}else{
								$query .= " `{$k}` = '', ";
							}
						}
					}else{
						// Regular field assignment
						$v = $this->escape($v);
						$query .= " `{$k}` = '{$v}', ";
					}
				}
			}
			$query = trim($query, ' ,');
			$complete = "UPDATE `{$table}` SET {$query} WHERE {$where}";
			return $this->query($complete);
		}



		/**
		 * Escape a string
		 * @param mixed $value
		 * @return mixed
		 */
		function escape($value)
		{
			if(is_array($value))
			{
				foreach($value as $k=>$v)
				{
					$value[$k] = $this->escape($v);
				}
				return $value;
			}else{
				return mysql_real_escape_string($value);
			}
		}



		/**
		 * Recursively remove slash characters from an array or string.
		 * @param mixed $value
		 * @return mixed
		 */
		function stripslashes_deep($value)
		{
			if(is_array($value))
			{
				foreach($value as $k=>$v)
				{
					// Preserve NULL and BOOL values
					if($v != NULL && !is_bool($v))
					{
						$value[$k] = $this->stripslashes_deep($v);
					}
				}
				return $value;
			}else{
				return stripslashes($value);
			}
		}



		/**
		 * Get all of the table names for the current database,
		 * store the resulting array in $this->tables
		 * @return bool
		 */
		function get_table_names()
		{
			// Get all of the table names
			if(!$this->query("SHOW TABLES"))
			{
				return FALSE;
			}
			// Get results from query
			if(mysql_num_rows($this->q) == 0)
			{
				return FALSE;
			}else{
				while($r = mysql_fetch_array($this->q))
				{
					$this->tables[] = $r[0];
				}
				return TRUE;
			}
		}



		/**
		 * Get valid enum values for a column. (borrowed from php.net)
		 * @param string $table
		 * @param string $field
		 * @param bool $ucfirst_values
		 * @return <type> 
		 */
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
					sort($types);
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



		// Compatibility functions
		public function get_column_names($table_name){ return $this->column_names($table_name); }
		public function get_table_info($table_name){ return $this->table_info($table_name); }
	}

?>
