<?
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");

if ($submit) {
   $sql = "update users set emailbouncecount = 0 where cookie = '$visitor'";
   if ($Debug) {
      echo $sql;
   }
      
   $result = mysql_query($sql);    
   if ($result) {
      if ($Debug) {
         echo "I would have taken you to '$origin' now, but debugging is on<br>\n";
      } else {
         // Redirect browser to PHP web site
         if ($origin == "/index.php3") {
            $origin = "/";
         }
         header("Location: $origin");
         exit;  /* Make sure that code below does not get executed when we redirect. */
      }
   } else {
      $errors .= 'Something went terribly wrong there.<br>';
   }
}

?>
<html>
<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - your email is bouncing</title>
</head>

<? include("./_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">
<font size="+2">your email is bouncing</font>
</td></tr>
<tr>
<td valign="top" width="100%">
<table width="100%" border="0">

<tr><td bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">
bouncing?  what do you mean?
</font></td>
</tr>
</tr><td>

<p>You are a registered user. You have indicted that we can send you email.  This will either
be part of your watch list notifications or as an announcement.  You can view these settings
on the customization page (see the link on the right hand side of the page).</p>

<p>The problem is that the email we are sending you is not getting to you.  It is bouncing back
to us.  So we have stopped sending out messages to you.  If you wish to continue to receive such
messages, you should update your email address on the customization page.</p>
</tr><td>
<tr><td bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">
How to fix the problem
</font></td>
</tr>
<tr><td>
<p>There are two things which might have caused your email to bounce:</p>
<ol>
  <li>Your email address has changed.</li>
  <li>There was a problem with your email but it's been fixed.</li>
</ol>

<p>If your email address has changed, please update it on the <a href="customize.php3">customize</a> page.</p>

<p>If there was a problem with your email, such as your server was down, you can 
tell FreshPorts that you want it to start using your email address again by pressing 
the button below.</p>

</td></tr>
<tr><td><center>
<form action="<?php echo $PHP_SELF . "?origin=" . $origin ?>" method="POST">
<input TYPE="submit" VALUE="There was a problem, but it's fixed now" name="submit">
</form>
</centre>
</td></tr>
</table>
</td>
  <td valign="top" width="*">
    <? include("./_private/side-bars.php3") ?>
 </td>
</tr>
</table>
<? include("./_private/footer.inc") ?>
</body>
	</html>
