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
		function ImageCreateFromBMP($filename)
		{
		/*********************************************/
		/* Fonction: ImageCreateFromBMP              */
		/* Author:   DHKold                          */
		/* Contact:  admin@dhkold.com                */
		/* Date:     The 15th of June 2005           */
		/* Version:  2.0B                            */
		/*********************************************/
		 //Ouverture du fichier en mode binaire
		   if (! $f1 = fopen($filename,"rb")) return FALSE;

		 //1 : Chargement des ent?tes FICHIER
		   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
		   if ($FILE['file_type'] != 19778) return FALSE;

		 //2 : Chargement des ent?tes BMP
		   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
						 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
						 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
		   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
		   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		   $BMP['decal'] = 4-(4*$BMP['decal']);
		   if ($BMP['decal'] == 4) $BMP['decal'] = 0;

		 //3 : Chargement des couleurs de la palette
		   $PALETTE = array();
		   if ($BMP['colors'] < 16777216 && $BMP['colors'] != 65536)
		   {
			$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
			#nei file a 16bit manca la palette,
		   }

		 //4 : Cr?ation de l'image
		   $IMG = fread($f1,$BMP['size_bitmap']);
		   $VIDE = chr(0);

		   $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
		   $P = 0;
		   $Y = $BMP['height']-1;
		   while ($Y >= 0)
		   {
			$X=0;
			while ($X < $BMP['width'])
			{
			 if ($BMP['bits_per_pixel'] == 24)
				$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
			 elseif ($BMP['bits_per_pixel'] == 16)
			 { 
				$COLOR = unpack("n",substr($IMG,$P,2));
				$blue  = (($COLOR[1] & 0x001f) << 3) + 7;
				$green = (($COLOR[1] & 0x03e0) >> 2) + 7;
				$red   = (($COLOR[1] & 0xfc00) >> 7) + 7;
				$COLOR[1] = $red * 65536 + $green * 256 + $blue;
			 }
			 elseif ($BMP['bits_per_pixel'] == 8)
			 { 
				$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			 }
			 elseif ($BMP['bits_per_pixel'] == 4)
			 {
				$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
				if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			 }
			 elseif ($BMP['bits_per_pixel'] == 1)
			 {
				$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
				if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
				elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
				elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
				elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
				elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
				elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
				elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
				elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
				$COLOR[1] = $PALETTE[$COLOR[1]+1];
			 }
			 else
				return FALSE;
			 imagesetpixel($res,$X,$Y,$COLOR[1]);
			 $X++;
			 $P += $BMP['bytes_per_pixel'];
			}
			$Y--;
			$P+=$BMP['decal'];
		   }

		 //Fermeture du fichier
		   fclose($f1);

		 return $res;
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
				$imginfo = getimagesize($image);
				$tmp_ext = ".".str_replace("image/","",$imginfo['mime']);
				if($tmp_ext != $ext)
				{
					$ext = $tmp_ext;
				}
				if($ext == ".jpg" || $ext == ".jpeg")
					$img = imagecreatefromjpeg($image);
				else if($ext == ".gif")
					$img = imagecreatefromgif($image);
				else if($ext == ".png")
					$img = imagecreatefrompng($image);
				else if($ext == ".bmp")
					$img = $this->imagecreatefrombmp($image);
				else
					return false;
				
				if($img == NULL)
					return false;
					
				$imginfo = getimagesize($image);
				$max = ($imginfo[0] > $imginfo[1]) ? $imginfo[0] : $imginfo[1];
				$scale = ($max < $this->dimension) ? 1 : $this->dimension / $max;
				$width = $imginfo[0] * $scale;
				$height = $imginfo[1] * $scale;
				$thumbnail = imagecreatetruecolor($width,$height);
				imagecopyresampled($thumbnail,$img,0,0,0,0,$width,$height,$imginfo[0],$imginfo[1]);
				if($ext == ".jpg" || $ext == ".jpeg")
					imagejpeg($thumbnail,"./".$this->thumbnail_path."/".$timage[0]."/".$thumbnail_name,95);
				else if($ext == ".gif")
					imagegif($thumbnail,"./".$this->thumbnail_path."/".$timage[0]."/".$thumbnail_name);
				else if($ext == ".png")
					imagepng($thumbnail,"./".$this->thumbnail_path."/".$timage[0]."/".$thumbnail_name);
				else if($ext == ".bmp")
					imagejpeg($thumbnail,"./".$this->thumbnail_path."/".$timage[0]."/".$thumbnail_name,95);
				else
					return false;
				imagedestroy($img);
				imagedestroy($thumbnail);
				return true;
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
			if($ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "png" && $ext != "bmp")
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
			if($upload == "")
				return false;
			$ext = explode('.',$upload['name']);
			$count = count($ext);
			$ext = $ext[$count-1];
			$ext = strtolower($ext);
			if($ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "png" && $ext != "bmp")
				return false;
			$ext = ".".$ext;
			$fname = hash('sha1',hash_file('md5',$upload['tmp_name']));
			move_uploaded_file($upload['tmp_name'],"./tmp/".$fname.$ext);
			$f = fopen("./tmp/".$fname.$ext,"rb");
			if($f == "")
				return false;
			$data = '';
			while(!feof($f))
				$data .= fread($f,4096);
			fclose($f);
			if(preg_match("#<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext#si", $data) == 1)
			{	
				unlink("./tmp/".$fname.$ext);
				return false;
			}
			$iinfo = getimagesize("./tmp/".$fname.$ext);
			if(substr($iinfo['mime'],0,5) != "image" || $iinfo[0] < $min_upload_width && $min_upload_width != 0 || $iinfo[0] > $max_upload_width && $max_upload_width != 0 || $iinfo[1] < $min_upload_height && $min_upload_height != 0 || $iinfo[1] > $max_upload_height && $max_upload_height != 0 || !$this->checksum("./tmp/".$fname.$ext))
			{
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
			if($f == "")
				return false;
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
