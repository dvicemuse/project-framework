<?php

	// Do not show warnings
	error_reporting(E_ALL ^ E_WARNING);

	// Make sure the script is being run from the command line
	$using_cli = (@$_SERVER['argc'] >= 1);
	if($using_cli === FALSE)
	{
		die("This script must be run from the command line.");
	}

	// Initialize framework
	include_once('system/Framework.php');
	$frm = new Framework;


	// No parameters
	if(!isset($_SERVER['argv'][1]))
	{
		//echo "\033[31mred\033[37m\r\n";
		//echo "\033[32mgreen\033[37m\r\n";
		//echo "\033[41;30mblack on red\033[40;37m\r\n";
		echo "
Available actions are:

\033[41;30mBUILD\033[40;37m
	Run build actions (create the user table).
	\033[32mcli.php build\033[37m

\033[41;30mGENERATE\033[40;37m
	Create a model + controller + test suite + template folder with [name]
	\033[32mcli.php generate [name]\033[37m

		\n";
		exit;
	}


	// Perform framework build actions
	if($_SERVER['argv'][1] == 'build')
	{
		// Drop the user table
		$frm->load_helper('Db')->query("DROP TABLE IF EXISTS `user`;");
		
		// Create the user table
		$create_user_table = "
			CREATE TABLE `user` (
			  `user_id` int(10) unsigned NOT NULL auto_increment,
			  `user_email` varchar(75) default NULL,
			  `user_password` char(40) default NULL,
			  `user_first_name` varchar(50) default NULL,
			  `user_last_name` varchar(50) default NULL,
			  `user_update_hash` varchar(32) default NULL,
			  `user_last_login` datetime default NULL,
			  `user_create_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
			  PRIMARY KEY  (`user_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;
		";
		$frm->Db->query($create_user_table);
		
		// Insert a test user
		$frm->Db->query("INSERT INTO `user` (`user_id`, `user_email`, `user_password`, `user_first_name`, `user_last_name`, `user_update_hash`, `user_last_login`, `user_create_time`) VALUES(1, 'test@test.com', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 'Test', 'User', '', '', '');");
		
		// Verify
		$check = $frm->Db->get_rows('SELECT user_id FROM user');
		if($check !== FALSE && count($check) == 1)
		{
			echo "User table created.\n";
		}else{
			echo "ERROR: User table not created.\n";
		}
		
	}

	echo "\n";

?>
