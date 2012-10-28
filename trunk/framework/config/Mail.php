<?php
/**
 * @file Mail.php
 * @package    Itul.Framework.Config
 *
 * @copyright  Copyright (C) 1999 - 2012 i-Tul Design and Software, Inc. All rights reserved.
 * @license    see LICENSE.txt
 */

/**
 * @class Mail_Config
 * @brief Mail Config class. Settings use for system mail responses.
 *
 * @since    1.0.0
 */
class Mail_Config
{
	/**
	 * @var string $no_reply_name
	 * @brief The no reply name displayed to recipients of system email.
	 *
	 * @since  1.0.0
	 */
	public $no_reply_name		= "Mail Robot";
	
	/**
	 * @var string $no_reply_address	
	 * @brief The no reply email address of system emails.
	 *
	 * @since  1.0.0
	 */
	public $no_reply_address	= "no-reply@site.com";

	
}