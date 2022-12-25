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
	
	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	GLOBAL $User;

	$errors          = 0;
	$AccountModified = 0;
	$deleted         = 0;

if (IsSet($_REQUEST['submit'])) $submit = $_REQUEST['submit'];
$visitor	= pg_escape_string($db, $_COOKIE[USER_COOKIE_NAME]);

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
	header('Location: /login.php');  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (IsSet($submit)) {
    #phpinfo();
    $Debug = 0;

    // process form
    syslog(LOG_ERR, 'into '. __FILE__);

	$confirmation = pg_escape_string($db, $_POST['confirmation']);

	if ($confirmation ==  $User->name) {
		$result = pg_exec($db, "BEGIN");
        
		// Delete from the user table. The database will take care of the rest
#		$sql = "DELETE FROM users WHERE cookie = '$visitor'";
		$sql = "SELECT DeleteUser('$User->id')";
		$result = pg_exec($db, $sql);
		if ($result) {
			$numrows = pg_affected_rows($result);
			if ($numrows == 1) {
                pg_exec($db, "COMMIT");
                # clear the cookie so they don't get "Your user details were not found. You have been logged out. Please return to the home page."
                freshports_CookieClear();
				$deleted = 1;
			} else {
                pg_exec($db, "ROLLBACK");
                $errors = 'I really tried to delete your account. I failed. Sorry.';
				syslog(LOG_ERR, 'attempted to delete user failed ' . $User->name . ' failed when trying to delete ' . $numrows . ' rows.');
            }
        } else {
            pg_exec($db, "ROLLBACK");

            syslog(LOG_ERR, 'attempt to delete user failed ' . $User->name . ' failed with error ' . pg_last_error($db));
            $errors = 'I could not delete that account. Sorry.';
        }
	} else {
        syslog(LOG_ERR, 'confirmation did not match: "' . $confirmation . '" != "' . $User->name . '"');
        $errors = 'The confirmation was not correct.';
    }
}

#	echo '<br>the page size is ' . $page_size . ' : ' . $email;

	freshports_Start('Delete User Account',
						'freshports - new ports, applications',
						'FreeBSD, index, applications, ports');
?>

<TABLE class="fullwidth borderless" ALIGN="center">
<TR><td class="content">
<TABLE class="fullwidth borderless">
  <TR>
    <TD height="20"><?php


if ($errors) {
echo '<TABLE class="fullwidth borderless">
<TR>
<TD>
<TABLE class="fullwidth borderless">
<TR class="accent"><TD><b>Delete Failed!</b></TD>
</TR>
<TR>
<TD>
  <TABLE class="fullwidth borderless" CELLPADDING="3">
  <TR VALIGN=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD width="100%">
  <p>The deleted failed.</p>';

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
}  // if ($errors)

echo '<TABLE class="fullwidth borderless">
<TR>
<TD VALIGN="top">
<TABLE class="fullwidth borderless">
<TR>
<td class="accent"><BIG>Customize</BIG></td>
</TR>
<TR>
<TD>';


if ($deleted) {
    ?>
    <br><h2>Your account has been deleted.</h2>
    <p>Please click <a href="/">here</a> to return to the home page.</p>
    <?php
} else {
echo '<p>To delete your account, please enter you account name and click on <i>delete account</i>.</p><br>';

require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

?>
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
<TABLE width="*" class="borderless">
          <TR>
            <TD VALIGN="top">
               <p>The account name is: <?php echo $User->name; ?><p>
               User Name: <INPUT SIZE="15" NAME="confirmation" VALUE="">
            </td>
        </tr>
        <tr>
            <td>
                <br>
                <h2>There is no undo for this</h2>
                <INPUT TYPE="submit" VALUE="Delete Account" NAME="submit">
            </td>
        </tr>
  
    </TABLE>
</FORM>

<?php
} // if ($deleted)
echo "</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>";

?>
</TD>
</TABLE>
</td>

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

</body>
</html>
