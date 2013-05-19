<?php

	class Auth extends User
	{
		public function __construct()
		{
			parent::__construct();
			
			// Remap to user table
			$this->model_name = 'user';

			// Load user if logged in
			if(isset($_SESSION['Login']['user_id']))
			{
				$this->orm_load($_SESSION['Login']['user_id']);
			}else{
				$this->_data['user_id'] = NULL;
			}
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
		
		
		
		/**
		 * Check login credentials
		 * @param string $user_email
		 * @param string $user_password
		 * @return bool|User
		 */
		static function check_login($user_email, $user_password)
		{
			$fw = new Framework;
			$check = $fw->load_model('User', TRUE)->where('user_email', trim($user_email))->where('user_password', sha1(trim($user_password)))->orm_load();
			if($check->count() > 0)
			{
				$_SESSION['Login'] = $check->first()->expose_data();
				return $check->first();
			}
			return FALSE;
		}
	}

?>