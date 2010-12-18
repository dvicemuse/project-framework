<?php

class Framework {

	// Framework variables
	public $config = array(
		'script_path'		=> '/var/www/',
		'web_path'			=> '',
		'file_path'			=> '/var/www/files/',
		'file_web_path'		=> 'http://38.108.125.251/files/',
		'full_web_path'		=> 'http://38.108.125.251/',
		'template_name'		=> 'flicker',
		'encryption_key'	=> '123654$#*(7j(gdj7@^Oej**9@9ska90be8$7os&u13o*i',
		'log_error'			=> TRUE,
		'database_host'		=> 'localhost',
		'database_user'		=> 'framework',
		'database_pass'		=> 'password',
		'database_name'		=> 'framework',

		'log_in_module'		=> 'dashboard', // Front end module to trigger log in action

		'no_reply_address'	=> 'no-reply@liquinoxhosting.com',
		'no_reply_name'		=> 'Mail Robot',
		'support_email'		=> 'dan@i-tul.com',

		'secure_module'		=> FALSE, // Require the user to log in?
		'disable_headers'	=> FALSE, // Hide the header and footer?
	);
	public $info;				// Debug information about the current module/page/routing
	public $modules;			// Loaded modules



	/**
	 * Include the system classes
	 */
	public function  __construct()
	{
		$class_files = array(
			'Controller_Base',
			'Db_Wrapper',
			'Model_Base',
		);
		foreach($class_files as $file)
		{
			$f = "{$this->config['script_path']}framework/system/{$file}.php";
			if(file_exists($f))
			{
				include_once($f);
			}else{
				die('Could not include system class.');
			}
		}
	}



	/*
	 * This method determines which module and page to display
	 */
	public function route()
	{
		// Parse the request url
		$_SERVER['REQUEST_URI'] = str_replace($this->config['web_path'], '', $_SERVER['REQUEST_URI']);
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
		if(!file_exists("{$this->config['script_path']}/framework/controller/{$routes[0]}.php") || !file_exists("template/{$this->config['template_name']}/{$routes[0]}/{$routes[1]}.php"))
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

		// Set the query vars
		$frm->info['query_vars'] = $query_vars;

		// Set the current module
		$frm->info['current_module'] = $routes[0];

		// Set the current page
		$frm->info['current_page'] = $routes[1];

		// Pass the routing information
		$frm->info['raw_route'] = $routes;

		// Render the current page
		$frm->render($routes[1]);
	}



	/*
	 * Load a model
	 * @param string model name
	 */
	function load_model($module_name)
	{
		$location = "{$this->config['script_path']}/framework/model/{$module_name}.php";
		// Make sure the module is not already loaded
		if(!is_object($this->$module_name))
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
		$location = "{$this->config['script_path']}/framework/helper/{$module_name}.php";
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
		$location = "{$this->config['script_path']}/framework/controller/{$module_name}.php";
		// Make sure the module is not already loaded
		if(file_exists($location))
		{
			if(!is_object($this->$module_name))
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
		if($this->config['secure_module'] == TRUE && $this->User->is_logged_in() === FALSE)
		{
			// Secure module, and user is not logged in
			include("{$this->config['script_path']}/template/{$this->config['template_name']}/User/login.php");
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
		include_once("{$this->config['script_path']}/template/{$this->config['template_name']}/{$this->info['current_module']}/{$page}.php");
		$this->render_foot();
	}



	/*
	 * Render the header (called by $this->render())
	 */
	function render_head()
	{
		if(is_array($this->config['disable_headers']) && in_array($this->info['current_page'], $this->config['disable_headers']))
		{
			return TRUE;
		}
		if(file_exists("template/{$this->config['template_name']}/{$this->info['current_module']}/head.php"))
		{
			include(("template/{$this->config['template_name']}/{$this->info['current_module']}/head.php"));
		}else if(file_exists("template/{$this->config['template_name']}/head.php")){
			include("template/{$this->config['template_name']}/head.php");
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
	if($data === TRUE || $data === FALSE)
	{
		var_dump($data);
	}else{
		print_r($data);
	}

	echo "</pre>";
}

?>