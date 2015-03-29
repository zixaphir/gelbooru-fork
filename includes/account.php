<?php 
	echo '<div id="content"><div id="user-index">';
	$user = new user(); 
	if($user->check_log())
	{
		echo '<h4><a href="index.php?page=login&amp;code=01">&raquo; Logout</a></h4>
		<p>Make like a tree and get out of here! Click here to logout of your account.</p>
		<h4><a href="index.php?page=account_profile&id='.$checked_user_id.'">&raquo; My Profile</a></h4>
		<p>It\'s your profile. Do you need me to explain more?</p>
		<h4><a href="index.php?page=favorites&amp;s=view&amp;id='.$_COOKIE['user_id'].'">&raquo; My Favorites</a></h4>
		<p>View all of your favorites and remove them if you wish.</p>';		
	}
	else
	{
		print '<h2>You are not logged in.</h2><h4><a href="index.php?page=login&amp;code=00">&raquo; Login</a></h4><p>If you already have an account you can login here. Alternatively, accessing features that require an account will automatically log you in if you have enabled cookies.</p>';
		if($registration_allowed == true)
			echo '<h4><a href="index.php?page=reg">&raquo; Sign Up</a></h4><p>You can access 90% of '.$site_url3.' without an account, but you can sign up for that extra bit of functionality. Just a login and password, no email required!</p>';
		else
			echo '<p><b>Registration is closed.</b></p>';
	}
?>
<h4><a href="index.php?page=favorites&amp;s=list">&raquo; Everyone's Favorites</a></h4>
<p>View everyone's favorites.</p>
<h4><a href="index.php?page=account-options">&raquo; Options</a></h4>
<p>Manage account options.</p>
</div></div></body></html>