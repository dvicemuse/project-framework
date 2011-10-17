<?php

	class Route_Config
	{
		public $base = array(
			// Home page
			'#^//$#D' => array('controller' => 'home', 'method' => 'index'),

			// Method request -> /dashboard/settings/
			'#/(.*?)/(.*?)/#' => array('controller' => 1, 'method' => 2),

			// Index request -> /dashboard/
			'#/(.*?)/#' => array('controller' => 1, 'method' => 'index'),
		);
	}

?>