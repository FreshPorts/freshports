<?
	# $Id: forgotten-password.php,v 1.1.2.4 2002-01-02 04:48:14 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");


#$Debug=1;

$MailSent = 0;

#if (!$submit) {
#   require( "./include/getvalues.php");
##   echo 'not submit';
#   #
#   # if they are logged in, put them over to customize, they shouldn't be here...
#   #
#   if ($UserID) {
#       header("Location: customize.php");
#       // Make sure that code below does not get executed when we redirect.
#       exit;
#   } else {
##      echo 'no userid';
#   }
#}

if ($submit) {
//   echo "UserID = $UserID\n";
//   $Debug=1;
   // process form

   $error = '';

   if ($Debug) {
      while (list($name, $value) = each($HTTP_POST_VARS)) {
         echo "$name = $value<br>\n";
      }
   }

   $OK = 1;

   if ($UserID) {
      $errors = "";
      $UserID = addslashes($UserID);

      if ($Debug) {
         echo $UserID . "<br>\n";
      }

      $sql = "select * from users where lower(name) = lower('$UserID')";

      if ($Debug) {
         echo "$sql<br>\n";
      }

      $result = pg_exec($db, $sql) or die('query failed ' . mysql_error());


      if (!pg_numrows($result)) {
         $LoginFailed = 1;
      }
   } else {
      if ($eMail) {
         $errors = "";
         $eMail = addslashes($eMail);

         if ($Debug) echo $eMail . "<br>\n";

         $sql = "select * from users where email = '$eMail'";

         if ($Debug) echo "$sql<br>\n";

         $result = pg_exec($db, $sql) or die('query failed ' . mysql_error());

         if (!pg_numrows($result)) {
            $eMailFailed = 1;
         }
      }
   }

   if (pg_numrows($result)) {
      // there is a result.  Let's fetch it.
      $myrow = pg_fetch_array ($result, 0);

      $OKToMail = 1;
      if ($myrow["emailbouncecount"] > 0) {
         $error = "Sorry, but previous email to you has bounced, so we're not going to try sending it out.  Please contact " .
                  'the <a href="mailto:webmaster@freshports.org?subject=I forgot my password">webmaster</a> for help.';
         $OKToMail = 0;
      } else {
         if ($myrow["email"] == "") {
             $error = 'Guess what?  You never gave us an email address.  So I guess you must ' . 
                      'contact the <a href="mailto:webmaster@freshports.org?subject=I forgot my password">webmaster</a> for help.';
             $OKToMail = 0;
         }
      }

      if ($OKToMail) {
         # send out email
//         $myrow = mysql_fetch_array($result);
         $message = "Someone, perhaps you, requested that you be emailed your password.\n".
                    "If that wasn't you, and this message becomes a nuisance, please\n".
                    "forward this message to webmaster@freshports.org and we will take\n". 
                    "care of it for you.\n" .
                    " \n" .
                    "Your User ID is:\n" .
                    $myrow["name"] . "\n" .
                    "\n" .
                    "Your password is:\n" .
                    $myrow["password"] . "\n" .
                    "\n" .
                    "the request came from $REMOTE_ADDR:$REMOTE_PORT";

         mail($myrow["email"], "FreshPorts - password", $message,
         "From: webmaster@freshports.org\nReply-To: webmaster@freshports.org\nX-Mailer: PHP/" . phpversion());

         $MailSent = 1;
      }
   }
}

   freshports_Start("Forgotten password",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");
?>

<table width="100%" border=0>
 <tr>
    <td>
<table width="100%" border=0>
<tr><td valign="top" width="100%">
<?
$Debug=1;

if ($error) {
      echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
            <tr>
            <td>
               <table width=100% border=0 cellpadding=1>
                  <tr bgcolor="#AD0040"><td><b><font color="#ffffff" size=+0>We have a problem!</font></b></td>
                 </tr> 
                 <tr bgcolor="#ffffff">
            <td>
              <table width=100% cellpadding=3 cellspacing=0 border=0>
              <tr valign=top>
               <td><img src="/images/warning.gif"></td>
               <td width=100%>
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
      echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
            <tr>
            <td>
               <table width=100% border=0 cellpadding=1>
                  <tr bgcolor="#AD0040"><td><b><font color="#ffffff" size=+0>UserID not found!</font></b></td>
                 </tr>
                 <tr bgcolor="#ffffff">
            <td>
              <table width=100% cellpadding=3 cellspacing=0 border=0>
              <tr valign=top>
               <td><img src="/images/warning.gif"></td>
               <td width=100%>
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

<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>
<td>


<table width=100% border=0 cellpadding=1 bgcolor="#AD0040">

<tr bgcolor="#AD0040"><td bgcolor="#AD0040"><font color="#ffffff" size="+2">
<?
if ($MailSent) {
   echo "Mail sent to your address";
} else {
   echo "Forgotten your password?";
}
?>
</font></td></tr>

<tr><td bgcolor="#ffffff">
<?
if ($MailSent) {
?>
<p>
Your password has been sent to the address we have on file.  If you still can't get logged in
please contact <a href="mailto:webmaster@freshports.org?subject=I forgot my password">the webmaster</a>
and we'll see what we can do.
</p>
<? } else {  ?>


<p>If you've forgotten your password, don't worry.  We've got that covered.</p>

<p>Please enter either your login or your email address (whichever you remember), then click on 'eMail Me!'.</p>

<p>We'll forward your password via clear text to your email account.  This isn't exactly totally secure, but then
we're only dealing with your FreshPorts login, not a financial transaction....</p>

<form action="<?php echo $PHP_SELF ?>" method="POST">
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p>User ID:<br>
      <input SIZE="15" NAME="UserID" value="<? echo $UserID ?>"></p>
      <p>email address:<br>
      <input NAME="eMail" VALUE = "<? echo $eMail ?>" size="20"></p>
      <p><input TYPE="submit" VALUE="eMail Me!" name=submit> &nbsp;&nbsp;&nbsp;&nbsp; <input TYPE="reset" VALUE="reset form">
</form>
<? } ?>
</td>

</tr>
</table>
</td>
</tr>
</table>
</td>
  <td valign="top" width="*">
    <? 
       unset($UserID);
       include("./include/side-bars.php");
    ?>
 </td>
</tr>
</table> 
</td></tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
