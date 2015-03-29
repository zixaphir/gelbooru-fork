<?php
	set_time_limit(0);
	require "inv.header.php";
	$user = new user();
	if(!$user->gotpermission('is_admin'))
	{
		header('Location: index.php');
		exit;
	}

	$dir = "./images/";
	$dirs = array();
	$image = new image();
	function is_valid_extension($img)
	{
		$ext = substr($img,-3,10);
		if($ext == "jpg")
			return true;
		else if($ext == "gif")
			return true;
		else if($ext == "png")
			return true;
		else if($ext == "bmp")
			return true;
		else
			return false;
	}
	$dir_contents = scandir($dir);
	foreach ($dir_contents as $item) 
	{
		if (is_dir($dir.$item) && $item != '.' && $item != '..') 
		{
			$dirs[] = $item;
		}
	}
	foreach($dirs as $current)
	{
		$dir_contents = scandir("./images/".$current."/");
		foreach ($dir_contents as $item) 
		{
			if ($item != '.' && $item != '..' && !is_dir($dir.$item) && is_valid_extension($item) && !file_exists("./thumbnails/$current/thumbnail_$item")) 
			{
				$image = new image();			
				if(!is_dir("./thumbnails/".$current."/"))
					$image->makethumbnailfolder($current);
				$image->thumbnail($current."/".$item);
				print "./thumbnails/".$current."/thumbnail_".$item."<br>
";
			}
		
		}
	}
?>