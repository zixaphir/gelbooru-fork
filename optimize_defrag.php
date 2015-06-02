<?php
	require "inv.header.php";
	$user = new user();
	if(!$user->gotpermission('is_admin'))
	{
		header('Location: index.php');
		exit;
	}
	//turn verbose on/off (on shows output of processed tables)
	$verbose = true;
	$query = "SHOW TABLE STATUS";
	$result = $db->query($query) or die($db->error);
	while($row = $result->fetch_assoc())
	{
		if($row['Data_free'] > 0)
		{
			$ret = "OPTIMIZE TABLE ".$row['Name'];
			$db->query($ret);
			$ret = "ANALYZE TABLE ".$row['Name'];
			$db->query($ret);
			if($verbose == true)
			{
				echo "Optimized table ".$row['Name']."<br />";
				echo "Analyzed table ".$row['Name']."<br />";
			}
		}
	}
	$result->free_result();
?>