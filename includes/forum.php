<?php
	$s = $_GET['s'];
	if($s == "list")
		require "forum/forum_list.php";
	else if($s == "view")
		require "forum/forum_view.php";
	else if($s == "remove")
		require "forum/forum_remove.php";
	else if($s == "edit")
		require "forum/forum_edit.php";
	else if($s == "add")
		require "forum/forum_add.php";
	else if($s == "search")
		require "forum/forum_search.php";
	else if($s == "post")
		require "forum/forum_post.php";
	else
		header("Location:index.php");
?>