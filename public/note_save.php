<?php
	require "../inv.header.php";
	$user = new user();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
	if($user->banned_ip($ip))
		exit;
	if(!$user->check_log())
		exit;
	$user_id = $checked_user_id;
	if(!$user->gotpermission('alter_notes'))
		exit;
	if(is_numeric($_GET['id']) && is_numeric($_GET['note']['post_id']) && is_numeric($_GET['note']['x']) && is_numeric($_GET['note']['y']) && is_numeric($_GET['note']['width']) && is_numeric($_GET['note']['height']))
	{
		$id = $db->real_escape_string($_GET['id']);
		$x = $db->real_escape_string($_GET['note']['x']);
		$y = $db->real_escape_string($_GET['note']['y']);
		$width = $db->real_escape_string($_GET['note']['width']);
		$height = $db->real_escape_string($_GET['note']['height']);
		$body = $db->real_escape_string(htmlentities($_GET['note']['body'], ENT_QUOTES,'UTF-8'));
		$body = str_replace("&lt;tn&gt;","<tn>", $body);
		$body = str_replace("&lt;/tn&gt;","</tn>", $body);
		$body = str_replace("&lt;br /&gt;","<br />",$body);
		$body = str_replace("&lt;br&gt;","<br />",$body);
		$body = str_replace("&lt;b&gt;","<b>",$body);
		$body = str_replace("&lt;/b&gt;","</b>",$body);
		$body = str_replace("&lt;i&gt;","<i>",$body);
		$body = str_replace("&lt;/i&gt;","</i>",$body);
		$post_id = $db->real_escape_string($_GET['note']['post_id']);
		$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
		$query = "SELECT COUNT(*) FROM $note_table WHERE post_id='$post_id' AND id='$id'";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		if($row['COUNT(*)'] == 1 && $id > 0)
		{
			$result->free_result();
			$query = "SELECT x, y, width, height, x, y, body, created_at, updated_at, ip,  version, user_id FROM $note_table WHERE id='$id' AND post_id='$post_id' LIMIT 1";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$query = "INSERT INTO $note_history_table(x, y, width, height, body, created_at, updated_at, ip, user_id, version, id, post_id) VALUES('".$row['x']."', '".$row['y']."', '".$row['width']."', '".$row['height']."', '".$row['body']."', '".$row['created_at']."', '".$row['updated_at']."', '".$row['ip']."', '". $checked_user_id."', '".$row['version']."', '$id', '$post_id')";
			$result->free_result();
			$db->query($query);
			$query = "UPDATE $note_table SET x='$x', y='$y', width='$width', height='$height', body='$body', updated_at=NOW(), user_id='".$checked_user_id."', ip='$ip', version=version+1 WHERE post_id='$post_id' AND id='$id'";
			$db->query($query);
		}
		else
		{
			$result->free_result();
			$date = date("Y-m-d H:i:s");
			$query = "SELECT COUNT(*) FROM $note_table WHERE post_id='$post_id'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			if($row['COUNT(*)'] < 1)
			{
				$result->free_result();
				$query = "INSERT INTO $note_table(x, y, width, height, body, post_id, id, ip, user_id, created_at, updated_at) VALUES('$x', '$y', '$width', '$height', '$body', '$post_id', '1', '$ip', '$checked_user_id', '$date', '$date')";
			}
			else
			{
				$result->free_result();
				$query = "INSERT INTO $note_table(x, y, width, height, body, post_id, id, ip, user_id, created_at, updated_at) VALUES('$x', '$y', '$width', '$height', '$body', '$post_id', notes_next_id($post_id), '$ip', '$checked_user_id', '$date', '$date')";
			}
			$db->query($query);
			$query = "SELECT id FROM $note_table WHERE post_id='$post_id' AND body='$body' AND ip='$ip' AND created_at='$date'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			print $row['id'].":".$id;
			$result->free_result();
		}
		$cache = new cache();
		$cache->destroy("cache/".$post_id."/post.cache");
	}
?>