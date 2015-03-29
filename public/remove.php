<?php
	require "../inv.header.php";
	$user = new user();
	$cache = new cache();
	if(!$user->check_log())
	{
		header('Location: ../index.php?page=account&s=home');
		exit;
	}		
	if(isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] != "")
	{
		$id = $db->real_escape_string($_GET['id']);
		if(isset($_GET['note_id']) && is_numeric($_GET['note_id']) && $_GET['note_id'] != "")
		{
			if(!$user->gotpermission('alter_notes'))
				exit;
			$note_id = $db->real_escape_string($_GET['note_id']);
			$query = "SELECT COUNT(*) FROM $note_table WHERE post_id='$id' AND id='$note_id'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			if($row['COUNT(*)'] == 1)
			{
				$result->free_result();
				$query = "DELETE FROM $note_table WHERE post_id='$id' AND id='$note_id'";
				$db->query($query);
				$query = "DELETE FROM $note_history_table WHERE post_id='$id' AND id='$note_id'";
				$db->query($query);
				$cache->destroy("cache/".$id."/post.cache");
				print $note_id;
			}
		}
		else if(isset($_GET['removepost']) && $_GET['removepost'] == 1)
		{
			$image = new image();
			if($image->removeimage($id) == true)
			{
				$cache->destroy_page_cache("cache/".$id);
				$query = "SELECT id FROM $post_table WHERE id < $id ORDER BY id DESC LIMIT 1";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$prev_id = $row['id'];
				$result->free_result();
				$query = "SELECT id FROM $post_table WHERE id > $id ORDER BY id ASC LIMIT 1";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$next_id = $row['id'];
				$date = date("Ymd");
				if(is_dir("$main_cache_dir".""."cache/".$prev_id) && "$main_cache_dir".""."cache/".$prev_id != "$main_cache_dir".""."cache/")
				$cache->destroy_page_cache("cache/".$prev_id);
				if(is_dir("$main_cache_dir".""."cache/".$next_id) && "$main_cache_dir".""."cache/".$next_id != "$main_cache_dir".""."cache/")				
				$cache->destroy_page_cache("cache/".$next_id);
				header("Location:../index.php?page=post&s=list");
			}
			else
				header("Location:../index.php?page=post&s=view&id=$id");
		}
		else if(isset($_GET['removecomment']) && $_GET['removecomment'] == 1)
		{
			$permission = $user->gotpermission('delete_comments');
			if($permission == true)
			{
				$post_id = $db->real_escape_string($_GET['post_id']);
				$query = "SELECT * FROM $comment_table WHERE id='$id' LIMIT 1";
				$result = $db->query($query);
				if($result->num_rows =="1")
				{
					$query = "DELETE FROM $comment_table WHERE id='$id'";
					$db->query($query);
					$query = "DELETE FROM $comment_vote_table WHERE comment_id='$id'";
					$db->query($query);
					$query = "UPDATE $post_count_table SET pcount=pcount-1 WHERE access_key = 'comment_count'";
					$db->query($query);
				}
				$cache = new cache();
				$cache->destroy_page_cache("cache/".$post_id);
				$cache->create_page_cache("cache/".$post_id);
			}
			header("Location:../index.php?page=post&s=view&id=$post_id");
		}
	}
?>