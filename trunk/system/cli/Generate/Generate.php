<?php

	#include_once(__DIR__."/Field_Builder.php");

	class CLI_Generate
	{
		// Description
		public $description = 'Create a model + test suite + controller + template folder with [name]';
		public $example = 'cli.php generate [name]';
		
		// Construct variables
		private $_fw;
		private $_args;

		// Validated name
		private $_name;
		private $_table_name;
		
		// Are we going to generate CRUD?
		private $_crud = FALSE;



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
			// Validate param
			$this->_validate();

			// Model name for reference
			echo console_text("Generating framework code for: '{$this->_name}'", 'green');

			// Check for CRUD
			#$this->_check_crud();

			// Generate controller
			$this->_controller();

			// Generate model
			$this->_model();

			// Generate model
			$this->_template();

			// When done just isn't enough
			echo console_text('Reticulating Splines', 'green');
			echo console_text('Done.', 'green');
			
			// Return
			return $this;
		}
		
		
		
		/**
		 * Validate the model/controller name passed
		 * @return CLI_Generate
		 */
		private function _validate()
		{
			// Name not specified
			if(!isset($this->_args[2]))
			{
				throw new Exception('Name expected.');
			}
			
			// Basic string check
			if(!preg_match('/^([A-Za-z]{1})([_a-zA-Z]{1,})$/', $this->_args[2], $m))
			{
				throw new Exception('Invalid characters in name.');
			}
			
			// Format the name to work in the framework
			$this->_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $this->_args[2])));
			$this->_table_name = strtolower($this->_name);
			
			// Done
			return $this;
		}
		
		
		
		/**
		 * Make the controller class file
		 * @return CLI_Generate
		 */
		private function _controller()
		{
			// Controller file
			$controller_file = "{$this->_controller_path()}{$this->_name}.php";

			// Does the controller file already exist?
			if($this->_fw->load_controller($this->_name) !== FALSE)
			{
				$replace_controller = $this->_get_input_confirm('Controller already exists. Replace?');
				if($replace_controller === FALSE)
				{
					echo console_text("Controller: SKIPPED", 'green');
					return $this;
				}else{
					echo console_text("Controller: REMOVING", 'green');

					if($this->_unlink($controller_file))
					{
						echo console_text("...SUCCESS", 'green');
					}else{
						echo console_text("...FAILED", 'red');
						return $this;
					}
				}
			}
		
			// Create controller from template
			echo console_text("Controller: CREATING", 'green');
			
			// Get template and fill in variables
			if($this->_render_template('controller', array('NAME' => $this->_name), $controller_file))
			{
				echo console_text("...SUCCESS", 'green');
			}else{
				echo console_text("...FAILED", 'red');
				return $this;
			}
			
			// Done
			return $this;
		}



		/**
		 * Make the controller class file
		 * @return CLI_Generate
		 */
		private function _model()
		{
			// Controller file
			$model_file = "{$this->_model_path()}{$this->_name}.php";

			// Does the model file already exist?
			if($this->_fw->load_model($this->_name) !== FALSE)
			{
				$replace_model = $this->_get_input_confirm('Model already exists. Replace?');
				if($replace_model === FALSE)
				{
					echo console_text("Model: SKIPPED", 'green');
					return $this;
				}else{
					echo console_text("Model: REMOVING", 'green');

					if($this->_unlink($model_file))
					{
						echo console_text("...SUCCESS", 'green');
					}else{
						echo console_text("...FAILED", 'red');
						return $this;
					}
				}
			}

			// Create model from template
			echo console_text("Model: CREATING", 'green');
			
			// Get template and fill in variables
			if($this->_render_template('model', array('NAME' => $this->_name), $model_file))
			{
				echo console_text("...SUCCESS", 'green');
			}else{
				echo console_text("...FAILED", 'red');
				return $this;
			}
			
			// Done
			return $this;
		}
		


		/**
		 * Make the template folder
		 * @return CLI_Generate
		 */
		private function _template()
		{
			// Controller file
			$template_path = "{$this->_template_path()}{$this->_name}";

			// Template folder already exists
			if(is_dir($template_path))
			{
				echo console_text("Template: EXISTS", 'green');
				echo console_text("...SKIPPED", 'green');
				return $this;
			}
			
			// Create template folder
			echo console_text("Template: CREATING", 'green');
			if(@mkdir($template_path) === TRUE)
			{
				echo console_text("...SUCCESS", 'green');
			}else{
				echo console_text("...FAILED", 'red');
			}
		
			// Done
			return $this;
		}	
		
		

		/**
		 * Get user input from console
		 * @return string
		 */
		private function _get_input($prompt_string = NULL)
		{
			if($prompt_string !== NULL)
			{ 
				echo console_text($prompt_string, 'red');
			}
			
			// Get line from user
			$handle = fopen ("php://stdin","r");
			$line = fgets($handle);
			return trim($line);
		}
		
		
		
		/**
		 * Get user input from console, require y/n
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
		 * Check if user wants CRUD generated as well
		 * @return CLI_Generate
		 */
		private function _check_crud()
		{
			$this->_crud = $this->_get_input_confirm("Would you like to add CRUD for no additional charge?");
		}

		
		
		private function _crud_variables()
		{
			$columns = $this->_fw->load_helper('Db')->get_rows("SHOW COLUMNS FROM `{$this->_table_name}`");
			#pr("SHOW COLUMNS FROM `{$this->_table_name}`");
			foreach($columns as $column)
			{
				$f = new Field_Builder($this->_fw, $this->_table_name, $column);
				$f->process();
				
				print_r($f);
			}
		}




		/**
		 * Unlink a file
		 * @param string $file_path
		 * @return bool
		 */
		private function _unlink($file_path)
		{
			// Writable?
			if(is_writable($file_path))
			{
				if(@unlink($file_path))
				{
					return TRUE;
				}
			}
			return FALSE;
		}


		
		/**
		 * Path to controller folder
		 * @return string
		 */
		private function _controller_path()
		{
			return $this->_fw->config()->path->application_path."framework/controller/";
		}


		
		/**
		 * Path to model folder
		 * @return string
		 */
		private function _model_path()
		{
			return $this->_fw->config()->path->application_path."framework/model/";
		}


		
		/**
		 * Path to template folder
		 * @return string
		 */
		private function _template_path()
		{
			return $this->_fw->config()->path->application_path."framework/template/";
		}
	}

?>