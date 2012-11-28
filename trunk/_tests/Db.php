<?php

// Include the SimpleTest unit test library
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(str_replace("_tests", "", dirname(__FILE__)) . '/system/Framework.php');

// Start the framework (to include system classes)
$f = new Framework();

function reset_data($Db)
{
	mysql_query("DROP TABLE IF EXISTS `modelbasetest123`;");
	mysql_query("CREATE TABLE  `modelbasetest123` (`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , `group` INT( 10 ) UNSIGNED NOT NULL , `text` VARCHAR( 255 ) NULL , `datetime` DATETIME NOT NULL, `date` DATE NOT NULL) ENGINE = MYISAM ;") or die(mysql_error());
	mysql_query("INSERT INTO `modelbasetest123` SET `group` = '1',  `text` = 'Apple',  `datetime` = '2010-12-3 5:00:00',  `date` = '2010-12-03' ");
	mysql_query("INSERT INTO `modelbasetest123` SET `group` = '1',  `text` = 'Orange',  `datetime` = '2010-12-3 5:00:00',  `date` = '2010-12-03' ");
	mysql_query("INSERT INTO `modelbasetest123` SET `group` = '2',  `text` = 'Grape',  `datetime` = '2010-12-3 5:00:00',  `date` = '2010-12-06' ");
	mysql_query("INSERT INTO `modelbasetest123` SET `group` = '2',  `text` = 'Banana',  `datetime` = '2010-12-3 5:00:00',  `date` = '2010-12-07' ");
	mysql_query("INSERT INTO `modelbasetest123` SET `group` = '2',  `text` = 'Mango',  `datetime` = '2010-12-3 5:00:00',  `date` = '2010-12-08' ");
	mysql_query("INSERT INTO `modelbasetest123` SET `group` = '3',  `text` = 'Pear',  `datetime` = '2010-12-3 5:00:00',  `date` = '2010-12-09' ");
	mysql_query("INSERT INTO `modelbasetest123` SET `group` = '4',  `text` = 'Orange',  `datetime` = '2010-12-3 5:00:00',  `date` = '2010-12-09' ");
	mysql_query("INSERT INTO `modelbasetest123` SET `group` = '5',  `text` = 'Grape',  `datetime` = '2010-12-3 5:00:00',  `date` = '2010-12-16' ");
}

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
		
		reset_data($f->Db);
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


	
	// Test Db->column_names()
	function testColumnNames()
	{
		$t = new Framework();
		$t->load_helper('Db');

		// Table exists
		$columns = $t->Db->column_names('modelbasetest123');
		$this->assertEqual(is_array($columns), TRUE);
		$this->assertEqual($columns[0], 'id');
		$this->assertEqual($columns[3], 'datetime');

		// Table does not exist
	    try {
	        $columns = $t->Db->column_names('FAKE_TABLE');
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
	}



	// Test Db->column_exists()
	function testColumnExists()
	{
		$t = new Framework();
		$t->load_helper('Db');

		// Table and column exists
		$columns = $t->Db->column_exists('modelbasetest123', 'id');
		$this->assertEqual($columns, TRUE);

		// Table exists and column does not exist
		$columns = $t->Db->column_exists('modelbasetest123', 'FAKE');
		$this->assertEqual($columns, FALSE);

		// Table does not exist
	    try {
	        $columns = $t->Db->column_names('FAKE_TABLE', 'id');
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
	}



	// Test Db->update()
	function testUpdate()
	{
		$t = new Framework();
		$this->Db = $t->load_helper('Db');
		
		// Invalid table name
	    try {
	        $columns = $this->Db->update('FAKE', array(), 'id = 1');
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}

		// Data array
	    try {
	        $columns = $this->Db->update('modelbasetest123', '', 'id = 1');
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
		
		// No where clause assignment
	    try {
	        $columns = $this->Db->update('modelbasetest123', array(), '');
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
		
		// Normal field update
		$this->Db->update('modelbasetest123', array('text' => 'NEW'), 'id = 1');
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 1");
		$this->assertEqual($row['text'], 'NEW');
		
		// NULL passed as field value
		$this->Db->update('modelbasetest123', array('text' => NULL), 'id = 1');
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 1");
		$this->assertNull($row['text']);

		// Empty string passed to nullable field
		$this->Db->update('modelbasetest123', array('text' => ''), 'id = 1');
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 1");
		$this->assertNull($row['text']);

		// NULL passed to non nullable field
		$return_id = $this->Db->update('modelbasetest123', array('group' => NULL), 'id = 1');
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 1 ");
		$this->assertEqual($row['group'], 0);

		// Date field
		$this->Db->update('modelbasetest123', array('date' => '4/15/1956'), 'id = 1');
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 1");
		$this->assertEqual($row['date'], '1956-04-15');

		// Date field invalid NULL not allowed
		$this->Db->update('modelbasetest123', array('date' => '100/100/1000'), 'id = 1');
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 1");
		$this->assertEqual($row['date'], '0000-00-00');

		// Date field invalid NULL allowed
		$this->Db->query('ALTER TABLE  `modelbasetest123` CHANGE  `date`  `date` DATE NULL DEFAULT NULL');
		$this->Db->table_info('modelbasetest123', TRUE); // Clear table info cache
		$this->Db->update('modelbasetest123', array('date' => '100/100/1000'), 'id = 1');
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = 1");
		$this->assertEqual($row['date'], NULL);
		
		// Clean data for next set of tests
		reset_data($this->Db);
		$this->Db->table_info('modelbasetest123', TRUE); // Clear table info cache
	}



	// Test Db->insert()
	function testInsert()
	{
		$t = new Framework();
		$this->Db = $t->load_helper('Db');

		// Data array
	    try {
	        $columns = $this->Db->insert('modelbasetest123', '');
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
		
		// Invalid table name
	    try {
	        $columns = $this->Db->insert('FAKE', array());
	        $this->fail("Exception was expected.");
	    } catch (Exception $e) {
	        $this->pass();
		}
		
		// ID return value and normal field insert
		$return_id = $this->Db->insert('modelbasetest123', array('text' => 'NEW'));
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = '{$return_id}' ");
		$this->assertEqual($row['text'], 'NEW');
		
		// NULL passed as field value
		$return_id = $this->Db->insert('modelbasetest123', array('text' => NULL));
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = '{$return_id}' ");
		$this->assertNull($row['text']);

		// Empty string passed to nullable field
		$return_id = $this->Db->insert('modelbasetest123', array('text' => ''));
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = '{$return_id}' ");
		$this->assertNull($row['text']);

		// NULL passed to non nullable field
		$return_id = $this->Db->insert('modelbasetest123', array('group' => NULL));
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = '{$return_id}' ");
		$this->assertEqual($row['group'], 0);

		// Date field
		$return_id = $this->Db->insert('modelbasetest123', array('date' => '4/15/1956'));
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = '{$return_id}' ");
		$this->assertEqual($row['date'], '1956-04-15');

		// Date field invalid, NULL not allowed
		$return_id = $this->Db->insert('modelbasetest123', array('date' => '100/100/1000'));
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = '{$return_id}' ");
		$this->assertEqual($row['date'], '0000-00-00');

		// Date field invalid NULL allowed
		$this->Db->query('ALTER TABLE  `modelbasetest123` CHANGE  `date`  `date` DATE NULL DEFAULT NULL');
		$this->Db->table_info('modelbasetest123', TRUE); // Clear table info cache
		$return_id = $this->Db->insert('modelbasetest123', array('date' => '100/100/1000'));
		$row = $this->Db->get_row("SELECT * FROM modelbasetest123 WHERE id = '{$return_id}'");
		$this->assertNull($row['date']);
		
		// Clean data for next set of tests
		reset_data($this->Db);
		$this->Db->table_info('modelbasetest123', TRUE); // Clear table info cache
	}	
}

?>