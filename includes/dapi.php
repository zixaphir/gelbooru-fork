<?php
	if($_GET['s'] == "post") 
	{
		// require "includes/dapi_post.php"; ?
		function createPostObject($row) {
			global $site_url, $image_folder, $thumbnail_url;
			$file_url = $site_url.'/'.$image_folder.'/'.$row['directory'].'/'.$row['image'];
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
				// 'parent_id'      => 'UNIMPLEMENTED',
				'preview_url'    => $thumbnail_url.'/'.$row['directory'].'/thumbnail_'.$row['image'],
				'rating'         => strtolower(substr($row['rating'], 0)),
				'tags'           => mb_trim($row['tags']),
				'id'             => $row['id'],
				// 'change'         => 'UNIMPLEMENTED',
				'md5'            => $row['hash'],
				'creator_id'     => $row['owner'],
				'created_at'     => $row['creation_date'],
				// 'status'         => 'UNIMPLEMENTED',
				'source'         => $row['source'],
				// 'has_notes'      => 'UNIMPLEMENTED',
				'has_comments'   => $row['last_comment'] != null
			));
		}

		function createPostXML($row) 
		{
			global $site_url, $image_folder, $thumbnail_url;
			$file_url = $site_url.'/'.$image_folder.'/'.$row['directory'].'/'.$row['image'];
			return '<post height="'.$row['height'].'" score="'.$row['score'].'" file_url="'.$file_url.'" sample_url="'.$file_url.'" sample_width="'.$row['width'].'" sample_height="'.$row['height'].'" preview_url="'.$thumbnail_url.'/'.$row['directory'].'/thumbnail_'.$row['image'].'" rating="'.strtolower(substr($row['rating'], 0)).'" tags="'.$row['tags'].'" id="'.$row['id'].'" width="'.$row['width'].'" change="UNIMPLEMENTED" md5="'.$row['hash'].'" creator_id="'.$row['owner'].'" has_children="UNIMPLEMENTED" created_at="'.$row['creation_date'].'" status="UNIMPLEMENTED" source="'.$row['source'].'" has_notes="UNIMPLEMENTED" has_comments="'.$row['last_comment'].'" preview_width="150" preview_height="150"/>'."\r\n";
		}

		if ($_GET['q'] == "index")
		{
			require "includes/api_list.php";
		}
		else if ($_GET['q'] == "view")
			/* TODO */
			die("{'error':'Not implemented'}");
		else
			die("{'error':'q not defined'}");
	}
	else
		die("{'error':'s not defined.'}");
?>