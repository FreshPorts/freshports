<?
   # $Id: whatnext.php3,v 1.4 2001-09-28 00:05:40 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");


   freshports_Start("title",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - what do you want done next?</title>
</head>
<? include("./include/header.php") ?>
<table width="100%">
<tr><td valign="top" width="100%">
<h2>What do you want done next?</h2>

<p>The following is the list of things to do.  Have a read and vote for the one that you want done first.
I will not necessarily do them in that order, but at least I'll know what you want.
<ul>
   <li><font size="+1">virtual categories</font> (if you compare <a href="categories.php">categories.php</a> and 
       <a href="http://freebsd.org/ports/">http://freebsd.org/ports</a> you'll find categories
       at FreeBSD which aren't listed in FreshPorts (e.g. Windowmaker).  These are virtual categories.
       You won't find /usr/ports/windowmaker in your ports tree.  All ports reside in a primary category (i.e. the 
       category in which you find them on your disk) which must be a physical category.  But a port can also reside
       in other categories, which may be virtual.  Virtual categories appear to be a convenient method for viewing
       similar ports)</li>
   <li><font size="+1">page customization</font> (allow users to specify page customization features such as * number of ports on main page * 
       number of days a brand new port is marked as <img src="/images/new.gif" width=28 height=11 alt="new!" hspace=2 align=absmiddle> 
       * when listing all ports in a category, max number of ports to show per page * user specified date format)</li>
   <li><font size="+1">stats page</font> (various <em>useful</em> statistics such as * most watched port * most viewed category * most view port * 
       most prolific committer * most updated category * most updated port * etc...)</li>
</ul>
</p>
<blockquote>
<blockquote>
<p>
What do you want done next?
<? 
   require('phpPolls/phpPollConfig.php3');
   require('phpPolls/phpPollUI.php3');
   poll_generateUI(1, "../thanks.php3");
?>
</p>
</blockquote>
</blockquote>
</td>
<td valign="top">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
