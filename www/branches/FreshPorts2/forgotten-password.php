<?
	# $Id: forgotten-password.php,v 1.1.2.11 2002-05-18 19:10:41 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");


$Debug=0;

$submit = $_POST["submit"];

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

      $result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());


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

         $result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());

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
         GLOBAL $mailto;
         $error = "Sorry, but previous email to you has bounced, so we're not going to try sending it out.  Please contact " .
                  'the <A HREF="' . $mailto . ':webmaster&#64;freshports.org?subject=I forgot my password">webmaster</A> for help.';
         $OKToMail = 0;
      } else {
         if ($myrow["email"] == "") {
             $error = 'Guess what?  You never gave us an email address.  So I guess you must ' . 
                      'contact the <A HREF="' . $mailto . ':webmaster@freshports.org?subject=I forgot my password">webmaster</A> for help.';
             $OKToMail = 0;
         }
      }

      if ($OKToMail) {
         # send out email
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
                    "the request came from " . $_SERVER["REMOTE_ADDR"] . ':' . $_SERVER["REMOTE_PORT"];

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

<TABLE WIDTH="100%" BORDER="0">
 <TR>
    <TD>
<TABLE WIDTH="100%" BORDER="0">
<TR><TD VALIGN="top" WIDTH="100%">
<?
$Debug=1;

if ($error) {
      echo '<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="#AD0040" WIDTH="100%">
            <TR>
            <TD>
               <TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
                  <TR BGCOLOR="#AD0040"><TD><b><FONT COLOR="#ffffff" SIZE=+0>We have a problem!</FONT></b></TD>
                 </TR> 
                 <TR BGCOLOR="#ffffff">
            <TD>
              <TABLE WIDTH="100%" CELLPADDING="3" BORDER="0">
              <TR VALIGN=top>
               <TD><img src="/images/warning.gif"></TD>
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
      echo '<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="#AD0040" WIDTH="100%">
            <TR>
            <TD>
               <TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
                  <TR BGCOLOR="#AD0040"><TD><b><FONT COLOR="#ffffff" SIZE=+0>UserID not found!</FONT></b></TD>
                 </TR>
                 <TR BGCOLOR="#ffffff">
            <TD>
              <TABLE WIDTH="100%" CELLPADDING="3" BORDER="0">
              <TR VALIGN=top>
               <TD><img src="/images/warning.gif"></TD>
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

<TABLE CELLPADDING="0" BORDER="0" BGCOLOR="#AD0040" WIDTH="100%"> <TR> <TD>


<TABLE WIDTH="100%" BORDER="0" CELLPADDING="1" BGCOLOR="#AD0040">

<TR BGCOLOR="#AD0040"><TD BGCOLOR="#AD0040"><FONT COLOR="#ffffff" SIZE="+2">
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
GLOBAL $mailto;
?>
<p>
Your password has been sent to the address we have on file.  If you still can't get logged in
please contact <A HREF="<? echo $mailto; ?>:webmaster&#63;freshports.org?subject=I forgot my password">the webmaster</A>
and we'll see what we can do.
</p>
<? } else {  ?>


<p>If you've forgotten your password, don't worry.  We've got that covered.</p>

<p>Please enter either your login or your email address (whichever you remember), then click on 'eMail Me!'.</p>

<p>We'll forward your password via clear text to your email account.  This isn't exactly totally secure, but then
we're only dealing with your FreshPorts login, not a financial transaction....</p>

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p>User ID:<br>
      <input SIZE="15" NAME="UserID" value="<? echo $UserID ?>"></p>
      <p>email address:<br>
      <input NAME="eMail" VALUE = "<? echo $eMail ?>" SIZE="20"></p>
      <p><input TYPE="submit" VALUE="eMail Me!" name=submit> &nbsp;&nbsp;&nbsp;&nbsp; <input TYPE="reset" VALUE="reset form">
</form>
<? } ?>
</TD>

</TR>
</TABLE>
</TD>
</TR>
</TABLE>
</TD>
  <TD VALIGN="top" WIDTH="*">
    <? 
       unset($UserID);
       include("./include/side-bars.php");
    ?>
 </TD>
</TR>
</TABLE> 
</TD></TR>
</TABLE>
<? include("./include/footer.php") ?>
</body>
</html>
