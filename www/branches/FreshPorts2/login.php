<?
	# $Id: login.php,v 1.1.2.18 2002-05-22 04:30:24 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

   require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
   require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
   require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

$Debug=0;

$origin = $_GET["origin"];

if ($Debug) echo "origin = '" . rawurlencode($origin) . "'<BR>\n";

$origin=rawurlencode($origin);

if ($_POST["UserID"]) {
   // process form

   if ($Debug) {
      while (list($name, $value) = each($HTTP_POST_VARS)) {
         echo "$name = $value<BR>\n";
      }
   }


   $OK = 1;

   $errors = "";
   $UserID   = addslashes($_POST["UserID"]);
   $Password = AddSlashes($_POST["Password"]);

   // test for existance of user id

   $sql = "select * from users where lower(name) = lower('$UserID')".
	  " and password = '$Password' ";

   if ($Debug) {
      echo "$sql<BR>\n";
   }

   $result = pg_exec($db, $sql) or die('query failed ' . pg_errormessage());


	if (!pg_numrows($result)) {
		$LoginFailed = 1;
	} else {
		$Cookie = UserToCookie($UserID);
		$row    = pg_fetch_array($result,0);
		$status = $row["status"];
		if ($Debug) echo "\$status = $status\n<BR>";

		GLOBAL $UserStatusActive;
		GLOBAL $UserStatusDisabled;
		GLOBAL $UserStatusUnconfirmed;

		if ($Debug) echo "\$UserStatusActive = '$UserStatusActive'\n<BR>";

		if ($status == $UserStatusActive) {
			if ($Debug) {
				echo "well, debug was on, so I would have taken you to '$origin'<BR>\n";
				echo "Cookie = $Cookie<BR>\n";
			} else {
				SetCookie("visitor", $Cookie, time() + 60*60*24*120, '/');
				// Redirect browser to PHP web site
				if ($origin == "/index.php" || origin == "") {
					$origin = "/";
				}
				header("Location: " . rawurldecode($origin));
				// Make sure that code below does not get executed when we redirect.
				exit;
			}
		} else {
			if ($status == $UserStatusDisabled) {
				$error .= "Your account has been disabled.  Please contact $ProblemSolverEmailAddress.";
			} else {
				if ($status == $UserStatusUnconfirmed) {
					$error .= "Your account needs to be enabled by following the directions in the email we have sent to you.";
				} else {
					$error .= "I have no idea what your account status is.";
				}
			}
		
		}
	}
}
   freshports_Start("Login",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
 <TR>
    <TD VALIGN="top" WIDTH="100%">
<?
if ($LoginFailed) {
?>
<TABLE WIDTH="100%" BORDER="1" ALIGN="center" CELLPADDING=1 CELLSPACING=0 BORDER="1">
<TR><TD VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
	<? freshports_PageBannerText("Login Failed!") ?>
</TR>
<TR BGCOLOR="#ffffff">
<TD>
  <TABLE WIDTH="100%" CELLPADDING=0 CELLSPACING=0 BORDER=0>
  <TR valign=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD WIDTH="100%">
  <p>The User ID and password you supplied could not be used to login.	This could be for one of the following reasons:</p>
 <ul>
 <li>The login id is incorrect
 <li>The password is incorrect
 <li>Both of the above
 </ul>
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
<BR>
<?
}

if ($error) {
?>
<TABLE WIDTH="100%" BORDER="1" ALIGN="center" CELLPADDING=1 CELLSPACING=0 BORDER="1">
<TR><TD VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
    <? freshports_PageBannerText("NOTICE"); ?>
</TR>

<TR BGCOLOR="#ffffff">
<TD>
  <TABLE WIDTH="100%" CELLPADDING=0 BORDER=0>
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




echo '<TABLE WIDTH="100%" BORDER="1" CELLPADDING="1" CELLSPACING="0" BGCOLOR="#AD0040">';

echo '<TR BGCOLOR="#AD0040">';

freshports_PageBannerText("Login");
echo '</TR>';

echo '<TR><TD BGCOLOR="#ffffff">';
include ($_SERVER['DOCUMENT_ROOT'] . "/include/login.php");

echo "Your browser must allow cookies for this login to work.";

echo "</TD>";
echo"
</TR>
</TABLE>
";

echo '<BR><A HREF="forgotten-password.php">Forgotten your password?</a>';

?>
</TD>
  <TD valign="top" WIDTH="*">
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
