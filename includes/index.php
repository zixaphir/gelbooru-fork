<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
<?php
	echo '<title>'.$site_url3.'</title>
	<link rel="stylesheet" type="text/css" media="screen" href="'.$site_url.'/default.css?2" title="default" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	</head>
	<body>
	<div id="static-index">
		<h1 style="font-size: 52px; margin-top: 1em;"><a href="'.$site_url.'">'.$site_url3.'</a></h1>
	';
?>
	<div class="space" id="links">
		<a href="index.php?page=post&amp;s=list" title="A paginated list of every post">Posts</a>
		<a href="index.php?page=comment&amp;s=list">Comments</a>
		<a href="index.php?page=reg">Register</a>
		<a href="index.php?page=favorites&amp;s=list">Favorites</a>
	</div>
	<div class="space">
		<form action="index.php?page=search" method="post">
			<input id="tags" name="tags" size="30" type="text" value="" /><br/>
			<input name="searchDefault" type="submit" value="Search" />
		</form>
	</div>
	<div style="font-size: 80%; margin-bottom: 2em;">
		<p>
<?php
	$query = "UPDATE $hit_counter_table SET count=count+1";
	$db->query($query);
	$query = "SELECT t1.pcount, t2.count FROM $post_count_table AS t1 JOIN $hit_counter_table as t2 WHERE t1.access_key='posts'";
	$result = $db->query($query);
	$row = $result->fetch_assoc();
	echo 'Serving '.number_format($row['pcount']).' posts  -  Running <a href="http://gelbooru.com/">Gelbooru</a> Beta 0.1.11
	</p><br />';
	for ($i=0;$i<strlen($row['pcount']);$i++) 
	{
		$digit=substr($row['pcount'],$i,1);
		print '<img src="./counter/'.$digit.'.gif" border="0" alt="'.$digit.'"/>'; 						
	}
	echo '<br /><br /><small>Total number of visitors so far:'.number_format($row['count']).'</small>
	<br /><br /></div></div><br /><br /><br /><br />
	</body></html>';
?>