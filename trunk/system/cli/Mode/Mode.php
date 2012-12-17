<?php

	class CLI_Mode
	{
		// Description
		public $description = 'Set or display the current build mode';
		public $example = 'cli.php mode [dev|prod|current]';
		
		// Construct variables
		private $_fw;
		private $_args;
		private $_mode;



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
			// Set mode
			$this->_mode = $this->_args[2];
			
			// Validate
			$this->_valid_mode();
			
			// Get or set
			if($this->_mode == 'current')
			{
				echo $this->_get_mode()."\n";
			}else{
				$this->_set_mode();
			}

			// Return
			return $this;
		}
		
		

		/**
		 * Check that a valid mode argument was passed
		 * @return CLI_Mode
		 */
		private function _set_mode()
		{
			if($this->_mode == 'dev')
			{
				// Check if dev folder exists
				if(is_dir($this->_dev_folder_path()) == FALSE)
				{
					// Attempt to create dev folder
					@mkdir($this->_dev_folder_path());
					
					// Check
					if(!is_dir($this->_dev_folder_path()))
					{
						throw new Exception('Failed to create development mode folder');
					}
				}
			}else if($this->_mode == 'prod'){
				// Check if dev folder exists
				if(is_dir($this->_dev_folder_path()) == TRUE)
				{
					// Attempt to remove dev folder
					@rmdir($this->_dev_folder_path());
					
					// Check
					if(is_dir($this->_dev_folder_path()))
					{
						throw new Exception('Failed to remove development mode folder');
					}
				}
			}else{
				throw new Exception('Unknown set mode');
			}
			
			return $this;
		}



		/**
		 * Return the current mode
		 * @return string
		 */
		private function _get_mode()
		{
			return (is_dir($this->_dev_folder_path())) ? "dev" : "prod";
		}		
		


		/**
		 * Check that a valid mode argument was passed
		 * @return CLI_Mode
		 */
		private function _valid_mode()
		{
			// Check
			if(in_array($this->_mode, array('dev', 'prod', 'current')))
			{
				return $this;
			}

			// Nope
			throw new Exception('Invalid argument');
		}
		
		
		
		/**
		 * Determine where the dev folder should be located
		 * @todo make less breakable
		 * @return string
		 */
		private function _dev_folder_path()
		{
			// Current file directory
			$current_directory = __DIR__;
			
			// Remove the path to the CLI folder from the end
			$framework_directory = trim(str_replace('/system/cli/Mode', '', $current_directory), '/');
			
			// Get all of the directory parts
			$directories = explode('/', $framework_directory);
			
			// Go one level below the framework
			array_pop($directories);
			
			// Glue directories back together
			return str_replace('//', '/', "/".implode('/', $directories)."/dev/");
		}
	}

?>