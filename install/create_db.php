<?php
	require "../config.php";
	require "../functions.global.php";
	$db = new mysqli($mysql_host,$mysql_user,$mysql_pass,$mysql_db) or die("Ooops?");
	$db->set_charset('utf8');
$query = "CREATE TABLE IF NOT EXISTS `$alias_table` (
  `tag` VARCHAR(255) DEFAULT NULL,
  `alias` VARCHAR(255) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  KEY `status` (`status`),
  KEY `alias` (`alias`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$comment_vote_table` (
  `ip` VARCHAR(255) DEFAULT NULL,
  `post_id` bigint(20) unsigned DEFAULT NULL,
  `comment_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$comment_table` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `comment` text,
  `ip` VARCHAR(255) DEFAULT NULL,
  `user` VARCHAR(255) DEFAULT NULL,
  `posted_at` int(11) DEFAULT NULL,
  `edited_at` int(11) default '0',
  `score` bigint(20) default '0',
  `post_id` bigint(20) unsigned NOT NULL,
  `spam` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `post_id` (`post_id`),
  KEY `posted_at` (`posted_at`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$deleted_image_table` (
  `hash` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$favorites_table` (
  `user_id` bigint(99) unsigned NOT NULL,
  `favorite` bigint(99) unsigned DEFAULT NULL,
  `added` int(11) unsigned DEFAULT NULL,  
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$favorites_count_table` (
  `user_id` bigint(20) unsigned NOT NULL,
  `fcount` bigint(20) unsigned default '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$folder_index_table` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` text,
  `count` int(10) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$forum_post_table` (
  `id` bigint(99) unsigned NOT NULL auto_increment,
  `title` text,
  `post` text NOT NULL,
  `author` varchar(256) DEFAULT NULL,
  `creation_date` int(11) DEFAULT NULL,
  `topic_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `post` (`post`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$forum_topic_table` (
  `id` bigint(99) unsigned NOT NULL auto_increment,
  `topic` text,
  `author` varchar(256) DEFAULT NULL,
  `last_updated` int(11) DEFAULT NULL,
  `creation_post` bigint(20) unsigned NOT NULL,
  `priority` int(99) unsigned default '0',
  `locked` tinyint(1) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$group_table` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `group_name` text,
  `delete_posts` tinyint(1) default '0',
  `delete_comments` tinyint(1) default '0',
  `admin_panel` tinyint(1) default '0',
  `reverse_notes` tinyint(1) default '0',
  `reverse_tags` tinyint(1) default '0',
  `default_group` tinyint(1) default '1',
  `is_admin` tinyint(1) default '0',
  `delete_forum_posts` tinyint(1) default '0',
  `delete_forum_topics` tinyint(1) default '0',
  `lock_forum_topics` tinyint(1) default '0',
  `edit_forum_posts` tinyint(1) default '0',
  `pin_forum_topics` tinyint(1) default '0',
  `alter_notes` tinyint(1) default '0',
  `can_upload` tinyint(1) default '1',  
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1
";
$db->query($query);
$query = "SELECT * FROM $group_table";
$result = $db->query($query);
if($result->num_rows == "0")
{
$query = "INSERT INTO `$group_table` (`id`, `group_name`, `delete_posts`, `delete_comments`, `admin_panel`, `reverse_notes`, `reverse_tags`, `default_group`, `is_admin`, `delete_forum_posts`, `delete_forum_topics`, `lock_forum_topics`, `edit_forum_posts`, `pin_forum_topics`, `alter_notes`, `can_upload` ) VALUES 
(1, 'Administrator', 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(2, 'Regular Member', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1)
";
$db->query($query);
}

$query = "CREATE TABLE IF NOT EXISTS `$hit_counter_table` (
  `count` bigint(20) unsigned default '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$note_table` (
  `id` int(10) unsigned default '0',
  `post_id` bigint(20) unsigned NOT NULL,
  `x` int(99) DEFAULT NULL,
  `y` int(99) DEFAULT NULL,
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  `body` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ip` VARCHAR(255) DEFAULT NULL,
  `version` bigint(20) unsigned default '1',
  `user_id` bigint(20) unsigned DEFAULT NULL,
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$note_history_table` (
  `id` int(10) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  `body` text,
  `version` int(10) unsigned DEFAULT NULL,
  `ip` VARCHAR(255) DEFAULT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  KEY `post_id` (`post_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$parent_child_table` (
  `id` bigint(20) NOT NULL auto_increment,
  `parent` bigint(20) NOT NULL,
  `child` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  KEY `child` (`child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$post_count_table` (
  `access_key` VARCHAR(255) DEFAULT NULL,
  `pcount` bigint(20) unsigned default '0',
  `last_update` VARCHAR(255) DEFAULT NULL,
  KEY `access_key` (`access_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$post_vote_table` (
  `rated` varchar(4) NOT NULL,
  `ip` VARCHAR(255) DEFAULT NULL,
  `post_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$post_table` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `creation_date` datetime DEFAULT NULL,
  `score` bigint(99) default '0',
  `hash` text NOT NULL,
  `last_comment` datetime DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `source` VARCHAR(255) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `owner` VARCHAR(256) DEFAULT NULL,
  `height` int(10) unsigned default '0',
  `width` int(10) unsigned default '0',
  `ext` varchar(10) DEFAULT NULL,
  `rating` text,
  `tags` text NOT NULL,
  `directory` text,
  `recent_tags` text,
  `spam` tinyint(1) default '0',
  `tags_version` bigint(20) unsigned default '1',
  `active_date` text NOT NULL,
  `ip` VARCHAR(255) DEFAULT NULL,
  `reason` text,
  `parent` bigint(20) NOT NULL default '0',
  `post_version` bigint(99) unsigned default '0',
  `comment_version` bigint(99) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `creation_date` (`creation_date`),
  KEY `parent` (`parent`),
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$tag_history_table` (
  `total_amount` BIGINT(99) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` bigint(20) unsigned NOT NULL,
  `tags` text,
  `active` tinyint(1) default 1, 
  `version` bigint(20) unsigned default 1,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ip` VARCHAR(255) DEFAULT NULL,
  KEY `id` (`id`),
  KEY `version` (`version`),
  PRIMARY KEY (`total_amount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$tag_index_table` (
  `tag` VARCHAR(255) NOT NULL,
  `index_count` bigint(20) unsigned default '0',
  `version` bigint(20) unsigned default '0',
  KEY `tag` (`tag`),
  KEY `index_count` (`index_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
";
$db->query($query);

$query = "CREATE TABLE IF NOT EXISTS `$user_table` (
  `id` bigint(99) unsigned NOT NULL auto_increment,
  `user` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `my_tags` TEXT DEFAULT NULL,
  `login_session` text,
  `ugroup` bigint(20) unsigned DEFAULT NULL,
  `mail_reset_code` text NULL,
  `forum_can_create_topic` tinyint(1) default '1',
  `forum_can_post` tinyint(1) default '1',
  `post_count` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
  `record_score` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
  `comment_count` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
  `tag_edit_count` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
  `forum_post_count` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,  
  `signup_date` VARCHAR(255) DEFAULT NULL,  
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1
";
$db->query($query);
$query = "CREATE TABLE IF NOT EXISTS `$banned_ip_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(255) DEFAULT NULL,
  `user` text,
  `reason` text,
  `date_added` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
$db->query($query);
$query = "CREATE FUNCTION notes_next_id(post BIGINT)
	RETURNS INTEGER
	NOT DETERMINISTIC
	BEGIN
	DECLARE iv1 INTEGER;
	DECLARE iv2 INTEGER;
	SELECT id INTO iv1 FROM $note_table WHERE post_id=post ORDER BY id DESC LIMIT 1;
	SET iv2 = (iv1+1);
	RETURN iv2;
	END";

$db->query($query);
$query = "SELECT * FROM $post_count_table WHERE access_key='posts'";
$result = $db->query($query);
if($result->num_rows =="0")
{
	$query = "INSERT INTO $post_count_table(access_key, last_update) VALUES('posts','20070101')";
	$db->query($query);
}
$query = "SELECT * FROM $post_count_table WHERE access_key='comment_count'";
$result = $db->query($query);
if($result->num_rows =="0")
{
	$query = "INSERT INTO $post_count_table(access_key, pcount) VALUES('comment_count','0')";
	$db->query($query);
}
$query = "SELECT * FROM $hit_counter_table";
$result = $db->query($query);
if($result->num_rows =="0")
{
	$query = "INSERT INTO $hit_counter_table(count) VALUES('0')";
	$db->query($query);
}
	print "<h1>See no errors above? Install then went well! Yay! Delete the install folder now.</h1>";
?>