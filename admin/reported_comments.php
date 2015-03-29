<?php
	if(!defined('_IN_ADMIN_HEADER_'))
		die;

	if(isset($_GET['unreport']) && is_numeric($_GET['unreport']))
	{
		$comment_id = $db->real_escape_string($_GET['unreport']);
		$query = "UPDATE $comment_table SET spam='0' WHERE id='$comment_id'";
		if($db->query($query))
		{
			$cache = new cache();
			$query = "SELECT post_id FROM $comment_table WHERE id='$comment_id'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$cache->destroy_page_cache("cache/".$row['post_id']);
			$cache->create_page_cache("cache/".$row['post_id']);
			echo '<center>Unflagged comment!</center>';
		}
	}
	echo '<div class="content"><table class="highlightable" style="font-size: 12px; width: 100%;"><tr><th>Remove?</th><th>Post ID:</th><th>Comment:</th><th>Score:</th><th>Date Posted:</th><th>Unflag:</th></tr>';
	//number of reports/page
	$limit = 50;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 4;
	
	if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
		$page = $db->real_escape_string($_GET['pid']);
	else
		$page = 0;
	$query = "SELECT COUNT(*) FROM $comment_table WHERE spam='1' ORDER BY id ASC";
	$result = $db->query($query);
	$row = $result->fetch_assoc();
	$numrows = $row['COUNT(*)'];
	if($numrows == 0)
		print "<h1>No reports found.</h1>";
	else
	{
		$pages = intval($numrows/$limit);
		if ($numrows%$limit) 
			$pages++;
		$current = ($page/$limit) + 1;
		if ($pages < 1 || $pages == 0 || $pages == "")
			$total = 1;
		else
			$total = $pages;
		$first = $page + 1;
		if (!((($page + $limit) / $limit) >= $pages) && $pages != 1)
			$last = $page + $limit;
		else
			$last = $numrows;
		$query = "SELECT id, comment, ip, user, posted_at, score, post_id FROM $comment_table WHERE spam='1' ORDER BY id LIMIT $page, $limit";
		$result = $db->query($query);
		while($row = $result->fetch_assoc())
		{
			$user = $row['user'];
			$date = $row['posted_at'];
			$date = date("M d, y | g:i a",$date);
			if($user == "Anonymous")
				$user = '<span style="color:#ff0000;">Anonymous</span>';
			else
				$user = '<span style="color: #007700;">'.$user.'</span>';

			echo '<tr><td><a href="../public/remove.php?id='.$row['id'].'&amp;removecomment=1&amp;post_id='.$row['post_id'].'">Remove</a></td><td><a href="../index.php?page=post&s=view&id='.$row['post_id'].'">'.$row['post_id'].'</a> '.$row['reason'].'</td><td style="width: 600px; padding: 5px 0px 5px 0px;">'.htmlentities($row['comment']).' - <span style="color: #770000; font-size: 11px;">'.$row['ip'].'</span> '.$user.'</td><td><center>'.$row['score'].'</center></td><td>'.$date.'</td><td><a href="'.$site_url.'/admin/?page=reported_comments&unreport='.$row['id'].'">Unflag</a></td></tr><tr><td><br></td></tr>';
		}
		$result->free_result();
		echo '</table><br /><br /><div id="paginator">';
		if($page == 0)
			$start = 1;
		else
			$start = ($page/$limit) + 1;
		$tmp_limit = $start + $page_limit;
		if($tmp_limit > $pages)
			$tmp_limit = $pages;
		if($pages > $page_limit)
			$lowerlimit = $pages - $page_limit;
		if($start > $lowerlimit)
			$start = $lowerlimit;
		$lastpage = $limit*($pages - 1);
		if($page != 0 && !((($page+$limit) / $limit) > $pages)) 
		{ 
			// Don't show back link if current page is first page.
			$back_page = $page - $limit;
			echo '<a href="?pid=0&page=reported_comments" alt="first page"><<</a> <a class="news" href="?pid='.$back_page.'&page=reported_comments" alt="back"><</a>';
		}
		for($i=$start; $i <= $tmp_limit; $i++) // loop through each page and give link to it.
		{
			$ppage = $limit*($i - 1);
			if($ppage >= 0)
			{
				if($ppage == $page)
					echo '<b>'.$i.'</b> ';
				else
					echo '<a href="?pid='.$ppage.'&page=reported_comments">'.$i.'</a>';
			}
		}
		if (!((($page+$limit) / $limit) >= $pages) && $pages != 1) 
		{ 
			// If last page don't give next link.
			$next_page = $page + $limit;
			echo '<a href="?pid='.$next_page.'&page=reported_comments" alt="next">></a> <a href="?pid='.$lastpage.'&page=reported_comments" alt="last page">>></a></font></center>';
		}
	}
?>
</div></div></body></html>