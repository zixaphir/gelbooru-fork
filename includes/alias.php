<?php
	//number of tags/page
	$limit = 40;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 6;
	require "header.php";
	if(isset($_POST['tag']) && $_POST['tag'] != "" && isset($_POST['alias']) && $_POST['alias'] != "")
	{
		$tag = $db->real_escape_string(str_replace(" ","_",mb_trim(htmlentities($_POST['tag'], ENT_QUOTES, 'UTF-8'))));
		$alias = $db->real_escape_string(str_replace(" ","_",mb_trim(htmlentities($_POST['alias'], ENT_QUOTES, 'UTF-8'))));
		$query = "SELECT COUNT(*) FROM $alias_table WHERE tag='$tag' AND alias='$alias'";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		if($row['COUNT(*)'] > 0)
			echo "<b>Tag/alias combination has already been requested.</b><br /><br />";
		else
		{
			$query = "INSERT INTO $alias_table(tag, alias, status) VALUES('$tag', '$alias', 'pending')";
			$db->query($query);
			echo "<b>Tag/alias combination has been requested.</b><br /><br />";
		}
	}

	echo 'You can suggest a new alias, but they must be approved by an administrator before they are activated.<br />
	<div style="color: #ff0000;">An example of how to use this: (Evangelion is the tag and Neon_Genesis_Evangelion is the alias.)</div><br /><br />
	';

	if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
		$page = $db->real_escape_string($_GET['pid']);
	else
		$page = 0;
	$query = "SELECT COUNT(*) FROM $alias_table WHERE status !='rejected'";
	$result = $db->query($query);
	$row = $result->fetch_assoc();
	$count = $row['COUNT(*)'];	
	$numrows = $count;
	$result->free_result();
	$query = "SELECT * FROM $alias_table WHERE status != 'rejected' ORDER BY alias ASC LIMIT $page, $limit";
	$result = $db->query($query) or die($db->error);
	$ccount = 0;
	print '<table class="highlightable" style="width: 100%;"><tr><th width="25%"><b>Tag:<small> (What you search for...)</small></b></th><th width="25%"><b>Alias:</b><small> (What it should be...)</small></th><th>Reason:</th></tr>';
	while($row = $result->fetch_assoc())
	{
		if($row['status']=="pending")
			$status = "pending-tag";
		else
			$status = "";
		echo '<tr class="'.$status.'"><td>'.$row['alias'].'</td><td>'.$row['tag'].'</td><td>'.$row['reason'].'</td></tr>';
	}
	echo '</table><br /><br />
	<form method="post" action=""><table><tr><td>
	<b>Name:</b></td><td><input type="text" name="alias" value=""/></td></tr>
	<tr><td><b>Alias to:</b></td><td><input type="text" name="tag" value=""/></td></tr>
	</table>
	<input type="submit" name="submit" value="Submit"/>
	</form>
	<div id="paginator">';
	$misc = new misc();
	print $misc->pagination($_GET['page'],$sub,$id,$limit,$page_limit,$numrows,$_GET['pid'],$tags);
?></div></body></html>