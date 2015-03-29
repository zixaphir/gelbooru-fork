<?php
	require "header.php";
	if(!defined('_IN_ADMIN_HEADER_'))
		die;
	if($_GET['page'] == "alias")
			require "alias.php";
	else if($_GET['page'] == "reported_posts")
			require "reported_posts.php";
	else if($_GET['page'] == "reported_comments")
			require "reported_comments.php";
	else if($_GET['page'] == "add_group")
			require "add_group.php";
	else if($_GET['page'] == "edit_group")
			require "edit_group_permission.php";
	else if($_GET['page'] == "ban_user")
			require "ban_user.php";					

?>
<br></body></html>