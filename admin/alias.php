<?php
	if(!defined('_IN_ADMIN_HEADER_'))
		die;

	if(isset($_GET['tag']) && $_GET['tag'] != "" && isset($_GET['alias']) && $_GET['alias'] != "")
	{
		if(isset($_POST['accept']) && is_numeric($_POST['accept']))
		{
			$tag = $db->real_escape_string($_GET['tag']);
			$alias = $db->real_escape_string($_GET['alias']);
			if($_POST['accept'] == 1)
			{
				$tagc = new tag();
				//tag boot, alias boots singular is better.
				$query = "UPDATE $alias_table SET status='accepted' WHERE tag='$tag' AND alias='$alias'";
				$db->query($query);
				//Convert all current posts from the AKA to the tag.
				$query = "SELECT * FROM $post_table WHERE tags LIKE '% ".str_replace('%','\%',str_replace('_','\_',$alias))." %'";
				$result = $db->query($query) or die($db->error);
				while($row = $result->fetch_assoc())
				{
					$tags = explode(" ",$row['tags']);
					foreach($tags as $current)
						$tagc->deleteindextag($current);
					$tmp = str_replace(' '.$alias.' ',' '.$tag.' ',$row['tags']);
					$tags = implode(" ",$tagc->filter_tags($tmp,$tag,explode(" ",$tmp)));
					$tags = mb_trim(str_replace("  ","",$tags));
					$tags2 = explode(" ",$tags);
					foreach($tags2 as $current)
						$tagc->addindextag($current);						
					$tags = " $tags ";
					$query = "UPDATE $post_table SET tags='$tags' WHERE id='".$row['id']."'";
					$db->query($query);
				}
			}
			else if($_POST['accept'] == 2)
			{
				$query = "UPDATE $alias_table SET status='rejected' WHERE tag='$tag' AND alias='$alias'";
				$db->query($query);
			}
			print '<meta http-equiv="refresh" content="2;url='.$site_url.'admin/?page=alias">';
			exit;
		}

		echo '<form method="post" action=""><table><tr><td>
		Accept<input type="radio" name="accept" value="1"/>
		Reject<input type="radio" name="accept" value="2"/>
		</td></tr><tr><td>
		<input type="submit" name="submit" value="Submit"/>
		</td></tr></table></form>';
	}
	else
	{
		$query = "SELECT tag, alias FROM $alias_table WHERE status='pending'";
		$result = $db->query($query);
		print '<div class="content"><table width="100%" border="0" class="highlightable">
		<tr><th>Alias [What it should be!]</th><th>Tag [What they search for!]</th></tr>';
		while($row = $result->fetch_assoc())
			print '<tr><td><a href="?page=alias&amp;alias='.$row['alias'].'&amp;tag='.$row['tag'].'">'.$row['tag'].'</a></td><td><a href="?page=alias&amp;alias='.$row['alias'].'&amp;tag='.$row['tag'].'">'.$row['alias'].'</a></td>';

		if($result->num_rows == 0)
			echo "<tr><td><h1>No aliases has been requested.</h1></td></tr>";
		$result->free_result();
	}
	$db->close();
?>
</table></div>