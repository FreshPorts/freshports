<?
   # $Id: thanks.php3,v 1.4 2001-10-02 17:36:01 dan Exp $
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

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - what do you want done next?</title>
</head>

<? include("./include/header.php") ?>
<table width="100%">
<tr><td valign="top" width="100%">
<h2>Thanks for voting.</h2>

<p>I'll let you know what's next.  Cheers</p>
</td>
<td valign="top">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
<? include("./include/footer.php") ?>
</body>
</html>
