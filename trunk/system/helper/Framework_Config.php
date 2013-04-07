<?php

	class Framework_Config
	{
		static private $_instance = NULL; // Singleton instance tracker
		private $_data = NULL;
		


		/**
		 * Db class is a singleton
		 * @return Framework_Config
		 */
		public static function & Instance()
		{
			if(is_null(self::$_instance))
			{
				self::$_instance = new self();
			}
			return self::$_instance;
		}



		/**
		 * Auto load the config info
		 * @return Framework_Config
		 */
		public function load()
		{
			// Load check
			if($this->_data !== NULL)
			{
				return $this->_data;
			}
			
			// Set data property
			$this->_data = new Config_Builder;
			
			// Mode
			$mode = 'prod';
			
			// Dev check
			$framework_directory = str_replace('/system/helper', '', __DIR__);
			
			// Get all of the directory parts
			$directories = explode('/', $framework_directory);
			
			// Go one level below the framework
			array_pop($directories);
			
			// Glue directories back together
			$dev_folder_location = str_replace('//', '/', "/".implode('/', $directories)."/dev/");
			
			// Dev check
			if(is_dir($dev_folder_location))
			{
				$mode = 'dev';
			}

			// Include configuration classes
			$handle = opendir(substr(__DIR__, 0, -14)."/framework/config/{$mode}/");
			while(false !== ($file = readdir($handle)))
			{
				if(trim($file, '.') != '' && substr($file, -4) == '.php')
				{
					include_once(substr(__DIR__, 0, -14)."/framework/config/{$mode}/".$file);
					$config_class_base_name = strtolower(substr($file, 0, -4));
					$config_class_name = substr($file, 0, -4)."_Config";
					
					// Initialize class
					$tmp = new $config_class_name;

					// Get vars
					$class_vars = get_object_vars($tmp);
					if(is_array($class_vars))
					{
						if(empty($config->$config_class_base_name))
						{
							$this->_data->$config_class_base_name = new Config_Builder;
						}
						foreach($class_vars as $name => $value)
						{
							$this->_data->$config_class_base_name->$name = $value;
						}
					}
				}
			}
			return $this->_data;
		}
		
		
		
	}

?>