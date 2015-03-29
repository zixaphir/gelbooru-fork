<?php
	require "../../inv.header.php";
	$user = new user();
	if($user->gotpermission('is_admin'))
	{
		$upgrades = "ALTER TABLE $user_table MODIFY COLUMN mail_reset_code TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL";
		$db->query($upgrades) or die($db->error);
		print "Altering mail_reset code Success<br><br>";
		$query = "SELECT * FROM $post_count_table WHERE access_key = 'comment_count'";
		$result = $db->query($query);
		if($result->num_rows == "0")
		{
			$upgrades = "INSERT INTO $post_count_table(pcount,access_key,last_update) VALUES('0','comment_count','0')";
			$db->query($upgrades) or die($db->error);
		}
		$upgrades = "SELECT * FROM $comment_table";
		$result = $db->query($upgrades) or die($db->error);
		$numrows = $result->num_rows;
		$upgrades = "UPDATE $post_count_table SET pcount = '$numrows' WHERE access_key = 'comment_count'";
		$db->query($upgrades) or die($db->error);

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
		$db->query($query) or die($db->error);
		print "Upgrades complete.";
	}
?>