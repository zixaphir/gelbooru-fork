<?php
	require "../inv.header.php";
	$user = new user();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);	
	if($user->banned_ip($ip))
		exit;
	if(is_numeric($_GET['id']))
	{
		if($user->check_log())
		{
			$id = $db->real_escape_string($_GET['id']);
			$query = "SELECT COUNT(*) FROM $favorites_table WHERE user_id='$checked_user_id' AND favorite='$id'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			if($row['COUNT(*)'] < 1)
			{
				$result->free_result();
				$query = "INSERT INTO $favorites_table(user_id, favorite) VALUES('$checked_user_id', '$id')";
				if($db->query($query))
				{
					$query = "SELECT COUNT(*) FROM $favorites_count_table WHERE user_id='$checked_user_id'";
					$result = $db->query($query);
					$row = $result->fetch_assoc();
					if($row['COUNT(*)'] < 1)
						$query = "INSERT INTO $favorites_count_table(user_id, fcount) VALUES('$checked_user_id','1')";
					else
						$query = "UPDATE $favorites_count_table SET fcount=fcount+1 WHERE user_id='$checked_user_id'";
					$db->query($query);
					echo "3";
				}
			}
			else
				echo "1";
		}
		else
			echo "2";
	}
?>