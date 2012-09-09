<?php

	class Framework_Config
	{
		static private $_instance = null; // Singleton instance tracker



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
			// Include configuration classes
			$handle = opendir(substr(__DIR__, 0, -14)."/framework/config/");
			while(false !== ($file = readdir($handle)))
			{
				if(trim($file, '.') != '' && substr($file, -4) == '.php')
				{
					include_once(substr(__DIR__, 0, -14)."/framework/config/".$file);
					$config_class_base_name = strtolower(substr($file, 0, -4));
					$config_class_name = substr($file, 0, -4)."_Config";
	
					$class_vars = get_class_vars($config_class_name);
					if(is_array($class_vars))
					{
						if(empty($this->$config_class_base_name))
						{
							$this->$config_class_base_name = new Config_Builder;
						}
						foreach($class_vars as $name => $value)
						{
							$this->$config_class_base_name->$name = $value;
						}
					}
				}
			}
			return $this;
		}
		
		
		
	}

?>