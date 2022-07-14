<?php
	#
	# $Id: new-user.php,v 1.6 2011-08-21 15:20:25 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	# for captcha
	session_start();

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');

	if (IN_MAINTENCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	if (defined('NO_LOGIN')) {
		ob_start();
		header( 'Location: /' );
		ob_end_flush();
		exit;
	}

	if (IsSet($_REQUEST['submit'])) $submit = $_REQUEST['submit'];

	$errors = '';

if (IsSet($submit)) {

	// process form

	/*
	while (list($name, $value) = each($_REQUEST)) {
		echo "$name = $value<BR>\n";
	}
	*/


	$OK = 1;

	$errors = "";

	$UserLogin    = $_REQUEST["UserLogin"];
	$email        = $_REQUEST["email"];
	$Password1    = $_REQUEST["Password1"];
	$Password2    = $_REQUEST["Password2"];
	$numberofdays = $_REQUEST["numberofdays"];
	
	if ($UserLogin == '') {
		$errors .= "Please enter a user id.<BR>";
		$OK = 0;
	}

	if ($UserLogin != trim($UserLogin, "\t\n\r\x0B")) {
		syslog(LOG_ERR, 'FreshPorts (odd UserLogin): '. $UserLogin);
		$errors .= "Please, just use plain text for your user id.  This event has been logged.<br>";
		$OK = 0;
	}

	if (!freshports_IsEmailValid($email)) {
		$errors .= "That email address doesn't look right to me<BR>";
		$OK = 0;
	}

	if ($email != trim($email, "\t\n\r\x0B")) {
		syslog(LOG_ERR, 'FreshPorts (odd email): '. $email);
		$errors .= "Please, just use plain text for your email.  This event has been logged.<br>";
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

	if (is_numeric($numberofdays)) {
		if ($numberofdays < 0 || $numberofdays > 9) {
			$numberofdays = 0;
		}
	} else {
		$numberofdays = 0;
	}

	if ( isset( $_POST["captcha"] ) )
	{
		if ( $_SESSION["captcha"] == $_POST["captcha"] )
		{
			//CAPTHCA is valid; proceed the message: save to database, send by e-mail ...
			// echo 'CAPTHCA is valid; proceed the message';
		}
		else
		{
			$errors .= 'Your CAPTHCA code is not valid<br>';
			syslog(LOG_ERR, "FreshPorts captcha failure: '" . $UserLogin . "', '" . $email . "', "  . $_SERVER['REMOTE_ADDR']);
			$OK = 0;
		}
	}



	#
	# make sure we have valid values in this variable.
	# by default, they don't get notified.
	#

	$UserCreated = 0;
	if ($OK) {
		// test for existance of user id

		$sql = "select * from users where name = '" . pg_escape_string($db, strtolower($UserLogin)) . "'";
		syslog(LOG_ERR, "FreshPorts new user: $sql");

		$result = pg_query($db, $sql) or die('query failed');

		// create user id if not found
		if(!pg_num_rows($result)) {
			syslog(LOG_ERR, "FreshPorts new user: '$UserLogin', '$email', " . $_SERVER["REMOTE_ADDR"] . ' confirmed: user id is new');

			$UserID = freshports_GetNextValue($Sequence_User_ID, $db);
			if (IsSet($UserID)) {
				$sql = "insert into " .
					"users (id, name, cookie, email, watch_notice_id, emailsitenotices_yn, type, ip_address, number_of_days, password_hash) " .
					"values ($1, $2, $3, $4, $5::integer, $6, $7, $8, $9::integer, crypt($10, gen_salt($11, $12::integer)))";

				syslog(LOG_ERR, "FreshPorts new user: '$UserID', '$UserLogin', '$email', " . $_SERVER["REMOTE_ADDR"]);

				$errors .= "<BR>sql=" . $sql;

				$result = pg_query_params($db, $sql, array(
					$UserID, $UserLogin, 'nocookie', $email, 1, 'N', 'U', $_SERVER["REMOTE_ADDR"],
					$numberofdays, $Password1, PW_HASH_METHOD, PW_HASH_COST
				));

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
		header("Location: welcome.php");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}
} else {
	// not submit

	// we can't do this if we are submitting because it overwrites the incoming values
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
}

	$Title = 'New User';
	freshports_Start($Title,
               $Title,
               'FreeBSD, index, applications, ports');
?>

<?php echo freshports_MainTable(); ?>
<TR><td class="content">
<?php
if ($errors != '') {
echo '<TABLE class="fullwidth borderless">
<TR>
<TD>
<TABLE class="fullwidth borderless">
<TR class="accent"><TD>Access Code Failed!</TD>
</TR>
<TR>
<TD>
  <p><IMG SRC="/images/warning.gif"> Some errors have occurred which must be corrected before your login can be created.</p>';

/*
  while (list($name, $value) = each($_REQUEST)) {
    echo "$name = $value<BR>\n";
  }
*/
echo $errors;

echo '<p>If you need help, please email postmaster@. </p>
</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>
<BR>';
}

if (!IsSet($submit) && $errors != '') {
  // provide default values for an empy form.
  require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
}

echo freshports_MainContentTable();

?>
      <TR>
		<? echo freshports_PageBannerText("New User Details"); ?>
      </TR>
      <TR>
        <TD>

<p class="element-details"><span>Please observe the following points:</span></p>

<ul>
<li>
You must supply a valid email address. Instructions to enable your account 
will be emailed to you at that address.

<li>If you have a spam filter, please allow all
mail from <CODE CLASS="code">unixathome.org</CODE> and <CODE CLASS="code">freshports.org</CODE>.

<li>Please disable any auto-responders for the above domains.  I get enough email
without being told when you'll be back from holiday or who else I can contact...

<li>Your browser must allow cookies for the login to work.

</ul>

<P>
Your cooperation with the above will make my life easier.  Thank you.

<hr>

<? require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/new-user.php'); ?>

	<hr>

    </TD>
  </TR>
</TABLE>
</TD>
<td class="sidebar">

	<?
	echo freshports_SideBar();
	?>

</td>
</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
