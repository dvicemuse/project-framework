<?php
/**
 * @file Mode.php
 * @package    ProjectFramework.Config
 *
 * @license    see LICENSE.txt
 */

/**
 * @class Mode_Config
 * @brief Mode Config class. Settings used for production vs. development environments.
 *
 * @since    1.0.0
 */
	class Mode_Config
	{
		public $current = NULL;
		
		public function __construct()
		{
			// Current file directory
			$current_directory = __DIR__;
			
			// Remove the path to the CLI folder from the end
			$framework_directory = trim(str_replace('/framework/config', '', $current_directory), '/');

			// Get all of the directory parts
			$directories = explode('/', $framework_directory);
			
			// Go one level below the framework
			array_pop($directories);
			array_pop($directories);
			
			// Glue directories back together
			$this->current = is_dir(str_replace('//', '/', "/".implode('/', $directories)."/dev/")) ? 'dev' : 'prod';
			
			// Return
			return $this;
		}
	}

?>