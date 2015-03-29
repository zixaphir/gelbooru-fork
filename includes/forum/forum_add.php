<?php
	$user = new user();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);	
	if($user->banned_ip($ip))
	{
		print "Action failed: ".$row['reason'];
		exit;
	}	
	if(!$user->check_log())
	{
		header("Location:index.php?page=reg");
		exit;
	}
	$add_forum_count = "UPDATE $user_table SET forum_post_count = forum_post_count+1 WHERE id='$checked_user_id'";	
	if(isset($_GET['t']) && $_GET['t'] == "post")
	{
		if(isset($_GET['pid']) && is_numeric($_GET['pid']) && isset($_POST['conf']) && $_POST['conf'] == 1)
		{
			$title = $db->real_escape_string(htmlentities($_POST['title'], ENT_QUOTES, 'UTF-8'));
			$post = $db->real_escape_string(htmlentities($_POST['post'], ENT_QUOTES, 'UTF-8'));
			$pid = $db->real_escape_string($_GET['pid']);
			$limit = $db->real_escape_string($_POST['l']);
			$uid = $checked_user_id;
			$query = "SELECT locked FROM $forum_topic_table WHERE id='$pid'";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			if($row['locked'] == true)
			{
				header("HTTP/1.1 404 Not Found");
				exit;
			}
			$query = "SELECT forum_can_post FROM $user_table WHERE id='$uid'";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			$user = $checked_username;
			$can_post = $row['forum_can_post'];
			if($can_post == false)
			{
				header("HTTP/1.1 404 Not Found");
				exit;
			}
			$query = "INSERT INTO $forum_post_table(title, post, author, creation_date, topic_id) VALUES('$title', '$post', '$user', '".mktime()."', '$pid')";
			$db->query($query) or die($db->error);
			$query = "SELECT LAST_INSERT_ID() as id FROM $forum_post_table";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			$id = $row['id'];
			$result->free_result();
			$query = "UPDATE $forum_topic_table SET last_updated='".mktime()."' WHERE id='$pid'";
			$db->query($query) or die($db->error);
			$db->query($add_forum_count);			
			$query = "SELECT COUNT(*) FROM $forum_post_table WHERE topic_id='$pid'";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			$numrows = $row['COUNT(*)'];
			$result->free_result();
			$pages = @intval($numrows/$limit);
			if($numrows%$limit>0) 
				$pages++;
			else
				$pages = 1;
			$ppid = $limit*($pages - 1);
			header("Location:index.php?page=forum&s=view&id=$pid&pid=$ppid#$id");
			exit;
		}	
	}
	else
	{
		if(isset($_POST['topic']) && $_POST['topic'] != "" && isset($_POST['post']) && $_POST['post'] != "" && isset($_POST['conf']) && $_POST['conf'] == 1)
		{
			$topic = $db->real_escape_string(htmlentities($_POST['topic'], ENT_QUOTES, 'UTF-8'));
			$post = $db->real_escape_string(htmlentities($_POST['post'], ENT_QUOTES, 'UTF-8'));
			$uid = $checked_user_id;
			$query = "SELECT forum_can_create_topic FROM $user_table WHERE id='$uid'";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			$user = $checked_username;
			$can_create_topic = $row['forum_can_create_topic'];
			if($can_create_topic == false)
			{
				header("HTTP/1.1 404 Not Found");
				exit;
			}
			$query = "INSERT INTO $forum_topic_table(topic, author, creation_post, last_updated) VALUES('$topic', '$user', '0', '".mktime()."')";
			$db->query($query) or die($db->error);
			$query = "SELECT LAST_INSERT_ID() as id FROM $forum_topic_table";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			$pid = $row['id'];
			$query = "INSERT INTO $forum_post_table(title, post, author, creation_date, topic_id) VALUES('$topic', '$post', '$user', '".mktime()."', '$pid')";
			$db->query($query) or die($db->error);
			$db->query($add_forum_count);			
			$query = "SELECT LAST_INSERT_ID() as id FROM $forum_post_table";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			$id = $row['id'];
			$query = "UPDATE $forum_topic_table SET creation_post='$id' WHERE id='$pid'";
			$db->query($query) or die($db->error);
			header("Location:index.php?page=forum&s=view&id=$pid#$id");
			exit;
		} 
	}
		require "includes/header.php";
?>
<form method="post" action="">
	<table><tr><td>
	Topic:<br/>	
	<input type="text" name="topic" value=""/>
	</td></tr>
	<tr><td>
	Post:<br />
	<textarea name="post" rows="4" cols="6" style="width: 600px; height: 200px;"></textarea>
	</td></tr>
	<tr><td>
	<input type="hidden" name="conf" id='conf' value="0"/>
	</td></tr>
	<tr><td>
	<input type="submit" name="submit" value="Create topic"/>
	</td></tr></table></form>
	<script type="text/javascript">
	//<![CDATA[
	document.getElementById('conf').value=1;
	//]]>
	</script>