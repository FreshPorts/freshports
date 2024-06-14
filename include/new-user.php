<?php
	#
	# $Id: new-user.php,v 1.3 2011-08-21 15:10:59 dan Exp $
	#
	# Copyright (c) 1998-2022 DVL Software Limited
	#
	
	
	# These are customization features (see www/customize.php) and are needed
	# by include/new-user.php - but with a new user, they must be set here.
	$set_focus_search = $set_focus_search ?? false;
	$page_size        = $page_size        ?? 0;
	$numberofdays     = $numberofdays     ?? 0;


?>

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<table class="borderless">
          <tr>
            <td>
<?php if (IsSet($Customize)) { ?>
              <label>Current password:<br>
              <INPUT TYPE="PASSWORD" NAME="Password" VALUE="<?php if (IsSet($Password)) echo htmlentities($Password) ?>" size="20"></label><br><br>

<?php } else { ?>

              <INPUT TYPE="hidden" NAME="ADD" VALUE="1">
              <label>User ID:<br>
              <INPUT SIZE="15" NAME="UserLogin" VALUE="<?php if (IsSet($UserLogin)) echo htmlentities($UserLogin) ?>" autofocus=""></label><br><br>
<?php } ?>
              <label>New password:<br>
              <INPUT TYPE="PASSWORD" NAME="Password1" VALUE="<?php if (IsSet($Password1)) echo htmlentities($Password1) ?>" size="20"></label><br><br>
              <label>New password again:<br>
              <INPUT TYPE="PASSWORD" NAME="Password2" VALUE="<?php if (IsSet($Password2)) echo htmlentities($Password2) ?>" size="20"></label>
<br>
<br>
Number of Days to show in side-bar:

<SELECT NAME="numberofdays" size="1">
    <OPTION <?php if ($numberofdays == "0") echo "selected " ?> VALUE="0">0</OPTION>
    <OPTION <?php if ($numberofdays == "1") echo "selected " ?> VALUE="1">1</OPTION>
    <OPTION <?php if ($numberofdays == "2") echo "selected " ?> VALUE="2">2</OPTION>
    <OPTION <?php if ($numberofdays == "3") echo "selected " ?> VALUE="3">3</OPTION>
    <OPTION <?php if ($numberofdays == "4") echo "selected " ?> VALUE="4">4</OPTION>
    <OPTION <?php if ($numberofdays == "5") echo "selected " ?> VALUE="5">5</OPTION>
    <OPTION <?php if ($numberofdays == "6") echo "selected " ?> VALUE="6">6</OPTION>
    <OPTION <?php if ($numberofdays == "7") echo "selected " ?> VALUE="7">7</OPTION>
    <OPTION <?php if ($numberofdays == "8") echo "selected " ?> VALUE="8">8</OPTION>
    <OPTION <?php if ($numberofdays == "9") echo "selected " ?> VALUE="9">9</OPTION>
</SELECT>

<br>
<br>
<label>
Set focus to search box: <input type="checkbox" id="set_focus_search" name="set_focus_search" value="set_focus_search"<?php if ($set_focus_search) echo ' checked'; ?>>
</label>

<br><br>
Number of results to display per page (e.g commits per page):
<?php
	  require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/page_options.php');
	  $PageOptions = new ItemsPerPage();
	  echo $PageOptions->DDLB_Choices('page_size', $page_size);

?>
            </td>
            <td valign="top">
              <label>Email Address (required):<br>
              <INPUT type="email" SIZE="35" NAME="email" required VALUE="<?php if (IsSet($email)) echo htmlentities($email) ?>"></label>


<br><br><br>

</td>

<?php

# include this CAPTCHA only for new registrations

if ( $_SERVER['SCRIPT_NAME'] == '/new-user.php' )
{
?>

<tr><td class="captcha">CAPTCHA:<br>
  (antispam code, type the three black symbols)<br>
  <table><tr><td><img src="/images/captcha/captcha.php" alt="captcha image"></td><td><input type="text" name="captcha" size="3" maxlength="3"></td></tr></table>
</td><td></td></tr>
<?php
}
?>    

<tr>
<td colspan="2">
<br><br>
            <INPUT TYPE="submit" VALUE="<?php if (IsSet($Customize)) { echo "update";} else { echo "create";} ?> account" NAME="submit">
            <INPUT TYPE="reset"  VALUE="reset form">
            </td>
          </tr>
    </table>
</FORM>

<hr>

<?php
if ( $_SERVER['SCRIPT_NAME'] != '/new-user.php' )
{
?>
<p>For your reporting needs, please visit <a href="/report-subscriptions.php">Report Subscriptions</a>.</p>
<h3><a href="/delete-account.php">Delete my account</a></h3>
<?php
}
