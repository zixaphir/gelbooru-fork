<?php
	$tags = urlencode($_POST['tags']);
	if($tags == "")
		$tags = "all";
	header("Location:index.php?page=post&s=list&tags=$tags");
?>