<?
	# $Id: customize.php,v 1.1.2.17 2002-09-11 13:39:34 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

?>
<script language="php">

$origin		= $_GET["origin"];
$submit 	= $_POST["submit"];
$visitor	= $_COOKIE["visitor"];

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
	header("Location: login.php?origin=" . $_SERVER["PHP_SELF"]);  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if ($submit) {
   $Debug = 0;

// process form

   $email					= AddSlashes($_POST["email"]);
   $Password1				= AddSlashes($_POST["$Password1"]);
   $Password2				= AddSlashes($_POST["Password2"]);
   $numberofdays			= AddSlashes($_POST["numberofdays"]);


   if (!is_numeric($numberofdays) || $numberofdays < 0 || $numberofdays > 9) {
      $numberofdays = 9;
   }

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
      // get the existing email in case we need to reset the bounce count
      $sql = "select email from users where cookie = '$visitor'";
      $result = pg_exec($db, $sql);
      if ($result) {
         $myrow = pg_fetch_array ($result, 0);

		$WatchNotice = new WatchNotice($db);
		$WatchNotice->FetchByFrequency($watchnotifyfrequency);

         $sql = "update users set ";
         $sql .= "email			= '$email', ";
         $sql .= "number_of_days      = $numberofdays ";

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
<TR><TD VALIGN="top" width="100%">
<TABLE width="100%" border="0">
  <TR>
    <TD height="20"><script language="php">

if (!$submit) {
	include( $_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");
    $numberofdays = $NumberOfDays;
}

if ($errors) {
echo '<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="#AD0040" width="100%">
<TR>
<TD>
<TABLE width="100%" BORDER="0" CELLPADDING="1">
<TR BGCOLOR="#AD0040"><TD><b><font color="#ffffff" size=+0>Access Code Failed!</font></b></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>
  <TABLE width="100%" CELLPADDING="3" BORDER="0">
  <TR VALIGN=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD width="100%">
  <p>Some errors have occurred which must be corrected before your login can be created.</p>';

echo $errors;

echo '<p>If you need help, please post a message on the forum. </p>
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
if ($AccountModified) {
   echo "Your account details were successfully updated.";
} else {
  // provide default values for an empy form.
//  $daystoshow = 20;
//  $maxarticles = 40;
//  $daysnew = 20;
//   require($_SERVER['DOCUMENT_ROOT'] . "/include/commonphp3.inc");
//	echo $DaysToShow,  '= days to show';

echo '<TABLE CELLPADDING="1" BORDER="0" BGCOLOR="#AD0040" WIDTH="100%">
<TR>
<TD VALIGN="top">
<TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
<TR>
<TD BGCOLOR="#AD0040" HEIGHT="29" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG>Customize</BIG></BIG></FONT></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>';

echo 'If you wish to change your password, supply your new password twice.  Otherwise, leave it blank.<br>';
include($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

$Customize=1;
include($_SERVER['DOCUMENT_ROOT'] . "/include/new-user.php");

echo "</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>";
}

</script></TD>
</TABLE>
</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
   <? include($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php") ?>
 </TD>
</TR>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
