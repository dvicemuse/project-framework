<?php

	class Dashboard_Controller extends Controller_Base
	{
		public function __construct()
		{
			// Call parent constructor
			parent::__construct();

			$this->require_login();
			//$this->disable_header(NULL);
		}



		public function index()
		{
			
		}
	}

?>