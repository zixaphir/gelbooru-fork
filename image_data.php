<?php
	if(!isset($_GET['start']) || !isset($_GET['limit']))
		die;
	require "inv.header.php";
	$start = $db->real_escape_string($_GET['start']);
	$limit = $db->real_escape_string($_GET['limit']);	
	if(!is_numeric($start) || !is_numeric($limit))
		die;
	if($limit > 100)
		$limit = 100;
	$query = "SELECT id, image, directory, score, rating, tags, height, width, hash FROM  $post_table WHERE id >= '$start' LIMIT $limit";
	$result = $db->query($query) or die($db->error);
	$count = $result->num_rows;
	header("Content-type: text/xml");
	print '<?xml version="1.0" encoding="UTF-8"?>';
	print '<posts count="'.$count.'" offset="0">';	
	while($row = $result->fetch_assoc())
	{
			$tags = str_replace("&#039;","'",$row['tags']);
			$tags = substr($tags,1,strlen($tags));
			$tags = substr($tags,0,strlen($tags)-1);
			if(strpos($tags,'&') !== false)
				$tags = str_replace("&", "&amp;", $tags);
			if(strpos($tags,'>') !== false)
				$tags = str_replace(">", "&gt;", $tags);
			if(strpos($tags,'<') !== false)
				$tags = str_replace("<", "&lt;", $tags);
			if(strpos($tags,"'") !== false)
				$tags = str_replace("'", "&apos;", $tags);
			if(strpos($tags,'"') !== false)
				$tags = str_replace('"', "&quot;", $tags);
			if(strpos($tags,'\r') !== false)
				$tags = str_replace('\r', "", $tags); 
			$thumbnail_data = getimagesize("./thumbnails/".$row['directory']."/thumbnail_".$row['image']);
			$thumb_width = $thumbnail_data[0];
			$thumb_height = $thumbnail_data[1];
			$rating = strtolower(substr($row['rating'],0,1));
			print '<post tags="'.$tags.'" score="'.$row['score'].'" md5="'.$row['hash'].'" rating="'.$rating.'" preview_url="'.$site_url.'/thumbnail.php/'.$row['id'].'/" preview_height="'.$thumb_height.'" preview_width="'.$thumb_width.'" height="'.$row['height'].'" width="'.$row['width'].'" id="'.$row['id'].'"/>';	
	}
?>
</posts>