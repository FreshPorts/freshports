<?
   # $Id: welcome.php3,v 1.12 2001-10-20 21:50:41 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");
   require("./include/getvalues.php");


   freshports_Start("New User",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>
<table width="100%" border="0">
<tr><td valign="top">
<table width="100%" border="0" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
  <tr>
    <td bgcolor="#AD0040" height="32"><font color="#FFFFFF" size="+1">Account created</font></td>
  </tr>
  <tr>
    <td height="20">Your account
<script language="php">
echo '"' . $UserName . '"'; </script> has been
    created. &nbsp; If you wish to change your settings, please use the customize link at right.
   </td>
  </tr>
  <tr><td>Click <a href="<? echo $origin?>">here</a> to return to your previous page</td></tr>
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
