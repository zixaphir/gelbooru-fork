<?php
	require "../../inv.header.php";
	$user = new user();
	if($user->gotpermission('is_admin'))
	{
		$upgrades = "ALTER TABLE $group_table ADD COLUMN alter_notes TINYINT(1) DEFAULT 0";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $group_table ADD COLUMN can_upload TINYINT(1) DEFAULT 1";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $tag_history_table ADD COLUMN ip VARCHAR(255) DEFAULT NULL";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $user_table ADD COLUMN my_tags TEXT DEFAULT NULL";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $tag_history_table ADD COLUMN total_amount BIGINT(99) UNSIGNED NOT NULL AUTO_INCREMENT AFTER `ip`, ADD PRIMARY KEY (`total_amount`)";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $forum_topic_table ENGINE=InnoDB";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $group_table ENGINE=InnoDB";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $note_table ENGINE=InnoDB";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $note_history_table ENGINE=InnoDB";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $parent_child_table ENGINE=InnoDB";		
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $user_table ADD COLUMN post_count INTEGER(11) UNSIGNED NOT NULL DEFAULT 0";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $user_table ADD COLUMN record_score INTEGER(11) UNSIGNED NOT NULL DEFAULT 0";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $user_table ADD COLUMN comment_count INTEGER(11) UNSIGNED NOT NULL DEFAULT 0";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $user_table ADD COLUMN tag_edit_count INTEGER(11) UNSIGNED NOT NULL DEFAULT 0";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $user_table ADD COLUMN forum_post_count INTEGER(11) UNSIGNED NOT NULL DEFAULT 0";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $user_table ADD COLUMN signup_date VARCHAR(255) DEFAULT NULL";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $favorites_table ADD COLUMN added INTEGER(11) UNSIGNED NOT NULL DEFAULT 0";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $comment_table MODIFY COLUMN edited_at INTEGER(11) DEFAULT 0";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $post_table MODIFY COLUMN owner VARCHAR(255) DEFAULT NULL";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $post_table MODIFY COLUMN ext VARCHAR(10) DEFAULT NULL";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $post_table MODIFY COLUMN source VARCHAR(255) DEFAULT NULL";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $post_table MODIFY COLUMN image VARCHAR(255) DEFAULT NULL";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $post_table MODIFY COLUMN title VARCHAR(255) DEFAULT NULL";
		$db->query($upgrades) or print $db->error;
		$upgrades = "ALTER TABLE $post_table DROP COLUMN local_copy";
		$db->query($upgrades) or print $db->error;
		$upgrades = "CREATE TABLE IF NOT EXISTS $banned_ip_table (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `ip` varchar(255) DEFAULT NULL,
		  `user` text,
		  `reason` text,
		  `date_added` int(11) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `ip` (`ip`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		$db->query($upgrades) or print $db->error;
		$upgrades ="ALTER TABLE $tag_history_table ADD COLUMN active BOOLEAN NOT NULL DEFAULT 1";
		$db->query($upgrades) or print $db->error;
		print "<br /><br />Upgrades complete.";
	}
?>