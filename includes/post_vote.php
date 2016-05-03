<?php
	if(isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['type']))
	{
		$user = new user();
		$id = $db->real_escape_string($_GET['id']);
		$type = $db->real_escape_string($_GET['type']);
		$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
		$user_id = "0";
		$query_part = "";
		if($user->check_log())
		{
			$user_id = $checked_user_id;
			$query_part = " OR post_id='$id' AND user_id='$user_id'";
		}
		if (!$anon_vote && $user_id == 0)
		{
			echo "Anonymous ratings are disabled.";
			exit;
		} 
		$query = "SELECT COUNT(*) FROM $post_vote_table WHERE post_id='$id' AND ip='$ip'".$query_part;
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		if($row['COUNT(*)'] < 1)
		{
			$result->free_result();
			if($type == "up") {
				$query = "UPDATE $post_table SET score=score+1 WHERE id='$id'";
			} else if($type == "down") {
				$query = "UPDATE $post_table SET score=score-1 WHERE id='$id'";
			} else
				exit;
			$db->query($query);
			$query = "INSERT INTO $post_vote_table(rated, ip, post_id, user_id) VALUES('$type', '$ip', '$id', '$user_id')";
			$db->query($query);
			$cache = new cache();
			$cache->destroy("cache/$id/post.cache");
		}
		else
		{
			$result->free_result();
		}
		$query = "SELECT score FROM $post_table WHERE id='$id'";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		echo $row['score'];
		$result->free_result();
		exit;
	}
?>