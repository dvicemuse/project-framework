<?php

// Include the SimpleTest unit test library
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(str_replace("_tests", "", dirname(__FILE__)) . '/framework/Framework.php');

// Start the framework (to include system classes)
$f = new Framework();

// Test class
class Modelbasetest123 extends Model_Base {
	function __construct() {
		// Connect to the database
		$this->load_helper('Db');
		// Create the test table
		mysql_query("DROP TABLE IF EXISTS `modelbasetest123`;");
		mysql_query("CREATE TABLE  `modelbasetest123` (`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , `group` INT( 10 ) UNSIGNED NOT NULL , `text` VARCHAR( 255 ) NOT NULL , `date` DATETIME NOT NULL) ENGINE = MYISAM ;") or die(mysql_error());
		// Insert test data
		$this->Db->insert('modelbasetest123', array('group' => '1', 'text' => 'Apple', 'date' => '2010-12-3 5:00:00'));
		$this->Db->insert('modelbasetest123', array('group' => '1', 'text' => 'Orange', 'date' => '2010-12-3 7:00:00'));
		$this->Db->insert('modelbasetest123', array('group' => '2', 'text' => 'Grape', 'date' => '2010-12-6 8:00:00'));
		$this->Db->insert('modelbasetest123', array('group' => '2', 'text' => 'Banana', 'date' => '2010-12-7 3:00:00'));
		$this->Db->insert('modelbasetest123', array('group' => '2', 'text' => 'Mango', 'date' => '2010-12-8 5:00:00'));
		$this->Db->insert('modelbasetest123', array('group' => '3', 'text' => 'Pear', 'date' => '2010-12-9 7:00:00'));
		$this->Db->insert('modelbasetest123', array('group' => '4', 'text' => 'Orange', 'date' => '2010-12-9 8:00:00'));
		$this->Db->insert('modelbasetest123', array('group' => '5', 'text' => 'Grape', 'date' => '2010-12-16 9:00:00'));
	}
	function  __destruct() {
		// Clean up
		mysql_query("DROP TABLE `modelbasetest123`");
	}
}

// Set up the unit test class
class Model_Base_Test extends UnitTestCase
{

	
	// Test Model_Base->get()
	function testGet()
	{
		$t = new Modelbasetest123;

		// Raw get
		$result = $t->get()->results();
		// Check for 8 results
		$this->assertEqual(count($result), 8);

		// Get w/int ID
		$info = $t->get(2)->result();
		$this->assertEqual($info['id'], 2);

		// Get w/string ID
		$info = $t->get('2')->result();
		$this->assertEqual($info['id'], 2);

		// Get w/ invalid string ID
		$info = $t->get('2fake')->result();
		$this->assertEqual($info, FALSE);
	}


	// Test Model_Base->where()
	function testWhere()
	{
		$t = new Modelbasetest123;

		// Single row string
		$result = $t->where('id', '1')->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 1);

		// Single row int
		$result = $t->where('id', 1)->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 1);

		// Multiple rows
		$result = $t->where('text', 'Orange')->get()->results();
		$this->assertEqual(count($result), 2);
		$this->assertEqual($result[0]['id'], 2);
		$this->assertEqual($result[1]['id'], 7);

		// Multiple rows failure
		$result = $t->where('id', 'fake')->get()->results();
		$this->assertEqual($result, FALSE);

		// Single row failure
		$result = $t->where('id', 'fake')->get()->result();
		$this->assertEqual($result, FALSE);

		// Multiple where
		$result = $t->where('group', 4)->where('text', 'Orange')->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 7);

		// Bad column name
	    try {
	        $result = $t->where('fake', 'value')->get();
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
	}



	function testLike()
	{
		$t = new Modelbasetest123;

		// Single row string
		$result = $t->like('id', '1')->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 1);

		// Single row int
		$result = $t->like('id', 1)->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 1);

		// Multiple rows wildcard end
		$result = $t->like('text', 'Oran%')->get()->results();
		$this->assertEqual(count($result), 2);
		$this->assertEqual($result[0]['id'], 2);
		$this->assertEqual($result[1]['id'], 7);

		// Multiple rows wildcard start
		$result = $t->like('text', '%ange')->get()->results();
		$this->assertEqual(count($result), 2);
		$this->assertEqual($result[0]['id'], 2);
		$this->assertEqual($result[1]['id'], 7);

		// Multiple rows super wildcard
		$result = $t->like('text', '%ng%')->get()->results();
		$this->assertEqual(count($result), 3);
		$this->assertEqual($result[0]['id'], 2);
		$this->assertEqual($result[1]['text'], 'Mango');

		// Multiple rows failure
		$result = $t->like('id', 'fake')->get()->results();
		$this->assertEqual($result, FALSE);

		// Single row failure
		$result = $t->like('id', 'fake')->get()->result();
		$this->assertEqual($result, FALSE);

		// Multiple like
		$result = $t->like('group', 4)->like('text', 'Orange')->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 7);

		// Bad column name
	    try {
	        $result = $t->like('fake', 'value')->get();
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
	}

	

	// Test Model_Base->where()
	function testOrder()
	{
		$t = new Modelbasetest123;

		// Single order
		$result = $t->order('id', 'desc')->get()->results();
		$this->assertEqual(count($result), 8);
		$this->assertEqual($result[0]['id'], 8);

		// Multiple order
		$result = $t->order('text', 'ASC')->order('group', 'desc')->get()->results();
		$this->assertEqual(count($result), 8);
		$this->assertEqual($result[3]['id'], 3);

		// Multiple order + where
		$result = $t->where('text', 'orange')->order('text', 'ASC')->order('group', 'desc')->get()->results();
		$this->assertEqual(count($result), 2);
		$this->assertEqual($result[1]['id'], 2);

		// Sort column does not exist
	    try {
	        $result = $t->order('fake', 'asc')->get();
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}

		// Sort order is invalid
	    try {
	        $result = $t->order('text', 'fake')->get();
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
	}
	


	// Test Model_Base->limit()
	function testLimit()
	{
		$t = new Modelbasetest123;

		// Simple limit
		$result = $t->limit(1)->order('id', 'asc')->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 1);

		// Simple limit string
		$result = $t->limit('1')->order('id', 'asc')->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 1);

		// Limit with offset
		$result = $t->limit(1, 4)->order('id', 'asc')->get()->results();
		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['id'], 5);

		// Limit multiple rows with offset
		$result = $t->limit(3, 4)->order('id', 'asc')->get()->results();
		$this->assertEqual(count($result), 3);
		$this->assertEqual($result[2]['id'], 7);

		// Limit of 0	
	    try {
	        $result = $t->limit(0)->get()->results();
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}

		// Alpha limit
	    try {
	        $result = $t->limit('abc')->get();
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}

		// Alpha offset
	    try {
	        $result = $t->limit('4', 'abc')->get();
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
	}
	
	
}

?>
