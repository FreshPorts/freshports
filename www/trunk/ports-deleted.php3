<?
   # $Id: ports-deleted.php3,v 1.12 2001-10-02 17:36:00 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");
   require("./include/getvalues.php");


   freshports_Start("title",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>
<?
#require( "./include/commonlogin.php3");
#require( "./include/getvalues.php3");
#require( "./include/freshports.php3");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - recently deleted ports</title>
</head>

 <? include("./include/header.php") ?>
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
   <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
