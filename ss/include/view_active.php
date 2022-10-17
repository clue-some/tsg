<?php
	if (! defined ('TBL_ACTIVE_USERS'))
	{
	  die ("Error processing page");
	}
	
	$q = "SELECT ku.username FROM ".TBL_ACTIVE_USERS." AS au"
		." JOIN ".TBL_KNOWN_USER." AS ku"
		." ON au.active_id = ku.user_id"
		." ORDER BY au.timestamp DESC, ku.username";
	$result = $database->query ($q);
	/* Error occurred, return given name by default */
	$num_rows = mysql_numrows ($result);
	if (! $result || ($num_rows < 0))
	{
	   echo "Error displaying info";
	}
	else if ($num_rows > 0)
	{
	   /* Display active users, with link to their info */
	   echo "<table align=\"left\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
	   echo "<tr><td><font size=\"2\">\n";
	   for ($i = 0; $i < $num_rows; $i++)
	   {
		  $uname = mysql_result ($result, $i, "username");
	
		  echo "<a href=\"userinfo.php?user=$uname\">$uname</a> / ";
	   }
	   echo "</font></td></tr></table><br>\n";
	}
?>