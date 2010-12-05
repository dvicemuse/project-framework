<?php

class User extends Model_Base
{
	/**
	 * Construct function
	 */
	public function __construct()
	{
		$this->load_helper('Db');
		$this->load_helper('Validate')->print_titles(FALSE)->print_errors(FALSE);
	}



	/**
	 * Log in
	 * @param array login data
	 * @return bool
	 */
	public function log_user_in($data)
	{
		// Validation rules
		$rules['user_email']		= array('reqd' => 'Please enter your email address.');
		$rules['user_password']		= array('reqd' => 'Please enter your password.');
		// Run validation
		if($this->Validate->run($data, $rules))
		{
			// Check username and passwords
			$res = $this->where('user_email', $data['user_email'])->where('user_password', sha1($data['user_password']))->get();
			if($res->count() == 1)
			{
				// Update last login
				$r = $res->result();
				$this->Db->query("UPDATE user SET user_last_login = NOW() WHERE user_id = '{$r['user_id']}' ");
				// Save result to session
				$_SESSION['Login']  = $res->result();
				$_SESSION['Login']['user_last_login'] = date('Y-m-d H:i:s');
				return TRUE;
			}else{
				$this->add_flash('Incorrect username or password.');
				return FALSE;
			}
		}else{
			$this->add_flash('Incorrect username or password.');
			return FALSE;
		}
		return FALSE;
	}



	/**
	 * Send a password reset email to a user's email address
	 * @param int $user_id
	 * @return bool
	 */
	public function send_password_reset_email($user_id)
	{
		// Check user ID
		if($this->exists($user_id) !== FALSE)
		{
			// Load user information
			$user = $this->get($user_id)->result();

			// Create a hash
			$hash = md5(time().'----'.$user['user_email']);

			// Set reset hash for user
			if($this->Db->update('user', array('user_update_hash' => $hash), " user_id = '{$user_id}' "))
			{
				// Send reset email
				$message = "
					To reset your password, please follow the instructions on the web page below.
					<br /><a href=\"{$this->config['full_web_path']}user/update_password/{$hash}/\">{$this->config['full_web_path']}user/update_password/{$hash}/</a>
				";

				// Send email
				return $this->load_helper('Email')->mail($user['user_email'], 'Password Reset Instructions', $message);
			}
		}
		return FALSE;
	}



	/**
	 * Update a user's password
	 * @param int $user_id
	 * @param string $new_password
	 * @return bool
	 */
	public function update_password($user_id, $new_password)
	{
		// Check user ID
		if($this->exists($user_id))
		{
			// Update the password
			return $this->Db->update('user', array('user_update_hash' => '', 'user_password' => sha1(trim($new_password))), " user_id = '{$user_id}' ");
		}
		return FALSE;
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