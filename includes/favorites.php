<?php
	$misc = new misc();
	if(isset($_GET['s']) && $_GET['s'] == "view")
	{
		//List how many images per page that should be here.
		//number of images/page
		$limit = 50;
		//number of pages to display. number - 1. ex: for 5 value should be 4
		$page_limit = 6;
		$cache = new cache();
		$domain = $cache->select_domain();
		header("Cache-Control: store, cache");
		header("Pragma: cache");
		require "includes/header.php";
		?>
		<script type="text/javascript">
		var posts = {}; var pignored = {};
		</script>
		<?php
		$id = $db->real_escape_string($_GET['id']);
		if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && 	$_GET['pid'] >= 0)
			$page = $db->real_escape_string($_GET['pid']);
		else
			$page = 0;
		$query = "SELECT fcount FROM $favorites_count_table WHERE user_id='$id'";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		$numrows = $row['fcount'];
		$result->free_result();
		if($numrows < 1)
			die("<h1>You have no favorites.</h1>");
		$images = '';
		$query = "SELECT t2.id, t2.image, t2.directory, t2.tags, t2.owner, t2.score, t2.rating FROM $favorites_table as t1 JOIN $post_table AS t2 ON t2.id=t1.favorite WHERE t1.user_id='$id' LIMIT $page, $limit";
		$result = $db->query($query);
		while($row = $result->fetch_assoc())
		{
			$tags = $row['tags'];
			$tags = substr($tags,1,strlen($tags));
			$tags = substr($tags,0,strlen($tags)-1);
			$images .= '<span class="thumb" style="margin: 10px;"><a href="index.php?page=post&amp;s=view&amp;id='.$row['id'].'" id="p'.$row['id'].'" onclick="document.location=\'index.php?page=post&amp;s=view&amp;id='.$row['id'].'\'; return false;"><img src="'.$thumbnail_url.$misc->getThumb($row['image'], $row['directory']).'" title="'.$tags.'" border="0" alt="image_thumb"/></a>'; (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] == $id) ? $images .= '<br /><a href="#" onclick="document.location=\'index.php?page=favorites&s=delete&id='.$row['id'].'&pid='.$page.'\'; return false;"><b>Remove</b></a></span>' : $images .= '</span>';
			$images .= '<script type="text/javascript">
			posts['.$row['id'].'] = {\'tags\':\''.str_replace('\\',"&#92;",str_replace(' ','%20',str_replace("'","&#039;",$tags))).'\'.split(/ /g), \'rating\':\''.$row['rating'].'\', \'score\':'.$row['score'].', \'user\':\''.str_replace('\\',"&#92;",str_replace(' ','%20',str_replace("'","&#039;",$row['owner']))).'\'}
			</script>';
		}
		$images .= '<div style="margin-top: 550px; text-align: right;"><a id="pi" href="#" onclick="showHideIgnored(\'0\',\'pi\'); return false;"></a></div>
		<script type="text/javascript">
		filterPosts(posts)
		</script>
		<div id=\'paginator\'>';
		echo $images;
		ob_flush();
		flush();
		$result->free_result();
		print $misc->pagination($_GET['page'],$_GET['s'],$id,$limit,$page_limit,$numrows,$page);
	}
	else if(isset($_GET['s']) && $_GET['s'] == "list")
	{
		//List how many users per page that should be here.
		//number of images/page
		$limit = 50;
		//number of pages to display. number - 1. ex: for 5 value should be 4
		$page_limit = 6;
		header("Cache-Control: store, cache");
		header("Pragma: cache");
		require "includes/header.php";
		echo'<div id="content"><div id="account-favorites-list"><table width="100%"><tr><th width="30%">User</th><th width="70%">Count</th></tr>';
		if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
			$page = $db->real_escape_string($_GET['pid']);
		else
			$page = 0;
		$query = "SELECT COUNT(*) FROM $favorites_count_table ORDER BY user_id";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		$numrows = $row['COUNT(*)'];
		$result->free_result();
		if($numrows < 1)
			die("</table></div><h1>No favorites exists.</h1></div></body></html>");
		$uid = '';
		$query = "SELECT t2.user, t1.user_id, t1.fcount FROM $favorites_count_table AS t1 JOIN $user_table AS t2 ON t2.id=t1.user_id ORDER BY t2.user ASC LIMIT $page, $limit";
		$result = $db->query($query);
		while($row = $result->fetch_assoc())
			echo '<tr class="'.$rowswitch.'"><td><a href="index.php?page=favorites&amp;s=view&amp;id='.$row['user_id'].'">'.$row['user'].'</a></td><td>'.$row['fcount'].'</td></tr>';
		$result->free_result();
		echo "</table></div><div id='paginator'>";
		print $misc->pagination($_GET['page'],$_GET['s'],$eh,$limit,$page_limit,$numrows,$page);
	}
	else if(isset($_GET['s']) && $_GET['s'] == "delete" && isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$pid = $_GET['pid'];
		$id = $db->real_escape_string($_GET['id']);
		$user_id = $db->real_escape_string($_COOKIE['user_id']);
		$query = "SELECT fcount FROM $favorites_count_table WHERE user_id='$user_id'";
		$result = $db->query($query) or die(mysql_error());
		$row = $result->fetch_assoc();
		$count = $row['fcount'];
		$result->free_result();
		if($count > 0)
		{
			$query = "DELETE FROM $favorites_table WHERE user_id='$user_id' and favorite='$id'";
			$db->query($query) or die(mysql_error());
			$query = "UPDATE $favorites_count_table SET fcount=fcount-1 WHERE user_id='$user_id'";
			$db->query($query) or die(mysql_error());
		}
		header("Location:index.php?page=favorites&s=view&id=$user_id&pid=".$pid."");
		exit;
	}
?></div></body></html>