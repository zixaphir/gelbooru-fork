<?php
	require "../inv.header.php";
	require "../includes/header.php";
?>

<div id="content">
<div class="help">
  <h1>Help: Posts</h1>
  <p>A post represents a single file that's been uploaded. Each post can have several tags, comments, and notes. If you have an account, you can also add a post to your favorites.</p>
  
<div class="section">
	<h4>Search</h4>
	<p>Searching for posts is straightforward. Simply enter the tags you want to search for, separated by spaces. For example, searching for <code>original panties</code> will return every post that has both the original tag <strong>AND</strong> the panties tag.</p>
</div>
  
<div class="section">
	<h4>Tag List</h4>
    <p>In both the listing page and the show page you'll notice a list of tag links with characters next to them. Here's an explanation of what they do: (Currently removed)</p>
    <dl>
      
      <dt>+</dt>
      <dd>This adds the tag to the current search.</dd>

      <dt>&ndash;</dt>
      <dd>This adds the negated tag to the current search.</dd>
           
      <dt>950</dt>
      <dd>The number next to the tag represents how many posts there are. This isn't always the total number of posts for that tag. It may be slightly out of date as cache isn't always refreshed.</dd>

    </dl>
    <p>When you're not searching for a tag, by default the tag list will show the last few tags added to the database. When you are searching for tags, the tag list will show related tags, alphabetically.</p>
</div>
  
</div></div></body></html>