<?php
	$query = "SELECT pcount FROM $post_count_table WHERE access_key='posts'";
	$result = $db->query($query) or die($db->error);	
	$row = $result->fetch_assoc();
	$result->close();
	$count = $row['pcount'];
	if($count < 1)
	{
		header("Location: index.php?page=post&s=list");
		exit;
	}
	$valid_post_found = false;
	if(isset($_COOKIE['tag_blacklist']))
		$blacklist = str_replace('&#92;',"\\",str_replace("&#039;","'",str_replace("%20"," ",$_COOKIE['tag_blacklist'])));
	else
		$blacklist = "";
	if(isset($_COOKIE['safe_only']))
	{
		$blacklist = explode(" ",$blacklist);
		if(!in_array("rating:explicit",$blacklist))
			$blacklist[] = "rating:explicit";
		if(!in_array("rating:questionable",$blacklist))
			$blacklist[] = "rating:questionable";
		$blacklist = implode(" ",$blacklist);
	}
	//prevents idiots from getting stuck in an infinity loop
	if(mb_strpos($blacklist,'rating:explicit',0,'UTF-8') !== false && mb_strpos($blacklist,'rating:questionable',0,'UTF-8') !== false && mb_strpos($blacklist,'rating:safe',0,'UTF-8') !== false)
		$override = true;
	else
		$override = false;
	//looks for a post with an acceptable rating to prevent eternal loop on missing ratings in combination with banned existing ratings
	$i = 0;
	$blacklist_array = explode(" ",$blacklist);
	while(!$valid_post_found)
	{
		$rand = mt_rand(1,$count);	
		$query = "SELECT id, rating, tags FROM $post_table WHERE id >='$rand' LIMIT 1";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$result->close();
		if(strpos($blacklist,'rating:'.strtolower($row['rating']),0) === false || $override || $i > 20)
			$valid_post_found = true;
		if($i < 20 && $valid_post_found == true)
		{
			foreach($blacklist_array as $current)
			{
				if(in_array($current,explode(" ",$row['tags'])) !== false)
				{
					$valid_post_found = false;
					break;
				}
			}
		}
		$i++;
	}
	header("Location:index.php?page=post&s=view&id=".$id);
?>