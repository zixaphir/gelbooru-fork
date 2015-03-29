<?php
	//number of topics/page
	$limit = 40;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 6;
	
	header("Cache-Control: store, cache");
	header("Pragma: cache");
	$misc = new misc();
	require "includes/header.php";
	
	echo '<form method="post" action="?page=forum&amp;s=search">
	<input type="text" name="search" value="" style="width: 40%;" />
	<input type="submit" name="submit" value="Search" />
	</form>
	<table class="highlightable" width="100%"><tr><th width="60%">Title</th><th width="10%">Created by</th><th width="10%">Updated</th><th width="5%">Replies</th>';
	if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
		$page = $db->real_escape_string($_GET['pid']);
	else
		$page = 0;
	if(isset($_GET['query']))
		$search = $db->real_escape_string($_GET['query']);
	else
		$search = '';
	$user = new user();
	if($search != "")
	{
		$tmp = explode(" ",$search);
		$rsearch = '&amp;query='.$search;
		$search = '';
		foreach($tmp as $current)
		{
			$search .= "'\"$current\"' ";
		}
		$search .= 'IN BOOLEAN MODE';
	}
	else
		$rsearch = '';
	if($search != '')
		$query = "SELECT COUNT(*) FROM $forum_topic_table as t1 JOIN $forum_post_table AS t2 ON (MATCH(t2.post) AGAINST($search)>0.5)";
	else
		$query = "SELECT COUNT(*) FROM $forum_topic_table";
	$result = $db->query($query);
	$row = $result->fetch_assoc();
	$numrows = $row['COUNT(*)'];
	if($search != "")
		$query = "SELECT t1.id, t1.topic, t1.last_updated, t1.priority, t1.author FROM $forum_topic_table AS t1 JOIN $forum_post_table AS t2 ON (MATCH(t2.post) AGAINST($search)>0.5) ORDER BY t1.priority DESC, t1.last_updated DESC LIMIT $page, $limit";
	else
		$query = "SELECT id, topic, last_updated, author, locked, priority FROM $forum_topic_table  ORDER BY priority DESC, last_updated DESC LIMIT $page, $limit";
	$result = $db->query($query) or die($db->error());
	if($user->gotpermission('delete_forum_topics') || $user->gotpermission('pin_forum_topics'))
		print '<th width="10%">Tools</th>';
	echo '</tr>';
	while($row = $result->fetch_assoc())
	{
		$que = "SELECT COUNT(*) FROM $forum_post_table WHERE topic_id='".$row['id']."'";
		$res = $db->query($que) or die($db->error());
		$ret = $res->fetch_assoc();
		$replies = $ret['COUNT(*)']-1;
		$date_now =	$misc->date_words($row['last_updated']);
		$sticky = "";
		$locked = "";
		if($row['priority'] =="1")
			$sticky ="Sticky: ";
		if($row['locked']=="1")
			$locked =' <span class="locked-topic">(locked)</span>';
			
		print '<tr>';
		print '<td>'.$sticky.'<a href="?page=forum&amp;s=view&amp;id='.$row['id'].'">'.$row['topic'].'</a>'.$locked.'</td><td>'.$row['author'].'</td><td>'.$date_now.'</td><td>'.$replies.'</td>'; 
		if($row['priority'] == 0)
		{
			if($user->gotpermission('pin_forum_topics'))
				print '<td><a href="index.php?page=forum&amp;s=edit&amp;pin=1&amp;id='.$row['id'].'&amp;pid='.$page.'">Pin</a> | ';
		}
		else
		{
			if($user->gotpermission('pin_forum_topics')) 
				print '<td><a href="index.php?page=forum&amp;s=edit&amp;pin=0&amp;id='.$row['id'].'&amp;pid='.$page.'">Unpin</a> | ';
		}
		if($user->gotpermission('delete_forum_topics'))
			print ' <a href="index.php?page=forum&amp;s=remove&amp;fid='.$row['id'].'&amp;pid='.$page.'">Delete</a></td>'; 
		echo '</tr>';
	}
	echo '</table><div class="paginator"><div id="paginator">';
	$misc = new misc();
	print $misc->pagination($_GET['page'],$_GET['s'],$row['id'],$limit,$page_limit,$numrows,$_GET['pid'],$_GET['tags'],$_GET['query']);
	echo '</div><br /><div id="footer"><a href="#" onclick="showHide(\'new_topic\'); return false;">New Topic</a> | <a href="'.$site_url.'/help/">Help</a>';
?>	
</div></div><form method="post" action="index.php?page=forum&amp;s=add" id="new_topic" style="display:none">
<table><tr><td>
Topic:<br/>	
<input type="text" name="topic" value=""/>
</td></tr><tr><td>
Post:<br />
<textarea name="post" rows="4" cols="6" style="width: 600px; height: 200px;"></textarea>
</td></tr><tr><td>
<input type="hidden" name="conf" id='conf' value="0"/>
</td></tr><tr><td>
<input type="submit" name="submit" value="Create topic"/>
</td></tr></table></form>
<script type="text/javascript">
//<![CDATA[
document.getElementById('conf').value=1;
//]]></script></body></html>