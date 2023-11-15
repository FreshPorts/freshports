<?php
	#
	# $Id: forgotten-password.php,v 1.3 2010-09-17 14:38:29 dan Exp $
	#
	# Copyright (c) 1998-2022 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once('/usr/local/share/phpmailer/PHPMailer.php');
	require_once('/usr/local/share/phpmailer/SMTP.php');


	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

$Debug = 0;

$submit = $_POST['submit'] ?? '';

$MailSent    = 0;
$LoginFailed = 0;
$eMailFailed = 0;

if ($submit) {
   // process form
   $error = '';

   if ($Debug) {
      foreach ($_REQUEST as $name => $value) {
         echo "$name = $value<br>\n";
      }
   }

   $OK = 1;

   $UserID = pg_escape_string($db, $_REQUEST["UserID"] );
   $eMail  = pg_escape_string($db, strtolower( $_REQUEST["eMail"] ) );

   if ($UserID) {
      $error = '';

      if ($Debug) {
         echo $UserID . "<br>\n";
      }

      $sql = 'select * from users where name = $1';

      if ($Debug) {
         echo "<pre>$sql</pre>\n";
      }

      $result = pg_query_params($db, $sql, array($UserID)) or die('query failed ' . pg_last_error($db));

      if (!pg_num_rows($result)) {
         $LoginFailed = 1;
      }
   } else {
      if ($eMail) {
         $error = '';

         if ($Debug) echo $eMail . "<br>\n";

         $sql = 'select * from users where lower(email) = $1';

         if ($Debug) echo "$sql<br>\n";

         $result = pg_query_params($db, $sql, array($eMail)) or die('query failed ' . pg_last_error($db));

         if (!pg_num_rows($result)) {
            $eMailFailed = 1;
         }
      }
   }

   if (pg_num_rows($result)) {
      // there is a result.  Let's fetch it, or rather, all of them
      while ( $myrow = pg_fetch_array ($result) ) {

        $OKToMail = 1;
        if ($myrow["emailbouncecount"] > 0) {
           $error = "Sorry, but previous email to you has bounced, so we're not sure it's going to get to you.  But we sent it out
						anyway.  Please contact " .
                    'the <a href="' . MAILTO . ':' . htmlentities(PROBLEM_SOLVER_EMAIL_ADDRESS . '?subject=I forgot my password') . '" rel="noopener noreferrer">webmaster</a> for help
                    if it doesn\'t arrive.';
           $OKToMail = 1;
           syslog(LOG_NOTICE, "Forgotten password: previous email to '" . $myrow['email'] . "' bounced");
        }

        if ($myrow["email"] == "") {
          $error = 'Guess what?  You never gave us an email address.  So I guess you must ' . 
              'contact the <a href="' . MAILTO . ':' . htmlentities(PROBLEM_SOLVER_EMAIL_ADDRESS . '?subject=I forgot my password') . '" rel="noopener noreferrer">webmaster</a> for help.';
              $OKToMail = 0;
          syslog(LOG_NOTICE, "Forgotten password: '" . $myrow['name'] . "' never supplied an email.");
        }

        if ($OKToMail) {
          $sql = 'insert into user_password_reset (user_id, ip_address) values ($1, $2) returning token';
          $token_result = pg_query_params($db, $sql, array($myrow["id"], $_SERVER['REMOTE_ADDR'])) or die('password token creation failed ' . pg_last_error($db));
          $token_row = pg_fetch_array ($token_result, 0);
          $token = $token_row["token"];
          
          # send out email
          $message = "Someone, perhaps you, requested to reset your password.\n".
                     "If that wasn't you, and this message becomes a nuisance, please\n".
                     "forward this message to webmaster@freshports.org and we will take\n". 
                     "care of it for you.\n" .
                     " \n" .
                     "Your login id is: " . $myrow["name"] . "\n\n" . 
                     "Your password recovery URL is:\n" .
                     "http://" . $_SERVER["HTTP_HOST"] . "/password-reset-via-token.php?token=" . $token . "\n" .
                     "\n" .
                     "the request came from " . $_SERVER["REMOTE_ADDR"] . ':' . $_SERVER["REMOTE_PORT"];

          try {
            $mail = new PHPMailer\PHPMailer\PHPMailer;

            // Settings
            $mail->IsSMTP();
            $mail->Host       = MAIL_SERVER;                   // SMTP server
            $mail->Port       = 25;                            // set the SMTP port for the smtp server
            $mail->SMTPDebug  = 0;                             // enables SMTP debug information (for testing)

            // Content
            $mail->ContentType = 'text/plain';
            $mail->Subject     = WEBSITE_NAME . '- password';
            $mail->Body        = $message;

            $mail->setFrom   (PROBLEM_SOLVER_EMAIL_ADDRESS, WEBSITE_NAME);
            $mail->addReplyTo(PROBLEM_SOLVER_EMAIL_ADDRESS, WEBSITE_NAME);

            $mail->addAddress($myrow["email"]);

            if ($mail->send()) {
              $MailSent = 1;
              syslog(LOG_NOTICE, "Forgotten password: email for '" . $myrow['name'] . "' sent to '" . $myrow['email'] . "': " . $token);
            } else {
              syslog(LOG_ERR, "freshports_UserSendToken send() failed with: " . $mail->ErrorInfo);
            }
          } catch (phpmailerException $e) {
            syslog(LOG_ERR, "forgotten-password.php has this error with PHPMailer: " . $e->errorMessage());
          }
        }
      }
   }
}

   $Title = 'Forgotten password';
   freshports_Start($Title,
               $Title,
               'FreeBSD, index, applications, ports');
?>

<table class="fullwidth borderless">
 <tr>
    <td>
<table class="fullwidth borderless">
<tr><td class="content">
<?php

if (IsSet($error) and $error != '') {
      echo '<table class="fullwidth borderless">
            <tr>
            <td>
               <table class="fullwidth borderless">
                 <tr class="accent"><td><b>We have a problem!</b></td>
                 </tr> 
                 <tr>
            <td>
              <table class="fullwidth borderless" CELLPADDING="3">
              <tr VALIGN="middle">
               <td><img src="/images/warning.gif" ALT="warning!"></td>
               <td WIDTH="100%">
            <p>';
      echo $error;     
      echo '</td>
       </tr>
       </table>
      </td>
      </tr>
      </table>
      </td>
      </tr>
      </table>
      <br>';
} else {

   if ($LoginFailed || $eMailFailed) {
      echo '<table class="fullwidth borderless">
            <tr>
            <td>
               <table class="fullwidth borderless">
                 <tr class="accent"><td><b>User ID not found!</b></td>
                 </tr>
                 <tr>
            <td>
              <table class="fullwidth borderless" CELLPADDING="3">
              <tr VALIGN=top>
               <td><img src="/images/warning.gif" ALT="warning!"></td>
               <td WIDTH="100%">
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
}
?>

<table class="fullwidth borderless"> <tr> <td>


<table class="fullwidth borderless" CELLPADDING="5">

<tr class="accent" ><td class="accent">
<?php
if ($MailSent) {
   echo "Mail sent to your address";
} else {
   echo "Forgotten your password?";
}
?>
</td></tr>

<tr><td>
<?php
if ($MailSent) {
?>
<p>
A password recovery URL has been sent to the address we have on file.  If you still can't get logged in
please contact <a href="<?php echo MAILTO; ?>:webmaster&#64;freshports.org?subject=I forgot my password" rel="noopener noreferrer">the webmaster</a>
and we'll see what we can do.
</p>
<?php } else {  ?>


<p>If you've forgotten your password, don't worry.  We've got that covered.</p>

<p>Please enter either your login or your email address (whichever you remember), then click on 'eMail Me!'.</p>

<p>We will send you an email with a link in it. Click on that link and you'll be able to set a new password.
This link will expire within a few hours. This isn't exactly totally secure, but then
we're only dealing with your FreshPorts login, not a financial transaction....</p>

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p>User ID:<br>
      <input SIZE="15" NAME="UserID" value="<?php if (IsSet($UserID)) echo htmlentities($UserID) ?>"></p>
      <p>email address:<br>
      <input NAME="eMail" VALUE = "<?php if (IsSet($eMail)) echo htmlentities($eMail) ?>" SIZE="20"></p>
      <p><input TYPE="submit" VALUE="eMail Me!" name=submit>
</form>
<?php } ?>
</td>

</tr>
</table>
</td>
</tr>
</table>
</td>

  <td class="sidebar">
	<?php 	echo freshports_SideBar();	?>
  </td>

</tr>
</table>
</td></tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
