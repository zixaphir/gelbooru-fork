<?php
	if(!defined('_IN_ADMIN_HEADER_'))
		die;

	$user = new user();
	if(!$user->gotpermission('is_admin'))
	{
		header('Location:../');
		exit;
	}
	if(isset($_POST['gname']) && $_POST['gname'] != "")
	{
		$name =$db->real_escape_string($_POST['gname']);
		$query = "SELECT COUNT(*) FROM $group_table WHERE group_name='$name'";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		if($row['COUNT(*)'] > 0)
			print "Group already exists.";
		else
		{
			if(isset($_POST['default']) && $_POST['default'] == true)
			{
				$query = "UPDATE $group_table SET default_group=FALSE";
				$db->query($query);
				$query = "INSERT INTO $group_table(group_name, default_group) VALUES('$name', TRUE)";
			}
			else
			{
				$query = "INSERT INTO $group_table(group_name, default_group) VALUES('$name', FALSE)";
			}
			if($db->query($query))
				print "Group added.";
			else
				print "Could not add group.";
		}
	}
?><div class="content">
<form method="post" action="">
<table><tr><td>Group name:<br />
<input type="text" name="gname"/>
</td></tr>
<tr><td>
<input type="submit" name="submit" value="Create group"/>
</td></tr>
<tr><td>
Is this group the default group? (a default group must exist)<br />
<input type="checkbox" name="default" />
</td></tr></table>
</form>