<?php

	abstract class Model_Base extends Framework
	{
		private $_where;


		/**
		 * Get the current model name
		 * @return string
		 */
		private function model_name()
		{
			return strtolower(get_class($this));
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
			}
			return $this;
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
			}
			return $this;
		}



		/**
		 *
		 * @param int $key_id
		 * @return object
		 */
		public function get($key_id = '')
		{
			$this->load_helper('Db');

			// Parameter is an integer (primary key)
			if(ctype_digit($key_id))
			{
				// Reset where, and add primary key limit
				$this->_where = array(0 => " AND {$this->model_name()}_id = '{$key_id}' ");
			}

			$clause = '';
			if(is_array($this->_where))
			{
				foreach($this->_where as $where)
				{
					$clause .= $where;
				}
			}

			// Reset where
			$this->_where = array();

			// Generate query
			$sql =  "SELECT * FROM {$this->model_name()} WHERE 1=1 {$clause}";

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
				}
			}
			return FALSE;
		}


		
	}

?>