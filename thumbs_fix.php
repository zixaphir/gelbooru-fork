<?php
	set_time_limit(0);
	require "inv.header.php";
	$user = new user();
	if(!$user->gotpermission('is_admin'))
	{
		header('Location: index.php');
		exit;
	}

	$image = new image();
	$misc = new misc();
	$dir  = "./images/";
	$dirs = array();

	function is_valid_extension($img)
	{
		$ext = explode('.', $img);
		$ext = array_pop($ext);
		switch ($ext)
		{
			case 'jpg':
			case 'jpeg':
			case 'webm':
			case 'png':
			case 'gif':
				return true;
			default:
				return false;
		}
	}

	$dir_contents = scandir($dir);
	foreach ($dir_contents as $current)
	{
		if (!is_dir($dir.$current) || $current == '.' || $current == '..')
		{
			continue;
		}

		$dir_contents = scandir("./images/".$current."/");
		if(!is_dir("./thumbnails/".$current."/"))
			$image->makethumbnailfolder($current);

		foreach ($dir_contents as $item)
		{
			$thumb = "./thumbnails".$misc->getThumb($item, $current);
			if ($item != '.' && $item != '..' && !is_dir($dir.$item) && is_valid_extension($item) && !file_exists($thumb))
			{
				$image->thumbnail($current."/".$item);
				print $thumb."<br>";
			}

		}
	}
?>