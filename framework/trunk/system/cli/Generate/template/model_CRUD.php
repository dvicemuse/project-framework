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
		 * @param User $user
		 * @return |NAME|
		 */
		public function check_permission_load($|NAME_LOWERCASE|_id, User $user)
		{
			if($this->exists($|NAME_LOWERCASE|_id) === FALSE){ throw new Exception('Invalid |NAME_LOWERCASE| ID.'); }
			
			return $this->orm_load($|NAME_LOWERCASE|_id);
		}
	}

?>