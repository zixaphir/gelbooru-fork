<?php
	require "inv.header.php";
	if(isset($_GET['page']) && $_GET['page'] != "")
	{
		if($_GET['page'] == "account")
		{
			header("Cache-Control: store, cache");
			header("Pragma: cache");
			require "includes/header.php";
			require "includes/account.php";
		}
		else if($_GET['page'] == "reg")
			require "includes/signup.php";
		else if($_GET['page'] == "login")
			require "includes/login.php";
		else if($_GET['page'] == "post")
			require "includes/posts.php";
		else if($_GET['page'] == "history")
			require "includes/history.php";
		else if($_GET['page'] == "account-options")
			require "includes/account_options.php";
		else if($_GET['page'] == "account_profile")
			require "includes/account_profile.php";
		else if($_GET['page'] == "comment")
			require "includes/comment.php";
		else if($_GET['page'] == "search")
			require "includes/search.php";
		else if($_GET['page'] == "favorites")
			require "includes/favorites.php";
		else if($_GET['page'] == "alias")
			require "includes/alias.php";
		else if($_GET['page'] == "reset_password")
			require "includes/reset_password.php";	
		else if($_GET['page'] == "forum")
			require "includes/forum.php";	
		else 
		{
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
?>