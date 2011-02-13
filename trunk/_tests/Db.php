<?php

// Include the SimpleTest unit test library
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(str_replace("_tests", "", dirname(__FILE__)) . '/framework/Framework.php');

// Start the framework (to include system classes)
$f = new Framework();

// Set up the unit test class
class Model_Base_Test extends UnitTestCase
{
	/**
	 * Insert test data
	 */
	function __construct()
	{
		$f = new Framework();
		$f->load_helper('Db');
		mysql_query("DROP TABLE IF EXISTS `modelbasetest123`;");
		mysql_query("CREATE TABLE  `modelbasetest123` (`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , `group` INT( 10 ) UNSIGNED NOT NULL , `text` VARCHAR( 255 ) NOT NULL , `date` DATETIME NOT NULL) ENGINE = MYISAM ;") or die(mysql_error());
		$f->Db->insert('modelbasetest123', array('group' => '1', 'text' => 'Apple', 'date' => '2010-12-3 5:00:00'));
		$f->Db->insert('modelbasetest123', array('group' => '1', 'text' => 'Orange', 'date' => '2010-12-3 7:00:00'));
		$f->Db->insert('modelbasetest123', array('group' => '2', 'text' => 'Grape', 'date' => '2010-12-6 8:00:00'));
		$f->Db->insert('modelbasetest123', array('group' => '2', 'text' => 'Banana', 'date' => '2010-12-7 3:00:00'));
		$f->Db->insert('modelbasetest123', array('group' => '2', 'text' => 'Mango', 'date' => '2010-12-8 5:00:00'));
		$f->Db->insert('modelbasetest123', array('group' => '3', 'text' => 'Pear', 'date' => '2010-12-9 7:00:00'));
		$f->Db->insert('modelbasetest123', array('group' => '4', 'text' => 'Orange', 'date' => '2010-12-9 8:00:00'));
		$f->Db->insert('modelbasetest123', array('group' => '5', 'text' => 'Grape', 'date' => '2010-12-16 9:00:00'));
	}



	/**
	 * Clean up
	 */
	function  __destruct()
	{
		mysql_query("DROP TABLE `modelbasetest123`");
	}



	// Test Db->query()
	function testQuery()
	{
		$t = new Framework();
		$t->load_helper('Db');

		// Simple query
		$result = $t->Db->query("SELECT * FROM modelbasetest123");
		$this->assertEqual($result, TRUE);

		// Make sure it saves the result
		$this->assertEqual(mysql_num_rows($t->Db->q), 8);

		// Bad query
	    try {
	        $result = $t->Db->query("FAKE QUERY");
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
	}



	// Test Db->get_row()
	function testRow()
	{
		$t = new Framework();
		$t->load_helper('Db');

		// Result returned
		$result = $t->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 1 LIMIT 1");
		$this->assertEqual(is_array($result), TRUE);
		$this->assertEqual($result['id'], 1);

		// No result, bool FALSE returned
		$result = $t->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 300 LIMIT 1");
		$this->assertIdentical($result, FALSE);
	}



	// Test Db->get_rows()
	function testRows()
	{
		$t = new Framework();
		$t->load_helper('Db');

		// Results returned
		$result = $t->Db->get_rows("SELECT * FROM modelbasetest123 WHERE `group` = 1 ORDER BY id ASC");
		$this->assertEqual(is_array($result), TRUE);
		$this->assertEqual($result[0]['text'], 'Apple');
		$this->assertEqual($result[1]['text'], 'Orange');

		// No result, bool FALSE returned
		$result = $t->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 300");
		$this->assertIdentical($result, FALSE);
	}


	
	// Test Db->get_rows()
	function testColumnNames()
	{
		$t = new Framework();
		$t->load_helper('Db');

		// Table exists
		$columns = $t->Db->column_names('modelbasetest123');
		$this->assertEqual(is_array($columns), TRUE);
		$this->assertEqual($columns[0], 'id');
		$this->assertEqual($columns[3], 'date');

		// Table does not exist
	    try {
	        $columns = $t->Db->column_names('FAKE_TABLE');
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
	}





	
}

?>