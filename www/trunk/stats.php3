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
<title>freshports</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">
 <? include("/www/freshports.org/_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">This is the first attempt at any type of stats.
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
   <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
</table>
</tr>
</table>
<? include("/www/freshports.org/_private/footer.inc") ?>
</body>
</html>
