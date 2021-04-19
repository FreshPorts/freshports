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

	if (IN_MAINTENCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	GLOBAL $User;

	$origin        = '/';
	$errors        = 0;
	$PasswordReset = 0;
	
	# user does not know password. Has been sent a token.
	# Match that token against a user id, then let them reset the password

if (IsSet($_REQUEST['origin'])) $origin	= pg_escape_string( $_REQUEST['origin'] );
if (IsSet($_REQUEST['submit'])) $submit = pg_escape_string( $_REQUEST['submit'] );
if (IsSet($_REQUEST['token']))  $token  = pg_escape_string( $_REQUEST['token'] );

syslog(LOG_NOTICE, "Password reset page: loaded with: " . $token);

if ($origin == '/index.php' || $origin == '') {
	$origin = '/';
}

if (IsSet($submit)) {
  $Debug = 0;

  // process form

  $Password1 = pg_escape_string( $_POST['Password1'] );
  $Password2 = pg_escape_string( $_POST['Password2'] );

  if ($Debug) {
    foreach($HTTP_POST_VARS as $name => $value) {
      echo "$name = $value<br>\n";
    }
  }

  $OK = 1;

  $errors = '';

  if ( !$Password1 || ( $Password1 != $Password2 ) ) {
    $errors .= 'The password was not confirmed.  It must be entered twice.<BR>';
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
        echo "I would have taken you to '$origin' now, but debugging is on<br>\n";
      }
    } else {
      $errors .= 'Something went terribly wrong there.<br>';
      syslog(LOG_NOTICE, "Password reset page: Password reset went wrong " . $sql . ' ' . pg_errormessage());
    }
  }
}

#	echo '<br>the page size is ' . $page_size . ' : ' . $email;

        $Title = 'Reset password via token';
	freshports_Start($Title,
						$Title,
						'FreeBSD, index, applications, ports');
?>

<TABLE WIDTH="<? echo $TableWidth; ?>" class="borderless" ALIGN="center">
<TR><TD VALIGN="top" width="100%">
<TABLE class="fullwidth borderless">
  <TR>
    <TD height="20"><?php


if ($errors) {
echo '<TABLE CELLPADDING="1" class="fullwidth borderless" BGCOLOR="' . BACKGROUND_COLOUR . '">
<TR>
<TD>
<TABLE class="fullwidth borderless" CELLPADDING="1">
<TR BGCOLOR="' . BACKGROUND_COLOUR . '"><TD><b><font color="#ffffff" size=+0>Access Code Failed!</font></b></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>
  <TABLE class="fullwidth borderless" CELLPADDING="3">
  <TR VALIGN=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD width="100%">
  <p>Some errors have occurred which must be corrected before your login can be created.</p>';

echo $errors;

echo '<p>If you need help, please email postmaster@. </p>
 </TD>
 </TR>
 </TABLE>
</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>
<br>';
}

if ($PasswordReset) {
   echo 'SUCCESS! Your password has been updated.  Please <a href="/login.php">login</a>';

} else {

echo '<TABLE CELLPADDING="1" class="fullwidth borderless" BGCOLOR="' . BACKGROUND_COLOUR . '">
<TR>
<TD VALIGN="top">
<TABLE class="fullwidth borderless" CELLPADDING="1">
<TR>
<TD BGCOLOR="' . BACKGROUND_COLOUR . '" HEIGHT="29" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG>Reset password via token</BIG></BIG></FONT></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>';

echo '<p>Please enter your new password twice.</p><br>';
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

$Customize=1;
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/password-reset-via-token.php');

echo "</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>";
}

?>

<p>

</TD>
</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
