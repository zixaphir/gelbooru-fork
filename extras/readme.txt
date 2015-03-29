Find the first instance of $data = ''; in post_view.php
Under </script> and before '; add in:
	<script type="text/javascript">
	bytebox.init();
	</script>

In the tag list area if you're unsure of where to put it.
<a rel ="bytebox" hrer="link to image">Original Image</a>





header.php add 
<script type="text/javascript" src="script/bytebox.js"></script>
<link rel="stylesheet" href="bytebox.css" type="text/css" media="screen" />

