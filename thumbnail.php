<?php
	require "inv.header.php";
	$id = $db->real_escape_string(basename($_SERVER["PATH_INFO"]));
	if(!is_numeric($id))
		die;
	$misc = new misc;
	$query = "SELECT image, directory, ext FROM $post_table WHERE id='$id' LIMIT 1";
	$result = $db->query($query);
	$row = $result->fetch_assoc();
	$f = fopen("./thumbnails".$misc->getThumb($row['image'], $row['dir']),"rb") or die;
	$data = '';
	header("Cache-Control: store, cache");
	header("Pragma: cache");
	header("Content-type: image/".str_replace(".","",$row['ext']));
	while(!feof($f))
	{
		$data .= fread($f, 8192);	
	}
	fclose($f);
	print $data;
	flush();
?>
