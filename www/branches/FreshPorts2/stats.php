<?
   # $Id: stats.php,v 1.2.2.1 2002-01-02 02:53:50 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");
   require("./include/getvalues.php");


   freshports_Start("Statistics",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>
<table width="100%" border="0">
<tr><td colspan="2">
<B>The graphs are broken.  A hazard of the move. I'll work on it later.</B>
<BR>
<BR>
Eventually, I'd like to modify these graphs so you can click on a port and be taken to
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
<?
/*
#<p><a href="<? echo $PHP_SELF ?>?graph=1">Most watched ports</a></p>
#<p><a href="<? echo $PHP_SELF ?>?graph=2">Top committers</a></p>
#<p><a href="<? echo $PHP_SELF ?>?graph=3">Biggest commits</a></p>
*/
?>
</td>
<td align="center">
<?
#if ($graph) {
#   echo '<IMG src="graphics.php?graph=' . $graph . '" width="500" height="475">';
#}
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
