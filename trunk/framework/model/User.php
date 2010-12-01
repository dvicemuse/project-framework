<?php

class User extends Framework
{
	function __construct()
	{
		$this->load_helper('Db');
		$this->load_helper('Validate');

		$this->disable_headers = array(
			'logout',
		);
		
		$this->Validate->print_field_title = FALSE;
		$this->Validate->print_errors = FALSE;
	}

	function get_user($user_id)
	{
		$user_id = intval($user_id);
		$info = $this->Db->get_row("SELECT * FROM user WHERE user_id = '{$user_id}' ");
		if($info === FALSE)
		{
			return FALSE;
		}
		$info['user_password'] = substr($info['user_password'], 0, 8);
		$info['user_password_confirm'] = substr($info['user_password'], 0, 8);
		
		return $info;
	}

	function user_exists($user_id)
	{
		if($this->get_user($user_id) !== FALSE)
		{
			return TRUE;
		}else{
			return FALSE;
		}
	}

	function get_user_by_email($user_email)
	{
		$user_email = $this->Db->escape($user_email);
		return $this->Db->get_row("SELECT * FROM user WHERE user_email = '{$user_email}'");
	}


	function get_users() {
		return $this->Db->get_rows("SELECT * FROM user ORDER BY user_email ASC ");
	}



	/**//*
	Login Methods
	/**/
	function log_user_in($data)
	{
		#pr($this);
		$rules['user_email']		= array('reqd' => 'Please enter your email address.');
		$rules['user_password']		= array('reqd' => 'Please enter your password.');
		$this->Validate->add_rules($rules);
		if($this->Validate->run($data, $rules))
		{
			$data['user_email'] = $this->Db->escape($data['user_email']);
			$data['user_password'] = sha1($this->Db->escape($data['user_password']));
			$res = $this->Db->get_row("
				SELECT * FROM
					user
				WHERE
					user_email = '{$data['user_email']}'
					AND user_password = '{$data['user_password']}'
			");
			if($res !== FALSE)
			{
				$update_last_login = $this->Db->query("UPDATE user SET user_last_login = NOW() WHERE user_id = '{$res['user_id']}' ");
				$_SESSION['Login']  = $res;
				$_SESSION['Login']['user_last_login'] = date('Y-m-d H:i:s');
				$_SESSION['Login']['status'] = true;

				return true;
			}else{
				$this->add_flash('Incorrect username or password.');
				return false;
			}
		}else{
			return false;
		}
		return FALSE;
	}


	function check_login()
	{
		if($_SESSION['Login']['status'] == true)
		{
			return true;
		}else{
			return false;
		}
	}








	/* PASSWORD RESET METHODS */
	function reset_password($data)
	{
		if($this->get_user_by_email($data['email']) !== FALSE)
		{
			// Create a hash (basic)
			$hash = time().'----'.$data['email'];
			$hash = md5(base64_encode($hash));
			// Update the record in the database (to keep people from making random valid hashes)
			$this->Db->update('user', array('user_update_hash' => $hash), " user_email = '{$data['email']}' ");
			// Send reset email
			$message = "
				To reset your password, please follow the instructions on the web page below.
				<br /><a href=\"{$this->config['full_web_path']}user/update_password/{$hash}/\">{$this->config['full_web_path']}user/update_password/{$hash}/</a>
			";
			
			// Load email module
			$this->load_helper('Email');
			
			$result = $this->Email->mail($data['email'], 'Password Reset Instructions', $message);
			$this->add_flash("Please check your email for password reset information.");
			return TRUE;
		}else{
			$this->add_flash("The specified email address was not found.");
			return FALSE;
		}
	}



	function validate_update_hash($hash)
	{
		$hash = $this->Db->escape($hash);
		$check = $this->Db->get_row("SELECT * FROM user WHERE user_update_hash = '{$hash}' ");
		if($check !== FALSE)
		{
			return $check['user_email'];
		}
		return FALSE;
	}




	function update_password($data)
	{
		$rules['new_password']			= array('reqd' => 'Please enter a new password.');
		$rules['new_password_confirm']	= array('reqd' => 'Please confirm your new password.');
		$this->Validate->add_rules($rules);
		if($this->Validate->run($data))
		{
			if(strlen($data['new_password']) < 8)
			{
				unset($this->Validate->data);
				$this->add_flash('Your password must be at least 8 characters long.');
				return FALSE;
			}
			if($data['new_password_confirm'] != $data['new_password'])
			{
				$this->add_flash('Your confirmation password did not match.');
				$this->Validate->data['new_password_confirm'] = '';
				$this->Validate->error['new_password_confirm'][] = 'Error';
				return FALSE;
			}else{
				// Update the password
				$data['hash'] = ereg_replace('[^0-9a-zA-Z]', '', $data['hash']);
				if($this->Db->update('user', array('user_update_hash' => '', 'user_password' => sha1(trim($data['new_password']))), " user_update_hash = '{$data['hash']}' ") !== FALSE)
				{
					$this->add_flash("Your password has been updated. Please log in.");
					return TRUE;
				}else{
					exit;
				}
			}
		}
	}




	function logout()
	{
		$_SESSION = array();
	}

}

?>