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
		foreach(array('Controller_Base', 'Db_Wrapper', 'ORM_Base', 'Model_Base') as $file)
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
		if($this->config->path->web_path == '/')
		{
			$this->config->path->web_path = '';
		}
		$request = str_replace($this->config->path->web_path, '', $_SERVER['REQUEST_URI']);

		// Remove leading and trailing slashes, then add back
		$request = "/".trim($request, '/')."/";

		// Loop through defineed routes
		if(is_array($this->config->route->base))
		{
			foreach($this->config->route->base as $pattern => $route)
			{
				// Pattern matched
				if(preg_match($pattern, $request, $m))
				{
					// Quick route definition check
					if(!isset($route['controller']) || !isset($route['method']))
					{
						throw new Exception('Route method not set.');
					}

					if($this->is_id($route['controller']))
					{
						$route['controller'] = ucfirst($m[$route['controller']]);
					}

					if($this->is_id($route['method']))
					{
						$route['method'] = $m[$route['method']];
					}

					// Set up the controller
					$frm = $this->load_controller($route['controller']);
					if($frm !== FALSE)
					{
						$frm->request = new Config_Builder;
						$frm->request->vars = explode('/', trim($request, '/'));
						$frm->request->controller_name = $route['controller'];
						$frm->request->method_name = $route['method'];
						$frm->request->raw = explode('/', trim($request, '/'));

						// Render the current page
						try
						{
							$frm->render($route['method']);
						}catch(Exception $e){
							pr($e);
						}
					}

					exit;
				}
			}
		}

		throw new Exception("Routes not defined.");
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
		$this->load_model('Auth');
		// If secure module, make sure user is logged in
		if(isset($this->config->user_authorization->check) && in_array($page, $this->config->user_authorization->check) && $this->User->is_logged_in() === FALSE)
		{
			// Secure module, and user is not logged in
			include("{$this->config->path->application_path}/framework/template/User/login.php");
			exit;
		}
		// See if a page load function exists
		if(method_exists($this, $this->request->method_name))
		{
			$function_name = $this->request->method_name;
			$this->$function_name();
		}

		// Check that template exists
		if(file_exists("{$this->config->path->application_path}/framework/template/{$this->request->controller_name}/{$page}.php"))
		{
			// Load requested page
			$this->render_head();
			include_once("{$this->config->path->application_path}/framework/template/{$this->request->controller_name}/{$page}.php");
			$this->render_foot();
		}else{
			echo "404 - Template not found.";
		}
	}



	/*
	 * Render the header (called by $this->render())
	 */
	function render_head()
	{
		if(file_exists("{$this->config->path->application_path}/framework/template/{$this->request->controller_name}/head.php"))
		{
			include("{$this->config->path->application_path}/framework/template/{$this->request->controller_name}/head.php");
		}else if(file_exists("{$this->config->path->application_path}/framework/template/head.php")){
			include("{$this->config->path->application_path}/framework/template/head.php");
		}
	}



	/*
	 * Render the footer (called by $this->render())
	 */
	function render_foot()
	{
		if(file_exists("{$this->config->path->application_path}/framework/template/{$this->request->controller_name}/foot.php"))
		{
			include("{$this->config->path->application_path}/framework/template/{$this->request->controller_name}/foot.php");
		}else if(file_exists("{$this->config->path->application_path}/framework/template/foot.php")){
			include("{$this->config->path->application_path}/framework/template/foot.php");
		}
	}



	/*
	 * Render a partial element (page placed in /template/my_template_name/Partial/)
	 */
	function render_partial($partial_name)
	{
		// Lock the file path to the partial directory
		$partial_name = str_replace('/', '', $partial_name);
		$location = "{$this->config->path->application_path}/framework/template/{$this->request->controller_name}/Partial/{$partial_name}.php";
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
