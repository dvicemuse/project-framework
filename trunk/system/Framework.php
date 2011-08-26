<?php

class Framework
{
	public $config;
	public $info;				// Debug information about the current module/page/routing
	public $modules;			// Loaded modules

	/**
	 * Include the system classes
	 */
	public function __construct()
	{
		$application_path = __DIR__;

		// Include system classes
		foreach(array('Controller_Base', 'Db_Wrapper', 'Model_Base') as $file)
		{
			$f = $application_path."/{$file}.php";
			if(file_exists($f))
			{
				include_once($f);
			}else{
				die('Could not include system class.');
			}
		}

		// Include configuration classes
		$this->config = new Config_Builder;
		$handle = opendir(substr($application_path, 0, -7)."/framework/config/");
		while(false !== ($file = readdir($handle)))
		{
			if(trim($file, '.') != '' && substr($file, -4) == '.php')
			{
				include_once(substr($application_path, 0, -7)."/framework/config/".$file);
				$config_class_base_name = strtolower(substr($file, 0, -4));
				$config_class_name = substr($file, 0, -4)."_Config";

				$class_vars = get_class_vars($config_class_name);
				if(is_array($class_vars))
				{
					if(empty($this->config->$config_class_base_name))
					{
						$this->config->$config_class_base_name = new Config_Builder;
					}
					foreach($class_vars as $name => $value)
					{
						$this->config->$config_class_base_name->$name = $value;
					}
				}
			}
		}
	}



	/*
	 * This method determines which module and page to display
	 */
	public function route()
	{
		// Parse the request url
		$_SERVER['REQUEST_URI'] = str_replace($this->config->path->web_path, '', $_SERVER['REQUEST_URI']);
		$routes = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

		// Check for variables passed through the URL (/page/my_var:value/)
		$query_vars = array();
		if(is_array($routes))
		{
			foreach($routes as $part)
			{
				if(preg_match('/([a-zA-Z_-]*):([0-9a-zA-Z_-]*)/', $part, $match))
				{
					$query_vars[$match['1']] = $match['2'];
				}else{
					$routes_new[] = $part;
				}
			}
			$routes = $routes_new;
		}

		// Empty module name, default to home
		if(empty($routes[0]))
		{
			$routes[0] = 'home';
		}

		// Empty page name, default to index
		if(empty($routes[1]))
		{
			$routes[1] = 'index';
		}else if(is_numeric($routes[1])){
			$routes[2] = $routes[1];
			$routes[1] = 'index';
		}

		// Prep the module name
		$routes[0] = ucfirst($routes[0]);

		// Make sure that the requested page exists
		if(!file_exists("{$this->config->path->application_path}framework/controller/{$routes[0]}.php") || !file_exists("{$this->config->path->application_path}template/{$this->config->path->template_name}/{$routes[0]}/{$routes[1]}.php"))
		{
			header("HTTP/1.0 404 Not Found");
			$routes[0] = 'Error';
			$routes[1] = 'index';
		}

		// Set up the controller
		$frm = $this->load_controller($routes[0]);

		// Check for controller
		if($frm === FALSE)
		{
			exit;
		}

		$frm->request = new Config_Builder;


		// Set the query vars
		$frm->info['query_vars'] = $query_vars;
		$frm->request->vars = $query_vars;

		// Set the current module
		$frm->info['current_module'] = $routes[0];
		$frm->request->controller_name = $routes[0];

		// Set the current page
		$frm->info['current_page'] = $routes[1];
		$frm->request->method_name = $routes[1];

		// Pass the routing information
		$frm->info['raw_route'] = $routes;
		$frm->request->raw = $routes;

		// Render the current page
		try
		{
			$frm->render($routes[1]);
		}catch(Exception $e){
			pr($e);
		}
	}



	/*
	 * Load a model
	 * @param string model name
	 */
	function load_model($module_name)
	{
		$location = "{$this->config->path->application_path}framework/model/{$module_name}.php";
		// Make sure the module is not already loaded
		if(!isset($this->$module_name))
		{
			if(file_exists($location))
			{
				// Load the module into the current object
				include_once($location);
				$this->$module_name = new $module_name();
				// Return the object
				return $this->$module_name;
			}else{
				return FALSE;
			}
		}else{
			// Return the object
			return $this->$module_name;
		}
	}



	/*
	 * Load a helper class
	 */
	function load_helper($module_name)
	{
		$location = "{$this->config->path->application_path}system/helper/{$module_name}.php";
		// Make sure the module is not already loaded
		if(isset($this->$module_name) && is_object($this->$module_name))
		{
			return $this->$module_name;
		}
		if(file_exists($location))
		{
			// Load the module into the current object
			include_once($location);

			if($module_name == 'Db')
			{
				$this->$module_name = Db::Instance();
			}else{
				$this->$module_name = new $module_name();
			}

			return $this->$module_name;
		}else{
			return FALSE;
		}
	}


	/*
	 * Load a controller class
	 */
	function load_controller($module_name)
	{
		$location = "{$this->config->path->application_path}framework/controller/{$module_name}.php";
		// Make sure the module is not already loaded
		if(file_exists($location))
		{
			if(!isset($this->$module_name))
			{
				// Load the module into the current object
				include_once($location);
				$module_name_controller = "{$module_name}_Controller";
				$this->$module_name_controller = new $module_name_controller();
				return $this->$module_name_controller;
			}
		}else{
			return FALSE;
		}
	}


	/*
	 * Render the current page (header, page, footer)
	 */
	function render($page)
	{
		$this->load_model('User');
		// If secure module, make sure user is logged in
		if(isset($this->config->user_authorization->check) && in_array($page, $this->config->user_authorization->check) && $this->User->is_logged_in() === FALSE)
		{
			// Secure module, and user is not logged in
			include("{$this->config->path->application_path}/template/{$this->config->path->template_name}/User/login.php");
			exit;
		}
		// See if a page load function exists
		$function_name = $this->info['current_page'];
		if(method_exists($this, $function_name))
		{
			$this->$function_name();
		}
		// Load requested page
		$this->render_head();
		include_once("{$this->config->path->application_path}/template/{$this->config->path->template_name}/{$this->info['current_module']}/{$page}.php");
		$this->render_foot();
	}



	/*
	 * Render the header (called by $this->render())
	 */
	function render_head()
	{
		#if(is_array($this->config['disable_headers']) && in_array($this->info['current_page'], $this->config['disable_headers']))
		#{
		#	return TRUE;
		#}
		if(file_exists("template/{$this->config->path->template_name}{$this->info['current_module']}/head.php"))
		{
			include(("template/{$this->config->path->template_name}/{$this->info['current_module']}/head.php"));
		}else if(file_exists("template/{$this->config->path->template_name}/head.php")){
			include("template/{$this->config->path->template_name}/head.php");
		}
	}



	/*
	 * Render the footer (called by $this->render())
	 */
	function render_foot()
	{
		if(is_array($this->config['disable_headers']) && in_array($this->info['current_page'], $this->config['disable_headers']))
		{
			return TRUE;
		}
		if(file_exists("template/{$this->config['template_name']}/{$this->info['current_module']}/foot.php"))
		{
			include("template/{$this->config['template_name']}/{$this->info['current_module']}/foot.php");
		}else if(file_exists("template/{$this->config['template_name']}/foot.php")){
			include("template/{$this->config['template_name']}/foot.php");
		}
	}



	/*
	 * Render a partial element (page placed in /template/my_template_name/Partial/)
	 */
	function render_partial($partial_name)
	{
		// Lock the file path to the partial directory
		$partial_name = str_replace('/', '', $partial_name);
		$location = "template/{$this->config['template_name']}/{$this->info['current_module']}/Partial/{$partial_name}.php";
		if(file_exists($location))
		{
			include($location);
		}
	}



	/**
	 * Check if a value really is an id.
	 * Handles string and int input.
	 * Empty strings return FALSE.
	 * @param int $int_id
	 * @return bool
	 */
	public function is_id($int_id)
	{
		return (strlen($int_id) > 0 && strlen($int_id) == strlen(intval($int_id)));
	}


}






// Global debug function
function pr($data)
{
	echo "<pre style=\"color:#FFF;background:#333;\">";
	if(is_bool($data) || $data === NULL)
	{
		var_dump($data);
	}else{
		print_r($data);
	}

	echo "</pre>";
}

class Config_Builder{}

?>