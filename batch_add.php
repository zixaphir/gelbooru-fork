<?php
	require "inv.header.php";
	//Give credit to which user?
	$user = "Anonymous";	
	$path = "import/";
	$image = new image();
	$folders = scandir($path);
	print "Processing folder for files... Please wait.<br><br>";
	//Scan directory for folders. Exclude . and ..
	foreach($folders as $folder)
	{
		if(is_dir($path.$folder) && $folder !="." && $folder !="..")
		{
			$cur_folder[] = $folder;
			$tags2[] = $folder;	
		}
	}
	$i = 0;
	foreach($cur_folder as $current_folder)
	{
		//Check for images in folder and add them one by one.
		$files = scandir($path.$current_folder);
		foreach($files as $file)
		{
			$extension = explode(".",$file);
			if($extension['1'] == "jpg" || $extension['1'] == "jpeg" || $extension['1'] == "png" || $extension['1'] == "bmp" || $extension['1'] == "gif")
			{
				$uploaded_image = false;
				//Extension looks good, toss it through the image processing section.
				$dl_url = $site_url.$path.rawurlencode($current_folder)."/".rawurlencode($file);
				$iinfo = $image->getremoteimage($dl_url);
				if($iinfo === false)
					$error = $image->geterror()."<br />Could not add the image.";
				else
					$uploaded_image = true;	
				//Ok, download of image was successful! (yay?)
				if($uploaded_image == true)
				{
					$iinfo = explode(":",$iinfo);
					$tclass = new tag();
					$misc = new misc();
					$ext = strtolower(substr($iinfo[1],-4,10000));
					$source = $db->real_escape_string(htmlentities($_POST['source'],ENT_QUOTES,'UTF-8'));
					$title = $db->real_escape_string(htmlentities($_POST['title'],ENT_QUOTES,'UTF-8'));
					$tags = strtolower($db->real_escape_string(str_replace('%','',htmlentities($tags2[$i],ENT_QUOTES,'UTF-8'))));
					$ttags = explode(" ",$tags);
					$tag_count = count($ttags);		
					if($tag_count == 0)
						$ttags[] = "tagme";
					if($tag_count < 5 && strpos($ttags,"tagme") === false)
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
					$tags = mb_trim($tags);
					$tags = " $tags ";
					$rating = "Questionable";
					$ip = "127.0.0.1";
					$isinfo = getimagesize("./images/".$iinfo[0]."/".$iinfo[1]);
					$query = "INSERT INTO $post_table(creation_date, hash, image, title, owner, height, width, ext, rating, tags, directory, source, active_date, ip) VALUES(NOW(), '".md5_file("./images/".$iinfo[0]."/".$iinfo[1])."', '".$iinfo[1]."', '$title', '$user', '".$isinfo[1]."', '".$isinfo[0]."', '$ext', '$rating', '$tags', '".$iinfo[0]."', '$source', '".date("Ymd")."', '$ip')";
					if(!is_dir("./thumbnails/".$iinfo[0]."/"))
						$image->makethumbnailfolder($iinfo[0]);
					if(!$image->thumbnail($iinfo[0]."/".$iinfo[1]))
						print "Thumbnail generation failed! A serious error occured and the image could not be resized.<br /><br />";
					if(!$db->query($query))
					{
						print "failed to upload image.";
						unlink("./images/".$iinfo[0]."/".$iinfo[1]);
						$image->folder_index_decrement($iinfo[0]);
						$ttags = explode(" ",$tags);
						foreach($ttags as $current)
							$tclass->deleteindextag($current);
					}
					else
					{
						$query = "SELECT id FROM $post_table WHERE hash='".md5_file('./images/'.$iinfo[0]."/".$iinfo[1])."' AND image='".$iinfo[1]."' AND directory='".$iinfo[0]."'  LIMIT 1";
						$result = $db->query($query);
						$row = $result->fetch_assoc();
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
						print "Image added.";
					}
				}
				print "Valid Extension<br>".$tags2[$i]." | ";
				print $file."<br><br>";
			}
		}
		$i++;
	}
?>