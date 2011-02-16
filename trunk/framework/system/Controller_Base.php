<?php

	abstract class Controller_Base extends Framework
	{
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
			if(is_array($_SESSION['Error']))
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
			if(is_array($_SESSION['Flash']))
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
		 * @return bool
		 */
		function add_flash($message)
		{
			if(!empty($message))
			{
				$_SESSION['Flash'][] = $message;
			}
			return TRUE;
		}



		/**
		 * Return the path to an image file
		 * @param string $path
		 * @return string
		 */
		public function image_url($path)
		{
			$path = trim($path, ' /');
			if($this->config['web_path'] == '/')
			{
				$wp = '';
			}else{
				$wp = $this->config['web_path'];
			}
			return "{$wp}/template/{$this->config['template_name']}/images/{$path}";
		}



		/**
		 * Return the path to a javascript file
		 * @param string $path
		 * @return string
		 */
		public function javascript_url($path)
		{
			$path = trim($path, ' /');
			if($this->config['web_path'] == '/')
			{
				$wp = '';
			}else{
				$wp = $this->config['web_path'];
			}
			return "{$wp}/template/{$this->config['template_name']}/js/{$path}";
		}



		/**
		 * Return the path to a CSS file
		 * @param string $path
		 * @return string
		 */
		public function css_url($path)
		{
			$path = trim($path, ' /');
			if($this->config['web_path'] == '/')
			{
				$wp = '';
			}else{
				$wp = $this->config['web_path'];
			}
			return "{$wp}/template/{$this->config['template_name']}/css/{$path}";
		}



		/**
		 * Return the current page path
		 * @return string
		 */
		public function page_path()
		{
			return "{$this->config['web_path']}/".strtolower($this->info['current_module'])."/{$this->info['current_page']}";
		}



		/**
		 * Reload the current  page
		 */
		public function reload_page()
		{
			header("Location: {$this->config['web_path']}/{$this->info['current_module']}/{$this->info['current_page']}");
			exit;
		}



	}

?>
