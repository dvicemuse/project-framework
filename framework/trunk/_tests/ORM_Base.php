<?php

// Include the SimpleTest unit test library
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(str_replace("_tests", "", dirname(__FILE__)) . '/system/Framework.php');

// Start the framework (to include system classes)
$f = new Framework();

// Test class
class User123 extends Model_Base
{
	function __construct()
	{
		parent::__construct();
		
		// Create the test table
		$this->load_helper('Db');
		mysql_query("DROP TABLE IF EXISTS `user123`;");
		mysql_query("
			CREATE TABLE IF NOT EXISTS `user123` (
			`user123_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`user123_name` varchar(255) DEFAULT NULL,
			`user123_birth_date` date DEFAULT NULL,
			PRIMARY KEY (`user123_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
		") or die(mysql_error());
		mysql_query("
			INSERT INTO
				`user123`
				(`user123_id`, `user123_name`, `user123_birth_date`)
			VALUES
				(1, 'User 1', '2010-10-10'),
				(2, 'User 2', '2010-01-01')
		") or die(mysql_error());
		
		// Set up transformation
  		$this->orm_transform('user123_birth_date', '_set_date', '_get_date');
	}



	protected function _validate()
	{
		return array(
			'user123_name' => array('reqd' => 'Required'),
			'user123_birth_date' => array('reqd' => 'Required', 'date' => 'Invalid date'),
		);
	}



	protected function _set_date($value)
	{
		if(strtotime($value) !== FALSE)
		{
			return date('Y-m-d', strtotime($value));
		}
		return NULL;
	}
	
	
	
	protected function _get_date($value)
	{
		if(strtotime($value) !== FALSE)
		{
			return date('m/d/Y', strtotime($value));
		}
		return NULL;
	}

	
	
	function  __destruct()
	{
		// Clean up
		mysql_query("DROP TABLE `user123`");
	}
}



// Set up the unit test class
class ORM_Base_Test extends UnitTestCase
{
	function testORM_Transform()
	{
		$u = new User123;
		$u->orm_load(1);
		
		// Transform is being done on orm_load YYY-MM-DD -> MM/DD/YYYY
		$this->assertEqual('10/10/2010', $u->birth_date());
		
		// Set a new birth date
		$u->set_birth_date('1/1/2000');

		// Save birth date
		$u->orm_save();
		
		// Check for new date
		$u->orm_load(1);
		$this->assertEqual('01/01/2000', $u->birth_date());
	}


}

?>