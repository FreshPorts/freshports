<?php
	#
	# $Id: customize.php,v 1.3 2008-08-06 13:36:16 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');

	GLOBAL $User;

	$origin          = '/';
	$errors          = 0;
	$AccountModified = 0;

if (IsSet($_REQUEST['origin'])) $origin	= $_REQUEST['origin'];
if (IsSet($_REQUEST['submit'])) $submit = $_REQUEST['submit'];
$visitor	= pg_escape_string($_COOKIE['visitor']);

if ($origin == '/index.php' || $origin == '') {
	$origin = '/';
}

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
	header('Location: /login.php?origin=' . $_SERVER['PHP_SELF']);  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (IsSet($submit)) {
	$Debug = 0;

// process form

	$email				= pg_escape_string($_POST['email']);
	$Password1			= $_POST['Password1'];
	$Password2			= $_POST['Password2'];
	$numberofdays			= pg_escape_string($_POST['numberofdays']);
	$page_size			= pg_escape_string($_POST['page_size']);

	if (!is_numeric($numberofdays) || $numberofdays < 0 || $numberofdays > 9) {
		$numberofdays = 9;
	}

	if ($Debug) {
		while (list($name, $value) = each($HTTP_POST_VARS)) {
			echo "$name = $value<br>\n";
		}
	}

	$OK = 1;

	$errors = '';

	if (!freshports_IsEmailValid($email)) {
		$errors .= 'That email address doesn\'t look right to me<BR>';
		$OK = 0;
	}

	if ($Password1 != $Password2) {
		$errors .= 'The password was not confirmed.  It must be entered twice.<BR>';
		$OK = 0;
	}

	if ($OK) {
		// get the existing email in case we need to reset the bounce count
		$sql = "select email from users where cookie = '$visitor'";
		$result = pg_exec($db, $sql);
		if ($result) {
			$myrow = pg_fetch_array ($result, 0);

			$sql = "
UPDATE users
   SET email          = '$email',
       number_of_days = $numberofdays,
       page_size      = $page_size";

			// if they are changing the email, reset the bouncecount.
			if ($myrow["email"] != $email) {
				$sql .= ", emailbouncecount = 0 ";
			}

			if ($Password1 != '') {
				$sql .= ", password_hash = crypt('" . 	pg_escape_string($Password1) . "', gen_salt('md5'))";
			}

			$sql .= " where cookie = '$visitor'";

			if ($Debug) {
#			phpinfo();
				echo '<pre>' . htmlentities($sql) . '</ore>';
			}

			$result = pg_exec($db, $sql);
			if ($result) {
				$AccountModified = 1;
			}
		}

		if ($AccountModified == 1) {
			if ($Debug) {
				echo "I would have taken you to '$origin' now, but debugging is on<br>\n";
			} else {
				header("Location: $origin");
				exit;  /* Make sure that code below does not get executed when we redirect. */
			}
		} else {
			$errors .= 'Something went terribly wrong there.<br>';
			$errors .= $sql . "<br>\n";
			$errors .= pg_errormessage();
		}
	}
} else {

	$email			= $User->email;
	$numberofdays	= $User->number_of_days;
	$page_size		= $User->page_size;
}

#	echo '<br>the page size is ' . $page_size . ' : ' . $email;

	freshports_Start('Customize User Account',
						'freshports - new ports, applications',
						'FreeBSD, index, applications, ports');
?>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD VALIGN="top" width="100%">
<TABLE width="100%" border="0">
  <TR>
    <TD height="20"><script language="php">


if ($errors) {
echo '<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="' . BACKGROUND_COLOUR . '" width="100%">
<TR>
<TD>
<TABLE width="100%" BORDER="0" CELLPADDING="1">
<TR BGCOLOR="' . BACKGROUND_COLOUR . '"><TD><b><font color="#ffffff" size=+0>Access Code Failed!</font></b></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>
  <TABLE width="100%" CELLPADDING="3" BORDER="0">
  <TR VALIGN=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD width="100%">
  <p>Some errors have occurred which must be corrected before your login can be created.</p>';

echo $errors;

echo '<p>If you need help, please post a message on the forum. </p>
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
if ($AccountModified) {
   echo "Your account details were successfully updated.";
} else {

echo '<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="' . BACKGROUND_COLOUR . '" WIDTH="100%">
<TR>
<TD VALIGN="top">
<TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
<TR>
<TD BGCOLOR="' . BACKGROUND_COLOUR . '" HEIGHT="29" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG>Customize</BIG></BIG></FONT></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>';

echo '<p>If you wish to change your password, supply your new password twice.  Otherwise, leave it blank.</p><br>';
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

$Customize=1;
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/new-user.php');

echo "</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>";
}

</script>

<p>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/spam-filter-information.php'); ?>

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
