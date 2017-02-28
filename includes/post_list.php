<?php
	//number of images/page
	$limit = 32;
	$tags_limit = 20;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 10;
	require "includes/header.php";
	$cache = new cache();
	$domain = $cache->select_domain();
	$misc = new misc();
?>
<script type="text/javascript">
var posts = {}; var pignored = {};
</script>
<section><div id="post-list">
<aside class="sidebar">
<div class="space">
<h5>Search</h5>
<form action="index.php?page=search" method="post">
<input id="tags" name="tags" size="20" type="text" value="<?php if(isset($_GET['tags']) && $_GET['tags'] != "all"){ print str_replace("%",'',str_replace("'","&#039;",str_replace('"','&quot;',$_GET['tags'])));}?>" />
<br /><input name="commit" style="margin-top: 3px; background: #fff; border: 1px solid #dadada; width: 172px;" type="submit" value="Search" />
</form>
<small>(Supports wildcard *)</small>
</div>
<div class="space">
</div>
<?php
	/*Begining of tag listing on left side of site.
	First let's get the current page we're on.	*/
	if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
		$page = $db->real_escape_string($_GET['pid']);
	else
		$page = 0;
	$search = new search();
	$no_cache = null;
	$tag_count = null;
	// No tags  have been searched for so let's check the last_update value to update our main page post count for parent posts. Updated once a day.
	if(!isset($_GET['tags']) || isset($_GET['tags']) && $_GET['tags'] == "all" || isset($_GET['tags']) && $_GET['tags'] == "")
	{
        if(isset($_GET['sort']) && $_GET['sort'] != "") {
            $sort = $_GET['sort'];
        } else {
            $sort = "score";
        }
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
		if(strpos(strtolower($new_tag_cache),"parent:") === false && strpos(strtolower($new_tag_cache),"user:") === false && strpos(strtolower($new_tag_cache),"rating:") === false && strpos($new_tag_cache,"*") === false)
			$new_tag_cache = $misc->windows_filename_fix($new_tag_cache);
		if(isset($_GET['pid']) && is_numeric($_GET['pid']) && $_GET['pid'] > 0)
			$page = ($_GET['pid']/$limit)+1;
		else
			$page = 0;
		if($tag_count > 1 || !is_dir("$main_cache_dir".""."search_cache/".$new_tag_cache."/") || !file_exists("$main_cache_dir".""."search_cache/".$new_tag_cache."/".$page.".html") || strpos(strtolower($new_tag_cache),"all") !== false || strpos(strtolower($new_tag_cache),"user:") !== false || strpos(strtolower($new_tag_cache),"rating:") !== false || substr($new_tag_cache,0,1) == "-" || strpos(strtolower($new_tag_cache),"*") !== false || strpos(strtolower($new_tag_cache),"parent:") !== false)
		{
			if(!is_dir("$main_cache_dir".""."search_cache/"))
				@mkdir("$main_cache_dir".""."search_cache");
			$query = $search->prepare_tags(implode(" ",$tags));
			$result = $db->query($query) or die($db->error);
			$numrows = $result->num_rows;
			$result->free_result();
			$no_cache = true;
			/* XXX FIXME
			if($tag_count > 1 || strtolower($new_tag_cache) == "all" || strpos(strtolower($new_tag_cache),"user:") !== false || strpos(strtolower($new_tag_cache),"rating:") !== false || substr($new_tag_cache,0,1) == "-" || strpos(strtolower($new_tag_cache),"*") !== false || strpos(strtolower($new_tag_cache),"parent:") !== false)
				$no_cache = false;
			else
			{
				if(!is_dir("$main_cache_dir".""."search_cache/".$new_tag_cache."/"))
					@mkdir("$main_cache_dir".""."search_cache/".$new_tag_cache."/");
				$no_cache = true;
			}
			*/
		}
		else
		{
			if(!is_dir("$main_cache_dir".""."search_cache/"))
				mkdir("$main_cache_dir".""."search_cache");
			$tags = $new_tag_cache;
			if(isset($_GET['pid']) && is_numeric($_GET['pid']) && $_GET['pid'] > 0)
				$page = ($_GET['pid']/$limit)+1;
			else
				$page = 0;

			$cache = new cache();
			$no_cache = true;
			if(is_dir("$main_cache_dir".""."search_cache/".$tags."/") && file_exists("$main_cache_dir".""."search_cache/".$tags."/".$page.".html"))
			{
				$data = $cache->load("search_cache/".$tags."/".$page.".html");
				echo $data;
				$numrows = 1;
				$no_cache = false;
			}
		}
	}
	//No images found
    
	if($numrows == 0)
		print '</ul></div></div><article><div><h1>Nobody here but us chickens!</h1>';
	else
	{
		if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
			$page = $db->real_escape_string($_GET['pid']);
		else
			$page = 0;
		if(!isset($_GET['tags']) || isset($_GET['tags']) && $_GET['tags'] == "all" || isset($_GET['tags']) && $_GET['tags'] == "") {
			if ($sort == 'score') { 
				$scoresort = "score DESC,";
			} else {
				$scoresort = "";
			}
			$query = "SELECT id, image, directory, score, rating, tags, owner FROM $post_table WHERE parent = '0' " . $search->blacklist_fragment() . " ORDER BY $scoresort id DESC LIMIT $page, $limit";
		} else {
			if($no_cache === true || $tag_count > 1 || strpos(strtolower($new_tag_cache),"user:") !== false || strpos(strtolower($new_tag_cache),"rating:") !== false || substr($new_tag_cache,0,1) == "-" || strpos(strtolower($new_tag_cache),"*") !== false || strpos(strtolower($new_tag_cache),"parent:") !== false)
				$query = $query." LIMIT $page, $limit";
		}
		if(!isset($_GET['tags']) || $no_cache === true || $tag_count > 1 || strtolower($_GET['tags']) == "all" || strpos(strtolower($new_tag_cache),"user:") !== false || strpos(strtolower($new_tag_cache),"rating:") !== false || substr($new_tag_cache,0,1) == "-" || strpos(strtolower($new_tag_cache),"*") !== false || strpos(strtolower($new_tag_cache),"parent:") !== false)
		{
			if($no_cache === true)
				ob_start();

			$gtags = array();
			$images = '';
			$script = '<script type="text/javascript">';
			$tcount = 0;
			$result = $db->query($query) or die($db->error);
			//Limit main tag listing to $tags_limit tags. Keep the loop down to the minimum really.
			while($row = $result->fetch_assoc())
			{
				$tags = mb_trim($row['tags']);
				if($tcount <= $tags_limit)
				{
					$ttags = explode(" ",$tags);
					foreach($ttags as $current)
					{
						if($current != "" && $current != " " && empty($gtags[$current]))
						{
							$gtags[$current] = $current;
							++$tcount;
						}
					}
				}
				$images .= '<span class="thumb"><a id="p'.$row['id'].'" href="index.php?page=post&amp;s=view&amp;id='.$row['id'].'"><img src="'.$thumbnail_url.$misc->getThumb($row['image'], $row['directory']).'" alt="post" border="0" title="'.$row['tags'].' score:'.$row['score'].' rating:'. $row['rating'].'"/></a></span>';
				$script .= 'posts['.$row['id'].'] = {\'tags\':\''.strtolower(str_replace('\\',"&#92;",str_replace("'","&#039;",$tags))).'\'.split(/ /g), \'rating\':\''.$row['rating'].'\', \'score\':'.$row['score'].', \'user\':\''.str_replace('\\',"&#92;",str_replace(' ','%20',str_replace("'","&#039;",$row['owner']))).'\'};';
			}
			$result->free_result();
            
            $newest = '';

            if ($sort == "score") 
            {
                $query = "SELECT id, image, directory, score, rating, tags, owner FROM $post_table WHERE parent = '0' " . $search->blacklist_fragment() . " ORDER BY id DESC LIMIT 8";
                $result = $db->query($query) or die($db->error);
                //Limit main tag listing to $tags_limit tags. Keep the loop down to the minimum really.
                $newest .= "<div id='newest_images'><h5><a href=index.php?page=post&s=list&tags=all&sort=newest>Newest Images</a></h5><div>";
                while($row = $result->fetch_assoc())
                {
                    $newest .= '<span class="thumb"><a id="p'.$row['id'].'" href="index.php?page=post&amp;s=view&amp;id='.$row['id'].'"><img src="'.$thumbnail_url.$misc->getThumb($row['image'], $row['directory']).'" alt="post" border="0" title="'.$row['tags'].' score:'.$row['score'].' rating:'. $row['rating'].'"/></a></span>';
                    $script .= 'posts['.$row['id'].'] = {\'tags\':\''.strtolower(str_replace('\\',"&#92;",str_replace("'","&#039;",$tags))).'\'.split(/ /g), \'rating\':\''.$row['rating'].'\', \'score\':'.$row['score'].', \'user\':\''.str_replace('\\',"&#92;",str_replace(' ','%20',str_replace("'","&#039;",$row['owner']))).'\'};';
                }
                $result->free_result();
                $newest .= "<div class=space style='clear:both;'></div></div></div><div id='highest_rated_images'><h5>Highest Rated Images</h5><div>";
                $images .= "</div></div>";
            }

			if(isset($_GET['tags']) && $_GET['tags'] != "" && $_GET['tags'] != "all")
				$ttags = $db->real_escape_string(str_replace("'","&#039;",$_GET['tags']));
			else
				$ttags = "";
			asort($gtags);
			/*Tags have been sorted in ascending order
			Let's now grab the index count from database
			Needs to be escaped before query is sent!
			URL Decode and entity decode for the links
			*/
			echo "<div id='tag_list'><h5>Tags</h5><ul>";
			foreach($gtags as $current)
			{
				$query = "SELECT index_count FROM $tag_index_table WHERE tag='".$db->real_escape_string(str_replace("'","&#039;",$current))."'";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$t_decode = urlencode(html_entity_decode($ttags,ENT_NOQUOTES,"UTF-8"));
				$c_decode = urlencode(html_entity_decode($current,ENT_NOQUOTES,"UTF-8"));
				echo '<li><a href="index.php?page=post&amp;s=list&amp;tags='.$t_decode."+".$c_decode.'">+</a><a href="index.php?page=post&amp;s=list&amp;tags='.$t_decode."+-".$c_decode.'">-</a> <span style="color: #a0a0a0;">? <a href="index.php?page=post&amp;s=list&amp;tags='.$c_decode.'">'.str_replace('_',' ',$current).'</a> '.$row['index_count'].'</span></li>';
			}
			//Print out image results and filter javascript
			echo '<li><br /><br /></li></ul></div></aside><article>
<div style="text-align: center; padding: 3px 6px; margin: 15px;">
Filter content you don\'t want to see with "-<i>tag</i>". For instance, -loli would remove all content tagged loli from the post list.
</div>';
			$images .= "</div><br /><br /><div style='margin-top: 550px; text-align: right;'><a id=\"pi\" href=\"#\" onclick=\"showHideIgnored('0','pi'); return false;\"></a></div><div id='paginator'>";
			$script .= 'filterPosts(posts);
			</script>';
            echo $newest;
			echo $images;
			echo $script;

			//Pagination function. This should work for the whole site... Maybe.
			print $misc->pagination($_GET['page'],$_GET['s'],$id,$limit,$page_limit,$numrows,$_GET['pid'],$_GET['tags'],false,$sort);

		}
		/* XXX FIXME
		//Cache doesn't exist for search, make one.
		if($no_cache === true)
		{
			$data = ob_get_contents();
			ob_end_clean();
			if(isset($_GET['pid']) && is_numeric($_GET['pid']) && $_GET['pid'] > 0)
				$page = ($_GET['pid']/$limit)+1;
			else
				$page = 0;
			if($new_tag_cache != "")
			{
				if(!is_dir("$main_cache_dir".""."search_cache/".$new_tag_cache))
					@mkdir("$main_cache_dir".""."search_cache/".$new_tag_cache);
				$cache->save("search_cache/".$new_tag_cache."/".$page.".html",$data);
			}
			echo $data;
		}
		*/
		ob_end_flush();
	}
?>
</article><footer><a href="javascript:;" id="gal-toggle">Open Gallery</a><br /><br /><a href="index.php?page=post&amp;s=add">Add</a> | <a href="help/">Help</a></footer><br /><br />
</div></div></section><script type="text/javascript" src="script/gallery.js"></script></body></html>