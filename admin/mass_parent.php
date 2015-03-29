<?php
	//Made to mass add parent to each post. Is this good enough?
	require "../inv.header.php";
	$user = new user();
	if(!$user->gotpermission('is_admin'))
	{
		header('Location:../');
		exit;
	}

	if(!isset($_POST['start']) && !isset($_POST['end']) && !isset($_POST['parent']))
	{
		print 'enter id\'s range to change!<br><Br>
		<form method="post" action="mass_parent.php">
		Makes the below a parent of this id:<br>
		<input type="text" name="parent">
		<Br><br>
		
		Starting #:<br>
		<input type="text" name="start">
		<br><br>
		
		Ending #:<br>
		<input type="text" name="end">
		<br><br>
		
		<input type="submit">
		</form>
		';
	}
	else
	{
		$cache = new cache();
		$start = $db->real_escape_string($_POST['start']);
		$end = $db->real_escape_string($_POST['end']);
		$parent_id = $db->real_escape_string($_POST['parent']);
		while($start<=$end)
		{
			$cache->destroy_page_cache("cache/".$start);
			$parent_check1 = "SELECT COUNT(*) FROM $post_table WHERE id='$parent_id'";
			$pres1 = $db->query($parent_check1);
			$prow1 = $pres1->fetch_assoc();
			if($prow1['COUNT(*)'] > 0)
			{
				$temp = "INSERT INTO $parent_child_table(parent,child) VALUES('$parent_id','$start')";
				$db->query($temp);
				$temp = "UPDATE $post_table SET parent='$parent_id' WHERE id='$start'";
				$db->query($temp);
			}
			$start++;
		}
	}
?>