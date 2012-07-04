<?php

	session_start();

	if(PHP_MAJOR_VERSION >= 5 && PHP_MINOR_VERSION >= 3)
	{
		include_once('system/Framework.php');
		$frm = new Framework;
		$frm->route();
	}else{
		pr("PHP >= 5.3 is required to make this thing work without major hacks.");
	}

?>
