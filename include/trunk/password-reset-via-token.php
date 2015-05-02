<?php
	#
	# $Id: password-reset-via-token.php,v 1.1 2010-09-17 14:44:55 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	if (IsSet($_REQUEST["origin"])) $origin = $_REQUEST["origin"];
	GLOBAL $numberofdays;
	GLOBAL $page_size;
?>

<form action="<?php echo $_SERVER["PHP_SELF"] . "?origin=" . htmlentities($origin) ?>" method="POST" NAME=f>
<TABLE width="*" border="0" cellpadding="1">
          <TR>
            <TD VALIGN="top">
              <INPUT TYPE="hidden" NAME="token" VALUE="<?php echo $token ?>">
               Password:<br>
               <INPUT TYPE="PASSWORD" NAME="Password1" VALUE="<?php if (IsSet($Password1)) echo htmlentities($Password1) ?>" size="20"><br><br>
               Confirm Password:<br>
               <INPUT TYPE="PASSWORD" NAME="Password2" VALUE="<?php if (IsSet($Password2)) echo htmlentities($Password2) ?>" size="20">
               <br><br>
            <INPUT TYPE="submit" VALUE="Set password" NAME="submit">
            </TD>
          </TR>
</TABLE>
</FORM>

