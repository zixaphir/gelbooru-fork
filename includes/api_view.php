<?php
	//number of comments/page
	$limit = 10;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 6;
	//Load required class files. post.class and cache.class
	$post = new post();
	$cache = new cache();
	header("Cache-Control: store, cache");
	header("Pragma: cache");
	$domain = $cache->select_domain();

	if(isset($_GET['t']) && $_GET['t'] == 'json')
		$api_type = 'json';
	else
		$api_type = 'xml';

	if ($api_type == 'json')
		header('Content-type: application/json');
	else
		header('Content-type: text/xml');

	if(isset($_GET['id']))
	{
		$id = $db->real_escape_string($_GET['id']);
		if(!is_numeric($id))
			$id = str_replace("#","",$id);
		$id = (int)$id;
	}
	else
	{
		if ($api_type == 'json')
			print '{"offset":"0","count":"0",posts":[]}';
		else
			print '<?xml version="1.0" encoding="UTF-8"?><posts offset="0" count="0"></posts>';
		exit;
	}
	//Load post_table data and the previous next values in array. 0 previous, 1 next.
	$post_data = $post->show($id);
	//Check if data exists in array, if so, kinda ignore it.
	if($post_data == "" || is_null($post_data))
	{
		if ($api_type == 'json') {
			print '{"offset":"0","count":"0",posts":[]}';
		}
		else
		{
			print '<?xml version="1.0" encoding="UTF-8"?><posts offset="0" count="0"></posts>';
		}
		exit;
	}
	$prev_next = $post->prev_next($id);

	if(!is_dir("$main_cache_dir".""."\api_cache/$id"))
		$cache->create_page_cache("cache/$id");
	$data = $cache->load("api_cache/".$id."/post.".$api_type.".cache");
	if($data !== false)
	{
		echo str_replace("f6ca1c7d5d00a2a3fb4ea2f7edfa0f96a6d09c11717f39facabad2d724f16fbb",$domain,$data);
		flush();
	}
	else
	{
		ob_start();

		if ($api_type == 'json')
		{
			header('Content-type: application/json');
			$posts = array(createPostObject($post_data));
			$postsArr = array('offset' => 0, 'count' => 1, 'posts' => $posts);
			echo json_encode($postsArr);
		}
		else
		{
			header('Content-type: text/xml');
			$posts = '<?xml version="1.0" encoding="UTF-8"?><posts offset="0" count="1">'."\r\n";
			$posts .= createPostXML($post_data);
			$posts .= '</posts>';
			echo $posts;
		}
		$data = '';
		$data = ob_get_contents();
		ob_end_clean();
		$cache->save("cache/".$id."/post.".$api_type.".cache",$data);
		echo str_replace("f6ca1c7d5d00a2a3fb4ea2f7edfa0f96a6d09c11717f39facabad2d724f16fbb",$domain,$data);
	}
?>