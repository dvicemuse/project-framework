<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');

class TestOfLogging extends UnitTestCase
{
	function testFirstLogMessagesCreatesFileIfNonexistent()
	{
		$x = 1;
		$y = 2;
		$this->assertEqual($x, $y);
	}
}

?>
