<?php
/**
 * @file Memcache.php
 * @package    ProjectFramework.Config
 *
 * @license    see LICENSE.txt
 */

/**
 * @class Memcache_Config
 * @brief Memcache Config class.
 *
 * @since    1.0.0
 */
class Memcache_Config
{
	/**
	 * @var string $host
	 * @brief The host address to connect to memcache
	 *
	 * @since  1.0.0
	 */
	public $host		= '';
	
	/**
	 * @var string $port	
	 * @brief The port to connect to memcache
	 *
	 * @since  1.0.0
	 */
	public $port		= '';
	
	/**
	 * @var string $cache_time	
	 * @brief The number of seconds to cache items
	 *
	 * @since  1.0.0
	 */
	public $cache_time	= '600';
	
}