<?php

	class |NAME|_Controller extends Controller_Base
	{
		public function __construct()
		{
			parent::__construct();
			
			//$this->require_login(NULL, NULL);
		}



		/**
		 * Index
		 */
		public function index()
		{
			$this->records = $this->load_helper('Scaffold')->set_table_name('|NAME_LOWERCASE|')->search()->results();
		}
		
		
		
		/**
		 * Add/edit form
		 */
		public function manage()
		{
			// Load validate helper
			$this->load_helper('Validate');
			
			// |NAME| object
			$|NAME_LOWERCASE| = $this->load_model('|NAME|');

			// Load data for update
			if(isset($this->request->raw[2]))
			{
				// Check ID/permissions
				try
				{
					$|NAME_LOWERCASE|->check_permission_load($this->request->raw[2]);
				}catch(Exception $e){
					die('Could not load object.');
				}

				// Set data to validate
				$this->load_helper('Validate')->set_data($|NAME_LOWERCASE|->expose_data());
			}
			
			// Process form
			if(isset($_POST['action']) && $_POST['action'] == 'save')
			{
				try
				{
					$|NAME_LOWERCASE|->orm_set($_POST)->orm_save();
					$this->add_flash('Saved.')->redirect('|NAME_LOWERCASE|');
				}catch(ORM_Exception $e){
					$this->Validate = $e->getValidate();
				}
			}
		}



		/**
		 * Delete
		 */
		public function delete()
		{
			// Load validate helper
			$this->load_helper('Validate');
			
			// |NAME| object
			$music = $this->load_model('|NAME|');

			// Load object
			if(isset($this->request->raw[2]))
			{
				// Check ID/permissions
				try
				{
					$|NAME_LOWERCASE|->check_permission_load($this->request->raw[2])->orm_delete();
					$this->add_flash('Deleted.')->redirect('|NAME_LOWERCASE|');
				}catch(Exception $e){
					die('Could not load object.');
				}
			}
			exit;
		}
	}

?>