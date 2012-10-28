<?php
/**
 * @file Db.php
 * @package    Itul.Framework.Config
 *
 * @copyright  Copyright (C) 1999 - 2012 i-Tul Design and Software, Inc. All rights reserved.
 * @license    see LICENSE.txt
 */

/**
 * @class Db_Config
 * @brief Database Config class. Stores the database credentials used to access the tables needed for the framework.
 * These should be changed before running any cli functions.
 *
 * @since    1.0.0
 */
class Db_Config
{
	/**
	 * @var string $host
	 * @brief The hostname for the database server.
	 * 
	 * @since  1.0.0
	 */
	public $host		= 'localhost';
	
	/**
	 * @var string $username
	 * @brief The username for accessing the database.
	 * 
	 * @since  1.0.0
	 */
	public $username	= 'root';
	
	/**
	 * @var string $password
	 * @brief The password for the user.
	 * 
	 * @since  1.0.0
	 */
	public $password	= 'password';
	
	/**
	 * @var string $database
	 * @brief The database name for the framework tables.
	 * 
	 * @since  1.0.0
	 */
	public $database	= 'framework';
	
}