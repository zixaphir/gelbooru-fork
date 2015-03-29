<?php
	$userc = new user();
	$cache = new cache();
	$tclass = new tag();
	if(isset($_GET['type']) && isset($_GET['id']) and is_numeric($_GET['id']))
	{
		$type = $db->real_escape_string($_GET['type']);
		$id = $db->real_escape_string($_GET['id']);
		$pid = $db->real_escape_string($_GET['pid']);
		if($type == "note")
		{
			header("Cache-Control: store, cache");
			header("Pragma: cache");
			require "includes/header.php";
			$pid = $db->real_escape_string($_GET['pid']);
			$query = "SELECT updated_at, user_id, version, body FROM $note_history_table where id='$id' AND post_id='$pid' ORDER BY version DESC";
			$result = $db->query($query);
			$count = $result->num_rows;
			while($row = $result->fetch_assoc())
			{
				$ret = "SELECT user FROM $user_table WHERE id='".$row['user_id']."'";
				$set = $db->query($ret);
				$retme = $set->fetch_assoc();
				if($retme['user'] == "" || $retme['user'] == null)
					$user = "Anonymous";
				else
					$user = $retme['user'];
				$set->free_result();
				echo '<a href="index.php?page=post&s=view&id='.$pid.'">'.$pid.'</a> <a href="index.php?page=history&type=note&id='.$id.'&pid='.$pid.'">'.$id.'</a>'.$row['body'].' '.$user.' '.$row['updated_at'].' <a href="#" onclick="if(confirm(\'Do you really want to revert to this point?\')){document.location=\'index.php?page=history&type=revert&id='.$id.'&pid='.$pid.'&version='.$row['version'].'\'; return false;}">Revert</a><br /><br />';
			}
			$result->free_result();
			if($count <= 0)
				echo '<h1>This note has no history!</h1>';
		}
		else if($type == "page_notes")
		{
			header("Cache-Control: store, cache");
			header("Pragma: cache");
			require "includes/header.php";
			print '<table width="100%" class="highlightable" id="history">
			<tr><th width="1%"></th><th width="4%">Post</th><th width="5%">Date</th><th width="10%">User</th><th width="60%">Body</th><th width="10%">Options</th></tr>';			
			$query = "SELECT id, updated_at, user_id, version, body FROM $note_history_table where post_id='$id' ORDER BY id,version DESC";
			$result = $db->query($query);
			$count = $result->num_rows;
			while($row = $result->fetch_assoc())
			{
				$ret = "SELECT user FROM $user_table WHERE id='".$row['user_id']."'";
				$set = $db->query($ret);
				$retme = $set->fetch_assoc();
				if($retme['user'] == "" || $retme['user'] == null)
					$user = "Anonymous";
				else
					$user = $retme['user'];
				$set->free_result();
				echo '<tr><td></td><td><a href="index.php?page=post&s=view&id='.$id.'">'.$id.'</a></td><td>'.$row['updated_at'].'</td><td>'.$user.'</td><td>'.$row['body'].'</td><td><a href="#" onclick="if(confirm(\'Do you really want to revert to this point?\')){document.location=\'index.php?page=history&type=revert&id='.$row['id'].'&pid='.$id.'&version='.$row['version'].'\'; return false;}">Revert</a></td></tr>';
			}
			print "</table>";
			$result->free_result();
			if($count <= 0)
				echo '<h1>This post has no note history!</h1>';
		}
		else if($type == "tag_history")
		{
			header("Cache-Control: store, cache");
			header("Pragma: cache");
			require "includes/header.php";
			$query = "SELECT tags, version, user_id, updated_at FROM $tag_history_table WHERE id='$id' AND active='1' ORDER BY total_amount DESC";
			$result = $db->query($query) or die($db->error);
			$count = $result->num_rows;
			print '<table width="100%" class="highlightable" id="history">
			<tr><th width="1%"></th><th width="4%">Post</th><th width="5%">Date</th><th width="10%">User</th><th width="60%">Tags</th><th width="10%">Options</th></tr>';			
			while($row = $result->fetch_assoc())
			{
				$ret = "SELECT user FROM $user_table WHERE id='".$row['user_id']."'";
				$set = $db->query($ret);
				$retme = $set->fetch_assoc();
				if($retme['user'] == "" || $retme['user'] == null)
					$user = "Anonymous";
				else
					$user = htmlentities($retme['user'], ENT_QUOTES, 'UTF-8');
				echo '<tr><td></td><td><a href="index.php?page=post&s=view&id='.$id.'">'.$id.'</a></td><td>'.$row['updated_at'].'</td><td>'.$user.'</td><td>'.$row['tags'].'</td><td><a href="#" onclick="if(confirm(\'Do you really want to revert to this point?\')){document.location=\'index.php?page=history&type=revert_tags&id='.$id.'&version='.$row['version'].'\'; return false;}">Revert</a></td></tr>';
			}
			print "</table>";
			$result->free_result();
			if($count <= 0)
				echo '<h1>This post has no tag history!</h1>';
		}
		else if($type == "revert")
		{
			if($userc->gotpermission('reverse_notes'))
			{
					$pid = $db->real_escape_string($_GET['pid']);
					$version = $db->real_escape_string($_GET['version']);
					$query = "SELECT updated_at, x, y, width, height, body, user_id, ip FROM $note_history_table WHERE id='$id' AND post_id='$pid' AND version='$version'";
					$result = $db->query($query);
					$row = $result->fetch_assoc();
					$query = "UPDATE $note_table SET updated_at='".$row['updated_at']."', x='".$row['x']."', y='".$row['y']."', width='".$row['width']."', height='".$row['height']."', body='".$row['body']."', user_id='".$row['user_id']."', ip='".$row['ip']."', version='$version' WHERE id='$id' AND post_id='$pid'";
					$result->free_result();
					$db->query($query);
					$query = "DELETE FROM $note_history_table WHERE id='$id' AND post_id='$pid' AND version >= '$version'";
					$db->query($query);
						$cache->destroy("cache/".$id."/post.cache");
					header("Location:index.php?page=post&s=view&id=$pid");
			}
			header("Location:index.php?page=post&s=view&id=$pid");
		}
		else if($type == "revert_tags")
		{
			$version = $db->real_escape_string($_GET['version']);
			if($userc->gotpermission('reverse_tags'))
			{
					$misc = new misc();
					$query = "SELECT t1.tags, t2.tags AS t2_tags FROM $tag_history_table AS t1 JOIN $post_table AS t2 ON t2.id='$id' WHERE t1.id='$id' AND t1.version='$version'";
					$result = $db->query($query) or die($db->error);
					$row = $result->fetch_assoc();
					$tmp = explode(" ",mb_trim($row['t2_tags']));
					foreach($tmp as $current)
					{
						if(is_dir("$main_cache_dir".""."\search_cache/".$misc->windows_filename_fix($current)."/") && $current != "")
							$cache->destroy_page_cache("search_cache/".$misc->windows_filename_fix($current)."/");
						$tclass->deleteindextag($current);							
					}
					$tmp = explode(" ",mb_trim($row['tags']));
					foreach($tmp as $current)
					{
						if(is_dir("$main_cache_dir".""."\search_cache/".$current."/") && $current != "")
							$cache->destroy_page_cache("search_cache/".$current."/");
						$tclass->addindextag($current);							
					}					

					$query = "UPDATE $post_table SET tags='".$row['tags']."', recent_tags='".$row['tags']."', tags_version='$version' WHERE id='$id'";
					$result->free_result();
					$db->query($query);
					$query = "UPDATE $tag_history_table SET active='0' WHERE id='$id' AND version > '$version'";
					$db->query($query);
					$cache->destroy("cache/".$id."/post.cache");					
					header("Location:index.php?page=post&s=view&id=$id");
			}
			else
				header("Location:index.php?page=post&s=view&id=$id");
		}
	}
	else
		header("Location:index.php");
?>