<?
	# $Id: customize.php,v 1.1.2.6 2002-03-25 02:09:28 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

?>
<script language="php">

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
	header("Location: login.php?origin=" . $PHP_SELF);  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if ($submit) {
//$Debug = 1;

// process form
   if ($Debug) {
      while (list($name, $value) = each($HTTP_POST_VARS)) {
         echo "$name = $value<br>\n";
      }
   }

   $OK = 1;

   $errors = "";

	if (!freshports_IsEmailValid($email)) {
		$errors .= "That email address doesn't look right to me<BR>";
		$OK = 0;
	}

   if ($Password1 != $Password2) {
      $errors .= "The password was not confirmed.  It must be entered twice.<BR>";
      $OK = 0;
   }

   $AccountModified = 0;
   if ($OK) {
      if ($emailsitenotices_yn == "ON") {
          $emailsitenotices_yn_value = "t";
      } else {
         $emailsitenotices_yn_value = "f";
      }

      if ($FormatDateSelect != "") {
         $FormatDate = $FormatDateSelect;
      } else {
         if ($FormatDateCustom != "") {
           $FormatDate = $FormatDateCustom;
         } else {
           $FormatDate = $FormatDateDefault;
        }
      }

      if ($FormatTimeSelect != "") {
         $FormatTime = $FormatTimeSelect;
      } else {
         if ($FormatTimeCustom != "") {
           $FormatTime = $FormatTimeCustom;
         } else {
           $FormatTime = $FormatTimeDefault; 
        }
      }

      // get the existing email in case we need to reset the bounce count
      $sql = "select email from users where cookie = '$visitor'";
      $result = pg_exec($db, $sql);
      if ($result) {
         $myrow = pg_fetch_array ($result, 0);

		$WatchNotice = new WatchNotice($db);
		$WatchNotice->FetchByFrequency($watchnotifyfrequency);

         $sql = "update users set ";
         $sql .= "email			= '$email', ";
         $sql .= "emailsitenotices_yn = '$emailsitenotices_yn_value',";
         $sql .= "watch_notice_id     = $w$WatchNotice->id ";

         // if they are changing the email, reset the bouncecount.
         if ($myrow["email"] != $email) {
            $sql .= ", emailbouncecount = 0 ";
         }

         if ($Password1 != '') {
            $sql .= ", password = '$Password1'";
         }

         $sql .= " where cookie = '$visitor'";

         if ($Debug) {
            echo $sql;
         }

         $result = pg_exec($db, $sql);
         if ($result) {
			$AccountModified = 1;
         }
      }

      if ($AccountModified == 1) {
         if ($Debug) {
            echo "I would have taken you to '$origin' now, but debugging is on<br>\n";
         } else {
            header("Location: $origin");
            exit;  /* Make sure that code below does not get executed when we redirect. */
         }
      } else {
         $errors .= 'Something went terribly wrong there.<br>';
         $errors .= $sql . "<br>\n";
         $errors .= pg_errormessage();
      }
   }
} else {
}

   freshports_Start("Customize User Account",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");
</script>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">
<table width="100%" border="0">
  <tr>
    <td height="20"><script language="php">

if (!$submit) {
	include( "./include/getvalues.php");
}

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
if ($AccountModified) {
   echo "Your account details were successfully updated.";
} else {
  // provide default values for an empy form.
//  $daystoshow = 20;
//  $maxarticles = 40;
//  $daysnew = 20;
//   require( "./include/commonphp3.inc");
//	echo $DaysToShow,  '= days to show';

echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>
<td valign="top">
<table width=100% border=0 cellpadding=1>
<TD BGCOLOR="#AD0040" HEIGHT="29" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG>Customize</BIG></BIG></FONT></TD>
</tr>
<tr bgcolor="#ffffff">
<td>';

echo 'If you wish to change your password, supply your new password twice.  Otherwise, leave it blank.<br>';
include("./include/getvalues.php");

$Customize=1;
include("./include/new-user.php");

echo "</td>
</tr>
</table>
</form>
</td>
</tr>
</table>";
}

</script></td>
</table>
</td>
  <td valign="top" width="*">
   <? include("./include/side-bars.php") ?>
 </td>
</tr>
  </tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
