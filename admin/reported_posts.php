<?php
	if(!defined('_IN_ADMIN_HEADER_'))
		die;

	if(isset($_GET['unreport']) && is_numeric($_GET['unreport']))
	{
		$post_id = $db->real_escape_string($_GET['unreport']);
		$query = "UPDATE $post_table SET spam='0' WHERE id='$post_id'";
		if($db->query($query))
		{
			$cache = new cache();
			$cache->destroy_page_cache("cache/".$post_id);
			print "<center>Unflagged Post!</center>";
		}
	}

	//number of reports/page
	$limit = 20;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 4;
	print '<div class="content"><table class="highlightable" style="width: 100%; font-size: 12px;"><tr><th>Post ID:</th><th>Reason:</th><th>Score:</th><th>Date Posted:</th><th>Unflag:</th></tr>';
	if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
		$page = $db->real_escape_string($_GET['pid']);
	else
		$page = 0;
	$query = "SELECT COUNT(*) FROM $post_table WHERE spam=TRUE";
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
		$query = "SELECT id, directory, image, reason FROM $post_table WHERE spam=TRUE ORDER BY id LIMIT $page, $limit";
		$result = $db->query($query);
		while($row = $result->fetch_assoc())
			echo '<tr><td style="width: 180px;"><center><a href="../index.php?page=post&s=view&id='.$row['id'].'"><img src="'.$site_url.'/'.$thumbnail_folder.'/'.$row['directory'].'/thumbnail_'.$row['image'].'"></a></center></td><td>'.$row['reason'].'</td><td>'.$row['score'].'</td><td>'.$row['creation_date'].'</td><td><a href="'.$site_url.'/admin/index.php?page=reported_posts&amp;unreport='.$row['id'].'">Unflag</a></td></tr><tr><td><br></td></tr>';
		$result->free_result();
		
		echo "</table><br /><br />";
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
			echo '<a href="?pid=0&amp;page=reported_posts" alt="first page"><<</a> <a class="news" href="?pid='.$back_page.'&amp;page=reported_posts" alt="back"><</a>';
		}
		for($i=$start; $i <= $tmp_limit; $i++) // loop through each page and give link to it.
		{
			$ppage = $limit*($i - 1);
			if($ppage >= 0)
			{
				if($ppage == $page)
					echo '<b>'.$i.'</b>';
				else
					echo '<a href="?pid='.$ppage.'">'.$i.'</a>';
			}
		}
		if (!((($page+$limit) / $limit) >= $pages) && $pages != 1) 
		{ 
			// If last page don't give next link.
			$next_page = $page + $limit;
			echo '<a href="?pid='.$next_page.'&amp;page=reported_posts" alt="next">></a> <a  href="?pid='.$lastpage.'&amp;page=reported_posts" alt="last page">>></a></font></center>';
		}
	}
?>
</div></div></body></html>