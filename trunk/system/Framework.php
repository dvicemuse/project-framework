<?php
/**
 * @file Framework.php
 * @package    ProjectFramework
 *
 * @license    see LICENSE.txt
 */

/**
 * @class Framework
 * @brief Framework application class.
 * Base application class for the PHP Project CMS system
 *
 * @package  ProjectFramework
 * @since    1.0.0
 */
class Framework
{
	/**
	 * @brief Constructor for the framework. Includes the system classes needed by framework
	 */
	public function __construct()
	{
		// Include system classes
		foreach(array('Controller_Base', 'Db_Wrapper', 'ORM_Base', 'Model_Base', 'ORM_Wrapper') as $file)
		{
			$f = __DIR__ . "/{$file}.php";
			if(file_exists($f))
			{
				include_once($f);
			}else{
				die('Could not include system class.');
			}
		}
	}




	/**
	 * @brief Route the application. Routing is the process of examining the request environment to determine which
	 * module and page should receive the request.
	 */
	public function route()
	{
		// Is this thing set up?
		$failed = FALSE;
		try
		{
			$this->load_helper('Db');
		}catch(Exception $e){
			$failed = TRUE;
		}
		if($failed || $this->config()->path->application_path == '')
		{
			// Paths not set up so just include the template
			include_once('framework/template/Error/not_configured.php');
			exit;
		}

		// Route this request
		$route = $this->load_helper('Framework_Route');

		try
		{
			// Set up the controller
			$frm = $this->load_controller($route->controller());
			if($frm !== FALSE)
			{
				// Request vars
				$frm->request = new Config_Builder;
				$frm->request->vars = $route->vars();
				$frm->request->controller_name = $route->controller();
				$frm->request->method_name = $route->method();
				$frm->request->raw = $route->raw();
	
				// Render the current page
				try
				{
					$frm->render($route->method());
				}catch(Exception $e){

					if($this->config()->mode->current == 'dev')
					{
						// Raw exception
						pr($e);
					}else{
						// Production error message
						header("HTTP/1.0 500 Internal Server Error");
						$frm = $this->load_controller('Error');
						$frm->request->controller_name = 'Error';
						$frm->request->method_name = 'error_500';
						$frm->render('error_500');
					}
				}
				exit;
			}
	
			throw new Exception("Routes not defined.");
		}catch(Exception $e){

			// @todo make this more elegant
			header("HTTP/1.0 404 Not Found");
			$frm = $this->load_controller('Error');
			$frm->request->controller_name = 'Error';

			$frm->request->method_name = 'error_404';
			$frm->render('error_404');
			die;
		}
	}




	/**
	 * @brief Load a model. Load an instance of the model requested into the name space of the current object.
	 * Subsequent requests can use $this->ModelName->method() to call methods of the object 
	 *  
	 * @param string $module_name - The name of the model to load
	 * @param bool $force_new - Force creation of a new object
	 * @return mixed - instance of the model requested or false if unable to locate the model
	 * 
	 * @example To load the User model use the following $this->load_model('User');
	 */
	function load_model($module_name, $force_new = FALSE)
	{
		// Check that the class exists
		if(class_exists($module_name) === FALSE)
		{
			$location = "{$this->config()->path->application_path}framework/model/{$module_name}.php";
			if(file_exists($location))
			{
				// Include the class file
				include_once($location);
			}
		}
		
		// Check that the class exists
		if(class_exists($module_name) !== FALSE)
		{
			if(!isset($this->$module_name) || $force_new === TRUE)
			{
				$this->$module_name = new $module_name();
				// Return the object
				return $this->$module_name;
			}else{
				// Return the object
				return $this->$module_name;
			}
		}
		
		// Class was not returned
		throw new Exception("Failed to load model class.");
	}




	/**
	 * @brief Load a helper. Load an instance of the helper requested into the name space of the current object.
	 * Subsequent requests can use $this->HelperName->method() to call methods of the object 
	 *  
	 * @param string $module_name - The name of the helper to load
	 * @return mixed - instance of the helper requested or false if unable to locate the helper
	 * 
	 * @example To load the Validate helper use the following $this->load_helper('Validate');
	 */
	function load_helper($module_name)
	{
		$location = __DIR__."/helper/{$module_name}.php";

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
			}else if($module_name == 'Framework_Config')
			{
				return Framework_Config::Instance();
			}else{
				$this->$module_name = new $module_name();
			}

			return $this->$module_name;
		}else{
			return FALSE;
		}
	}



	/**
	 * @brief Load a controller. Load an instance of the controller requested into the name space of the current object.
	 * Subsequent requests can use $this->ControllerName->method() to call methods of the object 
	 *  
	 * @param string $module_name - The name of the controller to load
	 * @return mixed - instance of the controller requested or false if unable to locate the controller
	 * 
	 * @example To load the Admin controller use the following $this->load_controller('Admin');
	 */
	function load_controller($module_name)
	{
		$location = "{$this->config()->path->application_path}framework/controller/{$module_name}.php";
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


	/**
	 * @brief Render the application. Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into the buffer.
	 * 
	 * @param string $page - template page to display (header, page, footer)
	 */
	function render($page)
	{
		$this->load_model('User');
		$this->load_model('Auth');

		// If secure module, make sure user is logged in		
		if(isset($this->_auth->user_authorization->check) && in_array($page, $this->_auth->user_authorization->check) && $this->User->is_logged_in() === FALSE)
		{
			// Secure module, and user is not logged in
			include("{$this->config()->path->application_path}/framework/template/User/login.php");
			exit;
		}
		// See if a page load function exists
		if(method_exists($this, $this->request->method_name))
		{
			$function_name = $this->request->method_name;
			$this->$function_name();
		}

		// Check that template exists
		if(file_exists("{$this->config()->path->application_path}/framework/template/{$this->request->controller_name}/{$page}.php"))
		{
			// Load requested page
			$this->render_head();
			include_once("{$this->config()->path->application_path}/framework/template/{$this->request->controller_name}/{$page}.php");
			$this->render_foot();
		}else{

			header("HTTP/1.0 404 Not Found");
			$frm = $this->load_controller('Error');
			$frm->request->controller_name = 'Error';
			$frm->request->method_name = 'error_404';
			$frm->render('error_404');
			die;
		}
	}




	/**
	 * @brief Render the header for the current page. Each controller can supply/use it's own unique header by simply placing
	 * a head.php file in the framework/template/ControllerName/ subdirectory
	 * 
	 * Called by $this->render($page);
	 */
	function render_head()
	{
		if(file_exists("{$this->config()->path->application_path}/framework/template/{$this->request->controller_name}/head.php"))
		{
			include("{$this->config()->path->application_path}/framework/template/{$this->request->controller_name}/head.php");
		}else if(file_exists("{$this->config()->path->application_path}/framework/template/head.php")){
			include("{$this->config()->path->application_path}/framework/template/head.php");
		}
	}




	/**
	 * @brief Render the footer for the current page. Each controller can supply/use it's own unique footer by simply placing
	 * a foot.php file in the framework/template/ControllerName/ subdirectory
	 * 
	 * Called by $this->render($page);
	 */

	 function render_foot()
	{
		if(file_exists("{$this->config()->path->application_path}/framework/template/{$this->request->controller_name}/foot.php"))
		{
			include("{$this->config()->path->application_path}/framework/template/{$this->request->controller_name}/foot.php");
		}else if(file_exists("{$this->config()->path->application_path}/framework/template/foot.php")){
			include("{$this->config()->path->application_path}/framework/template/foot.php");
		}
	}



	/**
	 * @brief Render a partial element for the current page. Each controller can supply/use it's own unique partial page elements
	 * by simply placing a $partial_name.php file in the framework/template/ControllerName/Partial subdirectory
	 * 
	 * @param string $partial_name - name for the partial page element to load 
	 */
	function render_partial($partial_name)
	{
		// Lock the file path to the partial directory
		$partial_name = str_replace('/', '', $partial_name);
		$location = "{$this->config()->path->application_path}/framework/template/{$this->request->controller_name}/Partial/{$partial_name}.php";
		if(file_exists($location))
		{
			include($location);
		}
	}



	/**
	 * @brief Loads the framework configuration. Gives easy access to $this->load_helper('Framework_Config')->load()
	 * 
	 * @return object Framework_Config - instance of all configuration setting in framework/config
	 */
	 public function config()
	 {
		return $this->load_helper('Framework_Config')->load();
	 }
	 


	/**
	 * @brief Check if a value is an integer id. Handles string and int input. Empty strings return FALSE.
	 * 
	 * @param integer $int_id - value to check if it is an integer
	 * @return boolean - true if value is an integer false otherwise
	 */
	public function is_id($int_id)
	{
		return (strlen($int_id) > 0 && strlen($int_id) == strlen(intval($int_id)));
	}


}



/**
 * @brief Displays print_r debug information. Shows debug information in a pre tag with styling
 * 
 * @param mixed $data - object, boolean or array to display with print_r or var_dump
 */ 
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


/**
 * @class Config_Builder
 * @brief Configuration Builder class. 
 * Wrapper class used by Framework_Config->load() for loading configuration information.
 *
 * @package  ProjectFramework
 * @since    1.0.0
 */
class Config_Builder{}
