<?php
	function __autoload($class)
	{
		require "classes/$class.class.php";
	}
?>