<?php
/**
 * @file Controller_Base.php
 * @package    Itul.Framework
 *
 * @copyright  Copyright (C) 1999 - 2012 i-Tul Design and Software, Inc. All rights reserved.
 * @license    see LICENSE.txt
 */

/**
 * @class Controller_Base
 * @brief Controller Base class. Abstract Base application class for the iTul CMS system
 *
 * @package  Itul.Framework
 * @since    1.0.0
 */
abstract class Controller_Base extends Framework
{
	/**
	 * @brief Constructor for Controller_Base.
	 */
	public function __construct()
	{ 
		parent::__construct();
	}



	/**
	 * @brief Add error to Session error variable. For display later
	 * with show_error(). Only sets session variable if configuration log_error is true
	 * 
	 * @param mixed $error - a string or array or errors to display
	 * @return object Controller_Base
	 */
	function add_error($error)
	{
		// See if error reporting is enabled
		if($this->config['log_error'] === TRUE)
		{
			$e = array(
				'request' => $_SERVER['REQUEST_URI'],
				'post' => $_POST,
				'get' => $_GET,
				'error' => $error
			);
			// Save to session
			$_SESSION['Error'][] = $e;
		}
		return $this;
	}



	/**
	 * @brief Display the Session error variable. Shows previously set errors from add_error() and 
	 * removes/empties Session error variable.
	 */
	function show_error()
	{
		if(isset($_SESSION['Error']) && is_array($_SESSION['Error']))
		{
			echo '<div class="error_message">';
			echo '<pre>';
			print_r($_SESSION['Error']);
			echo '</pre>';
			echo '</div>';
			$_SESSION['Error'] = '';
		}
	}



	/**
	 * @brief Add an information message to Session flash variable. For display later
	 * with show_flash().
	 *  
	 * @param string $message - Message to display to the user
	 * @return object Controller_Base
	 */
	function add_flash($message)
	{
		if(!empty($message))
		{
			$_SESSION['Flash'][] = $message;
		}
		return $this;
	}



	/**
	 * @brief Display the Session flash variable. Shows previously set informational messages from add_flash() and
	 * removes/empties Session flash variable.
	 */
	function show_flash()
	{
		if(isset($_SESSION['Flash']) && is_array($_SESSION['Flash']))
		{
			echo '<div class="width_container"><div id="succeed">';
			foreach($_SESSION['Flash'] as $m)
			{
				echo "<p>{$m}</p>\n";
			}
			echo '</div></div>';
		}
		$_SESSION['Flash'] = '';
	}



	/**
	 * @brief Get path to an image file. Creates a string usable in href's
	 * to the image. 
	 * 
	 * @param string $path - the name of the file, which can include subdirectories of the framework/template/images folder
	 * @return string - Full web path to the file
	 */
	public function image_url($path)
	{
		$path = trim($path, ' /');
		if($this->config()->path->web_path == '/')
		{
			$wp = '';
		}else{
			$wp = rtrim($this->config()->path->web_path, '/');
		}
		return "{$wp}/framework/template/images/{$path}";
	}



	/**
	 * @brief Get path to a javascript file. Creates a string usable in href's
	 * to the JavaScript file.
	 * 
	 * @param string $path - the name of the file, which can include subdirectories of the framework/template/js folder
	 * @return string - Full web path to the file
	 */
	public function javascript_url($path)
	{
		$path = trim($path, ' /');
		if($this->config()->path->web_path == '/')
		{
			$wp = '';
		}else{
			$wp = rtrim($this->config()->path->web_path, '/');
		}
		return "{$wp}/framework/template/js/{$path}";
	}



	/**
	 * @brief Get path to a CSS file. Creates a string usable in href's
	 * to the CSS file.
	 * 
	 * @param string $path - the name of the file, which can include subdirectories of the framework/template/css folder
	 * @return string - Full web path to the file
	 */
	public function css_url($path)
	{
		$path = trim($path, ' /');
		if($this->config()->path->web_path == '/')
		{
			$wp = '';
		}else{
			$wp = rtrim($this->config()->path->web_path, '/');
		}
		return "{$wp}/framework/template/css/{$path}";
	}



	/**
	 * @brief Reload the current page.
	 */
	public function reload_page()
	{
		header("Location: {$_SERVER['REQUEST_URI']}");
		exit;
	}



	/**
	 * @brief Return the path to a page. Creates a string usable in href's
	 * to the page.
	 * 
	 * @param string $controller - controller for the page
	 * @param string $method - method name for the page
	 * @param string $id - optional id or other parameters for method
	 * @return string - Full web path to the page with supplied aguments if anny
	 */
	public function page_link($controller = '', $method = '', $id = '')
	{
		$str = "/".trim("{$this->config()->path->web_path}".strtolower($controller)."/{$method}/{$id}", '/')."/";
		if($str == '//'){ $str = '/'; }
		
		return $str;
	}
	
	
	
	/**
	 * @brief Redirect to a page.
	 * 
	 * @param string $controller - controller for the page
	 * @param string $method - method name for the page
	 * @param string $id - optional id or other parameters for method
	 */
	public function redirect($controller = '', $method = '', $id = '')
	{
		header("Location: {$this->page_link($controller, $method, $id)}");
		exit;
	}



	/**
	 * @brief Sets the active class for a page.
	 * 
	 * @param string $compare - page to check if its the current one 
	 */
	public function active($compare = '')
	{
		if(is_array($compare))
		{
			foreach($compare as $c)
			{
				if($this->info['current_page'] == $c)
				{
					echo 'class="active"';
					break;
				}
			}
		}else{
			echo ($this->info['current_page'] == $compare ? 'class="active"' : '');
		}
	}



	/**
	 * @brief Set login requirements for a page.
	 *
	 * @param string|array $pages_requiring_login - string or array of strings for pages that require login
	 * @param string|array $pages_to_exclude - string or array of strings for pages that do not require login
	 * @return object Controller_Base
	 * 
	 * @example $this->require_login() == Auth on all pages
	 * @example $this->require_login(NULL, 'index') == Auth on all pages except index
	 * @example $this->require_login(NULL, array('index', 'list')) == Auth on all pages except index and list
	 * @example $this->require_login(array('index', 'list')) == Auth on index and list
	 */
	public function require_login($pages_requiring_login = NULL, $pages_to_exclude = NULL)
	{
		// Get methods
		$all_methods = get_class_methods(get_class());
		$controller_methods = get_class_methods(get_called_class());

		$require_auth = array();

		// Login on all pages
		if($pages_requiring_login === NULL)
		{
			if(is_array($controller_methods))
			{
				foreach($controller_methods as $controller_method)
				{
					if(!in_array($controller_method, $all_methods))
					{
						$require_auth[] = $controller_method;
					}
				}
			}
		}else{
			if(is_array($pages_requiring_login))
			{
				foreach($pages_requiring_login as $page_requiring_login)
				{
					if(!in_array($page_requiring_login, $controller_methods))
					{
						throw new Exception("Unknown method: {$page_requiring_login}");
					}
					$require_auth[] = $page_requiring_login;
				}
			}else{
				if(!in_array($pages_requiring_login, $controller_methods))
				{
					throw new Exception("Unknown method: {$pages_requiring_login}");
				}
				$require_auth[] = $pages_requiring_login;
			}
		}

		// Now handle exclusions
		if($pages_to_exclude !== NULL)
		{
			if(is_array($pages_to_exclude))
			{
				// Array
				foreach($pages_to_exclude as $page_to_exclude)
				{
					$search = array_search($page_to_exclude, $require_auth);
					if($search !== FALSE)
					{
						unset($require_auth[$search]);
					}
				}
			}else{
				// String
				$search = array_search($pages_to_exclude, $require_auth);
				if($search !== FALSE)
				{
					unset($require_auth[$search]);
				}
			}
		}

		// Add to config
		$this->_auth->user_authorization = new Config_Builder;
		$this->_auth->user_authorization->check = $require_auth;

		// Return
		return $this;
	}


}

?>