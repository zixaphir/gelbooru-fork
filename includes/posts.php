<?php
	if($_GET['s'] == "add")	
		require "includes/post_add.php";
	else if($_GET['s'] == "view")
		require "includes/post_view.php";
	else if($_GET['s'] == "list")
		require "includes/post_list.php";
	else if($_GET['s'] == "vote")
		require "includes/post_vote.php";
	else if($_GET['s'] == "random")
		require "post_random.php";
?>