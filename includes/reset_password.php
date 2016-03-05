<?php
	session_start();
	$user = new user();
	if($user->check_log())
		header("Location: index.php?page=account");
	else
	{
		header("Cache-Control: store, cache");
		header("Pragma: cache");
		require "includes/header.php";
	}
	if(isset($_POST['username']) && $_POST['username'] != "")
	{
		$user = $db->real_escape_string(htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8'));
		$query = "SELECT email, id FROM $user_table WHERE user='$user' LIMIT 1";
		$result = $db->query($query);
		$count = $result->num_rows;
		if($count > 0)
		{
			$row = $result->fetch_assoc();
			if($row['email'] != "" && $row['email'] != NULL && strpos($row['email'],"@") !== false && strpos($row['email'],".") !== false && strlen($row['email']) > 2)
			{
				$misc = new misc();
				$code = hash('sha256',rand(132,1004958327747882664857));
				$link = $site_url."/index.php?page=reset_password&code=".$code."&id=".$row['id'];
				$body = 'A password reset has been requested for your account.<br /><br /> If you didn\'t request this, please ignore this email.<br /><br />To reset you password, please click on this link: <a href="'.$link.'">'.$link.'</a>';
				$misc->send_mail($row['email'],$email_recovery_subject,$body);
				$query = "UPDATE $user_table SET mail_reset_code='$code' WHERE id='".$row['id']."'";
				$db->query($query);
				print "An email with a reset link has been sent to your mailbox.<br />";
			}
			else
				print "No email has been added to this account.<br />";
		}
		else
			print "No email has been added to this account.<br />";
	}
	if(isset($_GET['code']) && $_GET['code'] != "" && isset($_GET['id']) && $_GET['id'] != "" && is_numeric($_GET['id']))
	{
		$id = $db->real_escape_string($_GET['id']);
		$code = $db->real_escape_string($_GET['code']);
		$query = "SELECT id FROM $user_table WHERE id='$id' AND mail_reset_code='$code' LIMIT 1";
		$result = $db->query($query) or die($db->error);
		if($result->num_rows > 0)
		{
			$_SESSION['reset_code'] = $code;
			$_SESSION['tmp_id'] = $id;
			echo '<form method="post" action="index.php?page=reset_password">
			<table><tr><td>
			Enter your new password:
			<input type="password" name="new_password" value="" />
			</td></tr>
			<tr><td>
			<input type="submit" name="submit" value="submit" />
			</td></tr>
			</table>
			</form>';
		}
		else
		{
			print "Invalid reset link.<br />";
		}
	}
	if(isset($_POST['new_password']) && $_POST['new_password'] != "" && isset($_SESSION['tmp_id']) && $_SESSION['tmp_id'] != "" && is_numeric($_SESSION['tmp_id']) && isset($_SESSION['reset_code']) && $_SESSION['reset_code'] != "")
	{
		$code = $db->real_escape_string($_SESSION['reset_code']);
		$id = $db->real_escape_string($_SESSION['tmp_id']);
		$pass = $db->real_escape_string($_POST['new_password']);
		$user = new user();
		$query = "SELECT id FROM $user_table WHERE id='$id' AND mail_reset_code='$code'";
		$result = $db->query($query) or die($db->error);
		if($result->num_rows > 0)
		{
			$user->update_password($id,$pass);
			$query = "UPDATE $user_table SET mail_reset_code='' WHERE id='$id' AND mail_reset_code='$code'";
			$db->query($query);
			unset($_SESSION['tmp_id']);
			unset($_SESSION['reset_code']);
			print "Your password has been changed.<br />";
		}
	}
	if(!isset($_GET['code']) && $_GET['code'] == "")
	{
		echo'<form method="post" action="index.php?page=reset_password">
		<table><tr><td>
		Username:
		<input type="text" name="username" value="" />
		</td></tr>
		<tr><td>
		<input type="submit" name="submit" value="submit" />
		</td></tr>
		</table></form>';
	}
?>