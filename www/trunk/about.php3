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
<table width="100%" border="0">
<tr><td colspan="2">
<font size="+2">about this site</font>
</td></tr>
<tr>
<td valign="top" width="100%">
<table width="100%" border="0">

<tr><td bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">
what's a port?
</font></td>
</tr>
</tr><td>

<p>A port is the term used to describe a collection of files which makes it extremely
easy to install an application.  As it says in the <a href="http://www.freebsd.org/ports/">
FreeBSD Ports description</a>: <em>Installing an application is as simple as downloading 
the port, unpacking it and typing <b>make</b> in the port directory</em>. If you want an application, 
the port is the Way To Go(TM)</p>

<p>So off you go to the ports tree to install your favourite port.  It's quite easy. It's simple.
And you love that new application.  And you want to know when the port is updated.  That's where
we come in.</p>

</td></tr>
<tr><td height="10"></td></tr>
<tr><td bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">
what is freshports?
</font></td>
</tr>
</tr><td>

<p>freshports lists the change made to the ports tree. If you wish, freshports can email you 
when your favourite port has been updated.</p>

</td></tr>
<tr><td height="10"></td></tr><tr><td bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">
OK, whose bright idea was this?
</font></td>
</tr>
</tr><td>
<p>This site was created by Dan Langille.  His other web feats include 
<a href="http://www.freebsddiary.org/">The FreeBSD Diary</a>, <a 
href="http://www.racingsystem.com">The Racing System</a>, and an ability
to avoid reading the inane comments on <a href="http://slashdot.org">slashdot</a>.
But Dan didn't create the site all by himself.  Have a look at <a href="authors.php3">
About the Authors</a> for details of who else helped.</p>
</table>
</td>
  <td valign="top" width="*">
    <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
</table>
<? include("/www/freshports.org/_private/footer.inc") ?>
</body>
</html>
