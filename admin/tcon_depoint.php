<?php
	require "../inv.header.php";
	$user = new user();
	if(!$user->gotpermission('is_admin'))
		header("Location:../");
		exit;
	}
	$s1 = "UPDATE posts set score='-100' where tags LIKE '% toddlercon %' AND rating !='safe'";
	$db->query($s1) or die($db->error);
?>