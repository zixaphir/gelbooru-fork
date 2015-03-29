<?php
	require "../inv.header.php";
	$user = new user();
	if(!$user->gotpermission('admin_panel'))
	{
		header("Location:../");
		exit;
	}
	define('_IN_ADMIN_HEADER_',true);
	require "left_menu.php";
?>