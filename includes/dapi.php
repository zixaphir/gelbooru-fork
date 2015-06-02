<?php
	if($_GET['s'] == "post")
		// require "includes/dapi_post.php"; ?

        if ($_GET['q'] == "index")
        {
            if ($_GET['t'] == "json")
                require "includes/japi_list.php";
            else
                die("Not implemented");
            /* TODO
                require "includes/dapi_list.php";
            */
        }
        else if ($_GET['q'] == "view")
            /* TODO */
            die("Not implemented");
        else
            die("q not defined");
    else
        die("s not defined.");
?>