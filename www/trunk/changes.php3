<?
   
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
?>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">
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
<p>May 12

<ul>
  <li>Login/Logout should now take you back to the page from which they were invoked</li>
  <li>Port description now includes <em>Also listed in</em>, <em>required to build</em>, and <em>required
    to run</em>.</li>
</ul>
</p>

<hr>
<p>These are the planned changes</p>

<p>Add a search</p>

<p>Cater for virtual categories.  You'll no doubt notice that some ports are listed in multple categories
but those categories aren't listed in your /usr/ports/ directory.  Why is that?  That is because those
categories aren't real;  they are virutal.  One day, freshports will cater for virtual categories.  The data
is all there in the database.  I just have to code it.</p>

<p>oh oh, I've found some ports which have PORTNAME values in common.  For example /usr/ports/www/netscape4-communicator.us
Now this may be a big problem which needs to be overcome.  Damn.</p>

<p>mark deleted ports as deleted</p>

</td>
  <td valign="top" width="*">
    <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
</table>
<? include("/www/freshports.org/_private/footer.inc") ?>
</body>
</html>
