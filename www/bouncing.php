<?php
	#
	# $Id: bouncing.php,v 1.2 2006-12-17 12:06:08 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$Debug = 0;

	$origin		= $_GET["origin"];
	$submit		= $_POST["submit"];
	$visitor	= $_COOKIE["visitor"];

if ($origin == "/index.php" || $origin == "") {
	$origin = "/";
}

if ($submit) {
   $sql = "update users set emailbouncecount = 0 where cookie = '" . pg_escape_string($visitor) . "'";
   if ($Debug) {
      echo $sql;
   }
      
   $result = pg_exec($db, $sql);
   if ($result) {
      if ($Debug) {
         echo "I would have taken you to '" . htmlentities($origin) . "' now, but debugging is on<br>\n";
      } else {
         // Redirect browser to PHP web site
         if ($origin == "/index.php" || $origin == '') {
            $origin = "/";
         }
         header("Location: $origin");
         exit;  /* Make sure that code below does not get executed when we redirect. */
      }
   } else {
      echo 'Something went terribly wrong there.<br>';
   }
}
   freshports_Start("your email is bouncing",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(NOBORDER); ?>

<tr>
<?php echo freshports_PageBannerText("Bouncing?  What do you mean?"); ?>
</tr>
<tr><td>

<p>You are a registered user. You have indicated that we can send you email.  This will either
be part of your watch list notifications or as an announcement.  You can view these settings
on the customization page (see the link on the right hand side of the page).</p>

<p>The problem is that the email we are sending you is not getting to you.  It is bouncing back
to us.  So we have stopped sending out messages to you.  If you wish to continue to receive such
messages, you should update your email address on the customization page.</p>
</td></tr>
<TR><TD HEIGHT="20">
</TD></TR>
<tr>
<?php echo freshports_PageBannerText("How to fix the problem"); ?>
</tr>
<tr><td>
<p>There are two things which might have caused your email to bounce:</p>
<ol>
  <li>Your email address has changed.</li>
  <li>There was a problem with your email but it's been fixed.</li>
</ol>

<p>If your email address has changed, please update it on the <a href="customize.php">customize</a> page.</p>

<p>If there was a problem with your email, such as your server was down, you can 
tell FreshPorts that you want it to start using your email address again by pressing 
the button below.</p>

</td></tr>
<tr><td><CENTER>
<form action="<?php echo $_SERVER["PHP_SELF"] . "?origin=" . htmlentities($origin) ?>" method="POST">
<input TYPE="submit" VALUE="There was a problem, but it's fixed now" name="submit">
</form>
</CENTER>
</td></tr>
</table>
</td>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
