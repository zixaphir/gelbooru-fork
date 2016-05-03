<?php
	// At start of script
	$time_start = microtime(true); 

	require "inv.header.php";
	if(isset($_GET['page']) && $_GET['page'] != "")
	{
		switch ($_GET['page']) {
			case 'reg':
				require 'includes/signup.php';
				break;
			case 'account-options':
				require "includes/account_options.php";
				break;
			case 'account':
				header("Cache-Control: store, cache");
				header("Pragma: cache");
				require "includes/header.php";
				require "includes/account.php";
				break;
			case 'post':
				require "includes/posts.php";
				break;
			case 'dapi':
				require "includes/dapi.php";
				exit;
			case 'login':
			case 'history':
			case 'account_profile':
			case 'comment':
			case 'search':
			case 'favorites':
			case 'forum':
			case 'alias':
			case 'reset_password':
			case 'users':
				require "includes/" . $_GET["page"] . ".php";
				break;
			default:
				header("Location:".$site_url."/");
				exit;
		}
	}
	else
	{
		header("Cache-Control: store, cache");
		header("Pragma: cache");
		require "includes/index.php";
	}
	echo "<center><small>Memory Usage: " . round((memory_get_usage()/1048576), 2) . " MB, Total execution time: " . (microtime(true) - $time_start) * 1000 . "ms</small></center>";
?>