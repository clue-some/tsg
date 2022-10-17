<HTML>
<TITLE>PHPBuddy getting screen resolution</TITLE>
<!--
(c) http://www.phpbuddy.com (Feel free to use this script but keep this message intact)
Author: Ranjit Kumar (Cheif Editor phpbuddy.com)
-->
<HEAD>
<?
if(isset($HTTP_COOKIE_VARS["users_resolution"]))
	$screen_res = $HTTP_COOKIE_VARS["users_resolution"];
else //means cookie is not found set it using Javascript
{
?>
<script language="javascript">
<!--
writeCookie();

function writeCookie() 
{
 var today = new Date();
 var the_date = new Date("December 31, 2023");
 var the_cookie_date = the_date.toGMTString();
 var the_cookie = "users_resolution="+ screen.width +"x"+ screen.height;
 var the_cookie = the_cookie + ";expires=" + the_cookie_date;
 document.cookie=the_cookie
	 
 location = 'get_resolution.php';
}
//-->
</script>
<?
}
?>
</HEAD>
<BODY>
<?php
	echo "Your Screen resolution is set at ". $screen_res;
?>
</BODY>
</HTML>
