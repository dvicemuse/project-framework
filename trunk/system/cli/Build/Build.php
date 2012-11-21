<?php

	class CLI_Build
	{
		// Description
		public $description = 'Build the framework configuration';
		public $example = 'cli.php build';
		
		// Construct variables
		private $_fw;
		private $_args;



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
			// Path
			echo console_text("Updating path config...", 'green');
			$this->_path();

			// Db
			echo console_text("Updating database config...", 'green');
			$this->_database();			
			
			// Encryption
			echo console_text("Updating encryption config...", 'green');
			$this->_encryption();

			// Mail
			echo console_text("Updating mail config...", 'green');
			$this->_mail();
			
			// Return
			return $this;
		}

		

		/**
		 * Update database configuration
		 * @return CLI_Build
		 */
		private function _mail()
		{
			// No reply name
			$reply_name = $this->_get_input("Mail no-reply name (Mail Robot):");
			echo console_text("UPDATING NO-REPLY NAME", 'green');
			$this->_set_config_variable('Mail', 'no_reply_name', $reply_name);
			echo console_text("...SUCCESS", 'green');
			
			// No reply address
			$reply_address = $this->_get_input("Mail no-reply address (no-reply@domain.com):");
			echo console_text("UPDATING NO-REPLY ADDRESS", 'green');
			$this->_set_config_variable('Mail', 'no_reply_address', $reply_address);
			echo console_text("...SUCCESS", 'green');
						
			return $this;
		}



		/**
		 * Update database configuration
		 * @return CLI_Build
		 */
		private function _encryption()
		{
			// Check if user wants to updsate encryption key
			if($this->_get_input_confirm('Update encryption key?'))
			{
				echo console_text("GENERATING KEY", 'green');
				$encryption_key = substr(str_shuffle("!@#$%^&*()_+0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 15);
				echo console_text("UPDATING KEY", 'green');
				$this->_set_config_variable('Encryption', 'encryption_key', $encryption_key);
				echo console_text("...SUCCESS", 'green');
			}else{
				echo console_text("...SKIPPED", 'green');
			}
			
			return $this;
		}
		


		/**
		 * Update database configuration
		 * @return CLI_Build
		 */
		private function _database()
		{
			// Host
			$host = $this->_get_input("Database host:");
			echo console_text("UPDATING HOST", 'green');
			$this->_set_config_variable('Db', 'host', $host);
			echo console_text("...SUCCESS", 'green');

			// Database name
			$database = $this->_get_input("Database name:");
			echo console_text("UPDATING DATABASE NAME", 'green');
			$this->_set_config_variable('Db', 'database', $database);
			echo console_text("...SUCCESS", 'green');

			// User name
			$database_user = $this->_get_input("Database user name:");
			echo console_text("UPDATING USER NAME", 'green');
			$this->_set_config_variable('Db', 'username', $database_user);
			echo console_text("...SUCCESS", 'green');

			// Password
			$database_password = $this->_get_input("Database password:");
			echo console_text("UPDATING DATABASE PASSWORD", 'green');
			$this->_set_config_variable('Db', 'password', $database_password);
			echo console_text("...SUCCESS", 'green');
			
			return $this;
		}



		/**
		 * Update path configuration
		 * @return CLI_Build
		 */
		private function _path()
		{
			// Full web path
			$full_web_path = $this->_get_input("Full URL used to access this site? ex: http://framework.com/dashboard/");
			echo console_text("UPDATING FULL WEB PATH", 'green');
			$this->_set_config_variable('Path', 'full_web_path', $full_web_path);
			echo console_text("...SUCCESS", 'green');
			
			// Parse out relative web path
			echo console_text("PARSING WEB PATH", 'green');
			$url_parts = parse_url($full_web_path);
			$web_path = '/';
			if(isset($url_parts['path']))
			{
				$web_path = str_replace("//", '/', "/".trim($url_parts['path'], '/')."/");
			}
			echo console_text("UPDATING WEB PATH", 'green');
			$this->_set_config_variable('Path', 'web_path', $web_path);
			echo console_text("...SUCCESS", 'green');

			// Application path
			echo console_text("PARSING APPLICATION PATH", 'green');
			$path = str_replace('system/cli/Build', '', __DIR__);
			echo console_text("...SUCCESS", 'green');
			echo console_text("UPDATING APPLICATION PATH", 'green');
			$this->_set_config_variable('Path', 'application_path', $path);
			echo console_text("...SUCCESS", 'green');
			
			return $this;
		}


		
		/**
		 * Write a value to a config file
		 * @param string $config_name
		 * @param string $variable_name
		 * @param string $value
		 * @return CLI_Build
		 */
		private function _set_config_variable($config_name, $variable_name, $value)
		{
			// Look for the variable in the config file
			// Assumes format public $VAR_NAME	= '';
			// Single or double quotes will match, single quotes will be written
			$pattern = '#public\s{0,}?\$'.$variable_name.'\s{0,}?=\s{0,}?[\'"](.*)?[\'"];#Usi';
			if(preg_match($pattern, $this->_config_file_to_string($config_name), $m))
			{
				// Make the new variable declaration (somewhat complicated to keep spacing lined up)
				list($set) = explode('=', $m[0]);
				$set .= "= '".str_replace("'", '\\'."'", $value)."';"; // Escape single quotes
				
				// Load config file to string
				$config_file_string = $this->_config_file_to_string($config_name);
				
				// Replace previous variable declaration with new
				$config_file_string = str_replace($m[0], $set, $config_file_string);
				
				// Write new config file
				$this->_config_file_write($config_name, $config_file_string);
				
			}else{
				// Variable name not defined in config file
				// Implement new variable logic here
				throw new Exception("Could not get variable \${$variable_name} from {$config_name}.");
			}

			return $this;
		}
		
		
		
		/** 
		 * Get the string contents of a config file
		 * @param string $config_name
		 * @return string
		 */
		private function _config_file_to_string($config_name)
		{
			return file_get_contents($this->_config_file_location($config_name));
		}
		
		
		
		/**
		 * Write a string to a config file
		 * @param string $config_name
		 * @param string $string_contents
		 * @return CLI_Build
		 */
		private function _config_file_write($config_name, $string_contents)
		{
			$location = $this->_config_file_location($config_name);
			if(file_put_contents($location, $string_contents) === FALSE)
			{
				throw new Exception("Failed to write config file {$config_name}.");
			}
			
			return $this;
		}
		
		

		/**
		 * Get the string path to a config file
		 * @param string $config_name
		 * @return string
		 */
		private function _config_file_location($config_name)
		{
			$location = str_replace('/system/cli/Build', '', __DIR__) . "/framework/config/{$config_name}.php";
			if(file_exists($location))
			{
				if(is_writable($location))
				{
					return $location;
				}
				throw new Exception("Config file {$config_name} is not writable.");
			}
			throw new Exception("Config file {$config_name} does not exist.");
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
				echo console_text($prompt_string, 'red');
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