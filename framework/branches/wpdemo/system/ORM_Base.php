<?php
/**
 * @file ORM_Base.php
 * @package    ProjectFramework
 *
 * @license    see LICENSE.txt
 */

/**
 * @class ORM_Base
 * @brief Base Object Relation Model class. Abstract base class for CMS ORM classes
 *
 * @package  ProjectFramework
 * @since    1.0.0
 */
	abstract class ORM_Base extends Framework
	{
		/**
		 * @var $_loaded
		 * @brief boolean check to ensure object currently loaded via ORM_load call
		 */
		protected $_loaded = FALSE;
		
		/**
		 * @var $_data
		 * @brief array of data for object/model
		 */
		protected $_data;
		
		/**
		 * @var $_to_many
		 * @brief array defining one to many relationships
		 */
		protected $_to_many = array();
		
		/**
		 * @var $_to_many_map
		 * @brief array defining one to many relationships with mapping tables
		 */
		protected $_to_many_map = array();
		
		/**
		 * @var $_to_one
		 * @brief array defining one to one relationships
		 */
		protected $_to_one = array();
		
		/**
		 * @var $_to_one_map
		 * @brief array defining one to one relationships with mapping tables
		 */
		protected $_to_one_map = array();
		
		/**
		 * @var $_transform
		 * @brief array of transformation mappings for to/from tables
		 */
		protected $_transform = array();

		/**
		 * @var $_hook
		 * @brief array of event hooks
		 */
		protected $_hook = array();


		/**
		 * Construct function
		 */
		public function __construct()
		{
			parent::__construct();
		}



		/**
		 * @brief Get the current model name.
		 * 
		 * @return string - lowercase model/class name
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
		 * @brief Load an object.
		 * 
		 * @param int|string $id - primary key identifier for the object to load
		 * @return mixed - instance or array of ORM_Wrapper
		 * @throws Exception - when id doesn't exist or the table doesn't exist
		 */
		public function orm_load($id = NULL)
		{
			// Load record by ID from database
			if($id !== NULL)
			{
				if($this->is_id($id))
				{
					$load_data = $this->load_helper('Db')->get_row("SELECT * FROM `{$this->config()->db->prefix}{$this->model_name()}` WHERE `{$this->model_name()}_id` = '{$id}' LIMIT 1");
					if($load_data !== FALSE)
					{
						$this->_loaded = TRUE;
						$this->_data = $load_data;
						$this->_orm_from_database_transform();
						return $this;
					}
				}
				throw new Exception("Object with ID '{$id}' does not exist.");
			}
			
			// Use the model base query builder settings
			$return = new ORM_Wrapper;
			$results = $this->get();
			if($results->count() != 0)
			{
				foreach($results->results() as $row)
				{
					$eval = '$object = new '.ucfirst($this->model_name()).';';
					eval($eval);
					$return->push($object->orm_set_load($row)->_orm_from_database_transform());
				}
			}
			return $return;
		}


		/**
		 * @brief Magic method override.
		 * 
		 * @param string $name - method to call
		 * @param array $arguments - parameters for method
		 * @return mixed - instance of a ORM_Base derived class | array of ORM_Wrapper | unknown
		 * @throws Exception - if unable to identify/locate the method $name
		 */
		public function __call($name, $arguments)
		{
			// Data column exists
			if(array_key_exists("{$this->model_name()}_{$name}", $this->_data))
			{
				return $this->_data["{$this->model_name()}_{$name}"];
			}else if(array_key_exists($name, $this->_data)){
				return $this->_data[$name];
			}

			// Set value
			if(substr($name, 0, 4) == 'set_')
			{
				$name = substr($name, 4);
				if(isset($this->_data["{$this->model_name()}_{$name}"]))
				{
					$this->_data["{$this->model_name()}_{$name}"] = $arguments[0];
				}
				return $this;
			}

			// One to many
			if(isset($this->_to_many[$name]))
			{
				$sql = "SELECT * FROM `{$this->config()->db->prefix}{$this->_to_many[$name]}` WHERE `{$this->model_name()}_id` = '{$this->_data["{$this->model_name()}_id"]}' ";
				
				if(!empty($arguments[0]))
				{
					$sql .= " AND ".$arguments[0];
				}
				$get_records = $this->load_helper('Db')->get_rows($sql);

				$return = new ORM_Wrapper;

				if($get_records !== FALSE)
				{
					foreach($get_records as $r)
					{
						$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $this->_to_many[$name])));
						$this->load_model($model_name);
						$eval = '$object = new '.$model_name.';';
						eval($eval);
						$return->push($object->orm_set_load($r)->_orm_from_database_transform());
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
						`{$this->config()->db->prefix}{$this->_to_one_map[$name]}`
						JOIN `{$this->config()->db->prefix}{$join_table}` ON `{$this->config()->db->prefix}{$join_table}`.`{$join_table}_id` = `{$this->config()->db->prefix}{$this->_to_one_map[$name]}`.`{$join_table}_id`
					WHERE
						`{$this->config()->db->prefix}{$this->_to_one_map[$name]}`.`{$this->model_name()}_id` = '{$this->_data["{$this->model_name()}_id"]}'
				";
				
				$get_record = $this->load_helper('Db')->get_row($sql);

				$return = new ORM_Wrapper;

				if($get_record !== FALSE)
				{
					$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $join_table)));
					$this->load_model($model_name);
					$eval = '$object = new '.$model_name.';';
					eval($eval);
					
					$return->push($object->orm_load($get_record["{$join_table}_id"]));
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
						`{$this->config()->db->prefix}{$this->_to_many_map[$name]}`
						JOIN `{$this->config()->db->prefix}{$join_table}` ON `{$this->config()->db->prefix}{$join_table}`.`{$join_table}_id` = `{$this->config()->db->prefix}{$this->_to_many_map[$name]}`.`{$join_table}_id`
					WHERE
						`{$this->config()->db->prefix}{$this->_to_many_map[$name]}`.`{$this->model_name()}_id` = '{$this->_data["{$this->model_name()}_id"]}'
				";
				
				if(!empty($arguments[0]))
				{
					$sql .= " AND ".$arguments[0];
				}
				
				$get_record = $this->load_helper('Db')->get_rows($sql);

				$return = new ORM_Wrapper;

				if($get_record !== FALSE)
				{
					foreach($get_record as $record)
					{
						$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $join_table)));
						$this->load_model($model_name);
						$eval = '$object = new '.$model_name.';';
						eval($eval);
						$return->push($object->orm_load($record["{$join_table}_id"]));
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
				// Set relationship
				$this->_to_many[$table] = $table;
	
				// Done
				return $this;
			}else{
				// Set relationship
				$this->_to_many_map[$table] = $map_table;
	
				// Done
				return $this;
			}

			throw new Exception("Relationship table does not exist.");
		}



		/**
		 * @brief Set one to one relationship.
		 * 
		 * @param string $table - name of the table/model
		 * @param string $map_table - table with id to id relationship mapping
		 * @return object - ORM_Base
		 * @throws Exception - if table doesn't exist
		 */
		public function has_one($table, $map_table = NULL)
		{
			if($map_table === NULL)
			{
				// Set relationship
				$this->_to_one[$table] = $table;

				// Done
				return $this;
			}else{
				// Set relationship
				$this->_to_one_map[$table] = $map_table;
	
				// Done
				return $this;
			}

			throw new Exception("Table '{$table}' does not exist.");
		}



		/**
		 * @brief Check if a primary key exists.
		 * 
		 * @param int $key_id - primary key to check existance of
		 * @return bool - true if the key is found, false otherwise
		 * @throws Exception - if key is not numeric
		 */
		public function exists($key_id)
		{
			// Primary key
			if(strlen($key_id) == strlen(preg_replace('/\D*/', '', $key_id)))
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
		 * @brief Add an ORM event hook listener
		 * 
		 * @param string $event_name
		 * @param string $callback_method
		 * @return object - ORM_Base derived object
		 */		
		public function orm_hook($event_name, $callback_method = NULL, $arguments = NULL)
		{
			if(!empty($event_name) && $callback_method != NULL)
			{
				// Setting callback
				$this->_hook[$event_name][] = $callback_method;
				return $this;
			}else if(is_array($arguments)){
				// Calling callbacks
				// Loop through any hook methods
				if(isset($this->_hook[$event_name]) && is_array($this->_hook[$event_name]))
				{
					foreach($this->_hook[$event_name] as $hook)
					{
						// Call hook method
						call_user_func_array(array($this, $hook), $arguments);
					}
				}
				// Return current object
				return $this;
			}
			// Bad parameters
			throw new Exception('Invalid parameters in hook call.');
		}



		/**
		 * @brief Set orm data for an object.
		 * 
		 * @param array $data_array
		 * @return object - ORM_Base derived object
		 * @throws Exception - if data is not an array 
		 */
		public function orm_set($data_array)
		{
			// Check that array was passed
			if(!is_array($data_array)){ throw new Exception('Array expected.');}
			
			// Add data to model
			foreach($data_array as $k => $v)
			{
				$this->_data[$k] = $v;
			}
			
			// Return
			return $this;
		}



		/**
		 * @brief Set orm data for an object and mark as loaded.
		 * 
		 * @param array $data_array
		 * @return object - ORM_Base derived object
		 * @throws Exception - if data is not an array 
		 */
		public function orm_set_load($data_array)
		{
			// Check that array was passed
			if(!is_array($data_array)){ throw new Exception('Array expected.');}
			
			// Add data to model
			foreach($data_array as $k => $v)
			{
				$this->_data[$k] = $v;
				$this->_loaded = TRUE;
			}
			
			// Return
			return $this;
		}
		
		
		
		/**
		 * @brief Return if model is loaded.
		 *
		 * @return bool
		 */
		public function is_loaded()
		{
			return $this->_loaded;
		}
	
		
		
		/**
		 * @brief - Save an object in the database.
		 * 
		 * @return object - ORM loaded model
		 * @throws Exception - object fails validation routine for save
		 */
		public function orm_save($additional_validation_array = array())
		{
			// Orm before save hook
			$this->orm_hook('before_save', NULL, array());

			// Validate
			$validate = $this->load_helper('Validate');
			
			// Validation rules
			$rules = array_merge($this->_validate(), $additional_validation_array);
			
			if($validate->run($this->_data, $rules))
			{
				// Transform data
				$this->_orm_to_database_transform();
				if($this->_loaded === TRUE)
				{
					// Update
					if($this->_data["{$this->model_name()}_id"] != '')
					{
						// Orm before update hook
						$this->orm_hook('before_update', NULL, array());
						
						$this->load_helper('Db')->update($this->config()->db->prefix . $this->model_name(), $this->_data, " {$this->model_name()}_id = '{$this->id()}' ");
						$this->orm_load($this->id());

						// Orm after update hook
						$this->orm_hook('after_update', NULL, array());
					}
				}else{
					// Orm before insert hook
					$this->orm_hook('before_insert', NULL, array());

					// Insert
					$id = $this->load_helper('Db')->insert($this->config()->db->prefix . $this->model_name(), $this->_data);
					$this->orm_load($id);
					
					// Orm after insert hook
					$this->orm_hook('after_insert', NULL, array());
				}
				
				// Orm after save hook
				$this->orm_hook('after_save', NULL, array());
				
				// Return
				return $this;
			}
			throw new ORM_Exception('Object failed validation.', 0, $validate);
		}



		/**
		 * @brief Delete an object.
		 * 
		 * @return bool - true if object deleted from database
		 * @throws Exception - object not currently loaded via orm_load call
		 */
		public function orm_delete()
		{
			if($this->_loaded === TRUE)
			{
				$this->load_helper('Db')->query("DELETE FROM `{$this->config()->db->prefix}{$this->model_name()}` WHERE `{$this->model_name()}_id` = '{$this->id()}' ");
				return TRUE;
			}
			throw new Exception('Object not loaded.');
		}
		
		
		
		/**
		 * @brief Transparent ORM column transformation.
		 * Pass a value through a model function on set/get
		 * 
		 * @param string $column - name of column to transform
		 * @param string $to_database - name of database to transform column to
		 * @param string $from_database - name of database to transform column from
		 * @return object - ORM_Base derived object
		 */
		 public function orm_transform($column, $to_database, $from_database)
		 {
			 $this->_transform[$column] = array('to_database' => $to_database, 'from_database' => $from_database);
			 
			 return $this;
		 }

		 
		 
		/**
		 * @brief Perform ORM transformations on load.
		 * 
		 * @return object - ORM_Base derived object
		 */
		private function _orm_from_database_transform()
		{
			foreach($this->_transform as $column => $function_array)
			{
				if(isset($this->_data[$column]))
				{
					$function = $function_array['from_database'];
					$this->_data[$column] = $this->$function($this->_data[$column]);
				}
			}
			return $this;
		}



		/**
		 * @brief Perform ORM transformations on save.
		 * 
		 * @return object - ORM_Base derived object
		 */
		private function _orm_to_database_transform()
		{
			foreach($this->_transform as $column => $function_array)
			{
				if(isset($this->_data[$column]))
				{
					$function = $function_array['to_database'];
					$this->_data[$column] = $this->$function($this->_data[$column]);
				}
			}
			return $this;
		}		 
		 
		
		
		/**
		 * @brief Default validation function.
		 * Should be defined/overridden in derived classes.
		 * 
		 * @return array - rules for validation functionality
		 */
		protected function _validate()
		{
			return array();
		}
		

		/**
		 * @brief Debug data function.
		 * 
		 * @return array
		 */
		public function expose_data()
		{
			return $this->_data;
		}
	}

	/**
	 * @brief ORM Exception object.
	 * 
	 */
	class ORM_Exception extends Exception
	{
		/**
		 * @var $_validate_object
		 * @brief validation object currently loaded via helper call
		 */
		private $_validate_object;
		
		public function __construct($message = null, $code = 0, $validate_object = NULL)
		{
			if(!$message)
			{
				throw new $this('Unknown '. get_class($this));
			}
			
			$this->_validate_object = $validate_object;
			
			parent::__construct($message, $code);
		}
		
		/**
		 * @brief Gets the validation object for this exception
		 * @return object - Validate class
		 */
		public function getValidate()
		{
			return $this->_validate_object;
		}
	}
	
	
?>