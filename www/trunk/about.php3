<?
   
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
//require( "/www/freshports.org/_private/freshports.php3");
?>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - about</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<html>
<body>
<? include("/www/freshports.org/_private/header.inc") ?>
<table width="100%">
<tr><td>
<h2>what's a port?</h2>

<p>A port is the term used to describe a collection of files which makes it extremely
easy to install an application.  As it says in the <a href="http://www.freebsd.org/ports/">
FreeBSD Ports description</a>:<em>Installing an application is as simple as downloading 
the port, unpacking it and typing make in the port directory</em>. So if you want application, 
the port is the Way To Go(TM)<p>

<p>So off you go to the ports tree to install your favourite port.  It's quite easy. It's simple.
And you love that new application.  And you want to know when the port is updated.  That's where
we come in.</p>

<h2>what is freshports?</h2>

<p>freshports lists the change made to the ports tree. If you wish, freshports can email you 
when your favourite port has been updated.</p>

<h2>OK, whose bright idea was this?</h2>
<p>This site was created by Dan Langille.  His other web feats include 
<a href="http://www.freebsddiary.org/">The FreeBSD Diary</a>, <a 
href="http://www.racingsystem.com">The Racing System</a>, and an ability
to avoid reading the inane comments on <a href="http://slashdot.org">slashdot</a>.
But Dan didn't create the site all by himself.  Have a look at <a href="authors.php3">
About the Authors</a> for details of who else helped.</p>

  <td valign="top" width="*">
    <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
</table>
<? include("/www/freshports.org/_private/footer.inc") ?>
</body>
</html>
