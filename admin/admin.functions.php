<?php
	function check_admin()
	{
		if(isset($_SESSION['id']) && is_numeric($_SESSION['id']) && isset($_SESSION['login_session']))
		{
			global $user_table;
			global $group_table;
			$user_id = mysql_real_escape_string($_SESSION['id']);
			$login_session = mysql_real_escape_string($_SESSION['login_session']);
			$query = "SELECT ugroup FROM $user_table WHERE id='$user_id' AND login_session='$login_session'";
			$result = mysql_query($query);
			$row = mysql_fetch_assoc($result);
			$gid = $row['ugroup'];
			$query = "SELECT admin_panel FROM $group_table WHERE id='$gid'";
			$result = mysql_query($query);
			$row = mysql_fetch_assoc($result);
			if($row['admin_panel'] != true)
				return false;
			else
				return true;
		}
		else
			return false;
	}
?>