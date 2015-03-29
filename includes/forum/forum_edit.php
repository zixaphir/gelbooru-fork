<?php
	$user = new user();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);	
	if($user->banned_ip($ip))
	{
		print "Action failed: ".$row['reason'];
		exit;
	}	
	if(!$user->check_log())
		exit;
	if(isset($_POST['title']) && isset($_POST['post']) && isset($_GET['pid']) && $_GET['pid'] != "" && isset($_GET['cid']) && $_GET['cid'] != "" && isset($_GET['ppid']) && $_GET['ppid'] != "")
	{
		$pid = $db->real_escape_string($_GET['pid']);
		$cid = $db->real_escape_string($_GET['cid']);
		$ppid = $db->real_escape_string($_GET['ppid']);
		$uid = $checked_user_id;
		$uname = $checked_username;
		$query = "SELECT author FROM $forum_post_table WHERE topic_id='$pid' AND id='$cid' LIMIT 1";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_assoc();
		if($row['author'] == $uname || $user->gotpermission('edit_forum_posts'))
		{
				$title = $db->real_escape_string(htmlentities($_POST['title'], ENT_QUOTES, 'UTF-8'));
				$post = $db->real_escape_string(htmlentities($_POST['post'], ENT_QUOTES, 'UTF-8'));
				$query = "UPDATE $forum_post_table SET title='$title', post='$post' WHERE topic_id='$pid' AND id='$cid'";
				$db->query($query) or die($db->error);
		}
		header("Location:index.php?page=forum&s=view&id=$pid&pid=$ppid#$cid");
		exit;
	}
	else if(isset($_GET['pin']) && $_GET['pin'] != "" && is_numeric($_GET['pin']) && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['pid']) && is_numeric($_GET['pid']))
	{
		if($user->gotpermission('pin_forum_topics'))
		{
			$pin = $db->real_escape_string($_GET['pin']);
			$id = $db->real_escape_string($_GET['id']);
			$pid = $db->real_escape_string($_GET['pid']);
			if($pin > 0)
				$query = "UPDATE $forum_topic_table SET priority='1' WHERE id='$id'";
			else
				$query = "UPDATE $forum_topic_table SET priority='0' WHERE id='$id'";
			$db->query($query) or die($db->error);
			header("Location:index.php?page=forum&s=list&pid=$pid");
			exit;
		}
		header("HTTP/1.1 404 Not Found");
	}
	else if(isset($_GET['lock']) && $_GET['lock'] != "" && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['pid']) && is_numeric($_GET['pid']))
	{
		if($user->gotpermission('lock_forum_topics'))
		{
			$id = $db->real_escape_string($_GET['id']);
			$lock = $db->real_escape_string($_GET['lock']);
			$pid = $db->real_escape_string($_GET['pid']);
			if($lock == "true")
				$query = "UPDATE $forum_topic_table SET locked=true WHERE id='$id'";
			else if($lock == "false")
				$query = "UPDATE $forum_topic_table SET locked=false WHERE id='$id'";
			$db->query($query) or die($db->error);
			header("Location:index.php?page=forum&s=view&id=$id&pid=$pid");
			exit;	
		}
		header("HTTP/1.1 404 Not Found");
	}
	header("HTTP/1.1 404 Not Found");
?>