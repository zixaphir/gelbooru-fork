<?php
	//number of comments/page
	$limit = 10;
	//number of pages to display. number - 1. ex: for 5 value should be 4
	$page_limit = 6;
	//Load required class files. post.class and cache.class
	$post = new post();
	$cache = new cache();
	header("Cache-Control: store, cache");
	header("Pragma: cache");
	$domain = $cache->select_domain();
	$id = $db->real_escape_string($_GET['id']);
	if(!is_numeric($id))
		$id = str_replace("#","",$id);
	$id = (int)$id;
	$date = date("Ymd");
	//Load post_table data and the previous next values in array. 0 previous, 1 next.
	$post_data = $post->show($id);
	//Check if data exists in array, if so, kinda ignore it.
	if($post_data == "" || is_null($post_data))
	{
		header('Location: index.php?page=post&s=list');
		exit;
	}
	$prev_next = $post->prev_next($id);
	
	if(!is_dir("$main_cache_dir".""."\cache/$id"))
		$cache->create_page_cache("cache/$id");
	$data = $cache->load("cache/".$id."/post.cache");
	if($data !== false)
	{
		echo str_replace("f6ca1c7d5d00a2a3fb4ea2f7edfa0f96a6d09c11717f39facabad2d724f16fbb",$domain,$data);
		flush();
		$tcount = 1;
	}
	else
	{
		ob_start();
		$tags = mb_trim(html_entity_decode($post_data['tags'], ENT_QUOTES, "UTF-8"));
		$ttags = explode(" ",$tags);
		$rating = $post_data['rating'];
		$lozerisdumb = "- ".str_replace('_',' ',str_replace('&quot;','\\"',$tags));
		require "includes/header.php";	
		echo '<div id="content"><div id="post-view">';
		if($post->has_children($id))
			echo '<div style="background: #f0f0f0; padding: 10px; text-align: center; border: 3px solid #dadada;">This post has <a href="index.php?page=post&s=list&tags=parent:'.$id.'"><b>child posts</b></a>. Child posts are often subsequent pages of a doujinshi, or minor variations of the parent post.</div><br><br>';

		echo '<div class="sidebar"><div class="space">
		<h5>Search</h5>
		<form action="index.php?page=search" method="post">
		<input id="stags" name="tags" size="20" type="text" />
		<br /><input name="commit" style="margin-top: 3px; background: #fff; border: 1px solid #dadada; width: 172px;" type="submit" value="Search" />
		</form><small>(Supports wildcard *)</small>
		</div>
		<div id="tag_list">
		<h5>Tags</h5>
		<ul>';
		foreach($ttags as $current)
		{
			$count = $post->index_count($current);
			echo '<li><span style="color: #a0a0a0;">? <a href="index.php?page=post&amp;s=list&amp;tags='.$current.'">'.str_replace('_',' ',$current)."</a> ".$count['index_count']."</span></li>";
		}
		echo '<li><br /><br /><br /><br /><br /><br /><br /><br /></li></ul></div></div>
		<b>Score</b> <a href="#" onclick="Javascript:post_vote(\''.$id.'\', \'up\')">+</a> <a href="#" onclick="Javascript:post_vote(\''.$id.'\', \'down\')">-</a> <a id="psc">'.$post_data['score'].'</a> ';
		if($post_data['spam'] == false)
			echo '<a id="rp'.$id.'"></a><a href="#" id="rpl'.$id.'" onclick="Element.toggle(\'report_form\')">Report post.</a><br /><form id="report_form" method="post" action="./public/report.php?type=post&amp;rid='.$id.'" style="display: none;">Reason for report:<br /><input type="text" name="reason" value=""/><input type="submit" name="submit" value="" style="display: none;"/></form>';
		else
			print '<b>Post reported</b><br />';
		echo '<div class="content" id="right-col"><div><div id="note-container">';

		$note_data = $post->get_notes($id);
		while($retme = $note_data->fetch_assoc())
		{
			echo '<div id="post-view"><div class="note-box" style="width: '.$retme['width'].'px; height: '.$retme['height'].'px; top: '.$retme['y'].'px; left: '.$retme['x'].'px; display: block;" id="note-box-'.$retme['id'].'">
			<div class="note-corner" id="note-corner-'.$retme['id'].'"></div></div></div>
			<div style="display: none; top: '.($retme['width']+$retme['y']+5).'px; left: '.$retme['x'].'px;" class="note-body" id="note-body-'.$retme['id'].'" title="Click to edit">'.$retme['body'].'</div>
			';
		}
		echo '<img alt="img" src="f6ca1c7d5d00a2a3fb4ea2f7edfa0f96a6d09c11717f39facabad2d724f16fbb/images/'.$post_data['directory'].'/'.$post_data['image'].'" id="image" onclick="Note.toggle();" style="margin-right: 70px;"/><br />Posted on '.$post_data['creation_date'].' by  <a href="index.php?page=account_profile&amp;uname='.$post_data['owner'].'">'.$post_data['owner'].'</a><br /><p id="note-count"></p>
		<script type="text/javascript">
		//<![CDATA[
		Note.post_id = '.$id.';';
		$notes = '';
		$note_data = $post->get_notes($id);
		while($retme = $note_data->fetch_assoc())		
			echo 'Note.all.push(new Note('.$retme['id'].', false));';
		echo 'Note.updateNoteCount();
		Note.show();
		//]]></script>';
		echo '<a href="#" onclick="if(confirm(\'Are you sure you want to delete this post?\')){var f = document.createElement(\'form\'); f.style.display = \'none\'; this.parentNode.appendChild(f); f.method = \'POST\'; f.action = \'./public/remove.php?id='.$id.'&amp;removepost=1\'; f.submit();}; return false;">Remove</a> | <a href="#" onclick="Note.create('.$id.'); return false;">Add note</a> | <a href="#" onclick="addFav(\''.$id.'\'); return false;">Keep</a> | <a href="#" onclick="showHide(\'edit_form\'); return false;">Edit</a> | <a href="#" onclick="document.location=\'index.php?page=history&amp;type=page_notes&amp;id='.$id.'\'; return false;">Note history</a> | <a href="index.php?page=history&amp;type=tag_history&amp;id='.$id.'">Tag History</a>'; ?> <?php $prev_next['0'] != "" ? print ' | <a href="index.php?page=post&amp;s=view&amp;id='.$prev_next['0'].'">Previous</a>' : print ""; $prev_next['1'] != "" ? print ' | <a href="index.php?page=post&amp;s=view&amp;id='.$prev_next['1'].'">Next</a>' : print ""; $row['parent'] == 0 ? print "<br />" : print "<br /><a href=\"index.php?page=post&s=view&id=".$row['parent']."\">Parent</a> | ";
?>
		<form method="post" action="./public/edit_post.php" id="edit_form" name="edit_form" style="display:none">
		<table><tr><td>
		Rating<br />
		<input type="radio" name="rating" <?php if($post_data['rating'] == "Explicit"){ print 'checked="checked"'; } ?> value="e" />Explicit
		<input type="radio" name="rating" <?php if($post_data['rating'] == "Questionable"){ print 'checked="checked"'; } ?> value="q" />Questionable
		<input type="radio" name="rating" <?php if($post_data['rating'] == "Safe"){print 'checked="checked"'; } ?> value="s" />Safe
		</td></tr>
<?php
		if($post_data['parent'] == "0")
			$post_data['parent'] = "";
		echo '<tr><td>Title<br />
		<input type="text" name="title" id="title" value="'.$post_data['title'].'" />
		</td></tr><tr><td>Parent<br />
		<input type="text" name="parent" value="'.$post_data['parent'].'" />
		</td></tr><tr><td>Next Post<br />
		<input type="text" name="next_post" id="next_post" value="'.$prev_next['1'].'"/>
		</td></tr><tr><td>Previous Post<br />
		<input type="text" name="previous_post" id="previous_post" value="'.$prev_next['0'].'"/>
		</td></tr><tr><td>Source<br />
		<input type="text" name="source" size="40" id="source" value="'.$post_data['source'].'" />
		</td></tr><tr><td>Tags<br />
		<textarea id="tags" name="tags" cols="40" rows="5">'.$tags.'</textarea>
		</td></tr><tr><td>My Tags<br />
		<div id="my-tags">
		<a href="index.php?page=account-options">Edit</a>
		</div></td></tr><tr><td>
		<input type="hidden" name="pconf" id="pconf" value="0"/>
		Recent Tags<br />
		'.$post_data['recent_tags'].'
		</td></tr><tr><td><input type="hidden" name="id" value="'.$id.'" />
		</td></tr><tr><td><input type="submit" name="submit" value="Save changes" />
		</td></tr></table></form>
		<script type="text/javascript">
		//<![CDATA[
			$(\'pconf\').value=1;			
		//]]>
		</script>
		<script type="text/javascript">
		//<![CDATA[
		var my_tags = readCookie("tags").split(/[, ]|%20+/g);
		var my_tags_length = my_tags.length;
		var temp_my_tags = Array();
		var g = 0;
		for(i in my_tags)
		{
			if(my_tags[i] != "" && my_tags[i] != " " && i <= my_tags_length)
			{
				temp_my_tags[g] = my_tags[i];				
				g++;
			}
		}
		my_tags = temp_my_tags;
		var links = \'\';
		j = 0;
		my_tags_length = my_tags.length;
		for(i in my_tags)
		{
			if(j < my_tags_length)
			{
				links = links+\'<a href="index.php?page=post&amp;s=list&amp;tags=\'+my_tags[i]+\'" id="t_\'+my_tags[i]+\'"\' + "onclick=\"javascript:toggleTags(\'"+my_tags[i]+"\',\'tags\',\'t_"+my_tags[i]+"\');" + \'return false;">\'+my_tags[i]+\'</a> \';
			}
			j++;
		}
		if(j > 0)
			$(\'my-tags\').innerHTML=links;
		else
			$(\'my-tags\').innerHTML=\'<a href="index.php?page=account-options">Edit</a>\';
		//]]>
		</script>
		<script type="text/javascript">
		//<![CDATA[
		function toggleTags(tag, id, lid)
		{
			temp = new Array(1);
			temp[0] = tag;
			tags = $(\'tags\').value.split(" ");
			if(tags.include(tag))
			{
				$(\'tags\').value=tags.without(tag).join(" ");
				$(lid).innerHTML=tag+" ";
			}
			else
			{
				$(\'tags\').value=tags.concat(temp).join(" ");
				$(lid).innerHTML="<b>"+tag+"</b> ";
			}
			return false;
		}
		//]]>
		</script>
		<br /><br />
		<script type="text/javascript">
		//<![CDATA[
		var posts = {}; posts['.$id.'] = {}; posts['.$id.'].comments = {}; posts['.$id.'].ignored = {}; var cthreshold = parseInt(readCookie(\'comment_threshold\')) || 0; var users = readCookie(\'user_blacklist\').split(/[, ]|%20+/g);
		//]]>
		</script>';

		$data = '';
		$data = ob_get_contents();
		ob_end_clean();
		$cache->save("cache/".$id."/post.cache",$data);
		echo str_replace("f6ca1c7d5d00a2a3fb4ea2f7edfa0f96a6d09c11717f39facabad2d724f16fbb",$domain,$data);
		flush();
	}
	$user = new user();
	$got_permission = $user->gotpermission('delete_comments');
	if(isset($_GET['pid']) && is_numeric($_GET['pid']) && $_GET['pid'] > "0")
	{
		$pid = ceil($_GET['pid']);
		$page = $pid;
	}
	else
	{
		$page = 0;
		$pid = 0;
	}
		
	$data = '';
	if(file_exists("cache/$id/comments.$pid.cache"))
		$data = $cache->load("cache/$id/comments.$pid.cache");

	if($data !== false && $data != "" && $got_permission === false)
	{
		echo $data;
		flush();
	}
	else
	{
		if($got_permission === false)
			ob_start();
		
		//Pagination starts here with dependencies. No need to change this usually... :3
		$comment = new comment();
		$misc = new misc();
		$count = $comment->count($id,$_GET['page'],$_GET['s']);
		echo "$count ";
		if($count > 1)
			print "comments";
		else
			print "comment";
		echo '<a href="#" id="ci" onclick="showHideIgnored('.$id.',\'ci\'); return false;"> (0 hidden)</a><br />';
		//List comments... Could this be better off as a function to use here and on the comment list page? :S
		$query = "SELECT SQL_NO_CACHE id, comment, user, posted_at, score, spam FROM $comment_table WHERE post_id='$id' ORDER BY posted_at ASC LIMIT $page, $limit";
		$result = $db->query($query);
		$ccount = 0;
		while($row = $result->fetch_assoc())
		{
			echo '<div id="c'.$row['id'].'" style="display:inline;"><br /><a href="index.php?page=account_profile&amp;uname='.$row['user'].'">'.$row['user'].'</a><br /><b>Posted on '.date('Y-m-d H:i:s',$row['posted_at']).' Score: <a id="sc'.$row['id'].'">'.$row['score'].'</a> (vote <a href="#" onclick="Javascript:vote(\''.$id.'\', \''.$row['id'].'\', \'up\'); return false;">Up</a>/<a href="#" onclick="Javascript:vote(\''.$id.'\', \''.$row['id'].'\', \'down\'); return false;">Down</a>)&nbsp;&nbsp;&nbsp;';
?>
			(<?php $row['spam'] == false ? print "<a id=\"rc".$row['id']."\"></a><a href=\"#\" id=\"rcl".$row['id']."\" onclick=\"Javascript:spam('comment','".$row['id']."')\">Report as spam</a>)" : print "<b>Reported</b>)"; $got_permission == true ? print ' (<a href="#" onclick="document.location=\'public/remove.php?id='.$row['id'].'&removecomment=1&post_id='.$id.'\'; return false;">Remove</a>)' : print ''; print "</b><br />".$misc->swap_bbs_tags($misc->short_url($misc->linebreaks($row['comment'])));?><br /></div>
			<script type="text/javascript">
			//<![CDATA[
			posts[<?php echo $id;?>].comments[<?php echo $row['id'];?>] = {'score':<?php echo $row['score'];?>, 'user':'<?php echo str_replace('\\',"&#92;",str_replace(' ','%20',str_replace("'","&#039;",$row['user'])));?>'}
			//]]>
			</script>
<?php
			if($got_permission !== false)
			{
				ob_flush();
				flush();
			}
			++$ccount;
		}
		echo "<br /><br /><div id='paginator'>";
		
		//Functionized the paginator... Let's see how well this works in practice.
		$misc = new misc();
		print $misc->pagination($_GET['page'],$_GET['s'],$id,$limit,$page_limit,$count,$pid);
		echo '<script type="text/javascript">
		//<![CDATA[
		filterComments(\''.$id.'\', \''.$ccount.'\')
		//]]></script></div><a href="#" onclick="Javascript:showHide(\'comment_form\'); return false;">>>Respond</a>
		<form method="post" action="index.php?page=comment&amp;id='.$id.'&amp;s=save" name="comment_form" id="comment_form" style="display:none">
		<table><tr><td>
		<textarea name="comment" rows="0" cols="0"></textarea>
		</td></tr><tr><td>Post as anonymous?<br />
		<input type="checkbox" name="post_anonymous"/>
		</td></tr><tr><td>
		<input type="submit" name="submit" value="Post comment"/>
		</td></tr><tr><td>
		<input type="hidden" name="conf" id="conf" value="0"/>
		</td></tr></table></form>
		<script type="text/javascript">
		//<![CDATA[
		document.getElementById(\'conf\').value=1;			
		//]]></script>';
		if($got_permission === false)
		{
			$data = '';
			$data = ob_get_contents();
			ob_end_clean();
			$cache->save("cache/$id/comments.$pid.cache",$data);
			echo $data;
			flush();
		}
	}
	ob_flush();
	flush();
?><br / ><br /><br / ><br /></div></div></div></div></div></body></html>