<?php
	$user = new user();
	if(!$user->check_log() && !$anon_vote)
	{
		header('Location: index.php?page=account&s=home');
		exit;
	}
	if(isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['type']))
	{
		$id = $db->real_escape_string($_GET['id']);
		$type = $db->real_escape_string($_GET['type']);
		$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
		$query_part = "";
		if($user->check_log())
		{
			$user_id = $checked_user_id;
			$query_part = " OR post_id='$id' AND user_id='$user_id'";
		}
		else
			$user_id = 0;
		$query = "SELECT COUNT(*) FROM $post_vote_table WHERE post_id='$id' AND ip='$ip'".$query_part;
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		if($row['COUNT(*)'] < 1)
		{
			$result->free_result();
			if($type == "up")
				$query = "UPDATE $post_table SET score=score+1 WHERE id='$id'";
			else if($type == "down")
				$query = "UPDATE $post_table SET score=score-1 WHERE id='$id'";
			else
				exit;
			$db->query($query);
			$query = "INSERT INTO $post_vote_table(ip, post_id, user_id, rated) VALUES('$ip', '$id', '$user_id', '$type')";
			$db->query($query);
			$query = "SELECT score FROM $post_table WHERE id='$id'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$cache = new cache();
			$cache->destroy("cache/$id/post.cache");
			echo $row['score'];
			$result->free_result();
		}
		else
		{
			$query = "SELECT score FROM $post_table WHERE id='$id'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			echo $row['score'];
			$result->free_result();
		}
	}	
?>