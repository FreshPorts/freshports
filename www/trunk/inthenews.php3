<?
   # $Id: inthenews.php3,v 1.8 2001-10-02 17:35:59 dan Exp $
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
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - in the news</title>
</head>

<? include("./include/header.php") ?>
<table width="100%">
<tr><td valign="top">
<font size="+2">in the news</font> 
<p>This page is just a place for me to record the freshports articles which appear
on other sites.  Links are recorded in reverse chronological order (i.e. newest first).  If you spot an article which 
is not listed here, please <a href="http://freshports.org/phorum/list.php?f=3">let me know</a>.
</p>
<p>
BSD Today - <a href="http://www.bsdtoday.com/2000/May/News146.html">Keeping track of your favorite ports</a>
</p>

<p>
slashdot - <a href="http://slashdot.org/article.pl?sid=00/05/10/1014226">BSD: FreshPorts</a>
</p>

Daily Daemon News - <a href="http://daily.daemonnews.org/view_story.php3?story_id=889">freshports site announncement</a>

</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
