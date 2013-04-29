<?php

	class CLI_Thumbnail
	{
		// Description
		public $description = 'Set up image resizing/caching';
		public $example = 'cli.php thumbnail';
		
		// Construct variables
		private $_fw;
		private $_args;
		
		private $_image_path;
		private $_cache_path;
		private $_web_path = 'resize';
		private $_resize_type = 'adaptive';



		/**
		 * Initialize plugin
		 * @param Framework $framework
		 * @param array $arguments
		 * @return CLI_Generate
		 */
		public function __construct($framework, $arguments)
		{
			// Save for later
			$this->_fw = $framework;
			$this->_args = $arguments;
			
			// Return
			return $this;
		}
		
		
		
		/**
		 * Plugin called, process args
		 * @return CLI_Generate
		 */
		public function start()
		{			
			// Ask for path info
			$this->_get_paths();

			// .htaccess update
			$this->_update_htaccess();

			// Move resize.php
			$this->_build();

			// Return
			return $this;
		}



		/**
		 *
		 *
		 */
		private function _update_htaccess()
		{
			// .htaccess update
			echo console_text("ADDING HTACCESS RULE", 'green');
			$htaccess_location = str_replace('/system/cli/Thumbnail', '', __DIR__)."/.htaccess";
			if(is_writable($htaccess_location))
			{
				// RewriteBase
				// Get file contents
				$htaccess_contents = file_get_contents($htaccess_location);

				// Look for RewriteBase and inject content after
				if(preg_match('/RewriteBase([\s]{1,})([\S]{1,})/s', $htaccess_contents, $m))
				{
					$htaccess_contents = str_replace($m[0], "{$m[0]}\n{$this->_htaccess_rule()}", $htaccess_contents);
				}
				
				@file_put_contents($htaccess_location, $htaccess_contents);
				echo console_text("...SUCCESS", 'green');
			}else{
				echo console_text("...HTACCESS FILE NOT WRITABLE", 'red');
			}
		}


		/**
		 * Generate line to insert into .htaccess file
		 * @return string
		 */
		private function _htaccess_rule()
		{
			return "RewriteRule ^{$this->_web_path}/([0-9]+)/([0-9]+)/(.*)$ resize.php?width=$1&height=$2&file=$3&type={$this->_resize_type}";
		}


		
		/**
		 * Get the image storage path, and cache storage path
		 * @return CLI_Thumbnail
		 */
		private function _get_paths()
		{
			// Image storage path
			$this->_image_path = $this->_get_input("Image storage path (/home/site/images/):");
			echo console_text("PATH CHECK", 'green');
			if(is_dir($this->_image_path))
			{
				echo console_text("...SUCCESS", 'green');
			}else{
				echo console_text("CREATING PATH", 'green');
				@mkdir($this->_image_path, 0777, TRUE);
				if(is_dir($this->_image_path))
				{
					echo console_text("...SUCCESS", 'green');
				}else{
					echo console_text("...FAILED", 'red');
					exit;
				}
			}

			// Image cache storage path
			$this->_cache_path = $this->_get_input("Image cache path (/home/site/cache/):");
			echo console_text("PATH CHECK", 'green');
			if(is_dir($this->_cache_path))
			{
				echo console_text("...SUCCESS", 'green');
			}else{
				echo console_text("CREATING PATH", 'green');
				@mkdir($this->_cache_path, 0777, TRUE);
				if(is_dir($this->_cache_path))
				{
					echo console_text("...SUCCESS", 'green');
				}else{
					echo console_text("...FAILED", 'red');
					exit;
				}
			}
			
			// Done
			return $this;
		}



		private function _build()
		{
			$variables = array(
				'IMAGE_LOCATION'		=> $this->_image_path,
				'IMAGE_CACHE_LOCATION'	=> $this->_cache_path,
			);
			
			echo console_text("GENERATING resize.php", 'green');
			
			$resize_path = str_replace('/system/cli/Thumbnail', '', __DIR__)."/resize.php";
			
			$write = TRUE;
			if(file_exists($resize_path) && !$this->_get_input_confirm("OVERWRITE EXISTING FILE?"))
			{
				echo console_text("...SKIPPED", 'green');
				$write = FALSE;
			}
			
			if($write === TRUE)
			{
				if($this->_render_template('resize', $variables, $resize_path))
				{
					echo console_text("...SUCCESS", 'green');
				}else{
					echo console_text("...FAILED", 'red');
				}
			}
		}


		/**
		 * Render a template, then save to path
		 * @param string $template_name
		 * @param array $variables
		 * @param string $file_save_path
		 * @return bool
		 */
		private function _render_template($template_name, $variables, $file_save_path)
		{
			$template_dir = __DIR__."/template/";
			$template_path = $template_dir.$template_name.".php";
		
			// Variables are in an array
			if(!is_array($variables))
			{
				throw new Exception("Template variable array expected.");
			}
			
			// Template exists?
			if(!file_exists($template_path))
			{
				throw new Exception("Template '{$template_name}' not found.");
			}
			
			// Load template to string
			$template_string = file_get_contents($template_path);
			
			// Replace variables
			foreach($variables as $search => $replace)
			{
				$template_string = str_replace("|{$search}|", $replace, $template_string);
			}

			// Return bool
			return !(file_put_contents($file_save_path, $template_string) === FALSE);
		}



		/**
		 * Get user input from console
		 * @param string $prompt_string
		 * @return string
		 */
		private function _get_input($prompt_string = NULL)
		{
			if($prompt_string !== NULL)
			{ 
				echo console_text($prompt_string, '');
			}
			
			// Get line from user
			$handle = fopen ("php://stdin","r");
			$line = fgets($handle);
			return trim($line);
		}



		/**
		 * Get user input from console, require y/n
		 * @param string $prompt_string
		 * @return string
		 */
		private function _get_input_confirm($prompt_string)
		{
			$input = $this->_get_input("{$prompt_string} (y/n)");
			while($input != 'y' && $input != 'n')
			{
				$input = $this->_get_input("{$prompt_string} (y/n)");
			}
			return ($input == 'y');
		}
	}

?>