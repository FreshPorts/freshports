<?
//$Debug=1;
require( "./_private/commonlogin.php3");
require( "./_private/freshports.php3");

if ($submit) {

// process form

if ($Debug) {
  while (list($name, $value) = each($HTTP_POST_VARS)) {
    echo "$name = $value<br>\n";
  }
}

  $OK = 1;

  $errors = "";

  // test for existance of user id
  $Cookie = UserToCookie($UserID);

if ($Debug) {
   echo "Cookie = $Cookie\n";
}

  $sql = "select * from users where username = '$UserID'".
	 " and password = '$Password'";

if ($Debug) {
   echo "$sql\n";
}

  $result = mysql_query($sql, $db) or die('query failed ' . mysql_error());


  if(!mysql_numrows($result)) {
     $LoginFailed = 1;
  } else {

    SetCookie("visitor", $Cookie, time() + 60*60*24*120, '/');
    // Redirect browser to PHP web site
    if ($origin == "/index.php3") {
       $origin = "/";
    }
    header("Location: $origin");
    // Make sure that code below does not get executed when we redirect.
    exit;       
  }
}
?>

<html>

<head>
<title>freshports -- Login</title>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
</head>

<body bgcolor="#ffffff" link="#0000cc">
 <? include("./_private/header.inc") ?>
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
include ("./_private/login.inc.php3");

echo "</td>
</tr>
</table>
</td>
</tr>
</table>";

?></td>
</td>
  <td valign="top" width="*">
    <? include("./_private/side-bars.php3") ?>
 </td>
</tr>
</table> 
</td></tr>
</table>
<? include("./_private/footer.inc") ?>
</body>
</html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
