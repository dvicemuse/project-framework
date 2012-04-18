<?php

	abstract class ORM_Base extends Framework
	{
		protected $_data;
		protected $_to_many = array();
		protected $_to_many_map = array();
		protected $_to_one = array();
		protected $_to_one_map = array();



		/**
		 * Construct function
		 */
		public function __construct()
		{
			parent::__construct();
		}



		/**
		 * Get the current model name
		 * @return string
		 */
		public function model_name()
		{
			// Check for name override
			if(isset($this->model_name))
			{
				return strtolower($this->model_name);
			}
			
			// Return class type
			return strtolower(get_class($this));
		}



		/**
		 * Load an object
		 * @param int|string $id
		 * @return ORM_Base
		 */
		public function orm_load($id = NULL)
		{
			// Make sure the table exists
			if($this->load_helper('Db')->table_info($this->model_name()) !== FALSE)
			{
				// Load record by ID from database
				if($id !== NULL)
				{
					$load_data = $this->load_helper('Db')->get_row("SELECT * FROM `{$this->model_name()}` WHERE `{$this->Db->get_primary_key($this->model_name())}` = '{$this->Db->escape($id)}'   ");
					if($load_data !== FALSE)
					{
						$this->_data = $load_data;
						return $this;
					}
					throw new Exception("Object with ID '{$id}' does not exist.");
				}
				
				// Use the model base query builder settings
				if($id === NULL)
				{
					$return = array();
					$results = $this->get();
					if($results->count() != 0)
					{
						$key = $this->load_helper('Db')->get_primary_key($this->model_name());
						foreach($results->results() as $row)
						{
							$eval = '$object = new '.ucfirst($this->model_name()).';';
							eval($eval);
							$return[] = $object->orm_load($row[$key]);
						}
					}
					return $return;
				}
			}
			throw new Exception("Table '{$this->_table}' does not exist.");
		}



		public function __call($name, $arguments)
		{
			// Data column exists
			if(isset($this->_data["{$this->model_name()}_{$name}"]))
			{
				return $this->_data["{$this->model_name()}_{$name}"];
			}else if(isset($this->_data[$name])){
				return $this->_data[$name];
			}

			// One to many
			if(isset($this->_to_many[$name]))
			{
				$sql = "SELECT `{$this->_to_many[$name]}_id` FROM `{$this->_to_many[$name]}` WHERE `{$this->model_name()}_id` = '{$this->_data["{$this->model_name()}_id"]}' ";
				
				if(!empty($arguments[0]))
				{
					$sql .= " AND ".$arguments[0];
				}
				$get_records = $this->load_helper('Db')->get_rows($sql);

				$return = array();

				if($get_records !== FALSE)
				{
					foreach($get_records as $r)
					{
						$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $this->_to_many[$name])));
						$this->load_model($model_name);
						$eval = '$object = new '.$model_name.';';
						eval($eval);
						$return[] = $object->orm_load($r["{$this->_to_many[$name]}_id"]);
					}
				}
				return $return;
			}
			
			// One to one
			if(isset($this->_to_one[$name]))
			{
				$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $this->_to_one[$name])));
				$this->load_model($model_name);
				$eval = '$object = new '.$model_name.';';
				eval($eval);
				
				$return = $object->orm_load($this->_data[$name."_id"]);
				if(is_object($return))
				{
					return $return;
				}
				
				return $return;
			}
			
			// One to one map
			if(isset($this->_to_one_map[$name]))
			{
				$join_table = array_search($this->_to_one_map[$name], $this->_to_one_map);
				
				$sql = "
					SELECT
						`{$join_table}`.*
					FROM
						`{$this->_to_one_map[$name]}`
						JOIN `{$join_table}` ON `{$join_table}`.`{$join_table}_id` = `{$this->_to_one_map[$name]}`.`{$join_table}_id`
					WHERE
						`{$this->_to_one_map[$name]}`.`{$this->model_name()}_id` = '{$this->_data["{$this->model_name()}_id"]}'
				";
				
				$get_record = $this->load_helper('Db')->get_row($sql);

				$return = NULL;

				if($get_record !== FALSE)
				{
					$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $join_table)));
					$this->load_model($model_name);
					$eval = '$object = new '.$model_name.';';
					eval($eval);
					
					$return = $object->orm_load($get_record["{$join_table}_id"]);
				}
				return $return;
			}

			// One to many map
			if(isset($this->_to_many_map[$name]))
			{
				$join_table = array_search($this->_to_many_map[$name], $this->_to_many_map);
				
				$sql = "
					SELECT
						`{$join_table}`.*
					FROM
						`{$this->_to_many_map[$name]}`
						JOIN `{$join_table}` ON `{$join_table}`.`{$join_table}_id` = `{$this->_to_many_map[$name]}`.`{$join_table}_id`
					WHERE
						`{$this->_to_many_map[$name]}`.`{$this->model_name()}_id` = '{$this->_data["{$this->model_name()}_id"]}'
				";
				
				if(!empty($arguments[0]))
				{
					$sql .= " AND ".$arguments[0];
				}
				
				$get_record = $this->load_helper('Db')->get_rows($sql);

				$return = array();

				if($get_record !== FALSE)
				{
					foreach($get_record as $record)
					{
						$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $join_table)));
						$this->load_model($model_name);
						$eval = '$object = new '.$model_name.';';
						eval($eval);
						$return[] = $object->orm_load($record["{$join_table}_id"]);
					}
				}
				return $return;
			}
			
			throw new Exception("Unknown call method.");
		}




		/**
		 * Set one to many relationship
		 * @param string $table
		 * @param string $map_table
		 * @return ORM_Base
		 */
		public function has_many($table, $map_table = NULL)
		{
			if($map_table === NULL)
			{
				// Check if table exists
				if($this->load_helper('Db')->table_info($table) !== FALSE)
				{
					// Set relationship
					$this->_to_many[$table] = $table;
	
					// Done
					return $this;
				}
			}else{
				// Check if table exists
				if($this->load_helper('Db')->table_info($table) !== FALSE && $this->load_helper('Db')->table_info($map_table) !== FALSE)
				{
					// Set relationship
					$this->_to_many_map[$table] = $map_table;
	
					// Done
					return $this;
				}
			}

			throw new Exception("Relationship table does not exist.");
		}



		/**
		 * Set one to one relationship
		 * @param string $table
		 * @param string $map_table
		 * @return ORM_Base
		 */
		public function has_one($table, $map_table = NULL)
		{
			if($map_table === NULL)
			{
				// Check if table exists
				if($this->load_helper('Db')->table_info($table) !== FALSE)
				{
					// Set relationship
					$this->_to_one[$table] = $table;
	
					// Done
					return $this;
				}
			}else{
				// Check if table exists
				if($this->load_helper('Db')->table_info($table) !== FALSE && $this->load_helper('Db')->table_info($map_table) !== FALSE)
				{
					// Set relationship
					$this->_to_one_map[$table] = $map_table;
	
					// Done
					return $this;
				}
			}

			throw new Exception("Table '{$table}' does not exist.");
		}



		/**
		 * Check if a primary key exists
		 * @param int $key_id
		 * @return bool
		 */
		public function exists($key_id)
		{
			// Primary key
			if(strlen($key_id) == strlen(ereg_replace('[^0-9]', '', $key_id)))
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



		/**
		 * Debug data function
		 */
		public function expose_data()
		{
			return $this->_data;
		}

	}

?>