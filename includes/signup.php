<?php
	if($registration_allowed != true)
		die("<br /><b>Registration is closed.</b>");
	$user = new user();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);	
	if($user->banned_ip($ip))
	{
		print "Action failed: ".$row['reason'];
		exit;
	}	
	if($user->check_log())
	{
		header("Location:index.php?page=account");
		exit;
	}
	if(isset($_POST['user']) && $_POST['user'] != "" && isset($_POST['pass']) && $_POST['pass'] != "" && isset($_POST['conf_pass']) && $_POST['conf_pass'] != "")
	{
		$misc = new misc();
		$username = $db->real_escape_string(str_replace(" ",'_',htmlentities($_POST['user'], ENT_QUOTES, 'UTF-8')));
		$password = $db->real_escape_string($_POST['pass']);
		$conf_password = $db->real_escape_string($_POST['conf_pass']);
		$email = $db->real_escape_string($_POST['email']);
		if($password == $conf_password)
		{
			$user = new user();
			if(!$user->signup($username,$password,$email))
			{
				require "includes/header.php";
				print "Signup failed. This can be caused by: a database error, a user with that username already exists, or your nick contains characters that are not allowed. Please make sure that your nick doesn't contain space, tab, ; or ,. Please also makes sure that your nick is atleast 3 characters.<br />";
			}
			else
			{
				$user->login($username,$password);
				header("Location:index.php?page=account");
				exit;
			}
		}
		else
		{
			require "includes/header.php";
			print "Passwords does not match.<br />";
		}
	}
	else
		require "includes/header.php";
?>
<form method="post" action="index.php?page=reg">
<table><tr><td>
Username:<br />
<input type="text" name="user" value="" />
</td></tr>
<tr><td>
Choose password:<br />
<input type="password" name="pass" value="" />
</td></tr>
<tr><td>
Confirm password:<br />
<input type="password" name="conf_pass" value="" />
</td></tr>
<tr><td>
Email (not required):<br />
<input type="text" name="email" value="" />
</td></tr>
<tr><td>
<input type="submit" name="submit" value="Register" />
</td></tr>
</table>
</form></div></body></html>