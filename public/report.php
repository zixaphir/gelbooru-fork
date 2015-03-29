<?php
	require "../inv.header.php";
	$user = new user();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);	
	if($user->banned_ip($ip))
		exit;
	if(!$user->check_log() && !$anon_report)
	{
		header('Location: index.php?page=account&s=home');
		exit;
	}	
	if(isset($_GET['type']) && $_GET['type'] != "" && isset($_GET['rid']) && is_numeric($_GET['rid']))
	{
		$type = $db->real_escape_string($_GET['type']);
		$rid = $db->real_escape_string($_GET['rid']);
		if($type == "comment")
		{
			$query = "UPDATE $comment_table SET spam=TRUE WHERE id='$rid'";
			if($db->query($query))
			{
				$cache = new cache();
				$query = "SELECT post_id FROM $comment_table where id='$rid'";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$cache->destroy_page_cache("cache/".$row['post_id']);
				$cache->create_page_cache("cache/".$row['post_id']);
				print "pass";
			}
			else
				print "fail";
		}
		else if($type == "post")
		{
			$user = new user();
			if(!$user->check_log())
			{
				header("Location: ../index.php?page=post&s=view&id=$rid");
				exit;
			}
			$reason = $db->real_escape_string(htmlentities($_POST['reason'], ENT_QUOTES, 'UTF-8'));
			if(strlen($reason) > 0)
			{
				$query = "UPDATE $post_table SET spam=TRUE, reason='$reason' WHERE id='$rid'";
				$db->query($query);
			}
			$cache = new cache();
			$cache->destroy("cache/".$rid."/post.cache");
			header("Location:../index.php?page=post&s=view&id=$rid");
		}
		else
			header("Location:../index.php");
		exit;
	}
?>