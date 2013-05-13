<?php

	/**
	 * Hello memcache
	 */
	class Db_Override extends Db
	{
		private $_use_memcache = FALSE;
		private $_conn_memcache = NULL;



		/**
		 * Construct
		 */
		public function __construct()
		{
			parent::__construct();
		}



		/**
		 * Make sure the right object type is returned as singleton
		 * Relying on DB::Instance() will return object Db
		 * @return object
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
		 * Connect to memcache
		 * @return Memcache
		 */
		private function _connect_memcache()
		{
			if($this->_conn_memcache === NULL)
			{
				// Start connection
				$this->_conn_memcache = new Memcache();
				$this->_conn_memcache->pconnect($this->config()->memcache->host, $this->config()->memcache->port);
			}

			return $this->_conn_memcache;	
		}



		/**
		 * get_row wrapper
		 */
		public function get_row($query)
		{
			// Calculate query hash
			$query_hash = md5($query);

			// Check memcache
			$check_cache = $this->_connect_memcache()->get($query_hash);

			// Check result
			if(!$check_cache)
			{
				// Not in cache, call get_row
				$result = parent::get_row($query);
				
				// Add to cache
				$this->_connect_memcache()->set($query_hash, $result, 0, $this->config()->memcache->cache_time);
				
				// Return
				return $result;
			}
			
			// Return cache result
			return $check_cache;
		}



		/**
		 * get_rows wrapper
		 */
		public function get_rows($query)
		{
			// Randomized query fix
			if(strpos($query, 'RAND()') !== FALSE){ return parent::get_rows($query); }
			
			// Calculate query hash
			$query_hash = md5($query);

			// Check memcache
			$check_cache = $this->_connect_memcache()->get($query_hash);

			// Check result
			if(!$check_cache)
			{
				// Not in cache, call get_row
				$result = parent::get_rows($query);
				
				// Add to cache
				$this->_connect_memcache()->set($query_hash, $result, 0, $this->config()->memcache->cache_time);
				
				// Return
				return $result;
			}
			
			// Return cache result
			return $check_cache;
		}






	}

?>