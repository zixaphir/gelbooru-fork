<?php
	if(isset($_GET['limit']) && $_GET['limit'] != "" && is_numeric($_GET['limit']) && $_GET['limit'] >= 0)
	{
		$limit = $db->real_escape_string($_GET['limit']);
		if ($limit > 100) {
			$limit = 100;
		}
	}
	else
		$limit = 32;
	if(isset($_GET['t']) && $_GET['t'] == 'json')
	{
		$api_type = 'json';
	}
	else
	{
		$api_type = 'xml';
	}
	if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
	{
		$pid  = $db->real_escape_string($_GET['pid']);
		$page = $pid * $limit;
	}
	else
		$page = $pid = 0;
	$no_cache = null;
	$tag_count = null;
	//No tags  have been searched for so let's check the last_update value to update our main page post count for parent posts. Updated once a day.
	if(!isset($_GET['tags']) || isset($_GET['tags']) && $_GET['tags'] == "all" || isset($_GET['tags']) && $_GET['tags'] == "")
	{
		$query = "SELECT pcount, last_update FROM $post_count_table WHERE access_key='posts'";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		$numrows = $row['pcount'];
		$date = date("Ymd");
		if($row['last_update'] < $date)
		{
			$query = "SELECT COUNT(id) FROM posts WHERE parent = '0'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$numrows = $row['COUNT(id)'];
			$query = "UPDATE $post_count_table SET pcount='".$row['COUNT(id)']."', last_update='$date' WHERE access_key='posts'";
			$db->query($query);
		}
	}
	else
	{
		//Searched some tag, deal with page caching of html files.
		$tags = $db->real_escape_string(str_replace("%",'',mb_trim(htmlentities($_GET['tags'], ENT_QUOTES, 'UTF-8'))));
		$tags = explode(" ",$tags);
		$tag_count = count($tags);
		$new_tag_cache = urldecode($tags[0]);
        ob_start(); // XXX Weird bug where "new misc();" causes 4 tabs to be output?
		$misc = new misc();
        ob_end_clean();
		if(strpos(strtolower($new_tag_cache),"parent:") === false && strpos(strtolower($new_tag_cache),"user:") === false && strpos(strtolower($new_tag_cache),"rating:") === false && strpos($new_tag_cache,"*") === false)
			$new_tag_cache = $misc->windows_filename_fix($new_tag_cache);
		if($tag_count > 1 || !is_dir("$main_cache_dir".""."api_cache/".$new_tag_cache."/") || !file_exists("$main_cache_dir".""."api_cache/".$new_tag_cache."/".$page.".json") || strpos(strtolower($new_tag_cache),"all") !== false || strpos(strtolower($new_tag_cache),"user:") !== false || strpos(strtolower($new_tag_cache),"rating:") !== false || substr($new_tag_cache,0,1) == "-" || strpos(strtolower($new_tag_cache),"*") !== false || strpos(strtolower($new_tag_cache),"parent:") !== false)
		{
			if(!is_dir("$main_cache_dir".""."api_cache/"))
				@mkdir("$main_cache_dir".""."api_cache");
			$search = new search();
			$query = $search->prepare_tags(implode(" ",$tags));
			$result = $db->query($query) or die($db->error);
			$numrows = $result->num_rows;
			$result->free_result();
			if($tag_count > 1 || strtolower($new_tag_cache) == "all" || strpos(strtolower($new_tag_cache),"user:") !== false || strpos(strtolower($new_tag_cache),"rating:") !== false || substr($new_tag_cache,0,1) == "-" || strpos(strtolower($new_tag_cache),"*") !== false || strpos(strtolower($new_tag_cache),"parent:") !== false)
				$no_cache = false;
			else
			{
				if(!is_dir("$main_cache_dir".""."api_cache/".$new_tag_cache."/"))
					@mkdir("$main_cache_dir".""."api_cache/".$new_tag_cache."/");
				$no_cache = true;
			}
		}
		else
		{
			if(!is_dir("$main_cache_dir".""."api_cache/"))
				mkdir("$main_cache_dir".""."api_cache");
			$tags = $new_tag_cache;
			$cache = new cache();
			$no_cache = true;
			if(is_dir("$main_cache_dir".""."api_cache/".$tags."/") && file_exists("$main_cache_dir".""."api_cache/".$tags."/".$page.".".$api_type))
			{
				$data = $cache->load("api_cache/".$tags."/".$page.".".$api_type);
				echo $data;
				$numrows = 1;
				$no_cache = false;
			}
		}
	}
	//No images found
	if($numrows == 0)
		if ($api_type == 'json') {
			print '{"offset":"'.$page.'","count":"0",posts":[]}';
		}
		else
		{
			print '<?xml version="1.0" encoding="UTF-8"?><posts offset="'.$page.'" count="0"></posts>';
		}
	else
	{
		if(!isset($_GET['tags']) || isset($_GET['tags']) && $_GET['tags'] == "all" || isset($_GET['tags']) && $_GET['tags'] == "")
			$query = "SELECT * FROM $post_table WHERE parent = '0' ORDER BY id DESC LIMIT $page, $limit";
		else
		{
			if($no_cache === true || $tag_count > 1 || strpos(strtolower($new_tag_cache),"user:") !== false || strpos(strtolower($new_tag_cache),"rating:") !== false || substr($new_tag_cache,0,1) == "-" || strpos(strtolower($new_tag_cache),"*") !== false || strpos(strtolower($new_tag_cache),"parent:") !== false)
				$query = $query." LIMIT $page, $limit";
		}
		if(!isset($_GET['tags']) || $no_cache === true || $tag_count > 1 || strtolower($_GET['tags']) == "all" || strpos(strtolower($new_tag_cache),"user:") !== false || strpos(strtolower($new_tag_cache),"rating:") !== false || substr($new_tag_cache,0,1) == "-" || strpos(strtolower($new_tag_cache),"*") !== false || strpos(strtolower($new_tag_cache),"parent:") !== false)
		{
			if($no_cache === true)
				ob_start();

			$result = $db->query($query) or die($db->error);

			if ($api_type == 'json')
			{
				header('Content-type: application/json');
				$posts = array();

				$i = 0;
				while($row = $result->fetch_assoc())
				{
					$posts[$i++] = createPostObject($row);
				}
				$postsArr = array('offset' => $page, 'count' => $numrows, 'posts' => $posts);
				$result->free_result();

				echo json_encode($postsArr);
			}
			else
			{
				header('Content-type: text/xml');
				$posts = '<?xml version="1.0" encoding="UTF-8"?><posts offset="'.$page.'" count="'.$numrows.'">'."\r\n";
				while($row = $result->fetch_assoc())
				{
					$posts .= createPostXML($row);
				}
				$posts .= '</posts>';
				echo $posts;
			}
		}
		//Cache doesn't exist for search, make one.
		if($no_cache === true)
		{
			$data = ob_get_contents();
			ob_end_flush();
			if($new_tag_cache != "")
			{
				if(!is_dir("$main_cache_dir".""."api_cache/".$new_tag_cache))
					@mkdir("$main_cache_dir".""."api_cache/".$new_tag_cache);
				$cache->save("api_cache/".$new_tag_cache."/".$page.".".$api_type,$data);
			}
		}
	}
?>