<?php

	class User_Controller extends Framework
	{
		function reset_password()
		{
			// User submitted login
			if(!empty($_POST['reset_submit']))
			{
				// Pass in login info to login class
				if($this->User->reset_password($_POST))
				{
					// Logged in, so send the user to the page they requested
					header("Location: {$this->config['web_path']}/dashboard/");
					exit;
				}
			}
		}



		function update_password()
		{
			$this->hash = $this->User->validate_update_hash($this->info['raw_route'][2]);
			if($this->hash === FALSE)
			{
				$this->add_flash("Invalid password update URL.");
				header("Location: {$this->config['web_path']}/dashboard/");
				exit;
			}
		
			// User submitted login
			if(!empty($_POST['update_submit']))
			{
				// Pass in login info to login class
				if($this->User->update_password($_POST))
				{
					// Logged in, so send the user to the page they requested
					header("Location: {$this->config['web_path']}/dashboard/");
					exit;
				}
			}
		}



		function logout()
		{
			$this->User->logout();
			header("Location: {$this->config['web_path']}/");
		}
	}

?>