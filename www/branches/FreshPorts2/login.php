<?
	# $Id: login.php,v 1.1.2.12 2002-02-28 21:54:44 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");

$Debug=0;

if ($Debug) echo "origin = '" . rawurlencode($origin) . "'<br>\n";

$origin=rawurlencode($origin);

if ($submit) {
   // process form

   if ($Debug) {
      while (list($name, $value) = each($HTTP_POST_VARS)) {
         echo "$name = $value<br>\n";
      }
   }


   $OK = 1;

   $errors = "";
   $UserID = addslashes($UserID);

   // test for existance of user id

   $sql = "select * from users where lower(name) = lower('$UserID')".
	  " and password = '$Password' ";

   if ($Debug) {
      echo "$sql<br>\n";
   }

   $result = pg_exec($db, $sql) or die('query failed ' . mysql_error());


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
				echo "well, debug was on, so I would have taken you to '$origin'<br>\n";
				echo "Cookie = $Cookie<br>\n";
			} else {
				SetCookie("visitor", $Cookie, time() + 60*60*24*120, '/');
				// Redirect browser to PHP web site
				if ($origin == "/index.php") {
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
 <tr>
    <td VALIGN="top">
<table width="100%" border="0">
<tr><td valign="top" width="100%">
<?
if ($LoginFailed) {
?>
<TABLE WIDTH="100%" BORDER="1" ALIGN="center" cellpadding=1 cellspacing=0 BORDER="1">
<tr><td VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
	<? freshports_PageBannerText("Login Failed!") ?>
</tr>
<tr bgcolor="#ffffff">
<td>
  <table width=100% cellpadding=0 cellspacing=0 border=0>
  <tr valign=top>
   <td><img src="/images/warning.gif"></td>
   <td width=100%>
  <p>The User ID and password you supplied could not be used to login.	This could be for one of the following reasons:</p>
 <ul>
 <li>The login id is incorrect
 <li>The password is incorrect
 <li>Both of the above
 </ul>
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
<br>
<?
}

if ($error) {
?>
<TABLE WIDTH="100%" BORDER="1" ALIGN="center" cellpadding=1 cellspacing=0 BORDER="1">
<tr><td VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
    <? freshports_PageBannerText("NOTICE"); ?>
</TR>

<tr bgcolor="#ffffff">
<td>
  <table width=100% cellpadding=0 cellspacing=0 border=0>
  <tr valign=top>
   <td><img src="/images/warning.gif"></td>
   <td width=100%>
<? echo $error ?>
 </td>
 </tr>
 </table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br>
<?
}




echo '<table width=100% border=1 cellpadding=1 cellspacing=0 bgcolor="#AD0040">';

echo '<tr bgcolor="#AD0040">';

freshports_PageBannerText("Login");
echo '</tr>';

echo '<tr><td bgcolor="#ffffff">';
include ("./include/login.php");

echo "Your browser must allow cookies for this login to work.";

echo "</td>";
echo"
</tr>
</table>
</td>
</tr>
</TABLE>
";

echo '<br><a href="forgotten-password.php">Forgotten your password?</a>';

?>
</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table> 
</td></tr>
</table>


<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
