<?php

	class Auth extends User
	{
		public function __construct()
		{
			parent::__construct();
			$this->model_name = 'user';

			$this->orm_load($_SESSION['Login']['user_id']);
		}



		/**
		 * Check if a user is logged in
		 * @return bool
		 */
		public function is_logged_in()
		{
			return !empty($_SESSION['Login']);
		}
	
	
		
		/**
		 * Clear login information from session
		 * @return bool
		 */
		public function logout()
		{
			unset($_SESSION['Login']);
			return TRUE;
		}
	}

?>