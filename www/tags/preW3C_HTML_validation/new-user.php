<?
	# $Id: new-user.php,v 1.1.2.14 2002-03-25 02:09:53 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

if ($submit) {

	// process form

	/*
	while (list($name, $value) = each($HTTP_POST_VARS)) {
		echo "$name = $value<br>\n";
	}
	*/


	$OK = 1;

	$errors = "";

	if ($UserLogin == '') {
		$errors .= "Please enter a user id.<BR>";
		$OK = 0;
	}

	if (!freshports_IsEmailValid($email)) {
		$errors .= "That email address doesn't look right to me<BR>";
		$OK = 0;
	}

	if ($Password1 != $Password2) {
		$errors .= "The password was not confirmed.  It must be entered twice.<BR>";
		$OK = 0;
	} else {
		if ($Password1 == '') {
			$errors .= 'A password must be supplied<BR>';
			$OK = 0;
		}
	}

	#
	# make sure we have valid values in this variable.
	# by default, they don't get notified.
	#

	if ($watchnotifyfrequency == $WatchNoticeFrequencyNever       || $watchnotifyfrequency == $WatchNoticeFrequencyWeekly  ||
	    $watchnotifyfrequency == $WatchNoticeFrequencyFortnightly || $watchnotifyfrequency == $WatchNoticeFrequencyMonthly) {
		# do nothing
	} else {
		$watchnotifyfrequency == $WatchNoticeFrequencyDaily;
	}

	$WatchNotice = new WatchNotice($db);
	$WatchNotice->FetchByFrequency($watchnotifyfrequency);
	if (!IsSet($WatchNotice->id)) {
		echo "<B>Sorry, I didn't find that watch notice frequency ('$watchnotifyfrequency').  Has the table been populated?</B><BR>";
		exit;
	}


	$UserCreated = 0;
	if ($OK) {
		$Cookie = UserToCookie($UserLogin);
//		echo "checking database\n";

		// test for existance of user id

		$sql = "select * from users where cookie = '$Cookie'";

		$result = pg_exec($db, $sql) or die('query failed');

		// create user id if not found
		if(!pg_numrows($result)) {
 //			echo "confirmed: user id is new\n";

			# no need to validate that value as it's not put directly into the db.
			if ($emailsitenotices_yn == "ON") {
				$emailsitenotices_yn_value = "Y";
			} else {
				$emailsitenotices_yn_value = "N";
			}

			$email     = addslashes($email);
			$UserLogin = addslashes($UserLogin);
			$Password1 = addslashes($Password1);

			$UserID = freshports_GetNextValue($Sequence_User_ID, $db);
			if (IsSet($UserID)) {			
				$sql = "insert into users (id, name, password, cookie, email, " . 
						"watch_notice_id, emailsitenotices_yn, type, ip_address) values (";
				$sql .= "$UserID, '$UserLogin', '$Password1', '$Cookie', '$email', " .
						"'$WatchNotice->id', '$emailsitenotices_yn_value', 'S', '$REMOTE_ADDR')";

				$errors .= "<br>sql=" . $sql;

				$result = pg_exec($db, $sql);
				if ($result) {
					$UserCreated = 1;

					# if the mail out fails, we aren't handling it properly here.
					# we will.  eventually.
					#
					freshports_UserSendToken($UserID, $db);
				} else {
					$errors .= "OUCH! I couldn't add you to the database\n";
					$OK = 0;
				}
			} else {
				$errors .= "OUCH! I couldn't assign you a new UserID\n";
				$OK = 0;
			}

	    } else {
			$errors .= 'That User ID is already in use.  Please select a different  User ID.<BR>';
    	}
	}

	if ($UserCreated) {
		header("Location: welcome.php?origin=" . $origin);  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}
} else {
	// not submit

	$watchnotifyfrequency = $WatchNoticeFrequencyDaily;

	// we can't do this if we are submitting because it overwrites the incoming values
	require( "./include/getvalues.php");
	$emailsitenotices_yn = "ON";
}

   freshports_Start("New User",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");
?>

<script>
<!--
function setfocus() { document.f.UserLogin.focus(); }
// -->
</script>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">
<script language="php">
if ($errors) {
echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>
<td>
<table width=100% border=0 cellpadding=1>
<tr bgcolor="#AD0040"><td><b><font color="#ffffff" size=+0>Access Code Failed!</font></b></td>
</tr>
<tr bgcolor="#ffffff">
<td>
  <table width=100% cellpadding=3 cellspacing=0 border=0>
  <tr valign=top>
   <td><img src="/images/warning.gif"></td>
   <td width=100%>
  <p>Some errors have occurred which must be corrected before your login can be created.</p>';

/*
  while (list($name, $value) = each($HTTP_POST_VARS)) {
    echo "$name = $value<br>\n";
  }
*/
echo $errors;

echo '<p>If you need help, please post a message on the forum. </p>
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

if (!$submit && !$errors) {
  // provide default values for an empy form.
  require( "./include/getvalues.php");
}

</script>

<table cellpadding="1" cellspacing="0" border="0" bordercolor="#A2A2A2"
            bordercolordark="#A2A2A2" bordercolorlight="#A2A2A2" width="100%" cellpadding="5">
      <tr>
		<? freshports_PageBannerText("New User Details"); ?>
      </tr>
      <tr>
        <td>

<P><BIG><BIG>NOTE:</BIG>You must supply a valid email address.<BR>Instructions to enable your account 
will be emailed to you at that address.</BIG></P>
<P>Your browser must allow cookies for the login to work.</P>
<P>&nbsp;</P>

<? include("./include/new-user.php"); ?>

    </td>
  </tr>
</form>
</table>
</td>
  <td valign="top" width="*">
   <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
</a>
<? include("./include/footer.php") ?>
</body>
</html>
