<?php

// Include the SimpleTest unit test library
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(str_replace("_tests", "", dirname(__FILE__)) . '/system/Framework.php');

// Start the framework (to include system classes)
$f = new Framework();

// A model that will always exist
class UnitTestModel extends Framework{ }

// Set up the unit test class
class Model_Base_Test extends UnitTestCase
{
	// Test Model_Base->get()
	function testLoadModel()
	{
		// Base object
		$f = new Framework();
		
		// Include check
		$this->assertTrue(is_object($f->load_model('User')));
		
		// Does not include model file if class is already loaded
		$this->assertTrue(is_object($f->load_model('UnitTestModel')));
		
		// Multiple load model calls will return the same object
		$object_1 = $f->load_model('UnitTestModel');
		$f->UnitTestModel->prop = 12345;
		$object_2 = $f->load_model('UnitTestModel');
		$this->assertIdentical($object_1, $object_2);
		
		// Force new object parameter
		$f = new Framework();
		$object_1 = $f->load_model('UnitTestModel');
		$f->UnitTestModel->prop = 12345;
		$object_2 = $f->load_model('UnitTestModel', TRUE);
		$this->assertTrue(isset($object_2->prop) === FALSE);
		
		// Loading a model that does not exist == exception
		try
		{
			$f->load_model('FAKE123');
			$this->fail();
		}catch(Exception $e){
			$this->pass();
		}
		
		
		
	}
}

?>
