<?php
	#
	# $Id: new-user.php,v 1.3 2011-08-21 15:10:59 dan Exp $
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
<? if (!IsSet($Customize)) { ?>
              <INPUT TYPE="hidden" NAME="ADD" VALUE="1">
              User ID:<br>
              <INPUT SIZE="15" NAME="UserLogin" VALUE="<? if (IsSet($UserLogin)) echo htmlentities($UserLogin) ?>"><br><br>
<? } ?>
               Password:<br>
               <INPUT TYPE="PASSWORD" NAME="Password1" VALUE="<? if (IsSet($Password1)) echo htmlentities($Password1) ?>" size="20"><br><br>
               Confirm Password:<br>
               <INPUT TYPE="PASSWORD" NAME="Password2" VALUE="<? if (IsSet($Password2)) echo htmlentities($Password2) ?>" size="20">
            </TD>
            <TD VALIGN="top">
               email address (required):<br>
               <INPUT SIZE="35" NAME="email" VALUE="<? if (IsSet($email)) echo htmlentities($email) ?>">

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

Number of results to display per page (e.g commits per page):
<?php
	  require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/page_options.php');
	  $PageOptions = new ItemsPerPage();
	  echo $PageOptions->DDLB_Choices('page_size', $page_size);

?>
<br><br><BR>
            <INPUT TYPE="submit" VALUE="<? if (IsSet($Customize)) { echo "update";} else { echo "create";} ?> account" NAME="submit">
            <INPUT TYPE="reset"  VALUE="reset form">
            </TD>
          </TR>
<?php

# include this CAPTCHA only for new registrations

if ( $_SERVER['SCRIPT_NAME'] == '/new-user.php' )
{
?>

<tr><td align="center">CAPTCHA:<br>
  (antispam code, 3 black symbols)<br>
  <table><tr><td><img src="/images/captcha/captcha.php" alt="captcha image"></td><td><input type="text" name="captcha" size="3" maxlength="3"></td></tr></table>
</td></tr>
<?php
}
?>    
    </TABLE>
</FORM>

<p>For your reporting needs, please visit <A HREF="/report-subscriptions.php">Report Subscriptions</A></p>.

