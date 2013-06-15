<?php

	class Dashboard_Controller extends Controller_Base
	{
		public function __construct()
		{
			// Call parent constructor
			parent::__construct();

			// Make all pages require login
			$this->require_login();
		}

		public function index(){ }
	}

?>