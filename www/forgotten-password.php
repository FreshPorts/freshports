<?php
	#
	# $Id: forgotten-password.php,v 1.3 2010-09-17 14:38:29 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

$Debug = 0;


if (IsSet($_POST['submit'])) {
	$submit = $_POST['submit'];
}

$MailSent    = 0;
$LoginFailed = 0;
$eMailFailed = 0;

if (IsSet($submit)) {
   // process form
   $error = '';

   if ($Debug) {
      while (list($name, $value) = each($HTTP_POST_VARS)) {
         echo "$name = $value<br>\n";
      }
   }

   $OK = 1;

   $UserID = pg_escape_string( $_REQUEST["UserID"] );
   $eMail  = pg_escape_string( strtolower( $_REQUEST["eMail"] ) );

   if ($UserID) {
      $error = '';

      if ($Debug) {
         echo $UserID . "<br>\n";
      }

      $sql = "select * from users where name = '" . $UserID . "'";

      if ($Debug) {
         echo "<pre>$sql</pre>\n";
      }

      $result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());

      if (!pg_numrows($result)) {
         $LoginFailed = 1;
      }
   } else {
      if ($eMail) {
         $error = '';

         if ($Debug) echo $eMail . "<br>\n";

         $sql = "select * from users where lower(email) = '" . $eMail . "'";
			if ($Debug) {
				echo "<pre>This is the \$sql='$sql'</pre>\n";
				echo "<pre>$sql</pre>\n";
			}

         if ($Debug) echo "$sql<br>\n";

         $result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());

         if (!pg_numrows($result)) {
            $eMailFailed = 1;
         }
      }
   }

   if (pg_numrows($result)) {
      // there is a result.  Let's fetch it, or rather, all of them
      while ( $myrow = pg_fetch_array ($result) ) {

        $OKToMail = 1;
        if ($myrow["emailbouncecount"] > 0) {
           $error = "Sorry, but previous email to you has bounced, so we're not sure it's going to get to you.  But we sent it out
						anyway.  Please contact " .
                    'the <A HREF="' . MAILTO . ':webmaster&#64;freshports.org?subject=I forgot my password">webmaster</A> for help
                    if it doesn\'t arrive.';
           $OKToMail = 1;
           syslog(LOG_NOTICE, "Forgotten password: previous email to '" . $myrow['email'] . "' bounced");
        }

        if ($myrow["email"] == "") {
          $error = 'Guess what?  You never gave us an email address.  So I guess you must ' . 
              'contact the <A HREF="' . MAILTO . ':webmaster&#64;freshports.org?subject=I forgot my password">webmaster</A> for help.';
              $OKToMail = 0;
          syslog(LOG_NOTICE, "Forgotten password: '" . $myrow['name'] . "' never supplied an email.");
        }

        if ($OKToMail) {
          $sql = "insert into user_password_reset (user_id, ip_address) values (" . pg_escape_string($myrow["id"]) . ", '" . pg_escape_string($_SERVER['REMOTE_ADDR']) . "') returning token";
          $token_result = pg_exec($db, $sql) or die('password token creation failed ' . pg_errormessage());
          $token_row = pg_fetch_array ($token_result, 0);
          $token = $token_row["token"];
          
           # send out email
           $message = "Someone, perhaps you, requested that you be emailed your password.\n".
                      "If that wasn't you, and this message becomes a nuisance, please\n".
                      "forward this message to webmaster@freshports.org and we will take\n". 
                      "care of it for you.\n" .
                      " \n" .
                      "Your login id is: " . $myrow["name"] . "\n\n" . 
                      "Your password recovery URL is:\n" .
                      "http://" . $_SERVER["SERVER_NAME"] . "/password-reset-via-token.php?token=" . $token . "\n" .
                      "\n" .
                      "the request came from " . $_SERVER["REMOTE_ADDR"] . ':' . $_SERVER["REMOTE_PORT"];

           mail($myrow["email"], "FreshPorts - password", $message,
           "From: webmaster@freshports.org\nReply-To: webmaster@freshports.org\nX-Mailer: PHP/" . phpversion());

           $MailSent = 1;
          syslog(LOG_NOTICE, "Forgotten password: email for '" . $myrow['name'] . "' sent to '" . $myrow['email'] . "': " . $token);
        }
      }
   }
}

   freshports_Start('Forgotten password',
               'freshports - new ports, applications',
               'FreeBSD, index, applications, ports');
?>

<TABLE WIDTH="100%" BORDER="0">
 <TR>
    <TD>
<TABLE WIDTH="100%" BORDER="0">
<TR><TD VALIGN="top" WIDTH="100%">
<?

if (IsSet($error) and $error != '') {
      echo '<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="' . BACKGROUND_COLOUR . '" WIDTH="100%">
            <TR>
            <TD>
               <TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
                  <TR BGCOLOR="' . BACKGROUND_COLOUR . '"><TD><b><FONT COLOR="#ffffff" SIZE="+2">We have a problem!</FONT></b></TD>
                 </TR> 
                 <TR BGCOLOR="#ffffff">
            <TD>
              <TABLE WIDTH="100%" CELLPADDING="3" BORDER="0">
              <TR VALIGN="middle">
               <TD><img src="/images/warning.gif" ALT="warning!"></TD>
               <TD WIDTH="100%">
            <p>';
      echo $error;     
      echo '</TD>
       </TR>
       </TABLE>
      </TD>
      </TR>
      </TABLE>
      </TD>
      </TR>
      </TABLE>
      <br>';
} else {

   if ($LoginFailed || $eMailFailed) {
      echo '<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="' . BACKGROUND_COLOUR . '" WIDTH="100%">
            <TR>
            <TD>
               <TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
                  <TR BGCOLOR="' . BACKGROUND_COLOUR . '"><TD><b><FONT COLOR="#ffffff" SIZE="+2">UserID not found!</FONT></b></TD>
                 </TR>
                 <TR BGCOLOR="#ffffff">
            <TD>
              <TABLE WIDTH="100%" CELLPADDING="3" BORDER="0">
              <TR VALIGN=top>
               <TD><img src="/images/warning.gif" ALT="warning!"></TD>
               <TD WIDTH="100%">
              <p>The ';

      if ($LoginFailed) {
         echo "User ID";
      } else {
         echo "email";
      }

      echo ' you supplied could not be found.  Perhaps try your ';
      if ($LoginFailed) {
         echo "email";
      } else {
         echo "User ID";
      }
      echo ' instead?</p>
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
      <br>';
      }
}
?>

<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="<?php echo BACKGROUND_COLOUR; ?>" WIDTH="100%"> <TR> <TD>


<TABLE WIDTH="100%" BORDER="0" CELLPADDING="5" BGCOLOR="<?php echo BACKGROUND_COLOUR; ?>">

<TR BGCOLOR="<?php echo BACKGROUND_COLOUR; ?>"><TD BGCOLOR="<?php echo BACKGROUND_COLOUR; ?>"><FONT COLOR="#ffffff" SIZE="+2">
<?
if ($MailSent) {
   echo "Mail sent to your address";
} else {
   echo "Forgotten your password?";
}
?>
</FONT></TD></TR>

<TR><TD BGCOLOR="#ffffff">
<?
if ($MailSent) {
?>
<p>
A password recovery URL has been sent to the address we have on file.  If you still can't get logged in
please contact <A HREF="<? echo MAILTO; ?>:webmaster&#64;freshports.org?subject=I forgot my password">the webmaster</A>
and we'll see what we can do.
</p>
<? } else {  ?>


<p>If you've forgotten your password, don't worry.  We've got that covered.</p>

<p>Please enter either your login or your email address (whichever you remember), then click on 'eMail Me!'.</p>

<p>We will send you an email with a link in it. Click on that link and you'll be able to set a new password.
This link will expire within a few hours. This isn't exactly totally secure, but then
we're only dealing with your FreshPorts login, not a financial transaction....</p>

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p>User ID:<br>
      <input SIZE="15" NAME="UserID" value="<? if (IsSet($UserID)) echo $UserID ?>"></p>
      <p>email address:<br>
      <input NAME="eMail" VALUE = "<? if (IsSet($eMail)) echo $eMail ?>" SIZE="20"></p>
      <p><input TYPE="submit" VALUE="eMail Me!" name=submit>
</form>
<? } ?>
</TD>

</TR>
</TABLE>
</TD>
</TR>
</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TR>
</TABLE> 
</TD></TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
