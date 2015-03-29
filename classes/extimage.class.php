<?php
	//Does nothing except works as a patch for the importers
	class extimage extends image
	{
		function getremoteimage($url, $md5)
		{
			global $db, $min_upload_width, $min_upload_height, $max_upload_width, $max_upload_height, $post_table;
			$query = "SELECT COUNT(*) FROM $post_table WHERE hash='$md5' LIMIT 1";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			$count = $row['COUNT(*)'];
			$result->free_result();
			if($count > 0)
				return false;
			$misc = new misc();
			if($url == "" || $url == " ")
				return false;
			$ext = explode('.',$url);
			$count = count($ext);
			$ext = $ext[$count-1];
			$ext = strtolower($ext);
			if($ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "png" && $ext != "bmp")
				return false;
			$ext = ".".$ext;
			$valid_download = false;
			$name = basename($url);
			while(!$valid_download)
			{
				$data = '';
				$old = ini_set('default_socket_timeout', 120);
				$f = fopen($url,"rb");
				ini_set('default_socket_timeout', $old);
				if($f == "")
					return false;
				while(!feof($f))
					$data .= fread($f,4096); 
				fclose($f);
				$rand = rand(1,350293847576);
				while(file_exists("./tmp/".$rand.$name))
					$rand++;
				$f = fopen("./tmp/".$rand.$name,"w");
				fwrite($f,$data);
				fclose($f);
				$tmp_md5 = md5_file("./tmp/".$rand.$name);
				if($tmp_md5 == $md5)
					$valid_download = true;
				unlink("./tmp/".$rand.$name);
			}
			$cdir = $this->getcurrentfolder();
			if(!is_dir("./images/".$cdir."/"))
				$this->makefolder($cdir);
			if(preg_match("#<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext#si", $data) == 1)
				return false;
			$filename = hash('sha1',hash('md5',$url));
			$i = 0;
			while(file_exists("./images/".$cdir."/".$filename.$ext))
			{
				$i++;
				$filename = hash('sha1',hash('md5',$url.$i));
			}
			$f = fopen("./images/".$cdir."/".$filename.$ext,"w");
			if($f == "")
				return false;
			fwrite($f,$data);
			fclose($f);
			$iinfo = getimagesize("./images/".$cdir."/".$filename.$ext);
			if(substr($iinfo['mime'],0,5) != "image" || $iinfo[0] < $min_upload_width && $min_upload_width != 0 || $iinfo[0] > $max_upload_width && $max_upload_width != 0 || $iinfo[1] < $min_upload_height && $min_upload_height != 0 || $iinfo[1] > $max_upload_height && $max_upload_height != 0 || !$this->checksum("./images/".$cdir."/".$filename.$ext))
			{
				unlink("./images/".$cdir."/".$filename.$ext);
				return false;
			}
			$this->folder_index_increment($cdir);
			return $cdir.":".$filename.$ext;
		}
	}
?>