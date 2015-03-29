<?php
	//die("Maintenance mode. please try again in 1 hour.");
	error_reporting(0);
	ignore_user_abort(1);
	$misc = new misc();
	$userc = new user();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);	
	if($userc->banned_ip($ip))
	{
		print "Action failed: ".$row['reason'];
		exit;
	}	
	if(!$userc->check_log())
	{
		if(!$anon_can_upload)
			$no_upload = true;
	}
	else
	{
		if(!$userc->gotpermission('can_upload'))
			$no_upload = true;
	}
	if($no_upload)
	{
		print "You do not have permission to upload.";
		exit;
	}
	if(isset($_POST['submit']))
	{
		$image = new image();
		$uploaded_image = false;
		$parent = '';
		$error = '';
		if(empty($_FILES['upload']) && isset($_POST['source']) && $_POST['source'] != "" && substr($_POST['source'],0,4) == "http" || $_FILES['upload']['error'] != 0 && isset($_POST['source']) && $_POST['source'] != "" && substr($_POST['source'],0,4) == "http")
		{
			$iinfo = $image->getremoteimage($_POST['source']);
			if($iinfo === false)
				$error = $image->geterror()."<br />Could not add the image.";
			else
				$uploaded_image = true;
		}
		else if(!empty($_FILES['upload']) && $_FILES['upload']['error'] == 0)
		{
			$iinfo = $image->process_upload($_FILES['upload']);
			if($iinfo === false)
				$error = $image->geterror()."<br />An error occured. The image could not be added because it already exists or it is corrupted.";
			else
				$uploaded_image = true;
		}
		else
			print "No image given for upload.";
		if($uploaded_image == true)
		{
			$iinfo = explode(":",$iinfo);
			$tclass = new tag();
			$misc = new misc();
			$ext = strtolower(substr($iinfo[1],-4,10000));
			$source = $db->real_escape_string(htmlentities($_POST['source'],ENT_QUOTES,'UTF-8'));
			$title = $db->real_escape_string(htmlentities($_POST['title'],ENT_QUOTES,'UTF-8'));
			$tags = strtolower($db->real_escape_string(str_replace('%','',mb_strtolower(mb_trim(htmlentities($_POST['tags'],ENT_QUOTES,'UTF-8'))))));
			$ttags = explode(" ",$tags);
			$tag_count = count($ttags);		
			if($tag_count == 0)
				$ttags[] = "tagme";
			if($tag_count < 5 && strpos(implode(" ",$ttags),"tagme") === false)
				$ttags[] = "tagme";
			foreach($ttags as $current)
			{
				if(strpos($current,'parent:') !== false)
				{
					$current = '';
					$parent = str_replace("parent:","",$current);
					if(!is_numeric($parent))
						$parent = '';
				}
				if($current != "" && $current != " " && !$misc->is_html($current))
				{
					$ttags = $tclass->filter_tags($tags,$current, $ttags);
					$alias = $tclass->alias($current);
					if($alias !== false)
					{
						$key_array = array_keys($ttags, $current);
						foreach($key_array as $key)
							$ttags[$key] = $alias;
					}
				}
			}
			$tags = implode(" ",$ttags);
			foreach($ttags as $current)
			{
				if($current != "" && $current != " " && !$misc->is_html($current))
				{
					$ttags = $tclass->filter_tags($tags,$current, $ttags);
					$tclass->addindextag($current);
					$cache = new cache();
					
					if(is_dir("$main_cache_dir".""."search_cache/".$current."/"))
					{
						$cache->destroy_page_cache("search_cache/".$current."/");
					}
					else
					{
						if(is_dir("$main_cache_dir".""."search_cache/".$misc->windows_filename_fix($current)."/"))
							$cache->destroy_page_cache("search_cache/".$misc->windows_filename_fix($current)."/");		
					}
				}
			}
			asort($ttags);
			$tags = implode(" ",$ttags);
			$tags = mb_trim(str_replace("  ","",$tags));			
			if(substr($tags,0,1) != " ")
				$tags = " $tags";
			if(substr($tags,-1,1) != " ")
				$tags = "$tags ";
			$rating = $db->real_escape_string($_POST['rating']);
			if($rating == "e")
				$rating = "Explicit";
			else if($rating == "q")
				$rating = "Questionable";
			else
				$rating = "Safe";
			if($userc->check_log())
				$user = $checked_username;
			else
				$user = "Anonymous";

			$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
			$isinfo = getimagesize("./images/".$iinfo[0]."/".$iinfo[1]);
			$query = "INSERT INTO $post_table(creation_date, hash, image, title, owner, height, width, ext, rating, tags, directory, source, active_date, ip) VALUES(NOW(), '".md5_file("./images/".$iinfo[0]."/".$iinfo[1])."', '".$iinfo[1]."', '$title', '$user', '".$isinfo[1]."', '".$isinfo[0]."', '$ext', '$rating', '$tags', '".$iinfo[0]."', '$source', '".date("Ymd")."', '$ip')";
			if(!is_dir("./thumbnails/".$iinfo[0]."/"))
				$image->makethumbnailfolder($iinfo[0]);
			if(!$image->thumbnail($iinfo[0]."/".$iinfo[1]))
				print "Thumbnail generation failed! A serious error occured and the image could not be resized.<br /><br />";
			if(!$db->query($query))
			{
				print "failed to upload image."; print $query;
				unlink("./images/".$iinfo[0]."/".$iinfo[1]);
				$image->folder_index_decrement($iinfo[0]);
				$ttags = explode(" ",$tags);
				foreach($ttags as $current)
					$tclass->deleteindextag($current);
			}
			else
			{
				$query = "SELECT id, tags FROM $post_table WHERE hash='".md5_file('./images/'.$iinfo[0]."/".$iinfo[1])."' AND image='".$iinfo[1]."' AND directory='".$iinfo[0]."'  LIMIT 1";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$tags = $db->real_escape_string($row['tags']);
				$date = date("Y-m-d H:i:s");
				$query = "INSERT INTO $tag_history_table(id,tags,user_id,updated_at,ip) VALUES('".$row['id']."','$tags','$checked_user_id','$date','$ip')";
				$db->query($query) or die($db->error);				
				$cache = new cache();				
				if($parent != '' && is_numeric($parent))
				{
					$parent_check = "SELECT COUNT(*) FROM $post_table WHERE id='$parent'";
					$pres = $db->query($parent_check);
					$prow = $pres->fetch_assoc();
					if($prow['COUNT(*)'] > 0)
					{
						$temp = "INSERT INTO $parent_child_table(parent,child) VALUES('$parent','".$row['id']."')";
						$db->query($temp);
						$temp = "UPDATE $post_table SET parent='$parent' WHERE id='".$row['id']."'";
						$db->query($temp);
						$cache->destroy("cache/".$parent."/post.cache");	
					}
				}				
				if(is_dir("$main_cache_dir".""."cache/".$row['id']))
					$cache->destroy_page_cache("cache/".$row['id']);
				$query = "SELECT id FROM $post_table WHERE id < ".$row['id']." ORDER BY id DESC LIMIT 1";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$cache->destroy_page_cache("cache/".$row['id']);
				$query = "UPDATE $post_count_table SET last_update='20060101' WHERE access_key='posts'";
				$db->query($query);
				$query = "UPDATE $user_table SET post_count = post_count+1 WHERE id='$checked_user_id'";
				$db->query($query);
				print "Image added.";
			}
		}
	}
	header("Cache-Control: store, cache");
	header("Pragma: cache");
	require "includes/header.php";
	print $error;
?>
	<form method="post" target="" enctype="multipart/form-data">
	<table><tr><td>
	File:<br />
	<input type="file" name="upload" />
	<td></tr>
	<tr><td>
	Source:<br />
	<input type="text" name="source" value="" />
	</td></tr>
	<tr><td>
	Title:<br />
	<input type="text" name="title" value="" />
	</td></tr>
	<tr><td>
	Tags:<br />
	<input type="text" id="tags" name="tags" value="" /><br />
	&nbsp;&nbsp;&nbsp;&nbsp;Separate tags with spaces. (ex: green_eyes purple green_hair)
	</td></tr>
	<tr><td>
	Rating:<br />
	<input type="radio" name="rating" value="e" />Explicit
	<input type="radio" name="rating" value="q" checked="true" />Questionable
	<input type="radio" name="rating" value="s" />Safe
	</td></tr>
	<tr><td>
	My Tags:<br />
	<?php if(isset($_COOKIE['tags']) && $_COOKIE['tags'] != ""){$tags = explode(" ",str_replace('%20',' ',$_COOKIE['tags'])); foreach($tags as $current){echo "<a href=\"index.php?page=post&s=list&tags=".$current."\" id=\"t_".$current.'" onclick="toggleTags(\''.$current.'\',\'tags\',\'t_'.$current.'\'); return false;">'.$current.' </a>';}}else{echo '<a href="index.php?page=account-options">Edit</a>';} ?>
	<td></tr>
	<tr><td>
	<input type="submit" name="submit" value="Upload" />
	</td></tr>
	</table>
	</form>
	<script type="text/javascript">
	//<![CDATA[
	function toggleTags(tag, id, lid)
	{
		temp = new Array(1);
		temp[0] = tag;
		tags = $(id).value.split(" ");
		if(tags.include(tag))
		{
			$(id).value=tags.without(tag).join(" ");
			$(lid).innerHTML=tag+" ";
		}
		else
		{
			$(id).value=tags.concat(temp).join(" ");
			$(lid).innerHTML="<b>"+tag+"</b> ";
		}
	}
	//]]>
	</script></div></body></html>