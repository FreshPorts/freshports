<?php
	#
	# $Id: new-user.php,v 1.1.2.10 2003-03-06 14:20:45 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	$origin = $_REQUEST["origin"];
?>

<form action="<?php echo $_SERVER["PHP_SELF"] . "?origin=" . $origin ?>" method="POST" NAME=f>
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
               <INPUT SIZE="35" NAME="email" VALUE="<?echo $email ?>">

Number of Days to show in side-bar: 

<SELECT NAME="numberofdays" size="1">
    <OPTION <? if ($numberofdays == "0") echo "selected " ?> VALUE="0">0</OPTION>
    <OPTION <? if ($numberofdays == "1") echo "selected " ?> VALUE="1">1</OPTION>
    <OPTION <? if ($numberofdays == "2") echo "selected " ?> VALUE="2">2</OPTION>
    <OPTION <? if ($numberofdays == "3") echo "selected " ?> VALUE="3">3</OPTION>
    <OPTION <? if ($numberofdays == "4") echo "selected " ?> VALUE="4">4</OPTION>
    <OPTION <? if ($numberofdays == "5") echo "selected " ?> VALUE="5">5</OPTION>
    <OPTION <? if ($numberofdays == "6") echo "selected " ?> VALUE="6">6</OPTION>
    <OPTION <? if ($numberofdays == "7") echo "selected " ?> VALUE="7">7</OPTION>
    <OPTION <? if ($numberofdays == "8") echo "selected " ?> VALUE="8">8</OPTION>
    <OPTION <? if ($numberofdays == "9") echo "selected " ?> VALUE="9">9</OPTION>
</SELECT>
<br><br><BR>

Number of ports to display per page per category:
<SELECT NAME="page_size" size="1">
    <OPTION <? if ($page_size == 25)  echo "selected " ?> VALUE="25">25</OPTION>
    <OPTION <? if ($page_size == 50)  echo "selected " ?> VALUE="50">50</OPTION>
    <OPTION <? if ($page_size == 100) echo "selected " ?> VALUE="100">100</OPTION>
    <OPTION <? if ($page_size == 150) echo "selected " ?> VALUE="150">150</OPTION>
    <OPTION <? if ($page_size == 250) echo "selected " ?> VALUE="250">250</OPTION>
</SELECT>
<br><br><BR>
            <INPUT TYPE="submit" VALUE="<? if ($Customize) { echo "update";} else { echo "create";} ?> account" NAME="submit">
            <INPUT TYPE="reset"  VALUE="reset form">
            </TD>
          </TR>
</TABLE>
</FORM>

For your reporting needs, please visit <A HREF="/report-subscriptions.php">Report Subscriptions</A>.
