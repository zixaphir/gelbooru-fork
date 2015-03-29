<?php
	$userc = new user();
	$cache = new cache();
	$comment = new comment();
	$ip = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
	if($userc->check_log())
	{
		$user_id = $checked_user_id;
		$user = $checked_username;
	}
	else
	{
		$user = "Anonymous";
		$user_nolog = "Anonymous";
		$query = "SELECT id FROM $user_table WHERE user='$user' LIMIT 1";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		$user_id = $row['id'];
		$anon_id = $row['id'];
	}
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$id = $db->real_escape_string($_GET['id']);
		if(isset($_GET['s']) && $_GET['s'] == "edit" && isset($_POST['comment']))
		{
			if($_POST['conf'] != 1)
			{
				header("Location:index.php?page=post&s=view&id=$id");
				exit;
			}
			if($user == "Anonymous")
			{
				header("Location:index.php?page=post&s=view&id=$id");
				exit;	
			}
			$comment->edit($id,$_POST['comment'],$user);
			$cache = new cache();
			$cache->destroy_page_cache("cache/".$id);
			$cache->create_page_cache("cache/".$id);
			header("Location:index.php?page=post&s=view&id=$id");
		}
		if(isset($_GET['s']) && $_GET['s'] == "save" && isset($_POST['comment']))
		{
			if($_POST['conf'] != 1)
			{
				header("Location:index.php?page=post&s=view&id=$id");
				exit;
			}

			if(!$anon_comment && $user_nolog == "Anonymous")
			{
				header('Location: index.php?page=account&s=home');
				exit;
			}
			if(isset($_POST['post_anonymous']) || $_POST['post_anonymous'] == true)
			{
				$user = "Anonymous";
				$query = "SELECT id FROM $user_table WHERE user='$user' LIMIT 1";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$user_id = $row['id'];
				$anon_id = $row['id'];
			}
			$comment->add($_POST['comment'],$user,$id,$ip,$user_id);
			$cache = new cache();
			$cache->destroy_page_cache("cache/".$id);
			$cache->create_page_cache("cache/".$id);
			header("Location:index.php?page=post&s=view&id=$id");
		}
		else if(isset($_GET['s']) && isset($_GET['cid']) && is_numeric($_GET['cid']) && isset($_GET['vote']))
		{
			$vote = $_GET['vote'];
			$id = $_GET['post_id'];
			$cid = $_GET['cid'];
			if($user == "Anonymous" && !$anon_vote)
			{
				header('Location: index.php?page=account&s=home');
				exit;
			}
			$cache = new cache();
			@$cache->destroy_page_cache("cache/".$id);
			@$cache->create_page_cache("cache/".$id);
			$comment->vote($cid,$vote,$user,$id,$user_id);
		}
		else if(isset($_GET['s']) && $_GET['s'] === "view" && isset($_GET['cid']) && is_numeric($_GET['cid']))
		{
			header("Cache-Control: store, cache");
			header("Pragma: cache");
			require "includes/header.php";
			$cid = $db->real_escape_string($_GET['cid']);
			$query = "SELECT post_id, comment, user, posted_at, score FROM $comment_table WHERE id='$cid'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$misc = new misc();
			echo '<a href="index.php?page=post&s=view&id='.$row['post_id'].'">'.$row['post_id'].'</a> '.$misc->swap_bbs_tags($misc->linebreaks($misc->short_url(htmlentities($row['comment'],ENT_QUOTES,"UTF-8")))).' '.$row['user'].' '.$row['posted_at'].' '.$row['score'];
			$result->free_result();
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
		var posts = {}; posts.comments = {}; posts.ignored = {}; posts.totalcount = {}; posts.tags = {}; posts.rating = {}; posts.score = {}; posts.rating[0] = ''; var phidden = {}; var cthreshold = parseInt(readCookie('comment_threshold')) || 0; var users = readCookie('user_blacklist').split(/[, ]|%20+/g);
		//]]>
		</script>
		<?php
		if(isset($_GET['pid']) && $_GET['pid'] != "" && is_numeric($_GET['pid']) && $_GET['pid'] >= 0)
			$page = $db->real_escape_string($_GET['pid']);
		else
			$page = 0;
		$query = "SELECT pcount FROM $post_count_table WHERE access_key = 'comment_count'";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		if($row['pcount'] == 0)
		{
			echo '<h1>Nobody here but us chickens!</h1>';
			exit;
		}
		$numrows = $row['pcount'];
		$result->free_result();
		$pages = round($numrows/$limit);
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
		$query = "SELECT t1.id, t1.comment, t1.user, t1.posted_at, t1.score, t1.post_id, t1.spam, t2.image, t2.directory as dir, t2.tags, t2.rating, t2.score as p_score, t2.owner, t2.creation_date FROM $comment_table AS t1 JOIN $post_table AS t2 ON t2.id=t1.post_id ORDER BY t2.last_comment DESC,t1.id ASC LIMIT $page, $limit";
		$result = $db->query($query) or die($db->error);
		while($row = $result->fetch_assoc())
		{
			$posted_at = $row['posted_at'];
			$posted_at = date('Y-m-d H:i:s',$posted_at);
			if($img != $row['image'])
			{
				$ttags = explode(" ",$tags);
				if($tcount > 0)
				{
					$images .= '</div><div class="col3"><ul class="post-info"><li>'.$pat.'</li><li>rating:'.$rating.'</li><li>user:'.$user.'</li>';
					$ttcount = 0;
					foreach($ttags as $current)
					{
						if($ttcount < 15)
						{
							$images .= "<li><a href=\"index.php?page=post&amp;s=list&amp;tags=$current\">$current</a></li>";
							++$ttcount;
						}
					}
					$images .='</ul></div></div>';
					
				}
				$images .= '<div class="post" id="p'.$row['post_id'].'">';
				$pat = $row['creation_date'];
				$rating = $row['rating'];
				$user = $row['owner'];
				$tags = mb_trim($row['tags']);
				$images .= '<script type="text/javascript">
				//<![CDATA[
				posts.tags['.$row['post_id'].'] = \''.str_replace('\\',"&#92;",str_replace("'","&#039;",$tags)).'\'
				posts.rating['.$row['post_id'].'] = \''.$row['rating'].'\'
				posts.score['.$row['post_id'].'] = \''.$row['p_score'].'\'		
				//]]>
				</script>';
				if($img != "")
					$images .= '<script type="text/javascript">
					//<![CDATA[
					posts.totalcount['.$lastpid.'] = \''.$ptcount.'\'
					//]]>
					</script>';
				$ptcount = 0;
				$images .= '<div class="col1"><a href="index.php?page=post&amp;s=view&amp;id='.$row['post_id'].'"><img src="'.$thumbnail_url.'/'.$row['dir'].'/thumbnail_'.$row['image'].'" border="0" class="preview" title="'.$tags.'" alt="thumbnail"/></a></div><div class="col2">';
				$img = $row['image'];
			}
			$images .= '<div class="comment" id="c'.$row['id'].'"><h4><a href="index.php?page=account_profile&amp;uname='.$row['user'].'">'.$row['user'].'</a></h4><h6 class="comment-header">Posted on '.$posted_at.'  ('; $row['spam'] == false ? $images .= '<a id="rc'.$row['id'].'"></a><a href="#" id="rcl'.$row['id'].'" onclick="Javascript:spam(\'comment\',\''.$row['id'].'\')">Flag for deletion</a>)</h6>' : $images .= "<b>Already flagged</b>)</h6>"; $images .= "<div id=\"cbody".$row['id']."\"><p>".$misc->swap_bbs_tags($misc->short_url($misc->linebreaks($row['comment'])))."</p></div></div>
			<script type=\"text/javascript\">
			//<![CDATA[
			posts.comments[".$row['id']."] = {'score':".$row['score'].", 'user':'".str_replace('\\',"&#92;",str_replace(' ','%20',str_replace("'","&#039;",$row['user'])))."', 'post_id':'".$row['post_id']."'}
			//]]>
			</script>";	
			++$ccount;
			++$ptcount;
			++$tcount;
			$lastpid = $row['post_id'];
		}
		$ttags = explode(" ",$tags);
		$images .= '</div><div class="col3"><ul class="post-info">';
		$images .= "<li>$pat</li><li>rating:$rating</li><li>user:".htmlentities($user, ENT_QUOTES, 'UTF-8')."</li>";
		$ttcount = 0;
		foreach($ttags as $current)
		{
			if($ttcount < 15)
			{
				$images .= "<li><a href=\"index.php?page=post&amp;s=list&amp;tags=$current\">$current</a></li>";
				++$ttcount;
			}
		}
		$images .=	'</ul></div></div></div>';
		$result->free_result();
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
		//Pagination. Nothing really needs to be changed at this point.
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
			$back_page = $page - $limit;
			echo '<a href="?page=comment&amp;s=list&amp;pid=0"><<</a><a href="?page=comment&amp;s=list&amp;pid='.$back_page.'"><</a>';
		}
		for($i=$start; $i <= $tmp_limit; $i++) // loop through each page and give link to it.
		{
			$ppage = $limit*($i - 1);
			if($ppage == $page)
				echo '<b>'.$i.'</b> ';
			else
				echo '<a href="?page=comment&amp;s=list&amp;pid='.$ppage.'">'.$i.'</a>';
		}
		if (!((($page+$limit) / $limit) >= $pages) && $pages != 1) 
		{ 
			$next_page = $page + $limit;
			echo '<a href="?page=comment&amp;s=list&amp;pid='.$next_page.'">></a><a href="?page=comment&amp;s=list&amp;pid='.$lastpage.'">>></a>';
		}
	}
?>
</div></body></html>