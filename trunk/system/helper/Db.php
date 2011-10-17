<?php

	class Db extends Framework
	{
		public $conn;
		public $q;
		public $num_rows;
		private $table_info;
		private $error;
		static private $_instance = null; // Singleton instance tracker



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

			// Put the connection into $this->conn
			$this->conn = mysql_connect($this->config->db->host, $this->config->db->username, $this->config->db->password) or die(mysql_error());
			if($this->conn)
			{
				mysql_select_db($this->config->db->database, $this->conn);
				return;
			}
			throw new Exception("Unable to connect to the database.");
		}



		/**
		 * Execute a query
		 * @param string $query
		 * @return bool
		 */
		function query($query)
		{
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
			throw new Exception('Table contains no columns.');
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
			return FALSE;
		}



		/**
		 * Get an array of table info.
		 * @param string $table_name
		 * @return array
		 */
		public function table_info($table_name)
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
		 * insert ID, or FALSE on failure
		 * @param string $table
		 * @param array $data
		 * @return mixed.
		 */
		function insert($table, $data)
		{
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
				$complete = "INSERT INTO `{$table}` SET {$query}";
				if($this->query($complete))
				{
					return mysql_insert_id($this->conn);
				}else{
					return false;
				}
			}
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
			if(!strstr(' '.$where, '='))
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
				$data = $this->stripslashes_deep($data);
				$query = '';
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
					$value[$k] = $this->stripslashes_deep($v);
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



		// Compatibility functions
		public function get_column_names($table_name){ return $this->column_names($table_name); }
		public function get_table_info($table_name){ return $this->table_info($table_name); }
	}

?>
