<?php
/**
 * @file Path.php
 * @package    ProjectFramework.Config
 *
 * @license    see LICENSE.txt
 */

/**
 * @class Path_Config
 * @brief Path Config class. Settings used by framework to locate/set system file paths and urls. Remember to include trailing slashes on all paths
 *
 * @since    1.0.0
 */
class Path_Config
{
	/**
	 * @var string $web_path
	 * @brief The base web accessible path of the application. If framework is in a subdirectory of the site then this should include that. 
	 * Should not include protocol, port or domain.
	 *
	 * @since  1.0.0
	 */
	public $web_path			= '';
	
	/**
	 * @var string $full_web_path
	 * @brief The full web path of the application. Includes protocol, port and domain name
	 *
	 * @since  1.0.0
	 */
	public $full_web_path		= '';
	
	/**
	 * @var string $application_path
	 * @brief The file system path for the application.
	 * 
	 * @since  1.0.0
	 */
	public $application_path	= '';
}
