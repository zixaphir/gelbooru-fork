<?php
	if(!isset($_POST['settings']))
	{
		print '<form method="post" action="index.php">
		<table><tr><td>
		Username (for admin):<br />
		<input type="text" name="user" value=""/>
		</td></tr>
		<tr><td>
		Password:<br />
		<input type="text" name="pass" value=""/>
		</td></tr>
		<tr><td>
		Email:<br />
		<input type="text" name="email" value=""/>
		</td></tr>
		<tr><td>
		<input type="hidden" name="settings" value="1"/>
		</td></tr>
		<tr><td>
		<input type="submit" name="submit" value="Install"/>
		</td></tr></table></form>'; 
		exit;
	}
		require "create_db.php";
		$user = $db->real_escape_string($_POST['user']);
		$pass = $db->real_escape_string($_POST['pass']);
		$email = $db->real_escape_string($_POST['email']);
		$pass = sha1(md5($pass));
		$query = "SELECT * FROM $user_table";
		$result = $db->query($query);
		if($result->num_rows == "0")
		{
			$query = "INSERT INTO $user_table(user,pass,ugroup,email,signup_date) VALUES('$user','$pass','1','$email',NOW())";
			$db->query($query) or die($db->error);
		}
		else
			print "You've already installed the Gelbooru software in this database. A new user will not be added. You may wish to run the upgrade instead?<br /><br />";
		print "<br />Install went well. Log into your account using the username and password during this install.";
?>