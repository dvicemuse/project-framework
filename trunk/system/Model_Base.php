<?php
/**
 * @file Model_Base.php
 * @package    ProjectFramework
 *
 * @license    see LICENSE.txt
 */

/**
 * @class Model_Base
 * @brief Base Model class. Abstract base class for CMS model classes
 *
 * @package  ProjectFramework
 * @since    1.0.0
 */
	abstract class Model_Base extends ORM_Base
	{
		/**
		 * @var $_where
		 * @brief array of WHERE clause elements for resultset
		 */
		protected $_where;
		/**
		 * @var $_order
		 * @brief array of ORDER BY clause elements to sort resultset
		 */
		protected $_order;
		/**
		 * @var $_limit
		 * @brief string of LIMIT clause for resultset
		 */
		protected $_limit;


		/**
		 * Construct function
		 */
		public function __construct()
		{
			parent::__construct();
		}



		/**
		 * @brief Limit the number of items returned in the resultset.
		 * 
		 * @param int $rows - number of rows to return
		 * @param int $offset - page offset, defaults to 0 (first page)
		 * @return object - self/this
		 * @throws Exception - if rows not greater than 0 or offset isn't an integer 
		 */
		public function limit($rows, $offset = FALSE)
		{
			if($this->is_id($rows) &&  $rows > 0)
			{
				if($offset === FALSE || $this->is_id($offset))
				{
					$offset = $offset >= 0 ? $offset : 0;
					$this->_limit = " LIMIT ".trim(" {$offset},{$rows} ", ' ,');
					return $this;
				}
				throw new Exception("Page offset must be an integer.");
			}
			throw new Exception("Row limit must be an integer greater than 0.");
		}



		/**
		 * @brief Set the result order.
		 * 
		 * @param string $column - column name to sort results by
		 * @param string $order - ordinality of sort, DESC for descending ASC for ascending
		 * @return object - self/this
		 * @throws Exception - if column does not exist or ordinality is not DESC or SC 
		 */
		public function order($column, $value)
		{
			$value = strtoupper(trim($value));
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
		 * @brief AND element to the where clause.
		 * 
		 * @param string $column - column name to add to WHERE clause array
		 * @param string $value - value column must be equal to
		 * @return object - self/this
		 * @throws Exception - if column does not exist in the database table for the model 
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
		 * @brief AND element to the where clause.
		 * 
		 * @param string $where
		 * @return object - self/this
		 */
		public function custom_where($where)
		{
			$this->_where[] = " AND ({$where}) ";
			return $this;
		}



		/**
		 * @brief AND element to the where clause using LIKE.
		 * 
		 * @param string $column - column name to add to WHERE clause array
		 * @param string $value - pattern column must be like, add widlcards % _ to value to expand match
		 * @return object - self/this
		 * @throws Exception - if column does not exist in the database table for the model
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
		 * @brief Get result set using where, order and limit clauses.
		 * Resets where, order and limit clauses.
		 * 
		 * @param int $key_id - specific primary key id to retrieve, default retrieves all 
		 * @return object - DB_Wrapper of result set returned or empty
		 * @see DB_Wrapper
		 */
		public function get($key_id = FALSE)
		{
			$this->load_helper('Db');

			// Parameter is an integer (primary key)
			if($key_id != FALSE)
			{
				if($this->is_id($key_id))
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
		 * @brief Check if a primary key exists.
		 * 
		 * @param int $key_id - specific primary key id to check 
		 * @return bool - true if primary key is found, false otherwise
		 * @throws Exception - if key is not numeric
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

		
		
		/**
		 * @brief Create an array of column values ([ID] => [COLUMN VALUE]).
		 * Uses the primary key for the table as ID
		 * 
		 * @param string $field_display_name - COLUMN VALUE to display in dropdown, should match table column name  
		 * @param bool $blank_first - display a blank line as the first value, default is true
		 * @return array - result set usable in Validate->print_select statements
		 * @throws Exception - blank param is not a boolean or display field is not a column in the table
		 */
		public function dropdown($field_display_name, $blank_first = TRUE)
		{
			// Initialize return
			$return = array();
			
			// Bool check
			if(!is_bool($blank_first))
			{
				throw new Exception('Boolean expected.');
			}
			
			// Blank first element return
			if($blank_first === TRUE)
			{
				$return[''] = '&nbsp;';
			}
			
			// Get results
			$get = $this->get();
			if($get->count() > 0)
			{
				// Primary key
				$key = $this->load_helper('Db')->get_primary_key($this->model_name());
				
				// Add results to retun
				foreach($get->results() as $r)
				{
					// Does the display field exist
					if(!isset($r[$field_display_name]))
					{
						throw new Exception('Field display name not found in result.');
					}
					
					$return[$r[$key]] = $r[$field_display_name];
				}
			}
			
			return $return;
		}
		

	}

?>