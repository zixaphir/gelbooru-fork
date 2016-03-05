<?php
	class user
	{
		private $user_id;

		function __construct()
		{

		}

		function hashpass($pass)
		{
			if(!function_exists('hash'))
				return sha1(md5($pass));
			else
				return hash('sha1',hash('md5',$pass));
		}

		function user_exists($user)
		{
			global $db, $user_table;
			$user = $db->real_escape_string($user);
			$query = "SELECT COUNT(*) FROM $user_table WHERE user='$user'";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			if($row['COUNT(*)'] == 0 && strtolower($user) != "anonymous" && strtolower($user) != "admin")
				return false;
			else
				return true;
		}

		function signup($user,$pass,$email="")
		{
			global $db, $user_table, $group_table;
			if(strpos($user,' ') !== false || strpos($user,'	') !== false || strpos($user,';') !== false || strpos($user,',') !== false || strlen($user) < 3)
				return false;
			if($this->user_exists($user))
				return false;
			$user = $db->real_escape_string($user);
			$pass = $db->real_escape_string($pass);
			$email = $db->real_escape_string($email);
			$query = "SELECT id FROM $group_table WHERE default_group=TRUE";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			$gid = $row['id'];
			$result->free_result();
			$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
			$query = "INSERT INTO $user_table(user, pass, email, ip, ugroup, mail_reset_code, signup_date) VALUES('$user', '".$this->hashpass($pass)."', '$email', '$ip', '$gid', '', NOW())";
			$result = $db->query($query) or die($db->error);
			if($result)
				return true;
			else
				return false;
		}

		function login($user, $pass)
		{
			global $db, $site_url, $user_table;
			$user = $db->real_escape_string($user);
			$pass = $db->real_escape_string($pass);
			$pass = $this->hashpass($pass);
			$query = "SELECT * FROM $user_table WHERE user='$user' AND pass='$pass'";
			$result = $db->query($query);
			if($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				setcookie("user_id",$row['id'],time()+60*60*24*365);
				setcookie("pass_hash",$pass,time()+60*60*24*365);
				$this->session_tags($row['my_tags']);
				if(!isset($_COOKIE['tag_blacklist']) && $row['tags'] != "")
					setcookie("tag_blacklist",$row['tags'],time()+60*60*24*365);
				return true;
			}
			else
				return false;
		}

		function session_tags($tags) {
			setcookie("tags",str_replace(" ","%20",str_replace("'","&#039;",$tags)),time()+60*60*24*365);
		}

		function logout()
		{
			global $site_url;
			setcookie("user_id","",time()-60*60*24*365);
			setcookie("pass_hash","",time()-60*60*24*365);
			setcookie("tags","",time()-60*60*24*365);
			header('Location: index.php?page=account&s=home');
		}

		function check_log()
		{
			global $db, $user_table, $checked_username, $checked_user_id, $checked_user_group;
			$id = $db->real_escape_string($_COOKIE['user_id']);
			$pass_hash = $db->real_escape_string($_COOKIE['pass_hash']);
			$query = "SELECT * FROM $user_table WHERE id='$id' AND pass='$pass_hash'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			if($result->num_rows == 1)
			{
				$checked_username = $row['user'];
				$checked_user_id = $row['id'];
				$checked_user_group = $row['ugroup'];
				return true;
			}
			else
			{
				$checked_username = "Anonymous";
				$checked_user_id = "0";
				return false;
			}
		}

		function gotpermission($column)
		{
			global $db, $user_table, $group_table, $checked_user_group;
			if($this->check_log())
			{
				$ugroup = $checked_user_group;
				$query = "SELECT $column FROM $group_table WHERE id='$ugroup'";
				$result = $db->query($query) or die($db->error);
				$row = $result->fetch_assoc();
				if($row[''.$column.''] == true)
					return true;
				else
					return false;
			}
			else
				return false;
		}

		function loadpermissions()
		{
			if(isset($_COOKIE['user_id']))
			{
				global $db, $group_table, $user_table;
				$user_id = $db->real_escape_string($_COOKIE['user_id']);
				$query = "SELECT * FROM $group_table AS t1 JOIN $user_table AS t2 ON t2.id='$user_id' where t1.id=t2.ugroup";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				return $row;
			}
			else
			{
				global $db, $group_table;
				$user_id = $db->real_escape_string($_COOKIE['user_id']);
				$query = "SELECT * FROM $group_table where default_group=true";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				return $row;
			}
		}

		function update_password($id, $pass)
		{
			global $db, $user_table;
			$pass = $this->hashpass($pass);
			$query = "UPDATE $user_table SET pass='$pass' WHERE id='$id'";
			if($db->query($query))
				return true;
			else
				return false;
		}

		function banned_ip($ip)
		{
			global $db, $banned_ip_table, $row;
			$query = "SELECT * FROM $banned_ip_table WHERE ip='$ip' LIMIT 1";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			if($result->num_rows == 1)
				return true;
			else
				return false;
		}

	}
?>