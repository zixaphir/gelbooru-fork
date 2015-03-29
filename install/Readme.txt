Thanks for downloading the Gelbooru source code. I hope you enjoy it as much as we do.

To install:

1. Make sure to fill in the information in config.php. You need to set the host which MySQL is listening on, the username, password and database name. Set your site URL as well.
2. Please navigate to the install folder using your web browser. http://sitename.com/install/
3. When you are done installing, delete the install folder as well as the upgrade folder.
4. Give images, tmp, and thumbnails folders writable permissions. Every other directory will not be written to.
5. Check to make sure these are enabled in your php.ini, located at the installation path of PHP for Windows.
 - extension=php_mbstring.dll
 - extension=php_gd2.dll
 - extension=php_mysql.dll  // Is not requred for this version. Other scripts may need this though.
 - extension=php_mysqli.dll
 - gd.jpeg_ignore_warning = 1
6. gelbooru.xml should be renamed to your site name, and the link inside the file edited to make sure you can actually use the Firefox search for your site and not ours.
7. Header.php needs to be edited to reflect the gelbooru.xml change and index.php in the includes folder need to be modified to fit your server setup.

#Extra information#

If you are upgrading or installing for the first time:

  Having issues searching for tags with less than 3 characters?

   - Disable the stopword file for MySQL and also set the minchar length to 1.
	Example:
	 - ft_min_word_len=1
	 - ft_stopword_file=

   After that is done, please run "repair table posts;" and also "repair table forum_topics; repair table forum_posts;" in the MySQL command prompt. You can also run a repair using phpmyadmin on all the tables. Please read here for more information: 
   http://dev.mysql.com/doc/refman/5.1/en/fulltext-fine-tuning.html

		DELETE THE UPGRADES AND INSTALL DIRECTORY AFTER YOU ARE DONE!


-----------------------------------------------------

*Seeing errors?*
Search for error_reporting in your php.ini. Set it to this:

error_reporting = E_ALL & ~(E_NOTICE | E_USER_NOTICE) ; display all errors and warnings
display_errors = off (Not recommended, unless you have a error_log set)
