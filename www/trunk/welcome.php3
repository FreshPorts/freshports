<?
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");
?>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports -- New User</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<html>
<body>
<? include("./_private/header.inc") ?>
<table width="100%" border="0">
<tr><td valign="top">
<table width="100%" border="0" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
  <tr>
    <td bgcolor="#AD0040" height="32"><font color="#FFFFFF" size="+1">Account created</font></td>
  </tr>
  <tr>
    <td height="20">Your account
<script language="php">
echo '"' . $UserName . '"'; </script> has been
    created. &nbsp; If you wish to change your settings, please follow the link on the <a
    href="">home page</a>.</td>
  </tr>
</table>
</td>
  <td valign="top" width="*">
    <? include("./_private/side-bars.php3") ?>
 </td>
</tr>
</table>
<? include("./_private/footer.inc") ?>
</body>
</html>
