<?php

	include_once(__DIR__."/Field_Builder.php");

	class CLI_Generate
	{
		// Description
		public $description = 'Create a model + controller + template folder with [name]';
		public $example = 'cli.php generate [name]';
		
		// Construct variables
		private $_fw;
		private $_args;

		// Validated name
		private $_name;
		private $_table_name;
		
		// Are we going to generate CRUD?
		private $_crud = FALSE;
		private $_crud_data = array();



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
			$this->_check_crud();

			// Generate controller
			$this->_controller();

			// Generate model
			$this->_model();

			// Generate model
			$this->_template();

			// Done
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
			
			// Basic or CRUD template?
			$controller_template = 'controller';
			if($this->_crud === TRUE)
			{
				$controller_template = 'controller_CRUD';
			}
			
			// Get template and fill in variables
			if($this->_render_template($controller_template, array('NAME' => $this->_name, 'NAME_LOWERCASE' => strtolower($this->_name)), $controller_file))
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
		 * Make the model class file
		 * @return CLI_Generate
		 */
		private function _model()
		{
			// Controller file
			$model_file = "{$this->_model_path()}{$this->_name}.php";

			// Does the model file already exist?
			try
			{
				$this->_fw->load_model($this->_name);
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
			}catch(Exception $e){
				// Model does not exist, carry on
			}

			// Create model from template
			echo console_text("Model: CREATING", 'green');

			// Basic or CRUD template?
			$model_template = 'model';
			$validation_array_string = '';
			if($this->_crud === TRUE)
			{
				$model_template = 'model_CRUD';
				foreach($this->_crud_data as $form_builder)
				{
					$validation_array_string .= $form_builder->rule_string();
				}
				$validation_array_string = trim($this->indent_string(trim($validation_array_string), 4));
			}

			// Get template and fill in variables
			if($this->_render_template($model_template, array('NAME' => $this->_name, 'NAME_LOWERCASE' => strtolower($this->_name), 'VALIDATION_ARRAY' => $validation_array_string), $model_file))
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
				echo console_text("Template Folder: EXISTS", 'green');
				echo console_text("...SKIPPED", 'green');
			}else{
				// Create template folder
				echo console_text("Template Folder: CREATING", 'green');
				if(@mkdir($template_path) === TRUE)
				{
					echo console_text("...SUCCESS", 'green');
				}else{
					echo console_text("...FAILED", 'red');
				}
			}

			// Building CRUD?
			if($this->_crud)
			{
				// Folder exists
				if(is_dir($template_path))
				{
					$template_names = array('template_index', 'template_manage');
					
					// Make form field string
					$form_field_string = '';
					foreach($this->_crud_data as $form_builder)
					{
						$form_field_string .= $form_builder->field_string();
					}
					$form_field_string = trim($this->indent_string(trim($form_field_string), 1));
					
					foreach($template_names as $template_name)
					{
						// Get template and fill in variables
						echo console_text("Template File ".str_replace('template_', '', $template_name).".php: CREATING", 'green');
						if($this->_render_template($template_name, array('NAME' => $this->_name, 'NAME_LOWERCASE' => strtolower($this->_name), 'FORM_FIELDS' => $form_field_string), $template_path."/".str_replace('template_', '', $template_name).'.php'))
						{
							echo console_text("...SUCCESS", 'green');
						}else{
							echo console_text("...FAILED", 'red');
							return $this;
						}
					}
				}else{
					echo console_text("...ABORTING TEMPLATE FILE CREATION", 'red');
				}
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
			// Make sure there is a table
			try
			{
				// Will throw exception is does not exist
				$this->_fw->load_helper('Db')->get_rows("SHOW COLUMNS FROM `{$this->_table_name}`");
				
				// Ask
				$this->_crud = $this->_get_input_confirm("Would you like to add CRUD for no additional charge?");
				
				// Check response
				if($this->_crud)
				{
					$this->_crud_data_get();
				}

			}catch(Exception $e){
				// Table does not exist so do not offer to build
			}
		}

		
		
		/**
		 * Build the crud data array
		 */
		private function _crud_data_get()
		{
			$columns = $this->_fw->load_helper('Db')->get_rows("SHOW COLUMNS FROM `{$this->_table_name}`");
			foreach($columns as $column)
			{		
				$f = new Field_Builder($this->_fw, $this->_table_name, $column);
				$this->_crud_data[] = $f->process();
			}
		}



		/**
		 * Indent a block of text by line
		 * @param string $text
		 * @param int $indent_tabs
		 * @return string
		 */
		 private function indent_string($text, $indent_tabs)
		 {
			 $lines = explode("\n", str_replace("\r", "", $text));
			 
			 $indent = '';
			 for($x = 1; $x <= $indent_tabs; $x++)
			 {
				 $indent .= "\t";
			 }
			 
			 foreach($lines as $k => $v)
			 {
				 $lines[$k] = $indent.$v;
			 }
			 return implode("\n", $lines);
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