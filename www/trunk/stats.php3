<?
   # $Id: stats.php3,v 1.8 2001-10-02 17:36:00 dan Exp $
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
<title>freshports</title>
</head>

 <? include("./include/header.php") ?>
<table width="100%" border="0">
<tr><td colspan="2">Eventually, I'd like to modify these graphs so you can click on a port and be taken to
its description.  Anyone willing to do that should let me know.  Note: these stats only cover the period
since FreshPorts began and are updated once per day.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td colspan="5" bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">most watched ports</font></td>
  </tr>
<tr>
<td valign="top">
<p><a href="<? echo $PHP_SELF ?>?graph=1">Most watched ports</a></p>
<p><a href="<? echo $PHP_SELF ?>?graph=2">Top committers</a></p>
<p><a href="<? echo $PHP_SELF ?>?graph=3">Biggest commits</a></p>
</td>
<td align="center">
<?
if ($graph) {
   echo '<IMG src="graphics.php?graph=' . $graph . '" width="500" height="475">';
}
?>
</td></tr>
</table>
</td>
  <td valign="top" width="*">
   <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
