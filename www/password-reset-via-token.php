<?php
	#
	# $Id: password-reset-via-token.php,v 1.3 2010-09-21 11:08:10 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');

	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	GLOBAL $User;

	$errors        = 0;
	$PasswordReset = 0;
	
	# user does not know password. Has been sent a token.
	# Match that token against a user id, then let them reset the password

if (IsSet($_REQUEST['submit'])) $submit = pg_escape_string($db,  $_REQUEST['submit'] );
if (IsSet($_REQUEST['token']))  $token  = pg_escape_string($db,  $_REQUEST['token'] );

syslog(LOG_NOTICE, "Password reset page: loaded with: " . $token);

if (IsSet($submit)) {
  $Debug = 0;

  // process form

  $Password1 = pg_escape_string($db,  $_POST['Password1'] );
  $Password2 = pg_escape_string($db,  $_POST['Password2'] );

  if ($Debug) {
    foreach($_REQUEST as $name => $value) {
      echo "$name = $value<br>\n";
    }
  }

  $OK = 1;

  $errors = '';

  if ( !$Password1 || ( $Password1 != $Password2 ) ) {
    $errors .= 'The password was not confirmed.  It must be entered twice.<br>';
    syslog(LOG_NOTICE, "Password reset page: password not confirmed for " . $token);
    $OK = 0;
  }

  if ($OK) {
    $sql = "SELECT reset_password_token('". $Password1 . "', '" . $token . "') AS rowcount";
    if ($Debug) {
      echo $sql;
    }

    $result = pg_exec($db, $sql);
    if ($result) {
      $myrow = pg_fetch_array ($result);
      if ($myrow['rowcount'] == 1) {
          syslog(LOG_NOTICE, "Password reset page: Token deleted after reset: " . $token);
          $PasswordReset = 1;
      } else {
          syslog(LOG_NOTICE, "Password reset page: Token not deleted after reset: " . $token);
          $errors = "It seems that token is no longer valid.<br>";
      }
    }

    if ($PasswordReset == 1) {
      if ($Debug) {
        echo "I would have taken you to / now, but debugging is on<br>\n";
      }
    } else {
      $errors .= 'Something went terribly wrong there.<br>';
      syslog(LOG_NOTICE, "Password reset page: Password reset went wrong " . $sql . ' ' . pg_last_error($db));
    }
  }
}

#	echo '<br>the page size is ' . $page_size . ' : ' . $email;

        $Title = 'Reset password via token';
	freshports_Start($Title,
						$Title,
						'FreeBSD, index, applications, ports');
?>

<table class="fullwidth borderless" ALIGN="center">
<tr><td class="content">
<table class="fullwidth borderless">
  <tr>
    <td height="20"><?php


if ($errors) {
echo '<table class="fullwidth borderless">
<tr>
<td>
<table class="fullwidth borderless">
<tr class="accent"><td><b>Access Code Failed!</b></td>
</tr>
<tr>
<td>
  <table class="fullwidth borderless" CELLPADDING="3">
  <tr VALIGN=top>
   <td><img src="/images/warning.gif"></td>
   <td width="100%">
  <p>Some errors have occurred which must be corrected before your login can be created.</p>';

echo $errors;

echo '<p>If you need help, please email postmaster@. </p>
 </td>
 </tr>
 </table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br>';
}

if ($PasswordReset) {
   echo 'SUCCESS! Your password has been updated.  Please <a href="/login.php">login</a>';

} else {

echo '<table class="fullwidth borderless">
<tr>
<td VALIGN="top">
<table class="fullwidth borderless">
<tr>
<td class="accent"><BIG>Reset password via token</BIG></td>
</tr>
<tr>
<td>';

echo '<p>Please enter your new password twice.</p><br>';
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

$Customize=1;
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/password-reset-via-token.php');

echo "</td>
</tr>
</table>
</td>
</tr>
</table>";
}

?>

<p>

</td>
</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
