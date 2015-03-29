<?php
	require "../inv.header.php";
	$userc = new user();
	if(!$userc->gotpermission('is_admin'))
	{
		header('Location:../');
		exit;
	}
	if(isset($_POST['password']) && isset($_POST['group']) && is_numeric($_POST['group']))
	{
		$user = $db->real_escape_string($_POST['uid']);
		$pass = $db->real_escape_string($_POST['password']);
		$group = $db->real_escape_string($_POST['group']);
		
		if($pass != "")
			$query = "UPDATE $user_table SET pass='".$userc->hashpass($pass)."', ugroup='$group' WHERE user='$user'";
		else
			$query = "UPDATE $user_table SET ugroup='$group' WHERE user='$user'";

		if($db->query($query))
			print 'User edited.<meta http-equiv="refresh" content="2;url=edit_user.php">';
		else
			print 'Could not edit user.<meta http-equiv="refresh" content="2;url=edit_user.php">';
			
		exit;
	}
	else if(isset($_POST['user']) && $_POST['user'] != "")
	{
		$user = $db->real_escape_string($_POST['user']);
		echo '<form method="post" action="">
		<table><tr><td>
		User: '.$user.'</td></tr>
		<tr><td>
		New password?<br />
		<input type="text" name="password" value=""/>
		</td></tr>
		<tr><td>Group:<br /><select name="group">';
		$cgroup = false;
		$query = "SELECT t1.ugroup, t2.id, t2.group_name FROM $user_table AS t1 JOIN $group_table AS t2 WHERE t1.user='$user'";
		$result = $db->query($query);
		while($row = $result->fetch_assoc())
		{
			if($cgroup == false)
			{
				echo '<option value="'.$row['ugroup'].'">No change</option>';
				$cgroup = true;
			}
			echo '<option value="'.$row['id'].'">'.$row['group_name'].'</option>';
		}
		echo '</select></td></tr><tr><td>
		<input type="hidden" name="uid" value="'.$user.'"/>
		</td></tr>
		<tr><td>
		<input type="submit" name="submit" value="save"/>
		</td></tr></table></form>';
	}
	else
	{
		echo '<form method="post" action=""><table><tr><td>
		User:<br />
		<input type="text" name="user" value=""/>
		</td></tr>
		<tr><td>
		<input type="submit" name="submit" value="edit"/>
		</td></tr></table></form>';
	}
?>