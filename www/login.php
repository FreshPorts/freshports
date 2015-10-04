<?php
	#
	# $Id: login.php,v 1.4 2010-09-17 14:37:16 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user.php');

$Debug = 0;

$LoginFailed = 0;
$error       = '';
$origin      = '/';

if (IsSet($_GET['origin'])) $origin = $_GET['origin'];
if ($origin == '/index.php' || $origin == '') {
	$origin = '/';
}


if ($Debug) echo "origin = '" . rawurlencode($origin) . "'<BR>\n";
if ($Debug) phpinfo();

$origin = rawurlencode($origin);

if (IsSet($_REQUEST['LOGIN']) && $_REQUEST['UserID']) {
   // process form

   if ($Debug) {
      while (list($name, $value) = each($HTTP_POST_VARS)) {
         echo "$name = $value<BR>\n";
      }
   }

   $OK = 1;

   $UserID    = $_REQUEST['UserID'];
   $Password  = $_REQUEST['Password'];

   // test for existance of user id

   $sql = "select * from users where lower(name) = lower('" . pg_escape_string($UserID) . "')".
	  " and password_hash = crypt('" . pg_escape_string($Password) . "', password_hash) ";

   if ($Debug) {
      echo '<pre>' . htmlentities($sql) . '<pre>';
   }

   $result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());

	if (!pg_numrows($result)) {
		$LoginFailed = 1;
	} else {
		$row    = pg_fetch_array($result,0);
		$status = $row["status"];

		if ($Debug) echo "\$status = $status\n<BR>";

		GLOBAL $UserStatusActive;
		GLOBAL $UserStatusDisabled;
		GLOBAL $UserStatusUnconfirmed;

		if ($Debug) echo "\$UserStatusActive = '$UserStatusActive'\n<BR>";

		if ($status == $UserStatusActive) {
			if ($Debug) {
				echo "well, debug was on, so I would have taken you to '$origin'<BR>\n";
			} else {
				$user = new User($db);
				$Cookie = $user->createUserToken();
				# we should use $user to save this...

				$sql = "UPDATE users SET cookie = '" . pg_escape_string($Cookie) . "' WHERE id = " . pg_escape_string($row['id']);
				# if we were doing this in a user object, we could retry when there was a cookie collision and we get a unique index error
				$result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());

				SetCookie("visitor", $Cookie, time() + 60*60*24*120, '/');
				// Redirect browser to PHP web site
				if ($origin == "/index.php" || $origin == "") {
					$origin = "/";
				}
				header("Location: " . rawurldecode($origin));
				// Make sure that code below does not get executed when we redirect.
				exit;
			}
		} else {
			if ($status == $UserStatusDisabled) {
				$error .= "Your account has been disabled.  Please contact $ProblemSolverEmailAddress.";
			} else {
				if ($status == $UserStatusUnconfirmed) {
					$error .= 'Your account needs to be enabled by following the directions in the email we have sent to you.' . "<BR>\n";
					$error .= 'To have your activation details resent to the email address you supplied, click on the resend button' . "<BR>\n";
					$error .= '<form action="' . $_SERVER["PHP_SELF"] . "?origin=$origin" . ' method="POST">' . "\n";
					$error .= '<input type="hidden" name="user" value="' . htmlentities($UserID) . '">' . "\n";
					$error .= '<input TYPE="submit" VALUE="Resend" name=resend>' . "\n";
					$error .= '</form>' . "\n";
				} else {
					$error .= "I have no idea what your account status is.";
				}
			}
		
		}
	}
}

if (IsSet($_GET["resend"])) {
	$User = pg_escape_string($_GET["user"]);

	// get user id for that name

	$sql = "select id from users where lower(name) = lower('" . pg_escape_string($User) . "')";

	if ($Debug) {
		echo "$sql<BR>\n";
	}

	$result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());

	if (pg_numrows($result)) {
		$row    = pg_fetch_array($result,0);
		$ID		= $row["id"];
		if (freshports_UserSendToken($ID, $db)) {
			$error .= 'You should soon receive an email at the mail address you supplied. It will contain instructions to enable your account.';
		} else {
			$error .= 'I\'m sorry but I couldn\'t send your token.  Please contact ' . $ProblemSolverEmailAddress . '.';
		}
	} else {
		$error .= "Hmmm, I know nothing about you.  That can't be right.  Please contact $ProblemSolverEmailAddress.";
	}
}
?>


<?php
	$OnLoad = 'setfocus()';
   freshports_Start('Login',
               'freshports - new ports, applications',
               'FreeBSD, index, applications, ports');

?>

<script language="JavaScript" type="text/javascript">
<!--
function setfocus() { document.l.UserID.focus(); }
// -->
</script>

<?php echo freshports_MainTable(); ?>
 <TR>
    <TD VALIGN="top" WIDTH="100%">
<?
if ($LoginFailed) {
?>

<?php echo freshports_ErrorContentTable(); ?>

<TR><TD VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
	<? echo freshports_PageBannerText("Login Failed!") ?>
</TR>
<TR BGCOLOR="#ffffff">
<TD>
  <TABLE WIDTH="100%" CELLPADDING=0 CELLSPACING=0 BORDER=0>
  <TR valign=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD WIDTH="100%">
  <p>The User ID and password you supplied could not be used to login.	This could be for one of the following reasons:</p>
 <ul>
 <li>The login id is incorrect
 <li>The password is incorrect
 <li>Both of the above
 </ul>
 <p>If you need help, please ask in the forum. </p>
 </TD>
 </TR>
 </TABLE>
</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>
<BR>
<?
}

if ($error) {
?>
<?php echo freshports_ErrorContentTable(); ?>
<TR><TD VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
    <? echo freshports_PageBannerText("NOTICE"); ?>
</TR>

<TR BGCOLOR="#ffffff">
<TD>
  <TABLE WIDTH="100%" CELLPADDING=0 BORDER=0>
  <TR valign=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD WIDTH="100%">
<? echo $error ?>
 </TD>
 </TR>
 </TABLE>
</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>
<BR>
<?
}




echo '<TABLE WIDTH="100%" BORDER="1" CELLPADDING="1" CELLSPACING="0" BGCOLOR="' . BACKGROUND_COLOUR . '">';

echo '<TR BGCOLOR="' . BACKGROUND_COLOUR . '">';

echo freshports_PageBannerText("Login");
echo '</TR>';

echo '<TR><TD BGCOLOR="#ffffff">';
include ($_SERVER['DOCUMENT_ROOT'] . "/../include/login.php");

echo "Your browser must allow cookies for this login to work.";

echo "</TD>";
echo"
</TR>
</TABLE>
";

#echo '<BR><A HREF="forgotten-password.php">Forgotten your password?</a>';

?>
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
