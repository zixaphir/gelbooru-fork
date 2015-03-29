<?php
	//number of topics/page
	$limit = 20;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 6;
	$user = new user();
	$misc = new misc();
	header("Cache-Control: store, cache");
	header("Pragma: cache");

	if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && 	$_GET['pid'] >= 0)
		$page = $db->real_escape_string($_GET['pid']);
	else
		$page = 0;
	$id = $db->real_escape_string($_GET['id']);
	if($user->check_log())
	{
		$uname = $checked_username;
		$uid = checked_user_id;
	}
	$query = "SELECT COUNT(*) FROM $forum_post_table WHERE topic_id='$id'";
	$result = $db->query($query);
	$row = $result->fetch_assoc();
	$numrows = $row['COUNT(*)'];
	$result->free_result();
	if($numrows == 0)
	{
		header("Location: index.php?page=forum&s=list");
		exit;
	}
	require "includes/header.php";
	$query = "SELECT t1.id, t1.title, t1.post, t1.author, t1.creation_date, t2.creation_post FROM $forum_post_table  AS t1 JOIN $forum_topic_table AS t2 ON t2.id=t1.topic_id WHERE t1.topic_id='$id' ORDER BY id LIMIT $page, $limit";
	$result = $db->query($query) or die(mysql_error());
	print'<div id="forum" class="response-list">';
	while($row = $result->fetch_assoc())
	{
		$date_made = $misc->date_words($row['creation_date']);
		print '<div class="post"><div class="author">
		<h6 class="author"><a name="'.$row['id'].'"></a><a href="index.php?page=account_profile&amp;uname='.$row['author'].'" style="font-size: 14px;">'.$row['author'].'</a></h6>
		<span class="date">'.$date_made.' </span>
		</div><div class="content">
		<h6 class="response-title">'.$row['title'].'</h6>
		<div class="body">'.$misc->short_url($misc->swap_bbs_tags($misc->linebreaks($row['post']))).'</div>
		<div class="footer">';
    	if($uname == $row['author'] || $user->gotpermission('edit_forum_posts'))
			echo '<a href="#" onclick="showHide(\'c'.$row['id'].'\'); return false;">edit</a> |';
		else
			echo '<a href="">edit</a> |';
		echo ' <a href="#" onclick="javascript:document.getElementById(\'reply_box\').value=document.getElementById(\'reply_box\').value+\'[quote]'.$row['author'].' said:\r\n'.str_replace("'","\'",str_replace("\r\n",'\r\n',str_replace('&#039;',"'",$row['post']))).'[/quote]\'; return false;">quote</a> '; 
		if($user->gotpermission('delete_forum_posts') && $row['id'] != $row['creation_post'])
			print ' | <a href="index.php?page=forum&amp;s=remove&amp;pid='.$id.'&amp;cid='.$row['id'].'">remove</a><br />';
		if($uname == $row['author'] || $user->gotpermission('edit_forum_posts'))
			print '<form method="post" action="index.php?page=forum&amp;s=edit&amp;pid='.$id.'&amp;cid='.$row['id'].'&amp;ppid='.$page.'" style="display:none" id="c'.$row['id'].'"><table><tr><td><input type="text" name="title" value="'.$row['title'].'"/></td></tr><tr><td><textarea name="post" rows="4" cols="6" style="width: 450px; height: 150px;">'.$row['post'].'</textarea></td></tr><tr><td><input type="submit" name="submit" value="Edit"/></td></tr></table></form>';
		echo '</div></div></div>';
	}
	echo '<div class="paginator"><div id="paginator">';
	$misc = new misc();
	print $misc->pagination($_GET['page'],$_GET['s'],$row['id'],$limit,$page_limit,$numrows,$_GET['pid'],$_GET['tags']);
	echo '</div><center><br /><br />';
	$query = "SELECT locked FROM $forum_topic_table WHERE id='$id' LIMIT 1";
	$result = $db->query($query) or die(mysql_error());
	$row = $result->fetch_assoc();
	print ($row['locked'] == false) ? '<a href="#" onclick="showHide(\'reply\'); return false;">Reply</a> | ' : '';
	print '<a href="index.php?page=forum&amp;s=add">New Topic</a> | <a href="'.$site_url.'/help/">Help</a> | <b><a href="'.$site_url.'/index.php?page=forum&amp;s=list">Forum Index</a></b>';
	if($row['locked'] == false) 
	{
		if($user->gotpermission('lock_forum_topics'))
			print ' | <a href="index.php?page=forum&amp;s=edit&amp;lock=true&amp;id='.$id.'&amp;pid='.$page.'">Lock topic</a>';
	}
	else
	{	
		if($user->gotpermission('lock_forum_topics'))
			print ' | <a href="index.php?page=forum&amp;s=edit&amp;lock=false&amp;id='.$id.'&amp;pid='.$page.'">Unlock topic</a>';
	}		
	if($row['locked'] == false)
	{
		echo '</center><br /><br /><form method="post" action="index.php?page=forum&amp;s=add&amp;t=post&amp;pid='.$id.'" style="display:none" id="reply">
		<table><tr><td>
		Title<br />
		<input type="text" name="title" value=""/>
		</td></tr><tr><td>
		Body<br />
		<textarea id="reply_box" name="post" rows="4" cols="6" style="padding-left: 5px; padding-right: 5px; width: 600px; height: 200px;"></textarea>
		</td></tr><tr><td>
		<input type="hidden" name="l" value="'.$limit.'"/>
		</td></tr><tr><td>
		<input type="hidden" name="conf" id="conf" value="0"/>
		</td></tr><tr><td>
		<input type="submit" name="submit" value="Post"/>
		</td></tr></table></form>
		<script type="text/javascript">
		//<![CDATA[
		document.getElementById(\'conf\').value=1;
		//]]></script>';
	}
?>
</div></div></body></html>