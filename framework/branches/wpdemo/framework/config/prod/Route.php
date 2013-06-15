<?php
/**
 * @file Route.php
 * @package    ProjectFramework.Config
 *
 * @license    see LICENSE.txt
 */

/**
 * @class Route_Config
 * @brief Routing Config class. Regular expressions used to route requests to intended targets.
 *
 * @since    1.0.0
 */
class Route_Config
{
	/**
	 * @var array $base
	 * @brief The base routing expressions used by the framework. Format of the routing expression is also an array with regular expression as the index and params of 
	 * 'controller'=>'NameOfController' or 'controller'=> # (where # is numeric group in the regular expression)
	 * 'method'=> 'NameOfMethod' or 'method'=> # (where # is numeric group in the regular expression)
	 * NameOfController and NameOfMethod must be the lowercase strings of the controller class and method within the class
	 *
	 * @since  1.0.0
	 */
	public $base = array(
		/**
		 * @brief routing expression for Home page
		 * @since  1.0.0
		 */
		'#^//$#D' => array('controller' => 'Home', 'method' => 'index'),

		/**
		 * @brief routing expression for method request - /controllername/methodname/
		 * @since  1.0.0
		 */
		'#/(.*?)/(.*?)/#' => array('controller' => 1, 'method' => 2),

		/**
		 * @brief routing expression for index - /controllername/ 
		 * @since  1.0.0
		 */
		'#/(.*?)/#' => array('controller' => 1, 'method' => 'index'),
	);
}
