<?php

// Include the SimpleTest unit test library
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(str_replace("_tests", "", dirname(__FILE__)) . '/system/Framework.php');

// Start the framework (to include system classes)
$f = new Framework();

// Set up the unit test class
class Validate_Helper_Test extends UnitTestCase
{
	/**
	 * Insert test data
	 */
	function __construct()
	{
		$fw = new Framework();
		$this->v = $fw->load_helper('Validate');
		$this->Db = $fw->load_helper('Db');
	}



	// Test reqd
	function testReqd()
	{
		// Value not set
		$rules	= array('test_field' => array('reqd' => 'Error'));
		$data	= array('test_field' => '');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Value set
		$rules	= array('test_field' => array('reqd' => 'Error'));
		$data	= array('test_field' => 'Value');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test max
	function testMax()
	{
		// Over max length
		$rules	= array('test_field' => array('max[9]' => 'Error'));
		$data	= array('test_field' => '1234567890');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Less than max length
		$rules	= array('test_field' => array('max[100]' => 'Error'));
		$data	= array('test_field' => '1234567890');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Equal to max length
		$rules	= array('test_field' => array('max[10]' => 'Error'));
		$data	= array('test_field' => '1234567890');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test min
	function testMin()
	{
		// Over min length
		$rules	= array('test_field' => array('min[9]' => 'Error'));
		$data	= array('test_field' => '1234567890');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Less than min length
		$rules	= array('test_field' => array('min[100]' => 'Error'));
		$data	= array('test_field' => '1234567890');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Equal to min length
		$rules	= array('test_field' => array('min[10]' => 'Error'));
		$data	= array('test_field' => '1234567890');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test exact
	function testExact()
	{
		// Less than exact length
		$rules	= array('test_field' => array('exact[10]' => 'Error'));
		$data	= array('test_field' => '123456789');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// More than exact length
		$rules	= array('test_field' => array('exact[1]' => 'Error'));
		$data	= array('test_field' => '1234567890');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Equal to exact length
		$rules	= array('test_field' => array('exact[10]' => 'Error'));
		$data	= array('test_field' => '1234567890');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test exact
	function testMatch()
	{
		// Field 1 does not match field 2
		$rules	= array('test_field' => array('match[test_field_2]' => 'Error'));
		$data	= array('test_field' => 'One', 'test_field_2' => 'Two');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Field 1 matches field 2
		$rules	= array('test_field' => array('match[test_field_2]' => 'Error'));
		$data	= array('test_field' => 'Match', 'test_field_2' => 'Match');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Field 2 does not exist
		$rules	= array('test_field' => array('match[test_field_2]' => 'Error'));
		$data	= array('test_field' => 'Match');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');
	}



	// Test alpha
	function testAlpha()
	{
		// Not alpha
		$rules	= array('test_field' => array('alpha' => 'Error'));
		$data	= array('test_field' => 'ABC123');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Alpha
		$rules	= array('test_field' => array('alpha' => 'Error'));
		$data	= array('test_field' => 'ABC');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test numeric
	function testNumeric()
	{
		// Not numeric
		$rules	= array('test_field' => array('numeric' => 'Error'));
		$data	= array('test_field' => 'ABC123');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Numeric
		$rules	= array('test_field' => array('numeric' => 'Error'));
		$data	= array('test_field' => '123');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Numeric (decimal)
		$rules	= array('test_field' => array('numeric' => 'Error'));
		$data	= array('test_field' => '123.123');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Numeric (negative)
		$rules	= array('test_field' => array('numeric' => 'Error'));
		$data	= array('test_field' => '-123');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Numeric (negative decimal)
		$rules	= array('test_field' => array('numeric' => 'Error'));
		$data	= array('test_field' => '-123.894');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test numeric
	function testPositive()
	{
		// Negative
		$rules	= array('test_field' => array('positive' => 'Error'));
		$data	= array('test_field' => -500);
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Zero
		$rules	= array('test_field' => array('positive' => 'Error'));
		$data	= array('test_field' => 0);
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Positive
		$rules	= array('test_field' => array('positive' => 'Error'));
		$data	= array('test_field' => '123.123');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Not a number
		$rules	= array('test_field' => array('positive' => 'Error'));
		$data	= array('test_field' => 'ABC');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test numeric
	function testMoney()
	{
		// Not money
		$rules	= array('test_field' => array('money' => 'Error'));
		$data	= array('test_field' => 'ABC');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Money
		$rules	= array('test_field' => array('money' => 'Error'));
		$data	= array('test_field' => '$5,000.58');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test numeric_thousands
	function testNumericThousands()
	{
		// Not numeric thousands
		$rules	= array('test_field' => array('numeric_thousands' => 'Error'));
		$data	= array('test_field' => 'ABC');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Numeric thousands
		$rules	= array('test_field' => array('numeric_thousands' => 'Error'));
		$data	= array('test_field' => '5,000');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test alphanumeric
	function testAlphanumeric()
	{
		// Not alphanumeric
		$rules	= array('test_field' => array('alphanumeric' => 'Error'));
		$data	= array('test_field' => '$ABC123');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Alphanumeric
		$rules	= array('test_field' => array('alphanumeric' => 'Error'));
		$data	= array('test_field' => 'ABC123');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test no_space
	function testNoSpace()
	{
		// Has spaces
		$rules	= array('test_field' => array('no_space' => 'Error'));
		$data	= array('test_field' => 'ABC 123');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// No spaces
		$rules	= array('test_field' => array('no_space' => 'Error'));
		$data	= array('test_field' => 'ABC123');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test emali
	function testEmail()
	{
		// Not an email
		$rules	= array('test_field' => array('email' => 'Error'));
		$data	= array('test_field' => 'test');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Not an email
		$rules	= array('test_field' => array('email' => 'Error'));
		$data	= array('test_field' => 'test@test');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');

		// Valid email (Simple)
		$rules	= array('test_field' => array('email' => 'Error'));
		$data	= array('test_field' => 'test@test.com');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Valid email (2 character domain)
		$rules	= array('test_field' => array('email' => 'Error'));
		$data	= array('test_field' => 'test@test.co');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Valid email (Non top level domain)
		$rules	= array('test_field' => array('email' => 'Error'));
		$data	= array('test_field' => 'test@test.co.uk');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Valid email (Camel case)
		$rules	= array('test_field' => array('email' => 'Error'));
		$data	= array('test_field' => 'testEmailAddress@test.com');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



	// Test date
	function testDate()
	{
		// Valid delimeters
		$delimiters = array('-', '/');
		foreach($delimiters as $delimiter)
		{
			// YYYY-MM-DD
			$rules	= array('test_field' => array('date' => 'Error'));
			$data	= array('test_field' => "2000{$delimiter}05{$delimiter}05");
			$this->v->run($data, $rules);
			$this->assertFalse(isset($this->v->error['test_field']));
	
			// MM-DD-YYYY
			$rules	= array('test_field' => array('date' => 'Error'));
			$data	= array('test_field' => "05{$delimiter}05{$delimiter}2000");
			$this->v->run($data, $rules);
			$this->assertFalse(isset($this->v->error['test_field']));

			// Invalid date (Month 99)
			$rules	= array('test_field' => array('date' => 'Error'));
			$data	= array('test_field' => "2000{$delimiter}99{$delimiter}05");
			$this->v->run($data, $rules);
			$this->assertEqual($this->v->error['test_field'][0], 'Error');

			// Invalid date (No dilimeter)
			$rules	= array('test_field' => array('date' => 'Error'));
			$data	= array('test_field' => "20000505");
			$this->v->run($data, $rules);
			$this->assertEqual($this->v->error['test_field'][0], 'Error');
		}
	}



	// Test time
	function testTime()
	{
		// Valid times
		$times = array(
			'01:15 AM',
			'01:15AM',
			'01:15Am',

			'1:15 AM',
			'1:15AM',
			'1:15Am',
			
			'01:15 PM',
			'01:15PM',
			'01:15Pm',
			
			'12:59AM',
			'12:59Pm',
		);
		foreach($times as $time)
		{
			$rules	= array('test_field' => array('time' => 'Error'));
			$data	= array('test_field' => $time);
			$this->v->run($data, $rules);
			$this->assertFalse(isset($this->v->error['test_field']));
		}

		// Invalid times
		$times = array(
			'01:95 AM',
			'01:60 AM',
			'01:15A',
			'51:00Am',
			'13:00AM'
		);
		foreach($times as $time)
		{
			$rules	= array('test_field' => array('time' => 'Error'));
			$data	= array('test_field' => $time);
			$this->v->run($data, $rules);
			$this->assertEqual($this->v->error['test_field'][0], 'Error');
		}
	}




	// Test unique
	function testUnique()
	{
		// Create test table
		$this->Db->query("
			CREATE TABLE IF NOT EXISTS `unique_test_table` (
				`unique_column` varchar(20) NOT NULL,
				UNIQUE KEY `unique_column` (`unique_column`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		");
		
		// Insert test record
		$this->Db->query("INSERT INTO `unique_test_table` (`unique_column`) VALUES ('1');");
		
		// Value is unique
		$rules	= array('test_field' => array('unique[unique_test_table.unique_column]' => 'Error'));
		$data	= array('test_field' => "unique");
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Value is not unique
		$rules	= array('test_field' => array('unique[unique_test_table.unique_column]' => 'Error'));
		$data	= array('test_field' => "1");
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');
		
		// Remove test table
		$this->Db->query("DROP TABLE `unique_test_table`");
	}



	// Test exists
	function testExists()
	{
		// Create test table
		$this->Db->query("
			CREATE TABLE IF NOT EXISTS `exists_test_table` (
				`exists_column` varchar(20) NOT NULL,
				UNIQUE KEY `exists_column` (`exists_column`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		");
		
		// Insert test record
		$this->Db->query("INSERT INTO `exists_test_table` (`exists_column`) VALUES ('1');");
		
		// Value exists
		$rules	= array('test_field' => array('exists[exists_test_table.exists_column]' => 'Error'));
		$data	= array('test_field' => "1");
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Value does not exist
		$rules	= array('test_field' => array('exists[exists_test_table.exists_column]' => 'Error'));
		$data	= array('test_field' => "2");
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'Error');
		
		// Remove test table
		$this->Db->query("DROP TABLE `exists_test_table`");
	}



	// Test cond
	function testCond()
	{
		// Conditional bad value
		$rules	= array('test_field' => array('cond' => 'Error', 'email' => 'ErrorEmail'));
		$data	= array('test_field' => 'test@');
		$this->v->run($data, $rules);
		$this->assertEqual($this->v->error['test_field'][0], 'ErrorEmail');

		// Conditional no value
		$rules	= array('test_field' => array('cond' => 'Error', 'email' => 'ErrorEmail'));
		$data	= array('test_field' => '');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));

		// Conditional good value
		$rules	= array('test_field' => array('cond' => 'Error', 'email' => 'ErrorEmail'));
		$data	= array('test_field' => 'test@test.com');
		$this->v->run($data, $rules);
		$this->assertFalse(isset($this->v->error['test_field']));
	}



}

?>