<?php
	class tag
	{
		function __construct()
		{}
		
		function addindextag($tag)
		{
			global $db, $tag_index_table;
			$tag = $db->real_escape_string($tag);
			if($tag != "")
			{
				$query = "SELECT * FROM $tag_index_table WHERE tag='$tag'";
				$result = $db->query($query);
				if($result->num_rows == 1)
				{
					$row = $result->fetch_assoc();
					$query = "UPDATE $tag_index_table SET index_count='".($row['index_count'] + 1)."' WHERE tag='$tag'";
				}
				else
					$query = "INSERT INTO $tag_index_table(tag, index_count) VALUES('$tag', '1')";
				$db->query($query);
			}
		}
		
		function deleteindextag($tag)
		{
			global $db, $tag_index_table;
			$tag = $db->real_escape_string($tag);
			if($tag != "")
			{
				$query = "SELECT index_count FROM $tag_index_table WHERE tag='$tag'";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				if($row['index_count'] > 1)
					$query = "UPDATE $tag_index_table SET index_count='".($row['index_count'] - 1)."' WHERE tag='$tag'";
				else
					$query = "DELETE FROM $tag_index_table WHERE tag='$tag'";
				$db->query($query);
			}
		}
		
		function alias($tag)
		{
			global $db, $alias_table;
			$tag = $db->real_escape_string($tag);
			$query = "SELECT tag FROM $alias_table WHERE alias='$tag' AND status='accepted'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			if($row['tag'] != "" && $row['tag'] != NULL)
				return $row['tag'];
			return false;
		}
		
		function filter_tags($tags, $current, $ttags)
		{
			if(substr_count($tags, $current) > 1)
			{
				$temp_array = array();
				$key_array = array_keys($ttags, $current);
				$count = count($key_array)-1;
				for($i = 1; $i <= $count; $i++)
					$ttags[$key_array[$i]] = '';
				foreach($ttags as $current)
				{
					if($current != "" && $current != " ")
						$temp_array[] = $current;
				}
				$ttags = $temp_array;
			}
			return $ttags;
		}
	}
?>