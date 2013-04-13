#!/usr/bin/env php
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

	// Array for available plugin objects
	$plugins = array();

	// Get all cli plugins
	$path = __DIR__."/system/cli/";
	$handle = opendir($path);
	while(false !== ($file = readdir($handle)))
	{
		$php_class_path = $path."{$file}/{$file}.php";
		if(trim($file, '.') != '' && is_dir($path.$file) && is_file($php_class_path))
		{
			// Include class and initialize to plugin array
			include_once($php_class_path);
			$class_name = "CLI_{$file}";
			$plugins[$file] = new $class_name($frm, $_SERVER['argv']);
		}
	}

	// Check arguments for which plugin to load
	if(isset($_SERVER['argv'][1]) && isset($plugins[ucfirst($_SERVER['argv'][1])]))
	{
		// Call plugin start method
		try
		{
			$plugins[ucfirst($_SERVER['argv'][1])]->start();
		
		}catch(Exception $e){
			print("\n".console_text("Operation Failed:", 'black_on_red')."");
			die("{$e->getMessage()}\n\n");
		}
		// Done
		exit;
	}


	// If we made it this far show plugin list
	echo "\nPlugins Loaded (".count($plugins)."):\n\n";

	// List all plugins
	foreach($plugins as $param => $plugin)
	{
		echo console_text(strtoupper($param), 'black_on_red');
		echo console_text("\t{$plugin->description}\n", '');
		echo console_text("\t{$plugin->example}\n\n", 'green');
	}
	exit;


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


	function console_text($string, $formatting = '')
	{
		if(!in_array($formatting, array('', 'red', 'green', 'black_on_red'))){ die('Unexpected console text format.'); }
		switch($formatting)
		{
			case 'red':
				return "\033[31m{$string}\033[37m\r\n";
			break;
			case 'green':
				return "\033[32m{$string}\033[37m\r\n";
			break;
			case 'black_on_red':
				return "\033[41;30m{$string}\033[40;37m\r\n";
			break;
			default:
				return "{$string}\n";
		}
	}

?>