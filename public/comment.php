<?php
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = mysql_real_escape_string($_GET['id']);
		if(isset($_SERVER['HTTP_X_FORWARD_FOR'])) 
			$ip = mysql_real_escape_string($_SERVER['HTTP_X_FORWARD_FOR']);
		else
			$ip = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
		if(isset($_GET['s']) && $_GET['s'] == "save" && isset($_POST['comment']))
		{
			if($_POST['conf'] != 1)
			{
				header("Location:index.php?page=post&s=view&id=$id");
				exit;			
			}
			$comment = mysql_real_escape_string($_POST['comment']);
			if(isset($_SESSION['id']) && is_numeric($_SESSION['id']) && !isset($_POST['post_anonymous']) || isset($_SESSION['id']) && is_numeric($_SESSION['id']) && $_POST['post_anonymous'] != true)
			{	
				$query = "SELECT user FROM $user_table WHERE id='".mysql_real_escape_string($_SESSION['id'])."' AND login_session='".mysql_real_escape_string($_SESSION['login_session'])."'";
				$result = mysql_query($query);
				$row = mysql_fetch_assoc($result);
				if($row['user'] != "")
					$user = $row['user'];
				else
					$user = "Anonymous";
				mysql_free_result($result);
			}
			else
			{
				$user = "Anonymous";
			}
			$len = strlen($comment);
			$count = substr_count($comment, ' ', 0, $len);
			if($comment != "" && ($len - $count) >= 3)
			{
				$query = "INSERT INTO $comment_table(comment, ip, user, posted_at, post_id) VALUES('$comment', '$ip', '$user', NOW(), '$id')";
				mysql_query($query);
				$query = "UPDATE $post_table SET last_comment=NOW() WHERE id='$id'";
				mysql_query($query);
			}
			$cache = new cache();
			$cache->destroy_page_cache("cache/".$id);
			$cache->create_page_cache("cache/".$id);
			header("Location:index.php?page=post&s=view&id=$id");
		}
		else if(isset($_GET['s']) && isset($_GET['cid']) && is_numeric($_GET['cid']) && isset($_GET['vote']))
		{
			$id = mysql_real_escape_string($_GET['id']);
			$cid = mysql_real_escape_string($_GET['cid']);
			if(isset($_SESSION['id']))
				$query_part = " OR comment_id='$cid' AND post_id='$id' AND user_id='".mysql_real_escape_string($_SESSION['id'])."'";
			else
				$query_part = "";
			$query = "SELECT COUNT(*) FROM $comment_vote_table wHERE comment_id='$cid' AND post_id='$id' AND ip='$ip'".$query_part;
			$result = mysql_query($query);
			$row = mysql_fetch_assoc($result);
			$count = $row['COUNT(*)'];
			mysql_free_result($result);
			if($_GET['vote'] == "up")
				$query = "UPDATE $comment_table SET score=score+1 WHERE id='$cid' AND post_id='$id'";
			else if($_GET['vote'] == "down")
				$query = "UPDATE $comment_table SET score=score-1 WHERE  id='$cid' AND post_id='$id'";
			if($query != "")
			{
				if($count < 1)
				{
					mysql_query($query);
					$query = "INSERT INTO $comment_vote_table(ip,post_id,comment_id) VALUES('$ip', '$id', '$cid')";
					mysql_query($query);
				}
				$query = "SELECT score FROM $comment_table WHERE id='$cid' AND post_id='$id'";
				$result = mysql_query($query);
				$row = mysql_fetch_assoc($result);
				print $row['score'];
				mysql_free_result($result);
				$cache = new cache();
				$cache->destroy_page_cache("cache/".$id);
				$cache->create_page_cache("cache/".$id);
			}
			
		}
		else if(isset($_GET['s']) && $_GET['s'] === "view" && isset($_GET['cid']) && is_numeric($_GET['cid']))
		{
			header("Cache-Control: store, cache");
			header("Pragma: cache");
			require "includes/header.php";
			$cid = mysql_real_escape_string($_GET['cid']);
			$query = "SELECT post_id, comment, user, posted_at, score FROM $comment_table WHERE id='$cid'";
			$result = mysql_query($query);
			$row = mysql_fetch_assoc($result);
			echo '<a href="index.php?page=post&s=view&id='.$row['post_id'].'">'.$row['post_id'].'</a> '.$row['comment'].' '.$row['user'].' '.$row['posted_at'].' '.$row['score'];
			mysql_free_result($result);
		}
	}
	else if(isset($_GET['s']) && $_GET['s'] == "list")
	{
		//number of comments/page
		$limit = 15;
		//number of pages to display. number - 1. ex: for 5 value should be 4
		$page_limit = 4;
		
		header("Cache-Control: store, cache");
		header("Pragma: cache");
		$cache = new cache();
		$domain = $cache->select_domain();
		$misc = new misc();
		require "includes/header.php";
		?>
		<div id="comment-list2">
		<script type="text/javascript">
		//<![CDATA[
		var posts = {}; posts.comments = {}; posts.ignored = {}; posts.totalcount = {}; posts.tags = {}; posts.rating = {}; posts.rating[0] = ''; var phidden = {}; var cthreshold = parseInt(readCookie('comment_threshold')) || 0; var users = readCookie('user_blacklist').split(/[, ]|%20+/g);
		//]]>
		</script>
		<?php
		if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && 	$_GET['pid'] >= 0)
			$page = mysql_real_escape_string($_GET['pid']);
		else
			$page = 0;
		$query = "SELECT SUM(ccount) FROM comment_count";
		$result = mysql_query($query);
		$row = mysql_fetch_assoc($result);
		$numrows = $row['SUM(ccount)'];
		mysql_free_result($result);
		$pages = intval($numrows/$limit);
		if ($numrows%$limit) 
			$pages++;
		$current = ($page/$limit) + 1;
		if ($pages < 1 || $pages == 0 || $pages == "")
			$total = 1;
		else
			$total = $pages;
		$first = $page + 1;
		if (!((($page + $limit) / $limit) >= $pages) && $pages != 1)
			$last = $page + $limit;
		else
			$last = $numrows;
		$row_count = 0;
		$img = '';
		$ccount = 0;
		$ptcount = 0;
		$lastpid = '';
		$previd = '';
		$tcount = 0;
		$images = '';
		$query = "SELECT t1.id, t1.comment, t1.user, t1.posted_at, t1.score, t1.post_id, t1.spam, t2.image, t2.directory as dir, t2.tags, t2.rating, t2.owner, t2.creation_date FROM $comments_table AS t1 JOIN $post_table AS t2 ON t2.id=t1.post_id ORDER BY t2.last_comment DESC,t1.post_id ASC LIMIT $page, $limit";
		$result = mysql_query($query) or die(mysql_error());
		while($row = mysql_fetch_assoc($result))
		{
			//content
			if($img != $row['image'])
			{
				$ttags = explode(" ",$tags);
				if($tcount > 0)
				{
					$images .= '
					</div>
					<div class="col3">
						<ul class="post-info">
					 <li>'.$pat.'</li><li>rating:'.$rating.'</li><li>user:'.$user.'</li>';
					$ttcount = 0;
					foreach($ttags as $current)
					{
						if($ttcount < 15)
						{
						$images .= "<li><a href=\"index.php?page=post&amp;s=list&amp;tags=$current\">$current</a></li>";
						++$ttcount;
						}
					}
					
					$images .='	</ul>
					</div>
				</div>';
					
				}
				$images .= '<div class="post" id="p'.$row['post_id'].'">';
				$pat = $row['creation_date'];
				$rating = $row['rating'];
				$user = $row['owner'];
				$tags = $row['tags'];
				$tags = substr($tags,1,strlen($tags));
				$tags = substr($tags,0,strlen($tags)-1);
				$images .= '<script type="text/javascript">
		//<![CDATA[
		posts.tags['.$row['post_id'].'] = \''.str_replace('\\',"&#92;",str_replace("'","&#039;",$tags)).'\'
		posts.rating['.$row['post_id'].'] = \''.$row['rating'].'\'
		//]]>
		</script>';
				if($img != "")
					$images .= '<script type="text/javascript">
		//<![CDATA[
		posts.totalcount['.$lastpid.'] = \''.$ptcount.'\'
		//]]>
		</script>';
			$ptcount = 0;
			$images .= '<div class="col1"><a href="index.php?page=post&amp;s=view&amp;id='.$row['post_id'].'"><img src="'.$domain.'/thumbnails/'.$row['dir'].'/thumbnail_'.$row['image'].'" border="0" class="preview" title="'.$tags.'" alt="thumbnail"/></a></div><div class="col2">';
			$img = $row['image'];
			}
			$images .= '<div class="comment" id="c'.$row['id'].'"><h4><a href="index.php?page=comment&amp;id='.$row['post_id'].'&amp;s=view&amp;cid='.$row['id'].'">'.$row['user'].'</a></h4><h6 class="comment-header">Posted on '.$row['posted_at'].'  ('; $row['spam'] == false ? $images .= '<a id="rc'.$row['id'].'"></a><a href="#" id="rcl'.$row['id'].'" onclick="Javascript:spam(\'comment\',\''.$row['id'].'\')">Mark as spam</a>)</h6>' : $images .= "<b>Reported</b>)</h6>"; $images .= "<div id=\"cbody".$row['id']."\"><p>".$misc->linebreaks($misc->short_url(htmlentities($row['comment'],ENT_QUOTES,"UTF-8")))."</p></div></div>
		<script type=\"text/javascript\">
		//<![CDATA[
		posts.comments[".$row['id']."] = {'score':".$row['score'].", 'user':'".str_replace('\\',"&#92;",str_replace(' ','%20',str_replace("'","&#039;",$row['user'])))."', 'post_id':'".$row['post_id']."'}
		//]]>
		</script>";	
		
			//ob_flush();
			//flush();
		++$ccount;
		++$ptcount;
		++$tcount;
		$lastpid = $row['post_id'];
		}
			$ttags = explode(" ",$tags);
			$images .= '</div>
				<div class="col3">
					<ul class="post-info">';
					$images .= "<li>$pat</li><li>rating:$rating</li><li>user:$user</li>";
					$ttcount = 0;
					foreach($ttags as $current)
					{
						if($ttcount < 15)
						{
							$images .= "<li><a href=\"index.php?page=post&amp;s=list&amp;tags=$current\">$current</a></li>";
							++$ttcount;
						}
					}
					
				$images .=	'</ul>
				</div>
			</div>
		</div>';
		mysql_free_result($result);
		$images .= '<script type="text/javascript">
		//<![CDATA[
		posts.totalcount['.$lastpid.'] = \''.$ptcount.'\'
		//]]>
		</script>
		<br /><a href="#" id="ci" onclick="showHideCommentListIgnored(); return false;">(0 hidden)</a><br /><br />
		<script type="text/javascript">
		//<![CDATA[
		filterCommentList(\''.$ccount.'\')
		//]]>
		</script>';
		echo $images;
		ob_flush();
		flush();

		if($page == 0)
			$start = 1;
		else
			$start = ($page/$limit) + 1;
		$tmp_limit = $start + $page_limit;
		if($tmp_limit > $pages)
			$tmp_limit = $pages;
		if($pages > $page_limit)
			$lowerlimit = $pages - $page_limit;
		if($start > $lowerlimit)
			$start = $lowerlimit;
		$lastpage = $limit*($pages - 1);	
		print'<div id="paginator">';
			if($page != 0 && !((($page+$limit) / $limit) > $pages)) 
			{ 
				// Don't show back link if current page is first page.
				$back_page = $page - $limit;

				?><a href="#" onclick="document.location='?page=comment&amp;s=list&amp;pid=0'; return false;"><<</a><a href="#" onclick="document.location='?page=comment&amp;s=list&amp;pid=<?php print $back_page;?>'; return false;"><</a>
			<?php
			}
			for($i=$start; $i <= $tmp_limit; $i++) // loop through each page and give link to it.
			{
				$ppage = $limit*($i - 1);
				if($ppage == $page)
				{
					?>
					<b><?php print $i;?></b> 
					<?php // If current page don't give link, just text.
				}
				else
				{
					?><a href="#" onclick="document.location='?page=comment&amp;s=list&amp;pid=<?php print $ppage;?>'; return false;"><?php print $i;?></a><?php 
				}
			}
			if (!((($page+$limit) / $limit) >= $pages) && $pages != 1) 
			{ 
				// If last page don't give next link.
				$next_page = $page + $limit;
				?><a href="#" onclick="document.location='?page=comment&amp;s=list&amp;pid=<?php print $next_page;?>'; return false;">></a><a  href="#" onclick="document.location='?page=comment&amp;s=list&amp;pid=<?php print $lastpage;?>'; return false;">>></a>	
		<?php
			}
	ob_flush();
	flush();
	}
?></div>
</body>
</html>
