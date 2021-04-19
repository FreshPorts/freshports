<?php
	#
	# $Id: login.php,v 1.4 2010-09-17 14:37:16 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');

   require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user.php');

if (defined('NO_LOGIN')) {
  ob_start();
  header( 'Location: /' );
  ob_end_flush();
  exit;
}

$Debug = 0;

$LoginFailed = 0;
$error       = '';

if ($Debug) phpinfo();

if (IsSet($_REQUEST['LOGIN']) && $_REQUEST['UserID']) {
   // process form

   if ($Debug) {
      foreach ($HTTP_POST_VARS as $name => $value) {
         echo "$name = $value<BR>\n";
      }
   }

   $OK = 1;

   $UserID    = $_REQUEST['UserID'];
   $Password  = $_REQUEST['Password'];

   // test for existance of user id
   $result = getLoginDetails($db, LOGIN_QUERY, $UserID, $Password);
	if (!pg_numrows($result)) {
		$LoginFailed = 1;
	} else {
		$row    = pg_fetch_array($result,0);
		$status = $row["status"];
		$insecure_hash = $row["insecure_hash"];

		if ($Debug) echo "\$status = $status\n<BR>\$insecure_hash = $insecure_hash\n<BR>";

		// now that we have the correct password, upgrade from the insecure hash if required
		if ($insecure_hash === 't' && PW_HASH_METHOD === 'bf') {
			$sql = 'UPDATE users SET password_hash = crypt($2, gen_salt($3, $4)) WHERE lower(name) = lower($1)';
			if ($Debug || 0) {
				echo '<pre>' . htmlentities($sql) . '<pre>';
			}

			$result = pg_prepare($db, HASH_UPDATE_QUERY, $sql) or die('query failed ' . pg_errormessage());
			if ($result) {
				$result = pg_execute($db, HASH_UPDATE_QUERY, array($UserID, $Password, PW_HASH_METHOD, PW_HASH_COST)) or die('query failed ' . pg_errormessage());
			}
		}

		GLOBAL $UserStatusActive;
		GLOBAL $UserStatusDisabled;
		GLOBAL $UserStatusUnconfirmed;

		if ($Debug) echo "\$UserStatusActive = '$UserStatusActive'\n<BR>";

		if ($status == $UserStatusActive) {
			if ($Debug) {
				echo "well, debug was on, so I would have taken you to '/'<BR>\n";
				echo "Cookie = $Cookie<BR>\n";
			} else {
				$user = new User($db);
				$Cookie = $user->createUserToken();
				# we should use $user to save this...

				$sql = "UPDATE users SET cookie = '" . pg_escape_string($Cookie) . "' WHERE id = " . pg_escape_string($row['id']);
				# if we were doing this in a user object, we could retry when there was a cookie collision and we get a unique index error
				$result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());

				SetCookie(USER_COOKIE_NAME, $Cookie, array(
					'expires' => time() + 60*60*24*120,
					'path' => '/',
					'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
					'httponly' => TRUE,
					// it's probably common for users to navigate from other sites like portscout
					// we want them to still be logged in if that's the case
					'samesite' => 'Lax',
				));

				header("Location: /");
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
					$error .= '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
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
	$Title = 'Login';
	freshports_Start($Title,
               $Title,
               'FreeBSD, index, applications, ports');

?>

<?php echo freshports_MainTable(); ?>
 <TR>
    <TD VALIGN="top" WIDTH="100%">
<?
if ($LoginFailed) {
?>

<?php echo freshports_ErrorContentTable(); ?>

<TR><TD VALIGN=TOP>
<TABLE class="fullwidth">
<TR>
	<? echo freshports_PageBannerText("Login Failed!") ?>
</TR>
<TR BGCOLOR="#ffffff">
<TD>
  <TABLE class="fullwidth borderless" CELLPADDING="0">
  <TR valign=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD WIDTH="100%">
  <p>The User ID and password you supplied could not be used to login.	This could be for one of the following reasons:</p>
 <ul>
 <li>The login id is incorrect
 <li>The password is incorrect
 <li>Both of the above
 </ul>
 <p>If you need help, please email postmaster@. </p>
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
<TABLE class="fullwidth">
<TR>
    <? echo freshports_PageBannerText("NOTICE"); ?>
</TR>

<TR BGCOLOR="#ffffff">
<TD>
  <TABLE class="fullwidth borderless" CELLPADDING=0>
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




echo '<TABLE class="fullwidth bordered accent" CELLPADDING="1">';

echo '<TR class="accent">';

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
