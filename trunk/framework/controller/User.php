<?php

	class User_Controller extends Controller_Base
	{
		public function reset_password()
		{
			// Load modules
			$this->load_model('User');
			$this->load_helper('Validate')->print_titles(FALSE)->print_errors(FALSE);

			// Form submitted
			if(!empty($_POST))
			{
				// Check email address
				$get = $this->User->where('user_email', trim($_POST['email']))->get()->result();
				if($get !== FALSE)
				{
					// Send user account reset email
					if($this->User->send_password_reset_email($get['user_id']))
					{
						$this->add_flash('Please check your email for password reset instructions.');
						header("Location: {$this->page_link($this->config->path->log_in_controller)}");
						exit;
					}else{
						$this->add_flash('There was a problem sending your password reset instructions.');
						$this->reload_page();
					}
				}else{
					$this->add_flash('There is no account with that email address.');
					$this->reload_page();
				}
			}
		}



		public function update_password()
		{
			// Load modules
			$this->load_model('User');
			$this->load_helper('Validate')->print_titles(FALSE)->print_errors(FALSE);

			// Validate hash
			$user = $this->User->where('user_update_hash', $this->request->raw[2])->get()->result();
			if($user === FALSE)
			{
				// Hash does not exist
				header("Location: {$this->page_link($this->config->path->log_in_controller)}");
				exit;
			}

			// Form submitted
			if(!empty($_POST))
			{
				// Validation rules
				$rules['new_password']			= array('reqd' => 'Please enter a new password.', 'min[8]' => 'Password must be at least 8 characters in length.');
				$rules['new_password_confirm']	= array('reqd' => 'Please confirm your new password.', 'match[new_password]' => 'Passwords do not match.');

				// Run validation
				if($this->Validate->run($_POST, $rules))
				{
					// Update password
					if($this->User->update_password($user['user_id'], $_POST['new_password']))
					{
						// Redirect to the default log in url
						header("Location: {$this->page_link($this->config->path->log_in_controller)}");
						exit;
					}
				}else{
					// Pass error strings to display
					if(is_array($this->Validate->error))
					{
						// Get first error for each field
						foreach($this->Validate->error as $e)
						{
							$this->add_flash($e[0]);
						}
					}
				}
			}

			// Make user information available to template
			$this->data = $user;
		}



		public function logout()
		{
			$this->Auth->logout();
			header("Location: {$this->page_link($this->config->path->log_in_controller)}");
		}
	}

?>