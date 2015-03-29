<?php
	if(isset($_COOKIE['user_id']) && is_numeric($_COOKIE['user_id']) && isset($_COOKIE['pass_hash']) && $_COOKIE['pass_hash'] != "")
	{
		$user = new user();
		if(!$user->check_log())
		{
			setcookie("user_id","",time()-60*60*24*365);
			setcookie("pass_hash","",time()-60*60*24*365);
		}
	}
?>