<?php

	class Framework_Route extends Framework
	{
		private $_base_request = '';
		private $_controller = '';
		private $_method = '';
		private $_vars = NULL;
		
		
		public function __construct()
		{
			// The request
			$this->_base_request = $_SERVER['REQUEST_URI'];
			
			// Strip out the base path
			$this->_remove_base_path();
	
			// Loop through defined routes
			if(is_array($this->config()->route->base))
			{
				foreach($this->config()->route->base as $pattern => $route)
				{
					// Pattern matched
					if(preg_match($pattern, $this->_base_request, $m))
					{
						// Quick route definition check
						if(!isset($route['controller']) || !isset($route['method']))
						{
							throw new Exception('Route method not set.');
						}
	
						if($this->is_id($route['controller']))
						{
							$route['controller'] = ucfirst($m[$route['controller']]);
						}
	
						if($this->is_id($route['method']))
						{
							$route['method'] = $m[$route['method']];
						}
	
						// Set route vars
						$this->_method = $route['method'];
						$this->_controller = $route['controller'];
						
						// Return
						return $this;
					}
				}
			}	
		}
		
		
		private function _remove_base_path()
		{
			// Parse the request url
			$web_path = $this->config()->path->web_path;
			if($this->config()->path->web_path == '/')
			{
				$web_path = '';
			}
			
			// Remove the base path @todo make this not use str_replace
			$this->_base_request = str_replace($web_path, '', $this->_base_request);
			
			// Remove leading and trailing slashes, then add back
			$this->_base_request = "/".trim($this->_base_request, '/')."/";
		}
		
		
		
		/** 
		 * Expose the controller
		 */
		public function controller()
		{
			return $this->_controller;
		}



		/** 
		 * Expose the method
		 */
		public function method()
		{
			return $this->_method;
		}



		/** 
		 * Expose the request vars
		 * @todo implement
		 */
		public function vars()
		{
			// Have vars already been processed
			if(is_array($this->_vars))
			{
				return $this->_vars;
			}
			
			// Look for vars in request
			$this->_vars = array();
			$parts = explode('/', trim($this->_base_request, '/'));
			foreach($parts as $part)
			{
				if(preg_match('#([-_0-9A-Za-z]{1,})\:(.*)#', urldecode($part), $m))
				{
					$this->_vars[$m[1]] = $m[2];
				}
			}
			
			return $this->_vars;
		}



		/** 
		 * Expose the request raw array
		 * @return array
		 */
		public function raw()
		{
			return explode('/', trim($this->_base_request, '/'));
		}
	}

?>