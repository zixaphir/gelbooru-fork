<?php
	class misc
	{
		function short_url($text)
		{
			$pos = true;
			$offset = 0;
			$links = array();
			$urls = array();
			$i = 0;
			while($pos !== false && $i < 50)
			{
				$pos = strpos($text, 'http://', $offset);
				$offset = $pos+1;
				$links[] = $pos;
				$i++;

			}
			foreach($links as $pos)
			{
				$tmp_test = '';
				$pos2 = '';
				$offset = '';
				$url = '';
				if($pos !== false)
				{
					$offset = substr($text,0,$pos);
					$tmp_text = str_replace($offset,"",$text);
					$pos = strpos($tmp_text," ");
					$pos2 = strpos($tmp_text,"\r\n");
					$pos3 = strpos($tmp_text,"<br />");
					$pos4 = strpos($tmp_text,"	");
					if($pos2 < $pos && $pos2 !== false)
						$pos = $pos2;
					if($pos3 < $pos && $pos3 !== false)
						$pos = $pos3;
					if($pos4 < $pos && $pos4 !== false)
						$pos = $pos4;
					if($pos !== false)
					{
						$offset = substr($tmp_text,$pos,strlen($tmp_text));
						$tmp_text = str_replace($offset,"",$tmp_text);
					}
					$tmp_text = str_replace(" ","",$tmp_text);
					$url = $tmp_text;
					$tmp_text = str_replace("http://","",$tmp_text);
					$tmp_text_len = strlen($tmp_text);
					if($tmp_text_len > 60)
					{
						$url_first_part = substr($tmp_text,0,25);
						$url_second_part = substr($tmp_text,-25,$tmp_text_len);
						$display_url = '<a href="'.$url.'" rel="nofollow">http://'.$url_first_part."...".$url_second_part."</a>";
					}
					else
						$display_url = '<a href="'.$url.'" rel="nofollow">'.$url.'</a>';
					$urls[$url] = $display_url;
				}
			}
			foreach($urls as $url => $display_url)
				$text = str_replace($url,$display_url,$text);
			return $text;
		}

		function linebreaks($text)
		{
			if(strpos($text,"\r\n") !== false)
				return str_replace("\r\n","<br />",$text);
			return $text;
		}

		function send_mail($reciver, $subject, $body)
		{
			require "config.php";
			global $site_email, $site_url3;
			$headers = array();
			$eol = "\r\n";

			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/plain; charset=iso-8859-1";
			$headers[] = "From: no-reply at ".$site_url3." <$site_email>";
			$headers[] = "Subject: {$subject}";
			$headers[] = "X-Mailer: PHP/".phpversion();
			$headers[] = 'Content-Type: text/html; charset="UTF-8"';
			$headers[] = "Content-Transfer-Encoding: 8bit";

			if(substr($body,-8,strlen($body)) != $eol.$eol) {
				$body = $body.$eol.$eol;
			}

			if(@mail($reciver,$subject,$body,implode($eol, $headers))) {
				return true;
			} else {
				return false;
			}
		}

		function windows_filename_fix($new_tag_cache)
		{
			if(strpos($new_tag_cache,";") !== false)
				$new_tag_cache = str_replace(";","&#059;",$new_tag_cache);
			if(strpos($new_tag_cache,".") !== false)
				$new_tag_cache = str_replace(".","&#046;",$new_tag_cache);
			if(strpos($new_tag_cache,"*") !== false)
				$new_tag_cache = str_replace("*","&#042;",$new_tag_cache);
			if(strpos($new_tag_cache,"|") !== false)
				$new_tag_cache = str_replace("|","&#124;",$new_tag_cache);
			if(strpos($new_tag_cache,"\\") !== false)
				$new_tag_cache = str_replace("\\","&#092;",$new_tag_cache);
			if(strpos($new_tag_cache,"/") !== false)
				$new_tag_cache = str_replace("/","&#047;",$new_tag_cache);
			if(strpos($new_tag_cache,":") !== false)
				$new_tag_cache = str_replace(":","&#058;",$new_tag_cache);
			if(strpos($new_tag_cache,'"') !== false)
				$new_tag_cache = str_replace('"',"&quot;",$new_tag_cache);
			if(strpos($new_tag_cache,"<") !== false)
				$new_tag_cache = str_replace("<","&lt;",$new_tag_cache);
			if(strpos($new_tag_cache,">") !== false)
				$new_tag_cache = str_replace(">","&gt;",$new_tag_cache);
			if(strpos($new_tag_cache,"?") !== false)
				$new_tag_cache = str_replace("?","&#063;",$new_tag_cache);
			return $new_tag_cache;
		}

		function ReadHeader($socket)
		{
			$i=0;
			$header = "";
			while( true && $i<20 && !feof($socket))
			{
			   $s = fgets( $socket, 4096 );
			   $header .= $s;
			   if( strcmp( $s, "\r\n" ) == 0 || strcmp( $s, "\n" ) == 0 )
				   break;
			   $i++;
			}
			if( $i >= 20 )
			   return false;
			return $header;
		}

		function getRemoteFileSize($header)
		{
			if(strpos($header,"Content-Length:") === false)
				return 0;
			$count = preg_match($header,'/Content-Length:\s([0-9].+?)\s/',$matches);
			if($count > 0)
			{
				if(is_numeric($matches[1]))
					return $matches[1];
				else
					return 0;
			}
			else
				return 0;
		}

		function swap_bbs_tags($data)
		{
			$pattern = array();
			$replace = array();
			$pattern[] = '/\[quote\](.*?)\[\/quote\]/i';
			$replace[] = '<div class="quote">$1</div>';
			$pattern[] = '/\[b\](.*?)\[\/b\]/i';
			$replace[] = '<b>$1</b>';
			$pattern[] = '/\[spoiler\](.*?)\[\/spoiler\]/i';
			$replace[] = '<span class="spoiler">$1</span>';
			$pattern[] = '/\[post\](.*?)\[\/post\]/i';
			$replace[] = '<a href="index.php?page=post&s=view&id=$1">post #$1</a>';
			$pattern[] = '/\[forum\](.*?)\[\/forum\]/i';
			$replace[] = '<a href="index.php?page=forum&s=view&id=$1">forum #$1</a>';
			$count = count($pattern)-1;
			for($i=0;$i<=$count;$i++)
			{
				while(preg_match($pattern[$i],$data) == 1)
					$data =  preg_replace($pattern[$i], $replace[$i], $data);
			}
			return $data;
		}

		function date_words($date_now)
		{
			$hour_now = date('g:i:s A',$date_now);
			if($date_now+60*60*24 >= time())
				$date_now = "Today";
			else if($date_now+60*60*48 >= time())
				$date_now = "Yesterday";
			else if(((int)((time()-$date_now)/(24*60*60)))<=7)
			{
				$a = time()-$date_now;
				$a = (int)($a/(24*60*60));
				$date_now = $a." days ago";
			}
			else if(((int)((time()-$date_now)/(24*60*60)))<=31)
			{
				$a = time()-$date_now;
				$a = (int)($a/(24*60*60*7));
				$date_now = $a." weeks ago";
			}
			else if(((int)((time()-$date_now)/(24*60*60)))<=365)
			{
				$a = time()-$date_now;
				$a = (int)($a/(24*60*60*31));
				$date_now = $a." months ago";
			}
			else
			{
				$a = time()-$date_now;
				$a = ((int)($a/(24*60*60*365)));
				$date_now = $a." years ago";
			}
			$date_now = '<span title="'.$hour_now.'">'.$date_now.'</span>';
			return $date_now;
		}

		public function is_html($data)
		{
			if(preg_match("#<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext|<div|<frame|<iframe|<li|type=#si", $data) == 1)
				return true;
			else
				return false;
		}

		function pagination($page_type,$sub = false,$id = false,$limit = false,$page_limit = false,$count = false,$page = false,$tags = false, $query = false, $sort = false)
		{
			$lowerlimit = 0;
			$has_id = "";
			if(isset($id) && $id > 0)
				$has_id = '&amp;id='.$id.'';
			if(isset($tags) && $tags !="" && $tags)
				$has_tags = '&amp;tags='.str_replace(" ","+",urlencode($tags)).'';
			if(isset($sub) && $sub !="" && $sub)
				$sub = '&amp;s='.$sub.'';
			if(isset($query) && $query != "" && $query)
				$query = '&amp;query='.urlencode($query).'';
            if(isset($sort) && $sort != "" && $sort) 
                $has_sort = '&amp;sort='.$sort.'';
			$pages = intval($count/$limit);
			if ($count%$limit)
				$pages++;
			$current = ($page/$limit) + 1;
			$total = $pages;
			if ($pages < 1 || $pages == 0 || $pages == "")
				$total = 1;

			$first = $page + 1;
			$last = $count;
			if (!((($page + $limit) / $limit) >= $pages) && $pages != 1)
				$last = $page + $limit;
			$output = "";
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
			if($page != 0 && !((($page+$limit) / $limit) > $pages))
			{
				$back_page = $page - $limit;
				$output .=  '<a href="?page='.$page_type.''.$sub.''.$query.''.$has_id.''.$has_tags.'&amp;pid=0" alt="first page">&lt;&lt;</a><a href="?page='.$page_type.''.$sub.''.$query.''.$has_id.''.$has_tags.''.$has_sort.'&amp;pid='.$back_page.'" alt="back">&lt;</a>';
			}
			for($i=$start; $i <= $tmp_limit; $i++)
			{
				$ppage = $limit*($i - 1);
				if($ppage >= 0)
				{
					if ($ppage == $page)
						$output .=  ' <b>'.$i.'</b> ';
					else
						$output .=  '<a href="?page='.$page_type.''.$sub.''.$query.''.$has_id.''.$has_tags.''.$has_sort.'&amp;pid='.$ppage.'">'.$i.'</a>';
				}
			}
			if (!((($page+$limit) / $limit) >= $pages) && $pages != 1)
			{
				// If last page don't give next link.
				$next_page = $page + $limit;
				$output .= '<a href="?page='.$page_type.''.$sub.''.$query.''.$has_id.''.$has_tags.''.$has_sort.'&amp;pid='.$next_page.'" alt="next">&gt;</a><a href="?page='.$page_type.''.$sub.''.$query.''.$has_id.''.$has_tags.''.$has_sort.'&amp;pid='.$lastpage.'" alt="last page">&gt;&gt;</a>';
			}
			return $output;
		}

		function getThumb($image, $dir) {
			$thumb = explode('.', $image);
			array_pop($thumb);
			$thumb = implode('.', $thumb).".jpg";
			$thumb = '/'.$dir."/thumbnail_".$thumb;
			return $thumb;
		}

	}
?>