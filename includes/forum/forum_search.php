<?php
	if(isset($_POST['search']) && $_POST['search'] != "")
	{
		$search = $db->real_escape_string($_POST['search']);
		header("Location: index.php?page=forum&s=list&query=$search");	
	}
?>