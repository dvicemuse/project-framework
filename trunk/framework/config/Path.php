<?php
/**
 * @file Path.php
 * @package    Itul.Framework.Config
 *
 * @copyright  Copyright (C) 1999 - 2012 i-Tul Design and Software, Inc. All rights reserved.
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
	public $web_path			= "";
	
	/**
	 * @var string $full_web_path
	 * @brief The full web path of the application. Includes protocol, port and domain name
	 *
	 * @since  1.0.0
	 */
	public $full_web_path		= "http://192.168.1.104/";
	
	/**
	 * @var string $application_path
	 * @brief The file system path for the application.
	 * 
	 * @since  1.0.0
	 */
	public $application_path	= "/var/www/";
	
	/**
	 * @var string $log_in_controller
	 * @brief The controller responsible for login verification/authentication. Should not be changed unless you know what the consequences are.
	 * 
	 * @since  1.0.0
	 */
	public $log_in_controller	= "dashboard";
}
