<?php
	if($_GET['s'] == "post")
	{
		// require "includes/dapi_post.php"; ?
		$post = new post();

		function fixTags($tags)
		{
			$tags = mb_trim($tags);
			$tags = str_replace("&#039;","'",$tags);
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
			return $tags;
		}

		function getUserID($name)
		{
			global $db, $user_table;
			$result = $db->query("SELECT id FROM $user_table WHERE user='$name' LIMIT 1");

			if ($result)
				$user_id = $result->fetch_assoc()['id'];
			else
				$user_id = '';

			if ($user_id == 0)
				$user_id = '';

			return $user_id;
		}

		function createPostObject($row) {
			global $site_url, $image_folder, $thumbnail_url, $post;
			$file_url = $site_url.'/'.$image_folder.'/'.$row['directory'].'/'.$row['image'];

			$parent_id = $row['parent'];
			if ($parent_id == 0)
				$parent_id = '';

			return array('post' => array (
				'width'          => $row['width'],
				'height'         => $row['height'],
				'sample_width'   => $row['width'],
				'sample_height'  => $row['height'],
				'preview_width'  => '150px',
				'preview_height' => '150px',
				'score'          => $row['score'],
				'file_url'       => $file_url,
				'sample_url'     => $file_url,
				'parent_id'      => $parent_id,
				'preview_url'    => $thumbnail_url.'/'.$row['directory'].'/thumbnail_'.$row['image'],
				'rating'         => strtolower(substr($row['rating'], 0, 1)),
				'tags'           => fixTags($row['tags']),
				'id'             => $row['id'],
				// 'change'         => 'UNIMPLEMENTED',
				'md5'            => $row['hash'],
				'creator_id'     => getUserID($row['owner']),
				'created_at'     => $row['creation_date'],
				// 'status'         => 'UNIMPLEMENTED',
				'source'         => $row['source'],
				'has_notes'      => $post->has_notes($row['id']),
				'has_comments'   => !empty($row['last_comment']),
				'has_children'   => $post->has_children($row['id'])
			));
		}

		function createPostXML($row)
		{
			global $site_url, $image_folder, $thumbnail_url, $post;
			$parent_id = $row['parent'];
			if ($parent_id == 0)
				$parent_id = '';

			$has_notes = $post->has_notes($row['id']);
			if ($has_notes)
				$has_notes = 'true';
			else
				$has_notes = 'false';

			$has_comments = !empty($row['last_comment']);
			if ($has_comments)
				$has_comments = 'true';
			else
				$has_comments = 'false';

			$has_children = $post->has_children($row['id']);
			if ($has_children)
				$has_children = 'true';
			else
				$has_children = 'false';

			$file_url = $site_url.'/'.$image_folder.'/'.$row['directory'].'/'.$row['image'];
			return '<post height="'.$row['height'].'" score="'.$row['score'].'" file_url="'.$file_url.'" parent_id="'.$parent_id.'" sample_url="'.$file_url.'" sample_width="'.$row['width'].'" sample_height="'.$row['height'].'" preview_url="'.$thumbnail_url.'/'.$row['directory'].'/thumbnail_'.$row['image'].'" rating="'.strtolower(substr($row['rating'], 0, 1)).'" tags="'.fixTags($row['tags']).'" id="'.$row['id'].'" width="'.$row['width'].'" change="UNIMPLEMENTED" md5="'.$row['hash'].'" creator_id="'.getUserID($row['owner']).'" has_children="'.$has_children.'" created_at="'.$row['creation_date'].'" status="UNIMPLEMENTED" source="'.$row['source'].'" has_notes="'.$has_notes.'" has_comments="'.$has_comments.'" preview_width="150" preview_height="150"/>'."\r\n";
		}

		if ($_GET['q'] == "index") {
			require "includes/api_list.php";
		} else if ($_GET['q'] == "view") {
			require "includes/api_view.php";
		} else {
			die("{'error':'q not defined'}");
		}
	}
	else
		die("{'error':'s not defined.'}");
?>