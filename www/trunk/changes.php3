<?
   # $Id: changes.php3,v 1.14 2001-10-02 17:35:58 dan Exp $
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

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - changes</title>
</head>

<? include("./include/header.php") ?>
<table width="100%" border="0">
<tr><td valign="top">
<table width="100%" border="0" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
  <tr>
    <td bgcolor="#AD0040" height="22"><font color="#FFFFFF" size="+1">changes</font></td>
  </tr>
<tr><td>
This page contains the changes made and the changes planned.  Please put your wish 
lists in the <a href="http://freshports.org/phorum/list.php?f=3">feedback phorum</a>.
</td></tr>
<tr><td height="20"></td></tr>
<tr><td>
  <tr>
    <td bgcolor="#AD0040" ><font color="#FFFFFF" size="+1">July 25</font></td>
  </tr>
<tr><td>
<ul>
   <li>Added in the legal notice page.  It got lost somewhere.</li>
   <li>Change the user entry screens to prevent silly buggers from playing silly buggers with the new-user,
       customize, and search screens.  Thanks to schematic for bringing this to my attention.</li>
   <li>Set a deleted port to be undeleted if it is reactivated.  gtm 0.4.5 ftp, Tuesday, Jul 25, 01:40, 
       was the commit which prompt this problem to my attention [again, I had noticed it once before, but 
       thought I had already dealt with it].</li>
</ul>
</td></tr>
<tr><td height="20"></td></tr>
<tr><td>
  <tr>       
    <td bgcolor="#AD0040" ><font color="#FFFFFF" size="+1">July 24</font></td>
  </tr>
<tr><td>
<ul>
   <li>Added a facility to tell you if email to you from FreshPorts is bouncing.</li>
   <li>I've neglected this page for too long.   I missed out the upgrade to FreshPorts 1.1...</li>
</ul>
</td></tr>
<tr><td height="20"></td></tr>
<tr><td>
  <tr>       
    <td bgcolor="#AD0040" ><font color="#FFFFFF" size="+1">May 16</font></td>
  </tr>
<tr><td>
<ul>
   <li>Added <img src="/images/new.gif" width=28 height=11 alt="new!" hspace=2 align=absmiddle> to each port which is new to the tree.</li>
</ul>
</td></tr>
<tr><td height="20"></td></tr>
<tr><td>
  <tr>
    <td bgcolor="#AD0040" ><font color="#FFFFFF" size="+1">May 15</font></td>
  </tr>
<tr><td>
<ul>
   <li>Change the link in the side bar so Delete ports points to the 
   <a href="ports-deleted.php3">deleted ports page</a> and not the
   <a href="ports-new.php3">new ports page</a>. Did any of you notice?</li>

   <li>Fixed a problem with the watch ports page which displayed an error
       but correctly updated your watch list.  Sorry about that.</li>
</ul>
</td></tr>
<tr><td height="20"></td></tr>
<tr><td>
  <tr>       
    <td bgcolor="#AD0040" ><font color="#FFFFFF" size="+1">May 14</font></td>
  </tr>
<tr><td>

<ul>
  <li>Added a <a href="ports-deleted.php3">deleted ports page</a>.</li>
  <li>Mail notifications should now be working. The daily mailout had it's first run about an hour ago.
      Weekly notices go out on the 9th.  Fortnightly notices go out on the 9th and 23rd.  And monthly
      notices go out on the 23rd.</li>
  <li>A news feed is available at <a href="http://freshports.org/news.php3">http://freshports.org/news.php3</a>.
      And a simplified version is at <a href="http://freshports.org/news.txt">http://freshports.org/news.txt</a>.</li>
  <li>if someone goes to watch.php3, watch-categories.php3, or customize.php3, it should redirect you to the
      login screen, then take you back after you successfully login.</li>
</ul>
</p>
</td></tr>
<tr><td height="20"></td></tr>    
<tr><td>
  <tr>       
    <td bgcolor="#AD0040" ><font color="#FFFFFF" size="+1">May 13</font></td>
  </tr>
<tr><td>

<ul>
  <li>Added a simple <a href="search.php3">search</a> screen.</li>
<li>mark deleted ports as deleted</li>
</ul>
</p>

</td></tr>
<tr><td height="20"></td></tr>    
<tr><td>
  <tr>       
    <td bgcolor="#AD0040" ><font color="#FFFFFF" size="+1">May 12</font></td>
  </tr>
<tr><td>

<ul>
  <li>Login/Logout should now take you back to the page from which they were invoked</li>
  <li>Port description now includes <em>Also listed in</em>, <em>required to build</em>, and <em>required
    to run</em>.</li>
</ul>
</p>

</td></tr>
<tr><td height="20"></td></tr>    
<tr><td>
  <tr>       
    <td bgcolor="#AD0040" ><font color="#FFFFFF" size="+1">These are the planned changes</font></td>
  </tr>
<tr><td>
<ul>
<li>Cater for virtual categories.  You'll no doubt notice that some ports are listed in multple categories
but those categories aren't listed in your /usr/ports/ directory.  Why is that?  That is because those
categories aren't real;  they are virutal.  One day, freshports will cater for virtual categories.  The data
is all there in the database.  I just have to code it.</li>
<li>remember to grab packages.exists when that changes</li>
<li>allow ports to be {added to [and/or] removed from} personal watch lists from any page</li>
</ul>
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
