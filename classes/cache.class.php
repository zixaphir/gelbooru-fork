<?php
	class cache
	{
		function select_domain()
		{
			global $domains;
			$count = count($domains)-1;
			$i = rand(0,$count);
			return $domains[$i];
		}
		
		function save($file,$data)
		{
			global $main_cache_dir;
			if(!is_dir($main_cache_dir))
				@mkdir($main_cache_dir);
			if(!is_dir($main_cache_dir."/search_cache/"))
				@mkdir("$main_cache_dir/search_cache");
			if(!is_dir($main_cache_dir."/cache/"))
				@mkdir("$main_cache_dir/cache");
			$f = @fopen($main_cache_dir.$file,"w");
			@fwrite($f,$data);
			@fclose($f);
		}
		
		function load($file)
		{
			global $main_cache_dir;
			if(!is_dir($main_cache_dir))
				@mkdir($main_cache_dir);
			if(!is_dir($main_cache_dir."/search_cache/"))
				@mkdir("$main_cache_dir/search_cache");
			if(!is_dir($main_cache_dir."/cache/"))
				@mkdir("$main_cache_dir/cache");
			if(file_exists($main_cache_dir.$file))
			{
				$data = '';
				$f = fopen($main_cache_dir.$file,"r");
				while(!feof($f))
					$data .= fread($f,8912);
				fclose($f);
				return $data;
			}
			else
				return false;
		}
		
		function destroy_page_cache($dir)
		{
			global $main_cache_dir;
			if(!is_dir($main_cache_dir))
				@mkdir($main_cache_dir);
			if(!is_dir($main_cache_dir."/search_cache/"))
				@mkdir("$main_cache_dir/search_cache");
			if(!is_dir($main_cache_dir."/cache/"))
				@mkdir("$main_cache_dir/cache");
			if(substr($dir,-1,strlen($dir)) != "/")
				$dir = $dir."/";
			$dir = $main_cache_dir.$dir;
			if(!is_dir($dir))
				return;
			if($dir != "$main_cache_dir".""."cache/" && $dir != "$main_cache_dir".""."search_cache/" && is_dir($dir))
			{
				$dir_contents = @scandir($dir);
				foreach ($dir_contents as $item) 
				{
			 		if (is_dir($dir.$item) && $item != '.' && $item != '..') 
				  		$this->destroy_page_cache($dir.$item.'/');
			 		elseif (file_exists($dir.$item) && $item != '.' && $item != '..') 
				   		@unlink($dir.$item);
		   		}
				@rmdir($dir);
			}
		}
		
		function create_page_cache($dir)
		{
			global $main_cache_dir;
			if(!is_dir($main_cache_dir))
				@mkdir($main_cache_dir);
			if(!is_dir($main_cache_dir."/search_cache/"))
				@mkdir("$main_cache_dir/search_cache");
			if(!is_dir($main_cache_dir."/cache/"))
				@mkdir("$main_cache_dir/cache");
			$dir = $main_cache_dir.$dir;
			if(!is_dir($dir))
				@mkdir($dir);
		}
		
		function destroy($file)
		{
			global $main_cache_dir;
			if(!is_dir($main_cache_dir))
				@mkdir($main_cache_dir);
			if(!is_dir($main_cache_dir."/search_cache/"))
				@mkdir("$main_cache_dir/search_cache");
			if(!is_dir($main_cache_dir."/cache/"))
				@mkdir("$main_cache_dir/cache");
			$file = $main_cache_dir.$file;
			if(file_exists($file))
				unlink($file);
		}
	}
?>