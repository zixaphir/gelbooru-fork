<?php
	require "config.php";
	require "functions.global.php";
	require "autoload.php";
	$db = new mysqli($mysql_host,$mysql_user,$mysql_pass,$mysql_db) or die("Ooops?");
	$db->set_charset('utf8');
	require "auto_login.php";
?>