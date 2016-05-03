<?php
	if(!isset($lozerisdumb))
		$lozerisdumb = '';
	echo '<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>'.$site_url3.' '.$lozerisdumb.'</title>
		<link rel="stylesheet" type="text/css" media="screen" href="'.$site_url.'/default.css?2" title="default" />
		<link rel="search" type="application/opensearchdescription+xml" title="'.$site_url3.'" href="'.$site_url.'/default.xml" />
		<meta charset="utf-8">
		<meta name="description" content="A very large imageboard for Japanese related content that is anonymous." />
		<script src="'.$site_url.'/script/prototype.js?2" type="text/javascript"></script>
		<script src="'.$site_url.'/script/global.js?2" type="text/javascript"></script>
		<script src="'.$site_url.'/script/scriptaculous.js?2" type="text/javascript"></script>
		<script src="'.$site_url.'/script/builder.js?2" type="text/javascript"></script>
		<script src="'.$site_url.'/script/effects.js?2" type="text/javascript"></script>
		<script src="'.$site_url.'/script/dragdrop.js?2" type="text/javascript"></script>
		<script src="'.$site_url.'/script/controls.js?2" type="text/javascript"></script>
		<script src="'.$site_url.'/script/slider.js?2" type="text/javascript"></script>
		<script src="'.$site_url.'/script/notes.js?2" type="text/javascript"></script>
		<!--[if lt IE 7]>
			<script defer type="text/javascript" src="'.$site_url.'/script/pngfix.js?2"></script>
		<![endif]-->
	</head>
<body>
	<header>
		<h2><a href="'.$site_url.'/index.php">'.$site_url3.'</a></h2>
        <nav>
			<ul class="flat-list">
				<li><a href="'.$site_url.'/index.php?page=account">My Account</a></li>
				<li><a href="'.$site_url.'/index.php?page=post&amp;s=list&amp;tags=all">Posts</a></li>
				<li><a href="'.$site_url.'/index.php?page=comment&amp;s=list">Comments</a></li>
				<li><a href="'.$site_url.'/index.php?page=users&amp;s=list">Users</a></li>
				<li><a href="'.$site_url.'/index.php?page=alias&amp;s=list">Alias</a></li>
				<li><a href="'.$site_url.'/index.php?page=forum&amp;s=list">Forum</a></li>
				<li><a href="'.$site_url.'/index.php?page=post&amp;s=random">Random</a></li>
				<li><a href="'.$site_url.'/help/index.php">Help</a></li>
				<li id="notice"></li>
			</ul>
		</nav>
	</header>
	<div id="long-notice"></div>
';
?>