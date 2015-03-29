<?php
	class search
	{
		function __construct()
		{
		
		}
		
		function prepare_tags($search)
		{
			global $db, $post_table;
			$search = $db->real_escape_string($search);
			$tags = '';
			$aliased_tags = '';
			$original_tags = '';
			$parent = '';
			$ttags = explode(" ",$search);
			$g_rating = '';
			$g_owner = '';
			$g_tags = '';
			$g_parent = '';
			
			foreach($ttags as $current)
			{
				if(strpos(strtolower($current),'parent:') !== false)
				{
					$g_parent = str_replace("parent:","",$current);
					$parent = " AND id='$g_parent'";
 					if(!is_numeric($g_parent))
						$g_parent = '';					
					else
						$g_parent = " AND parent='$g_parent'";
					$current = '';
				}
				if($current != "" && $current != " ")
				{
					$len = strlen($current);
					$count = substr_count($current, '*', 0, $len);
					if(($len - $count) >= 2)
					{
						if(strpos(strtolower($current),'rating:')  !== false)
						{
							$rating = str_replace('rating:','',$current);
							if(substr($current,0,1) == "-")
							{
								$rating = substr($rating,1,strlen($rating)-1);
								$rating = ucfirst(strtolower($rating));
								$g_rating .= " AND rating != '$rating'";
							}
							else
							{
								$rating = ucfirst(strtolower($rating));
								$g_rating .= " AND rating = '$rating'";
							}
						}
						else if(strpos(strtolower($current),'user:')  !== false)
						{
							$owner = str_replace('user:','',$current);
							if(substr($current,0,1) == "-")
							{
								$owner = substr($owner,1,strlen($owner)-1);
								$g_owner = " AND owner != '$owner'";
							}
							else
								$g_owner = " AND owner = '$owner'";
						}
						else
						{
							$tclass = new tag();
							if(substr($current,0,1) == "-")
							{
								$current = substr($current,1,strlen($current)-1);
								$wildcard = strpos($current,"*");
								$alias = $tclass->alias($current);
								if($alias !== false)
								{
									if($wildcard === false)
									{
										$g_tags2 .= " AND tags NOT LIKE '% $alias %'";
										$g_tags2 .= " AND tags NOT LIKE '% $current %'";
									}
									else
									{
										$g_tags2 .= " AND tags NOT LIKE '% $alias %'";
										$g_tags2 .= " AND tags NOT LIKE '% $current %'";
									}
								}
								else
								{
									if($wildcard == false)
										$g_tags2 .= " AND tags NOT LIKE '% $current %'";
									else
										$g_tags2 .= " AND tags NOT LIKE '% $current %'";
								}
							}
							else if(substr($current,0,1) == "~")
							{
								$current = substr($current,1,strlen($current)-1);
								$alias = $tclass->alias($current);
								if($alias !== false)
								{
									$g_tags .= $alias;
									$g_tags .= $current;
								}
								else
									$g_tags .= $current;
							}
							else
							{
								$wildcard = strpos($current,"*");
								$alias = $tclass->alias($current);
								if($alias !== false)
									$g_tags .= $alias;
								else
									$g_tags .= str_replace("*","%",$current);

							}
						}
					}
				}
			}
			if($g_tags != "")
			{
				if($g_parent != "")
					$parent_patch = "OR tags LIKE '% $g_tags %' $parent $g_owner $g_rating";
				else
					$parent_patch = " AND parent='0'";
				$query = "SELECT id, image, directory, score, rating, tags, owner FROM $post_table WHERE tags LIKE '% $g_tags %' $g_tags2 $g_parent $g_owner $g_rating $parent_patch ORDER BY id DESC";
			}
			else if($g_parent != "" || $g_owner != "" || $g_rating != "")
			{
				if($g_parent != "")
				{
					$g_parent = str_replace('AND',"",$g_parent);
					$parent = substr($parent,4,strlen($parent));
					$parent_patch = "OR $parent $g_owner $g_rating";
				}				
				else if($g_owner != "")
					$g_owner = str_replace('AND',"",$g_owner);
				else if($g_rating != "")
					$g_rating = substr($g_rating,4,strlen($g_rating));
				if($g_parent == "")
					$parent_patch = " AND parent='0'";
				$query = "SELECT id, image, directory, score, rating, tags, owner FROM $post_table WHERE $g_parent $g_owner $g_rating $parent_patch ORDER BY id DESC";			
			}
			else
			{
				$count = substr_count($search, '*', 0, strlen($search));
				if(strlen($search)-$count > 0)
				{
					$res = str_replace("*","",$search);
					$query = "SELECT id, image, directory, score, rating, tags, owner FROM $post_table WHERE tags LIKE '% $res %' ORDER BY id DESC";
				}
				else
					$query = "SELECT id, image, directory, score, rating, tags, owner FROM $post_table ORDER BY id DESC";
			}			
			return $query;
		}
		
		function search_tags_count($search)
		{
			global $post_table;
			$date = date("Ymd");
			$query = "SELECT COUNT(*) FROM $post_table".$search;
			return $query;
		}
		
		function search_tags($search,$condition)
		{
			global $post_table;
			$date = date("Ymd");
			$query = "SELECT id, image, directory, score, rating, tags, owner FROM $post_table".$search.$condition;
			return $query;
		}
	}
?>