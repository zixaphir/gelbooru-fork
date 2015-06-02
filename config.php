<?php
	$mysql_host = "127.0.0.1";
	$mysql_user = "gelbooru";
	$mysql_pass = "gelbooru";
	$mysql_db = "gelbooru1";

	//site base url, no trailing slash.
	$site_url = "http://gelbooru.com/";
	//URL to the thumbnails directory
	$thumbnail_url = "http://gelbooru.com/thumbnails/";
	//Site Name. Displays in header.
	$site_url3 = "Default Booru";
	//folder containing the images..
	$image_folder = "images";
	//thumbnails dimension... same as in danbooru version...
	$dimension = 150;
	//thumbnails folder..
	$thumbnail_folder = "thumbnails";

	//user database table...
	$user_table = "users";
	//post table..
	$post_table = "posts";
	//tag index table
	$tag_index_table = "tag_index";
	//folder index table
	$folder_index_table = "folder_index";
	//favorites table
	$favorites_table = "favorites";
	//note table
	$note_table = "notes";
	//note history table
	$note_history_table = "notes_history";
	//comment table
	$comment_table = "comments";
	//comments vote table
	$comment_vote_table = "comment_votes";
	//posts vote table
	$post_vote_table = "post_votes";
	//group table
	$group_table = "groups";
	//tag historys
	$tag_history_table = "tag_history";
	//comment count table
	$comment_count_table = "comment_count";
	//cache table post count, updates once a day. (part of speed optimization)
	$post_count_table = "post_count";
	//hit counter table
	$hit_counter_table = "hit_counter";
	//favorites count table
	$favorites_count_table = "favorites_count";
	//alias table
	$alias_table = "alias";
	//parent/child table
	$parent_child_table = "parent_child";
	//forum_index_table
	$forum_topic_table = "forum_topics";
	//forum_post_table
	$forum_post_table = "forum_posts";
	//deleted image table
	$deleted_image_table = "delete_images";
	//banned ip table
	$banned_ip_table = "banned_ip";
	//domains for images. ex: http://img1.domain.com/, http://img2.domain.com/folder/folder/
	$domains = array(''.$site_url.'');

	//max image width for upload (0 for no limit)
	$max_upload_width = 0;
	//max image height for upload (0 for no limit)
	$max_upload_height = 0;
	//min image width for upload (0 for no limit)
	$min_upload_width = 150;
	//min image height for upload (0 for no limit)
	$min_upload_height = 150;

	//registration allowed?
	$registration_allowed = true;

	//mail settings
	$site_email = "noemail@example.com";
	$email_recovery_subject = "password reset for ".$site_url."";

	//enable or disable anonymous reports set false to disable
	$anon_report = false;
	//enable or disable anonymous edits set false to disable
	$anon_edit = true;
	//enable or disable anonymous comments set false to disable
	$anon_comment = true;
	//enable or disable anonymous voting set false to disable
	$anon_vote = false;
	//enable or disable anonymous post adding
	$anon_can_upload = true;
	//Edit limit in minutes. If time is over this, edit will not happen.
	$edit_limit = 20;
	//cache dir, all cache will be stored in subdirs to this. Put it on RAM or FAST Raid drives.
	//$main_cache_dir = "NUL:\\";
?>