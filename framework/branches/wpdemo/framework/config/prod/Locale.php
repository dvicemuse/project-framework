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
		'America/New_York'		=> 'Eastern Time',
	);



	/**
	 * @var string $states
	 * @brief States available in the framework
	 *
	 * @since  1.0.0
	 */
	public $states = array (
			'AL' => 'ALABAMA',
			'AK' => 'ALASKA',
			'AZ' => 'ARIZONA',
			'AR' => 'ARKANSAS',
			'CA' => 'CALIFORNIA',
			'CO' => 'COLORADO',
			'CT' => 'CONNECTICUT',
			'DE' => 'DELAWARE',
			'FL' => 'FLORIDA',
			'GA' => 'GEORGIA',
			'GU' => 'GUAM',
			'HI' => 'HAWAII',
			'ID' => 'IDAHO',
			'IL' => 'ILLINOIS',
			'IN' => 'INDIANA',
			'IA' => 'IOWA',
			'KS' => 'KANSAS',
			'KY' => 'KENTUCKY',
			'LA' => 'LOUISIANA',
			'ME' => 'MAINE',
			'MD' => 'MARYLAND',
			'MA' => 'MASSACHUSETTS',
			'MI' => 'MICHIGAN',
			'MN' => 'MINNESOTA',
			'MS' => 'MISSISSIPPI',
			'MO' => 'MISSOURI',
			'MT' => 'MONTANA',
			'NE' => 'NEBRASKA',
			'NV' => 'NEVADA',
			'NH' => 'NEW HAMPSHIRE',
			'NJ' => 'NEW JERSEY',
			'NM' => 'NEW MEXICO',
			'NY' => 'NEW YORK',
			'NC' => 'NORTH CAROLINA',
			'ND' => 'NORTH DAKOTA',
			'OH' => 'OHIO',
			'OK' => 'OKLAHOMA',
			'OR' => 'OREGON',
			'PW' => 'PALAU',
			'PA' => 'PENNSYLVANIA',
			'PR' => 'PUERTO RICO',
			'RI' => 'RHODE ISLAND',
			'SC' => 'SOUTH CAROLINA',
			'SD' => 'SOUTH DAKOTA',
			'TN' => 'TENNESSEE',
			'TX' => 'TEXAS',
			'UT' => 'UTAH',
			'VT' => 'VERMONT',
			'VI' => 'VIRGIN ISLANDS',
			'VA' => 'VIRGINIA',
			'WA' => 'WASHINGTON',
			'WV' => 'WEST VIRGINIA',
			'WI' => 'WISCONSIN',
			'WY' => 'WYOMING',
			);



}
