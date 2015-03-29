<?php
	$user = new user();
	if(!$user->check_log())
		exit;
		
	if(isset($_GET['pid']) && isset($_GET['cid']) && $_GET['pid'] != "" && $_GET['cid'] != "")
	{
		$pid = $db->real_escape_string($_GET['pid']);
		$cid = $db->real_escape_string($_GET['cid']);
		$uid = $checked_user_id;
		$uname = $checked_username;
		$query = "SELECT t1.author, t2.creation_post FROM $forum_post_table AS t1 JOIN $forum_topic_table AS t2 ON t2.id=t1.topic_id WHERE t1.topic_id='$pid' AND t1.id='$cid' LIMIT 1";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_assoc();
		if($row['author'] == $uname || $user->gotpermission('delete_forum_posts'))
		{
			//make sure we don't erase the first post of a topic, would cause a huge mess... just edit it, or delete the topic.
			if($row['creation_post'] != $cid)
			{
				$query = "DELETE FROM $forum_post_table WHERE id='$cid'";
				$db->query($query);
			}	
		}
		header("Location:index.php?page=forum&s=view&id=$pid");
		exit;
	}
	else if(isset($_GET['fid']) && is_numeric($_GET['fid']) && isset($_GET['pid']) && is_numeric($_GET['pid']))
	{
		if($user->gotpermission('delete_forum_topics'))
		{
			$fid = $db->real_escape_string($_GET['fid']);
			$pid = $db->real_escape_string($_GET['pid']);
			$query = "DELETE FROM $forum_post_table WHERE topic_id='$fid'";
			$db->query($query) or die($db->error);
			$query = "DELETE FROM $forum_topic_table WHERE id='$fid'";
			$db->query($query) or die($db->error);
			header("Location:index.php?page=forum&s=list&pid=$pid");
			exit;
		}
		header("HTTP/1.1 404 Not Found");
	}
	header("HTTP/1.1 404 Not Found");
?>