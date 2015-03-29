<?php
	require "../inv.header.php";
	if($_POST['pconf'] !="1")
		die;
	$misc = new misc();
	$user = new user();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);	
	if($user->banned_ip($ip))
	{
		print "Action failed: ".$row['reason'];
		exit;
	}
	if(!$user->check_log())
	{
		if(!$anon_edit)
		{
			header('Location: ../index.php?page=account');
			exit;
		}
	}
	$user_id = $checked_user_id;
	$id = $db->real_escape_string($_POST['id']);
	$tags = $db->real_escape_string(mb_strtolower(str_replace('%','',htmlentities($_POST['tags'], ENT_QUOTES, 'UTF-8'))));
	$ttags = explode(' ',$tags);
	asort($ttags);
	$tags = implode(" ",$ttags);
	$tags = mb_trim(str_replace("  ","",$tags));
	if(substr($tags,0,1) != " ")
		$tags = " $tags";
	if(substr($tags,-1,1) != " ")
		$tags = "$tags ";
	$parent = '';
	$query = "SELECT tags FROM $post_table WHERE id='$id'";
	$result = $db->query($query);
	$row = $result->fetch_assoc();
	if($tags != $row['tags'])
	{
		$cache = new cache();
		$tclass = new tag();
		$mtags = explode(" ",$row['tags']);
		$misc = new misc();
		foreach($mtags as $current)
		{
			if($current != "")
			{
				$tclass->deleteindextag($current);
				if(is_dir("$main_cache_dir".""."search_cache/".$current."/"))
					$cache->destroy_page_cache("search_cache/".$current."/");
				else
					if(is_dir("$main_cache_dir".""."search_cache/".$misc->windows_filename_fix($current)."/"))
				$cache->destroy_page_cache("search_cache/".$misc->windows_filename_fix($current)."/");			
			}
		}
		foreach($ttags as $current)
		{
			if($misc->is_html(html_entity_decode($current,ENT_QUOTES,'UTF-8')))
			{
				header('Location: ../index.php');
				exit;
			}
			if(strpos($current,'parent:') !== false)
			{
				$parent = str_replace("parent:","",$current);
				if(!is_numeric($parent))
					$parent = '';
				$query = "SELECT COUNT(*) FROM $post_table WHERE id='$parent'";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				if($row['COUNT(*)'] < 1)
					$parent = '';
				$current = '';										
			}
			if($current != "")
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
			if($current != "" && $current != " ")
			{
				$ttags = $tclass->filter_tags($tags,$current, $ttags);
				if(is_dir("$main_cache_dir".""."search_cache/".$misc->windows_filename_fix($current)."/"))
					$cache->destroy_page_cache("search_cache/".$misc->windows_filename_fix($current)."/");
				else
					if(is_dir("$main_cache_dir".""."search_cache/$current/"))
						$cache->destroy_page_cache("search_cache/$current/");
			}
		}
		asort($ttags);
		foreach($ttags as $current)
			$tclass->addindextag($current);
		$tags = implode(" ",$ttags);
		if(substr($tags,0,1) != " ")
			$tags = " $tags";
		if(substr($tags,-1,1) != " ")
			$tags = "$tags ";
		$date = date("Y-m-d H:i:s");
		$new_tags = str_replace($row['tags'],"",$tags);
		$ret = "SELECT tags_version FROM $post_table WHERE id='$id'";
		$set = $db->query($ret);
		$retme = $set->fetch_assoc();
		$version = $retme['tags_version'];
		$version = $version+1;
		$set->free_result();
		$result->free_result();
		$ret = "INSERT INTO $tag_history_table(id, tags, version, user_id, updated_at, ip) VALUES('$id', '$tags', '$version', '$user_id', '$date', '$ip')";
		$db->query($ret) or die($db->error);
		$ret = "UPDATE $post_table SET tags_version=tags_version+1 WHERE id='$id'";
		$db->query($ret) or die($db->error);
		$query = "UPDATE $user_table SET tag_edit_count = tag_edit_count+1 WHERE id='$user_id'";
		$db->query($query);
	}
	else
		$new_tags = '';
	$title = $db->real_escape_string($_POST['title']);
	$rating = $db->real_escape_string($_POST['rating']);
	if($rating == "e")
		$rating = "Explicit";
	else if($rating == "q")
		$rating = "Questionable";
	else
		$rating = "Safe";
	$source = $db->real_escape_string(htmlentities($_POST['source'], ENT_QUOTES, "UTF-8"));
	$tmp_parent = $db->real_escape_string($_POST['parent']);
	if($parent == $id)
		$parent = '';
	if($tmp_parent == $id)
		$tmp_parent = '';	
	if($tmp_parent != '' && is_numeric($tmp_parent))
	{
		$query = "SELECT COUNT(*) FROM $post_table WHERE id='$tmp_parent'";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		if($row['COUNT(*)'] > 0)
			$parent = $tmp_parent;
	}
	$query = "SELECT parent FROM $parent_child_table WHERE child='$id'";
	$result = $db->query($query);
	$row = $result->fetch_assoc();
	$cache = new cache();
	$tmp_parent = $row['parent'];
	if(is_numeric($row['parent']))
	{
		if($tmp_parent != $parent && $parent == '' || $tmp_parent != $parent && $parent == '0')
		{
			$query = "DELETE FROM $parent_child_table WHERE child='$id' AND parent='".$row['parent']."'";
			$db->query($query) or die($db->error);
			$cache->destroy("cache/".$row['parent']."/post.cache");
		}
		else if($tmp_parent != $parent)
		{
			$query = "UPDATE $parent_child_table SET parent='$parent' WHERE child='$id' AND parent='".$row['parent']."'";
			$db->query($query);
		}
		if($tmp_parent != $parent)
		{
			$misc = new misc();
			$mtags = explode(" ",$tags);
			foreach($mtags as $current)
			{
				if($current != "")
				{
					if(is_dir("../search_cache/".$misc->windows_filename_fix($current)."/"))
						$cache->destroy_page_cache("search_cache/".$misc->windows_filename_fix($current)."/");
				}	
			}
		}
	}
	else
	{
		if(is_numeric($parent))
		{
			$query = "INSERT INTO $parent_child_table(parent,child) VALUES('$parent','$id')";
			$db->query($query);
		}
	}
	if($tmp_parent != $parent && $parent != "" && is_numeric($parent))
	{
		$cache->destroy("cache/".$parent."/post.cache");
		$misc = new misc();
		$mtags = explode(" ",$tags);
		foreach($mtags as $current)
		{
			if($current != "")
			{
				if(is_dir("$main_cache_dir".""."search_cache/".$misc->windows_filename_fix($current)."/"))
					$cache->destroy_page_cache("search_cache/".$misc->windows_filename_fix($current)."/");
			}	
		}
	}
	$cache = new cache();
	$misc = new misc();
	$query = "SELECT rating, tags FROM $post_table WHERE id='$id' LIMIT 1";
	$result = $db->query($query) or die($db->error);
	$row = $result->fetch_assoc();
	if($row['rating'] != $rating)
	{
		$tmp_tags = explode(" ",$row['tags']);
		foreach($tmp_tags as $current)
			$cache->destroy_page_cache("search_cache/".$misc->windows_filename_fix($current)."/");
	}
	if($parent == '')
		$parent = 0;
	if($parent != $tmp_parent)
	{
		$query = "UPDATE $post_count_table SET last_update='20060101' WHERE access_key='posts'";
		$db->query($query);
	}
	$query = "UPDATE $post_table SET title='$title', tags='$tags', recent_tags='$new_tags', rating='$rating', source='$source', parent='$parent' WHERE id='$id'";
	$db->query($query);
	$cache->destroy("cache/".$id."/post.cache");
	@header("Location:../index.php?page=post&s=view&id=$id");
?>