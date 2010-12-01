<?php

	session_start();

	include_once('framework/Framework.php');
	$frm = new Framework;
	$frm->route();

?>