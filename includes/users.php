<?php
	//number of users/page
	$limit = 4;
	//number of posts per user
	$plimit = 5;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 10;
	require "includes/header.php";
	$cache = new cache();
	$search = new search();
	$domain = $cache->select_domain();
	$misc = new misc();
?>
<script type="text/javascript">
var posts = {}; var pignored = {};
</script>
<section><div id="user-albums">
<?php
	if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
		$page = $db->real_escape_string($_GET['pid']);
	else
		$page = 0;

	$query = "SELECT COUNT(id) FROM $user_table WHERE EXISTS (
	  SELECT 1
		FROM $post_table
		WHERE $post_table.owner = $user_table.user
		" . $search->blacklist_fragment() . "
	)";

	$result = $db->query($query);
	$row = $result->fetch_assoc();
	$numrows = $row['COUNT(id)'];
	$result->free_result();

	//No users found
	if($numrows == 0)
		print '<article><div><h1>Nobody here but us chickens!</h1>';
	else
	{
		echo '<article>';

		$query = "
			SELECT id, user
				FROM $user_table
				WHERE EXISTS (
					SELECT 1
						FROM $post_table
						WHERE $post_table.owner = $user_table.user
						" . $search->blacklist_fragment() . "
				)
				ORDER BY user ASC LIMIT $page, $limit;";

		$result = $db->query($query) or die($db->error);
		$script = "<script type='text/javascript'>";

		while ($row = $result->fetch_assoc()) {
			$user = $row['user'];
			$id = $row['id'];
			$query = "SELECT id, image, directory, score, rating, tags, owner FROM $post_table WHERE parent = '0' AND owner = '$user' " . $search->blacklist_fragment() . " ORDER BY id DESC LIMIT $plimit;";

			$result2 = $db->query($query) or die($db->error);

			echo "<div><div><h5><a href='/index.php?page=post&s=list&tags=user:$user'>$user</a></h5><a href='/index.php?page=account_profile&id=$id'>View Profile</a></div>";
			while ($row2 = $result2->fetch_assoc()) {
				echo '<span class="thumb"><a id="p'.$row2['id'].'" href="index.php?page=post&amp;s=view&amp;id='.$row2['id'].'"><img src="'.$thumbnail_url.$misc->getThumb($row2['image'], $row2['directory']).'" alt="post" border="0" title="'.$row2['tags'].' score:'.$row2['score'].' rating:'. $row2['rating'].'"/></a></span>';
				$script .= 'posts['.$row2['id'].'] = {\'tags\':\''.strtolower(str_replace('\\',"&#92;",str_replace("'","&#039;",$tags))).'\'.split(/ /g), \'rating\':\''.$row2['rating'].'\', \'score\':'.$row2['score'].', \'user\':\''.str_replace('\\',"&#92;",str_replace(' ','%20',str_replace("'","&#039;",$row2['owner']))).'\'};';
			}
			echo "</div><div class=space style='clear: both;'></div>";

		}

		$script .= 'filterPosts(posts);</script></article><div id=paginator>';
		echo $script;
		//Pagination function. This should work for the whole site... Maybe.
		print $misc->pagination($_GET['page'],$_GET['s'],$id,$limit,$page_limit,$numrows,$_GET['pid'],$_GET['tags']);
	}

?><footer><a href="help/">Help</a></footer><br /><br />
</div></div></section></body></html>