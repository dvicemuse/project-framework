<?php
/**
 * @file Mail.php
 * @package    ProjectFramework.Config
 *
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
	public $no_reply_name		= 'no-reply';
	
	/**
	 * @var string $no_reply_address	
	 * @brief The no reply email address of system emails.
	 *
	 * @since  1.0.0
	 */
	public $no_reply_address	= 'no-reply@podcastrocket.com';

	
}