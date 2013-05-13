<?php

	/**
	 * Sample override class. Calls to:
	 * $this->load_helper('Sample');
	 * will load this class and extend the base
	 * class in the system folder
	 */
	class Sample_Override extends Sample
	{
		public function __construct()
		{
			parent::__construct();
		}

		// Override/extension methods go here
	}

?>