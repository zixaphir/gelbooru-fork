<?php
	class image
	{
		private $image_path;
		private $thumbnail_path;
		private $dimension;
		public $error;
		function __construct()
		{
			global $image_folder;
			global $dimension;
			global $thumbnail_folder;
			$this->image_path = $image_folder;
			$this->thumbnail_path = $thumbnail_folder;
			$this->dimension = $dimension;
		}

        function imagick_thumbnail($image, $timage, $ext, $thumbnail_name)
        {
			if ($imginfo) {
				$tmp_ext = ".".str_replace("image/","",$imginfo['mime']);
				if($tmp_ext != $ext)
				{
					$ext = $tmp_ext;
				}
			}
            try {
                $imagick = new Imagick();
                $imagick->readImage($image);
                $imagick->thumbnailImage(150, 150, true);
                $imagick->writeImage("./".$this->thumbnail_path."/".$timage[0]."/".$thumbnail_name);
            }
            catch(Exception $e) {
				echo "Unable to load image." . $e->getMessage();
				return false;
            }
            return true;
        }

        function gd_thumbnail($image, $timage, $ext, $thumbnail_name)
        {
			$imginfo = getimagesize($image);

			if ($imginfo) {
				$tmp_ext = ".".str_replace("image/","",$imginfo['mime']);
				if($tmp_ext != $ext)
				{
					$ext = $tmp_ext;
				}
			}

			switch ($ext)
			{
				case '.jpg':
				case '.jpeg':
					$img = imagecreatefromjpeg($image);
					break;
				case '.gif':
					$img = imagecreatefromgif($image);
					break;
				case '.png':
					$img = imagecreatefrompng($image);
				case '.webm':
					$vid = new webm($image);
					if ($vid->valid_webm()) {
						$img = $vid->frame();
						$imginfo = [
							imagesx($img),
							imagesy($img)
						];
					} else {
						echo "not valid webm";
						return false;
					}
					break;
				default:
					echo "Invalid Filetype";
					return false;
			}

			if(!$img) {
				echo "Unable to create temporary image.";
				return false;
			}

			$max    = ($imginfo[0] > $imginfo[1]) ? $imginfo[0] : $imginfo[1];
			$scale  = ($max < $this->dimension) ? 1 : $this->dimension / $max;
			$width  = $imginfo[0] * $scale;
			$height = $imginfo[1] * $scale;

			$thumbnail = imagecreatetruecolor($width,$height);
			imagecopyresampled($thumbnail,$img,0,0,0,0,$width,$height,$imginfo[0],$imginfo[1]);

			switch ($ext)
			{
				case '.jpg':
				case '.jpeg':
				case '.webm':
					imagejpeg($thumbnail,"./".$this->thumbnail_path."/".$timage[0]."/".$thumbnail_name,95);
					break;
				case '.gif':
					imagegif($thumbnail,"./".$this->thumbnail_path."/".$timage[0]."/".$thumbnail_name);
					break;
				case '.png':
					imagepng($thumbnail,"./".$this->thumbnail_path."/".$timage[0]."/".$thumbnail_name);
					break;
				default:
					echo "Invalid Extension ".$ext.".";
					return false;
			}

			imagedestroy($img);
			imagedestroy($thumbnail);

			return true;
        }

		function thumbnail($image)
		{
			$timage = explode("/",$image);
			$image = $timage[1];
			$ext = explode(".",$image);
			$count = count($ext);
			$ext = $ext[$count-1];
			$ext = ".".$ext;
			$thumbnail_name = "thumbnail_".$image;
			$image = "./".$this->image_path."/".$timage[0]."/".$image;

            if (extension_loaded('imagick') && $ext != '.webm')
                return $this->imagick_thumbnail($image, $timage, $ext, $thumbnail_name);
            else
                return $this->gd_thumbnail($image, $timage, $ext, $thumbnail_name);
		}

		function getremoteimage($url)
		{
			global $min_upload_width, $min_upload_height, $max_upload_width, $max_upload_height;
			$misc = new misc();
			if($url == "" || $url == " ")
				return false;
			$ext = explode('.',$url);
			$count = count($ext);
			$ext = $ext[$count-1];
			$ext = strtolower($ext);
			if($ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "png" && $ext != "webm")
				return false;
			$ext = ".".$ext;
			$valid_download = false;
			$dl_count = 0;
			$name = basename($url);
			while(!$valid_download)
			{
				$data = '';
				$f = fopen($url,"rb");
				if($f == "")
					return false;
				while(!feof($f))
					$data .= fread($f,4096);
				fclose($f);
				if($dl_count == 0)
				{
					$l = fopen("./tmp/".$name."0".$ext,"w");
					fwrite($l,$data);
					fclose($l);
				}
				if($dl_count == 1)
				{
					$l = fopen("./tmp/".$name."1".$ext,"w");
					fwrite($l,$data);
					fclose($l);
				}
				if($dl_count == 1)
				{
					$tmp_size = filesize("./tmp/".$name."0".$ext);
					$size = filesize("./tmp/".$name."1".$ext);
					if($tmp_size >= $size)
					{
						$valid_download = true;
						unlink("./tmp/".$name."0".$ext);
						unlink("./tmp/".$name."1".$ext);
					}
					else
					{
						unlink("./tmp/".$name."0".$ext);
						copy("./tmp/".$name."1".$ext,"./tmp/".$name."0".$ext);
						unlink("./tmp/".$name."1".$ext);
						$dl_count = 0;
					}
				}
				$dl_count++;
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

		function getcurrentfolder()
		{
			global $db, $folder_index_table;
			$query = "SELECT name FROM $folder_index_table WHERE count < 1000 ORDER BY count DESC LIMIT 1";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$name = $row['name'];
			if($name != "")
				return $name;
			else
			{
				$query = "SELECT name FROM $folder_index_table WHERE COUNT >= '1000' ORDER BY id DESC LIMIT 1";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$nfolder = $row['name'] + 1;
				if($row['name'] == "")
				{
					$query = "INSERT INTO $folder_index_table(name, count) VALUES('1','0')";
					$db->query($query) or die($db->error);
					return '1';
				}
				return $nfolder;
			}
		}

		function makefolder($folder)
		{
			mkdir("./images/".$folder);
			copy("./images/index.html","./images/".$folder."/index.html");
			$this->makesqlfolder($folder);
		}

		function makesqlfolder($folder)
		{
			global $db, $folder_index_table;
			$query = "SELECT COUNT(*) FROM $folder_index_table WHERE name='$folder'";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			if($row['COUNT(*)'] <= 0)
			{
				$query = "INSERT INTO $folder_index_table(name, count) VALUES('$folder','0')";
				$db->query($query) or die($db->error);
			}
		}

		function process_upload($upload)
		{
			global $min_upload_width, $min_upload_height, $max_upload_width, $max_upload_height;
			if($upload == "") {
				echo "No data detected.";
				return false;
			}
			$ext = explode('.',$upload['name']);
			$count = count($ext);
			$ext = $ext[$count-1];
			$ext = strtolower($ext);
			if($ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "png" && $ext != "webm") {
				echo "Invalid extension: .".$ext.".";
				return false;
			}
			$ext = ".".$ext;
			$fname = hash('sha1',hash_file('md5',$upload['tmp_name']));
			move_uploaded_file($upload['tmp_name'],"./tmp/".$fname.$ext);
			$f = fopen("./tmp/".$fname.$ext,"rb");
			if($f == "") {
				echo "Could not open file for reading.";
				return false;
			}
			$data = '';
			while(!feof($f))
				$data .= fread($f,4096);
			fclose($f);
			if(preg_match("#<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext#si", $data) == 1)
			{
				echo "Invalid Data detected.";
				unlink("./tmp/".$fname.$ext);
				return false;
			}
			if ($ext === ".webm") 
            {
				$vid = new webm("./tmp/".$fname.$ext);
				if ($vid->valid_webm()) {
					$img = $vid->frame();
					$iinfo = [
						imagesx($img),
						imagesy($img)
					];
					$iinfo['mime'] = 'video/web';
				} else {
					echo "Invalid video file.";
					return false;
				}
			}
            else
            {
				$iinfo = getimagesize("./tmp/".$fname.$ext);
			}
			if( (substr($iinfo['mime'],0,5) != "image" && substr($iinfo['mime'],0,5) != "video") || $iinfo[0] < $min_upload_width && $min_upload_width != 0 || $iinfo[0] > $max_upload_width && $max_upload_width != 0 || $iinfo[1] < $min_upload_height && $min_upload_height != 0 || $iinfo[1] > $max_upload_height && $max_upload_height != 0 || !$this->checksum("./tmp/".$fname.$ext))
			{
				echo "Not a valid image or video file.";
				unlink("./tmp/".$fname.$ext);
				return false;
			}
			$ffname = $fname;
			$cdir = $this->getcurrentfolder();
			$i = 0;
			if(!is_dir("./images/".$cdir."/"))
				$this->makefolder($cdir);
			while(file_exists("./images/".$cdir."/".$fname.$ext))
			{
				$i++;
				$fname = hash('sha1',hash('md5',$fname.$i));
			}
			$f = fopen("./images/".$cdir."/".$fname.$ext,"w");
			if($f == "") {
				echo "Could not write file to disk.";
				return false;
			}
			fwrite($f,$data);
			fclose($f);
			$this->folder_index_increment($cdir);
			unlink("./tmp/".$ffname.$ext);
			return $cdir.":".$fname.$ext;
		}

		function folder_index_increment($folder)
		{
			global $db, $folder_index_table;
			$query = "UPDATE $folder_index_table SET count=count+1 WHERE name='$folder'";
			$db->query($query);
		}

		function folder_index_decrement($folder)
		{
			global $db, $folder_index_table;
			$query = "SELECT count FROM $folder_index_table WHERE name='$folder'";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_assoc();
			if($row['count'] > 0)
			{
				$query = "UPDATE $folder_index_table SET count=count-1 WHERE name='$folder'";
				$db->query($query);
			}
		}

		function makethumbnailfolder($folder)
		{
			mkdir("./thumbnails/".$folder."/");
			copy("./thumbnails/index.html","./thumbnails/".$folder."/index.html");
		}

		function removeimage($id)
		{
			global $db, $post_table, $note_table, $note_history_table, $user_table, $group_table, $favorites_table, $favorites_count_table, $comment_table, $comment_vote_table, $deleted_image_table;
			$can_delete = false;
			$id = $db->real_escape_string($id);
			$query = "SELECT directory, image, owner, tags, hash FROM $post_table WHERE id='$id'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$image = $row['image'];
			$dir = $row['directory'];
			$owner = $row['owner'];
			$tags = $row['tags'];
			$hash = $row['hash'];

			if(isset($_COOKIE['user_id']) && is_numeric($_COOKIE['user_id']) && isset($_COOKIE['pass_hash']))
			{
				$user_id = $db->real_escape_string($_COOKIE['user_id']);
				$pass_hash = $db->real_escape_string($_COOKIE['pass_hash']);
				$query = "SELECT user FROM $user_table WHERE id='$user_id' AND pass='$pass_hash'";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				$user = $row['user'];

				$query = "SELECT t2.delete_posts FROM $user_table AS t1 JOIN $group_table AS t2 ON t2.id=t1.ugroup WHERE t1.id='$user_id' AND t1.pass='$pass_hash'";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				if(strtolower($user) == strtolower($owner) && $user != "Anonymous" || $row['delete_posts'] == true)
					$can_delete = true;
			}

			if($can_delete == true)
			{
				$cache = new cache();
				$query = "SELECT parent FROM $post_table WHERE id='$id'";
				$result = $db->query($query);
				$row = $result->fetch_assoc();
				if($row['parent'] != "" && $row['parent'] != 0)
					$cache->destroy("../cache/".$row['parent']."/post.cache");
				$query = "DELETE FROM $post_table WHERE id='$id'";
				$db->query($query);
				$query = "DELETE FROM $note_table WHERE post_id='$id'";
				$db->query($query);
				$query = "DELETE FROM $note_history_table WHERE post_id='$id'";
				$db->query($query);
				$query = "DELETE FROM $comment_table WHERE post_id='$id'";
				$db->query($query);
				$query = "DELETE FROM $comment_vote_table WHERE post_id='$id'";
				$db->query($query);
				$query = "SELECT user_id FROM $favorites_table WHERE favorite='$id' ORDER BY user_id";
				$result = $db->query($query);
				while($row = $result->fetch_assoc())
				{
					$ret = "UPDATE $favorites_count_table SET fcount=fcount-1 WHERE user_id='".$row['user_id']."'";
					$db->query($ret);
				}

				$query = "DELETE FROM $favorites_table WHERE favorite='$id'";
				$db->query($query);
				$query = "DELETE FROM $parent_child_table WHERE parent='$id'";
				$db->query($query);
				$query = "SELECT id FROM $post_table WHERE parent='$id'";
				$result = $db->query($query);
				while($row = $result->fetch_assoc())
					$cache->destroy("../cache/".$id."/post.cache");
				$query = "UPDATE $post_table SET parent='' WHERE parent='$id'";
				$db->query($query);
				unlink("../images/".$dir."/".$image);
				unlink("../thumbnails/".$dir."/thumbnail_".$image);
				$this->folder_index_decrement($dir);
				$itag = new tag();
				$tags = explode(" ",$tags);

				$misc = new misc();
				foreach($tags as $tag)
				{
					if($tag != "")
					{
						$itag->deleteindextag($tag);
						if(is_dir("../search_cache/".$misc->windows_filename_fix($tag)."/"))
						$cache->destroy_page_cache("../search_cache/".$misc->windows_filename_fix($tag)."/");
					}
				}
				$query = "UPDATE $post_count_table SET last_update='20060101' WHERE access_key='posts'";
				$db->query($query);
				$query = "INSERT INTO $deleted_image_table(hash) VALUES('$hash')";
				$db->query($query);
				return true;
			}
			return false;
		}

		function checksum($file)
		{
			global $db, $post_table, $deleted_image_table;
			$i = 0;
			$tmp_md5_sum = md5_file($file);
			$query = "SELECT id FROM $post_table WHERE hash='$tmp_md5_sum'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$i = $row['id'];

			$query = "SELECT COUNT(*) FROM $deleted_image_table WHERE hash='$tmp_md5_sum'";
			$result = $db->query($query);
			$row = $result->fetch_assoc();
			$count = $row['COUNT(*)'];

			//print $tmp_md5_sum;
			if($i != "" && $i != NULL || $count > 0)
			{
				$this->error = "That image already exists. You can find it <a href=\"index.php?page=post&s=view&id=$i\">here</a><br />";
				return false;
			}
			else
				return true;
		}

		function geterror()
		{
			return $this->error;
		}
	}
?>
