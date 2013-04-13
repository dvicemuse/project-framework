<?php
/**
 * @file Locale.php
 * @package    ProjectFramework.Config
 *
 * @license    see LICENSE.txt
 */

/**
 * @class Locale_Config
 * @brief Locale Config class. Settings used by framework to set user locale options
 *
 * @since    1.0.0
 */
class Locale_Config
{
	/**
	 * @var string $time_zones
	 * @brief Time zones available in the framework
	 *
	 * @since  1.0.0
	 */
	public $time_zones		= array(
		'Pacific/Honolulu'		=> 'Hawaii',
		'America/Anchorage'		=> 'Alaska',
		'America/Los_Angeles'	=> 'Pacific Time',
		'America/Phoenix'		=> 'Arizona',
		'America/Boise'			=> 'Mountain Time',
		'America/Chicago'		=> 'Central Time',
		'America/Indianapolis'	=> 'Indiana (East)',
		'America/New_York'		=> 'Eastern Time',
	);
}
