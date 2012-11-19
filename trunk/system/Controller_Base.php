<?php

	abstract class Controller_Base extends Framework
	{
		public function __construct()
		{
			parent::__construct();
		}

		/**
		 * Add an error string or array
		 * @param mixed $error
		 * @return bool
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
			return TRUE;
		}



		/**
		 * Render the error array
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
		 * Show flash message
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
		 * Add a message
		 * @param string $message
		 * @return Controller_Base
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
		 * Return the path to an image file
		 * @param string $path
		 * @return string
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
		 * Return the path to a javascript file
		 * @param string $path
		 * @return string
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
		 * Return the path to a CSS file
		 * @param string $path
		 * @return string
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
		 * Reload the current page
		 */
		public function reload_page()
		{
			header("Location: {$_SERVER['REQUEST_URI']}");
			exit;
		}



		/**
		 * Return the path to a page
		 * @param string $model
		 * @param string $page
		 * @param string $id
		 * @return string
		 */
		public function page_link($model = '', $page = '', $id = '')
		{
			$str = "/".trim("{$this->config()->path->web_path}".strtolower($model)."/{$page}/{$id}", '/')."/";
			if($str == '//'){ $str = '/'; }
			
			return $str;
		}
		
		
		
		/**
		 * Redirect to a page
		 * @param string $model
		 * @param string $page
		 * @param string $id
		 */
		public function redirect($model = '', $page = '', $id = '')
		{
			header("Location: {$this->page_link($model, $page, $id)}");
			exit;
		}



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
		 * Set which pages require the user to be logged in
		 *
		 * @example
		 * $this->require_login() == Auth on all pages
		 * $this->require_login(NULL, 'index') == Auth on all pages except index
		 * $this->require_login(NULL, array('index', 'list')) == Auth on all pages except index and list
		 * $this->require_login(array('index', 'list')) == Auth on index and list
		 *
		 * @param string|array $pages_requiring_login
		 * @param string|array $pages_to_exclude
		 * @return Controller_Base
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
			$this->config()->user_authorization = new Config_Builder;
			$this->config()->user_authorization->check = $require_auth;

			// Return
			return $this;
		}


	}

?>