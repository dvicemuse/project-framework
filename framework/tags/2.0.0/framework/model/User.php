<?php

	class User extends Model_Base
	{
		public function __construct()
		{
			parent::__construct();

			//  Automatically hash the password
			$this->orm_transform('user_password', '_user_password_to', '_user_password_from');
		}


		/**
		 * Model validation
		 * @return array
		 */
		protected function _validate()
		{
			return array(
				'user_password' => array(
					'reqd' => 'Field is required.',
					'min[8]' => 'Minimum password length is 8 characters.',
				),
			);
		}


		/**
		 * Hash password to database
		 * @param string
		 * @return string
		 */
		protected function _user_password_to($value)
		{
			// Check if password has changed
			if($value != $this->load_model('User', TRUE)->orm_load($this->id())->password())
			{
				$value = sha1($value);
			}
			return $value;
		}
		
		
		
		/**
		 * Return password from database
		 * @param string $value
		 * @return string
		 */
		protected function _user_password_from($value)
		{
			return $value;
		}
		
		
		
		/**
		 * Send a password update email
		 * @return bool
		 */
		public function send_reset_email()
		{
			// Set reset hash for user
			$this->orm_set(array('user_update_hash' => md5(time().$this->email())))->orm_save();

			// Generate message
			$message = "
			To reset your password, please follow the instructions on the web page below.
			<br /><a href=\"{$this->config()->path->full_web_path}user/update_password/{$this->update_hash()}/\">{$this->config()->path->full_web_path}user/update_password/{$this->update_hash()}/</a>
			";

			// Send email
			return $this->load_helper('Email')->mail($this->email(), 'Password Reset Instructions', $message);
		}



	}

?>