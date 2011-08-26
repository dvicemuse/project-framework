<?php

	session_start();

	include_once('system/Framework.php');
	$frm = new Framework;
	$frm->route();

?>