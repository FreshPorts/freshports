<?
   
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
?>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - changes</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<html>
<body>
<? include("/www/freshports.org/_private/header.inc") ?>
<table width="100%">
<tr><td valign="top">
<h2>changes</h2>

<p>This is where I will attempt to list the changes to the freshports website.  Please put your wish 
lists in the <a href="http://freshports.org/phorum/list.php?f=3">feedback phorum</a>.
</p>
<p>May 14

<ul>
  <li>Added a <a href="ports-deleted.php3">deleted ports page.</a></li>
  <li>Mail notifications should now be working. The daily mailout had it's first run about an hour ago.
      Weekly notices go out on the 9th.  Fortnightly notices go out on the 9th and 23rd.  And monthly
      notices go out on the 23rd.</li>
  <li>A news feed is available at <a href="http://freshports.org/news.php3">http://freshports.org/news.php3</a>.
      And a simplified version is at <a href="http://freshports.org/news.txt">http://freshports.org/news.txt</a>.</li>
</ul>
</p>
<p>May 13

<ul>
  <li>Added a simple <a href="search.php3">search</a> screen.</li>
<li>mark deleted ports as deleted</li>
</ul>
</p>


<p>May 12

<ul>
  <li>Login/Logout should now take you back to the page from which they were invoked</li>
  <li>Port description now includes <em>Also listed in</em>, <em>required to build</em>, and <em>required
    to run</em>.</li>
</ul>
</p>

<hr>
<p>These are the planned changes</p>

<ul>
<li>Fix outgoing email. It appears the mail server isn't sending out messages. Gotta fix that</li>
<li>Cater for virtual categories.  You'll no doubt notice that some ports are listed in multple categories
but those categories aren't listed in your /usr/ports/ directory.  Why is that?  That is because those
categories aren't real;  they are virutal.  One day, freshports will cater for virtual categories.  The data
is all there in the database.  I just have to code it.</li>

<li>oh oh, I've found some ports which have PORTNAME values in common.  For example /usr/ports/www/netscape4-communicator.us. 
Now this may be a big problem which needs to be overcome.  Damn.</li>

<li>remember to grab packages.exists when that changes</li>
<li>allow ports to be {added to [and/or] removed from} personal watch lists from any page</li>
<li>if someone goes to watch.php3, watch-categories.php3, or customize.php3, it should redirect you to the
    login screen, then take you back after you successfully login.</li>
</ul>

</td>
  <td valign="top" width="*">
    <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
</table>
<? include("/www/freshports.org/_private/footer.inc") ?>
</body>
</html>
