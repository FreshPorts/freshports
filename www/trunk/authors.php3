<?
   # $Id: authors.php3,v 1.12 2001-10-02 17:35:58 dan Exp $
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
<html>
<?
#require( "./include/commonlogin.php3");      
#require( "./include/getvalues.php3");      
#require( "./include/freshports.php3");    
?>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->

<title>freshports - authors</title>

</head>
<? include("./include/header.php") ?>

<table width="100%">
<tr><td>
<h2>about the authors</h2>
<p>Dan Langille thought up the idea, found the data sources, bugged people to 
write scripts, and did the html and database work. But he certainly didn't 
do it alone.</p>

<p>Olaf wrote did the perl script for the log catcher.</p>

<p>icmpecho wrote the awk code for the log catcher and the log munger.</p>

<p>Adriel helped me with perl syntax.</p>

<p>Acme talked over data sources with me.</p>

<p>John Polstra and Satoshi Asami provided insight into cvs and ports as well
as encouragement.</p>

<p>Laz hung around, criticized, and suggested security improvments.</p>

<p>halflife did some prototype coding for me.</p>

<p>David Bushong did a FreshBSD site which is a freshmeat-look site.</p>

<p>lzh on undernet #perl helped me with my perl knowledge.  Some of his examples 
form the basis for some of the most important parts of the system.  Aquitaine
also showed me the PERL dbi->quote() function.</p>

<p>John Beige did the logo you see at the top of the page.<p>

</p>Wolfram Schneider's <a href="http://www.freebsd.org/cgi/ports.cgi">FreeBSD Ports Changes</a>
page provided much of the basis for this site.</p>

<p>Jay gave me the box on which FreshPorts runs.  Thanks.</p>

<p>And various people on undernet's #nz.general and #freebsd helped me with 
scripts and ideas.  That's not to mention that channel on efnet which I won't 
name just so it stays a secret.</p>

<p>I can usually be found via IRC in #freebsd and #freshports on undernet.</p>
</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
