<?php
	class post
	{
		function show($id)
		{
			global $db, $post_table;
			$id = $db->real_escape_string($id);
			$query = "SELECT * FROM $post_table WHERE id = '$id' LIMIT 1";
			$result = $db->query($query);
			if($result->num_rows == "0")
				return false;
			$row = $result->fetch_assoc();
			return $row;
		}
		
		function get_notes($id)
		{
			global $db, $note_table;
			$id = $db->real_escape_string($id);
			$query = "SELECT * FROM $note_table WHERE post_id='$id'";
			$result = $db->query($query);
			return $result;
		}
		
		function prev_next($id)
		{
			global $db, $post_table;
			$query = "SELECT SQL_NO_CACHE id FROM $post_table WHERE id < $id ORDER BY id DESC LIMIT 1";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$prev_next[] = $row['id'];
			$query = "SELECT SQL_NO_CACHE id FROM $post_table WHERE id > $id ORDER BY id DESC LIMIT 1";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$prev_next[] = $row['id'];
			return $prev_next;
		}
		
		function has_children($id)
		{
			global $db, $parent_child_table;
			$query = "SELECT * FROM $parent_child_table WHERE parent = '$id' LIMIT 1";
			$result = $db->query($query);
			if($result->num_rows == "0")
				return false;
			else
				return true;
		}
		
		function index_count($current)
		{
			global $db, $tag_index_table;
			$current = $db->real_escape_string(htmlentities($current, ENT_QUOTES, "UTF-8"));
			$query = "SELECT index_count FROM $tag_index_table WHERE tag='$current' LIMIT 1";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			return $row;
		}
	}
?>