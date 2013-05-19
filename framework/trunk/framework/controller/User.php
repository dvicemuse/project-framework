<?php

	class User_Controller extends Controller_Base
	{
		public function __construct()
		{
			parent::__construct();
			
			//$this->require_login(NULL, NULL);
			//$this->disable_header(NULL);
		}
		
		
		
		public function logout()
		{
			$this->Auth->logout();
			$this->redirect('');
		}
		
		
		
		public function reset_password()
		{
			if(isset($_POST['action']) && $_POST['action'] == 'reset')
			{
				$users = $this->load_model('User')->where('user_email', $_POST['user_email'])->orm_load();
				if($users->count() > 0)
				{
					$users->first()->send_reset_email();
				}
			}
		}
		
		
		
		public function update_password()
		{
			// Check update hash
			$users = $this->load_model('User')->where('user_update_hash', $this->request->raw[2])->orm_load();
			if($users->count() > 0)
			{
				// Valid
				$this->user = $users->first();
				
				if(isset($_POST['user_password']))
				{
					try
					{
						// Set post data to object and save
						$this->user->orm_set(array('user_password' => $_POST['user_password'], 'user_password_confirm' => $_POST['user_password_confirm']))->orm_save(array('user_password_confirm' => array('reqd' => '', 'match[user_password]' => '')));
						$this->user->orm_set(array('user_update_hash' => NULL))->orm_save();
						$this->add_flash('Password updated.')->redirect('');
					}catch(ORM_Exception $e){
						$this->Validate = $e->getValidate();
					}
				}
			}else{
				// Invalid
				throw new Exception('Invalid reset hash.');
			}
		}



	}

?>