<?php

	class |NAME| extends Model_Base
	{
		public function __construct()
		{
			parent::__construct();
		}



		/**
		 * Model validation
		 * @return array
		 */
		protected function _validate()
		{
			return array(
				|VALIDATION_ARRAY|
			);
		}
		
		
		
		/**
		 * Check permission and load object
		 * @param int $|NAME_LOWERCASE|_id
		 * @param int $user_id
		 * @return |NAME|
		 */
		public function check_permission_load($|NAME_LOWERCASE|_id, $user_id = NULL)
		{
			if($this->exists($|NAME_LOWERCASE|_id) === FALSE){ throw new Exception('Invalid |NAME_LOWERCASE| ID.'); }
			if($user_id !== NULL && $this->load_model('User')->exists($user_id) === FALSE){ throw new Exception('Invalid user ID.'); }
			
			return $this->orm_load($|NAME_LOWERCASE|_id);
		}
	}

?>