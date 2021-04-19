<?php
	#
	# $Id: customize.php,v 1.3 2008-08-06 13:36:16 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/page_options.php'); # needed to validate page_size
	
	if (IN_MAINTENCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	GLOBAL $User;

	$errors          = 0;
	$AccountModified = 0;

if (IsSet($_REQUEST['submit'])) $submit = $_REQUEST['submit'];
$visitor = pg_escape_string($_COOKIE[USER_COOKIE_NAME]);

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
	header('Location: /login.php');  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (IsSet($submit)) {
	$Debug = 0;

	// process form

	$email         = pg_escape_string($_POST['email']);
	$Password      = $_POST['Password'];
	$Password1     = $_POST['Password1'];
	$Password2     = $_POST['Password2'];
	$numberofdays  = pg_escape_string($_POST['numberofdays']);
	$page_size     = pg_escape_string($_POST['page_size']);

	# this is a checkbox
	if (IsSet($_POST['set_focus_search'])) {
		$set_focus_search = 'true';
	} else {
		$set_focus_search = 'false';
	}

	if (!is_numeric($numberofdays) || $numberofdays < 0 || $numberofdays > 9) {
		$numberofdays = 9;
	}

	$PageOptions = new ItemsPerPage();
	if (!is_numeric($page_size) || !array_key_exists($page_size, $PageOptions->Choices)) {
		$page_size = DEFAULT_NUMBER_OF_COMMITS;
	}

	if ($Debug) {
		foreach ($HTTP_POST_VARS as $name => $value) {
			echo "$name = $value<br>\n";
		}
	}

	$OK = 1;

	$errors = '';

	if (!freshports_IsEmailValid($email)) {
		$errors .= 'That email address doesn\'t look right to me<BR>';
		$OK = 0;
	}
	
	# new passwords supplied, but not existing password
	if ($Password1 && $Password2 && !$Password) {
		$errors .= 'If changing your password, remember to supply your existing password first.<BR>';
		$OK = 0;
	}

	if ($Password1 != $Password2) {
		$errors .= 'The new password was not confirmed.  It must be entered twice.<BR>';
		$OK = 0;
	}

	# if no errors so far, and all three password fields are populated
	if ($OK && $Password && $Password1 && $Password2) {
		$result = getLoginDetails($db, LOGIN_QUERY, $User->name, $Password);
		# there must be only 1 row in there.
		if (pg_numrows($result) != 1) {
			$errors .= 'That is NOT your current password.<br>';
			$OK = 0;
		}
	}

	if ($OK) {
		// get the existing email in case we need to reset the bounce count
		$sql = "select email from users where cookie = '$visitor'";
		$result = pg_exec($db, $sql);
		if ($result) {
			$myrow = pg_fetch_array ($result, 0);

			$sql = "
UPDATE users
   SET email            = '$email',
       number_of_days   = $numberofdays,
       page_size        = $page_size,
       set_focus_search = $set_focus_search";

			// if they are changing the email, reset the bouncecount.
			if ($myrow["email"] != $email) {
				$sql .= ", emailbouncecount = 0 ";
			}

			if ($Password1 != '') {
				$sql .= ", password_hash = crypt('" . pg_escape_string($Password1) . "'";
				$sql .= ", gen_salt('" . PW_HASH_METHOD . "', " . PW_HASH_COST ."))";
			}

			$sql .= " where cookie = '$visitor'";

			if ($Debug) {
				echo '<pre>' . htmlentities($sql) . '</ore>';
			}

			$result = pg_exec($db, $sql);
			if ($result) {
				$AccountModified = 1;
			}
		}

		if ($AccountModified == 1) {
			if ($Debug) {
				echo "I would have taken you to '' now, but debugging is on<br>\n";
			} else {
				header("Location: /");
				exit;  /* Make sure that code below does not get executed when we redirect. */
			}
		} else {
			$errors .= 'Something went terribly wrong there.<br>';
			$errors .= $sql . "<br>\n";
			$errors .= pg_errormessage();
		}
	}
} else {

	$email            = $User->email;
	$numberofdays     = $User->number_of_days;
	$page_size        = $User->page_size;
	$set_focus_search = $User->set_focus_search;
}

#	echo '<br>the page size is ' . $page_size . ' : ' . $email;

	$Title = 'Customize User Account';
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
<<<<<<< HEAD
echo '<TABLE CELLPADDING="1" class="fullwidth bordeless accent">
<TR>
<TD>
<TABLE class="fullwidth borderless" CELLPADDING="1">
<TR class="accent"><TD><b><font color="#ffffff" size=+0>Access Code Failed!</font></b></TD>
=======
echo '<TABLE CELLPADDING="1" class="fullwidth bordeless" BGCOLOR="' . BACKGROUND_COLOUR . '">
<TR>
<TD>
<TABLE class="fullwidth borderless" CELLPADDING="1">
<TR BGCOLOR="' . BACKGROUND_COLOUR . '"><TD><b><font color="#ffffff" size=+0>Access Code Failed!</font></b></TD>
>>>>>>> 7d09eb11 (Move usage of border attribute on tables to CSS class)
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
if ($AccountModified) {
   echo "Your account details were successfully updated.";
} else {

echo '<TABLE CELLPADDING="1" class="fullwidth borderless accent">
<TR>
<TD VALIGN="top">
<TABLE class="fullwidth borderless" CELLPADDING="1">
<TR>
<TD class="accent" HEIGHT="29" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG>Customize</BIG></BIG></FONT></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>';

echo '<p>If you wish to change your password, first type your existing password, then your new password twice.  Otherwise, leave them all blank.</p><br>';
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

?>

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
