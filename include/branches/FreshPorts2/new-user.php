<?
	# $Id: new-user.php,v 1.1.2.4 2002-04-20 02:39:36 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

?>

<form action="<?php echo $PHP_SELF . "?origin=" . $origin ?>" method="POST" NAME=f>
<TABLE width="*" border="0" cellpadding="1">
          <TR>
            <TD VALIGN="top">
<? if (!$Customize) { ?>
              <INPUT TYPE="hidden" NAME="ADD" VALUE="1">
              User ID:<br>
              <INPUT SIZE="15" NAME="UserLogin" VALUE="<? echo $UserLogin ?>"><br><br>
<? } ?>
               Password:<br>
               <INPUT TYPE="PASSWORD" NAME="Password1" VALUE="<?echo $Password1 ?>" size="20"><br><br>
               Confirm Password:<br>
               <INPUT TYPE="PASSWORD" NAME="Password2" VALUE="<?echo $Password2 ?>" size="20">
            </TD>
            <TD VALIGN="top">
               email address (required):<br>
               <INPUT SIZE="35" NAME="email" VALUE="<?echo $email ?>"><br><br>
               <INPUT TYPE="checkbox" NAME="emailsitenotices_yn" VALUE="ON" <? if ($emailsitenotices_yn == "ON") {echo " checked";}?>>Put me on the announcement mailing list (low volume)<br>
<br>
We can send you an email when something on your watch list changes.<br>
Send me, at most, one message per: <SELECT NAME="watchnotifyfrequency" size="1">
    <OPTION <? if ($watchnotifyfrequency == "Z") echo "selected " ?> VALUE="Z">Don't notify me</OPTION>
    <OPTION <? if ($watchnotifyfrequency == "D") echo "selected " ?> VALUE="D">Day</OPTION>
    <OPTION <? if ($watchnotifyfrequency == "W") echo "selected " ?> VALUE="W">Week (on Tuesdays)</OPTION>
    <OPTION <? if ($watchnotifyfrequency == "F") echo "selected " ?> VALUE="F">Fortnight (9th and 23rd)</OPTION>
    <OPTION <? if ($watchnotifyfrequency == "M") echo "selected " ?> VALUE="M">Month (23rd)</OPTION>  </SELECT>
<br><br>
            <INPUT TYPE="submit" VALUE="<? if ($Customize) { echo "update";} else { echo "create";} ?> account" NAME="submit">
            <INPUT TYPE="reset"  VALUE="reset form">
            </TD>
          </TR>
</TABLE>
</FORM>
