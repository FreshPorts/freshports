<?
   # $Id: login.php3,v 1.21 2001-10-20 21:50:39 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

//echo "UserID = $UserID";
   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");
//   require("./include/getvalues.php");

//$Debug=1;

if ($Debug) echo 'origin = ' . rawurlencode($origin) . "'<br>\n";

$origin=rawurlencode($origin);

if ($submit) {
//   $Debug=1;
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
   $Cookie = UserToCookie($UserID);

   if ($Debug) {
      echo "Cookie = $Cookie<br>\n";
   }

   $sql = "select * from users where username = '$UserID'".
	  " and password = '$Password' ";

   if ($Debug) {
      echo "$sql<br>\n";
   }

   $result = mysql_query($sql, $db) or die('query failed ' . mysql_error());


   if (!mysql_numrows($result)) {
      $LoginFailed = 1;
   } else {

      if ($Debug) {
         echo "well, debug was on, so I would have taken you to '$origin'<br>\n";
      } else {
         SetCookie("visitor", $Cookie, time() + 60*60*24*120, '/');
         // Redirect browser to PHP web site
         if ($origin == "/index.php3") {
            $origin = "/";
         }
         header("Location: " . rawurldecode($origin));
         // Make sure that code below does not get executed when we redirect.
         exit;
      }
   }
}
   freshports_Start("Login",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>

<table width="100%" border=0>
 <tr>
    <td>
<table width="100%" border=0>
<tr><td valign="top" width="100%">
<?
if ($LoginFailed) {
echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>
<td>
<table width=100% border=0 cellpadding=1>
<tr bgcolor="#AD0040"><td><b><font color="#ffffff" size=+0>Login Failed!</font></b></td>
</tr>
<tr bgcolor="#ffffff">
<td>
  <table width=100% cellpadding=3 cellspacing=0 border=0>
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
<br>';
}


echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>
<td>';


echo '<table width=100% border=0 cellpadding=1 bgcolor="#AD0040">';

echo '<tr bgcolor="#AD0040"><td bgcolor="#AD0040"><font color="#ffffff" size="+2">Login Details</font></td></tr>';

echo '<tr><td bgcolor="#ffffff">';
include ("./include/login.php");

echo "</td>";

echo"
</tr>
</table>
</td>
</tr>
</table>";

echo '<br><a href="forgotten-password.php3">Forgotten your password?</a>';

?></td>
</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table> 
</td></tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
