<?php

	class Db_Wrapper
	{
		private $_data = array();

		/**
		 * Set class data
		 * @param array $array
		 * @return object $this
		 */
		public function set($array)
		{
			if(is_array($array))
			{
				$this->_data = $array;
			}
			return $this;
		}

		public function result()
		{
			if(is_array($this->_data[0]))
			{
				return $this->_data[0];
			}else{
				return FALSE;
			}
		}


		public function results()
		{
			if(is_array($this->_data) && count($this->_data) > 0)
			{
				return $this->_data;
			}else{
				return FALSE;
			}
		}


		public function count()
		{
			if(is_array($this->_data) && count($this->_data) > 0)
			{
				return count($this->_data);
			}else{
				return 0;
			}
		}



	}


?>