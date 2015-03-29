<?php
	class comment
	{
		function __construct()
		{
		
		}
		//Add comments. Should follow rules set in the config as to anonymous commenting early on.
		function add($comment,$username,$post_id,$ip,$user_id)
		{
			global $db, $comment_table, $post_table, $post_count_table, $user_table;
			$len = strlen($comment);
			$count = substr_count($comment, ' ', 0, $len);
			if($comment != "" && ($len - $count) >= 3)
			{	
				$comment = $db->real_escape_string(htmlentities($comment,ENT_QUOTES,'UTF-8'));				
				$now_time = time();
				$query = "INSERT INTO $comment_table(comment, ip, user, posted_at, post_id) VALUES('$comment', '$ip', '$username', '$now_time', '$post_id')";
				$db->query($query) or die($db->error);
				$query = "UPDATE $post_table SET last_comment=NOW() WHERE id='$post_id'";
				$db->query($query);
				$query = "UPDATE $post_count_table SET pcount=pcount+1 WHERE access_key = 'comment_count'";
				$db->query($query);
				if($user != "Anonymous")
				{
					$query = "UPDATE $user_table SET comment_count = comment_count+1 WHERE id='$user_id'";
					$db->query($query);
				}
			}
		}
		//Edit comments, there is a limit to how many minutes you have to comment as well as a 3 character minimum.
		function edit($comment,$comment_id,$user)
		{
			global $db, $comment_table, $edit_limit;
			$len = strlen($comment);
			$count = substr_count($comment, ' ', 0, $len);
			if($comment != "" && ($len - $count) >= 3)
			{	
				$comment = $db->real_escape_string(htmlentities($comment,ENT_QUOTES,'UTF-8'));
				$comment_id = $db->real_escape_string($comment_id);
				$query = "SELECT posted_at FROM $comment_table WHERE id = '$comment_id' LIMIT 1";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$posted_at = $row['posted_at'];
				$edit_limit = ($edit_limit * 60) + $posted_at;
				$query = "UPDATE $comment_table SET comment ='$comment', edited_at='".time()."' WHERE user='$user' AND id='$comment_id' AND posted_at <= '$edit_limit'";
				$db->query($query);
			}
		}
		//Just the voting function. Nothing much needed to be edited here unless you want to change the vote score values...
		function vote($cid,$vote,$user,$id,$user_id)
		{
			global $db, $comment_vote_table, $comment_table;
			$id = $db->real_escape_string($id);
			$cid = $db->real_escape_string($cid);
			$user_id = $db->real_escape_string($user_id);
			$user = $db->real_escape_string(htmlentities($user, ENT_QUOTES, "UTF-8"));
			$vote = $db->real_escape_string($vote);
			$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
			$query_part = "";
			if($user != "Anonymous")
				$query_part = " OR comment_id='$cid' AND post_id='$id' AND user_id='".$user_id."'";
			$query = "SELECT comment_id FROM $comment_vote_table WHERE comment_id='$cid' AND post_id='$id' AND ip='$ip'".$query_part;
			$result = $db->query($query);
			$count = $result->num_rows;
			$result->free_result();
			if($count == 0)
			{
				if($vote == "up")
					$query = "UPDATE $comment_table SET score=score+1 WHERE id='$cid'";
				else 
					$query = "UPDATE $comment_table SET score=score-1 WHERE id='$cid'";
				$db->query($query);
				$query = "INSERT INTO $comment_vote_table(ip,post_id,comment_id) VALUES('$ip', '$id', '$cid')";
				$db->query($query);
			}
			$query = "SELECT score FROM $comment_table WHERE id='$cid'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			echo $row['score'];
		}
		
		//How many comments are set for this page, sub page, and post id?
		function count($id,$page,$sub)
		{
			global $db, $comment_table;
			$id = $db->real_escape_string($id);
			if($page == "post" && $sub == "view")
				$query = "SELECT id FROM $comment_table WHERE post_id='$id'";
			$result = $db->query($query);
			return $result->num_rows;
		}
	}
?>