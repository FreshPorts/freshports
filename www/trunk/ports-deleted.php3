<?
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - recently deleted ports</title>
</head>

 <? include("./_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">
This page shows the last <? echo $MaxNumberOfPorts ?> ports to be removed the ports tree.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">freshports - recently removed ports</font></td>
  </tr>
<tr><td>
Sorry, but we've disabled this page.  Sorry about that.  With luck, it will be back in FreshPorts2.
</td></tr>
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
