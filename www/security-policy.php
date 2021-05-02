<?php
	#
	# $Id: contact.php,v 1.1 2007-10-21 16:59:05 dan Exp $
	#
	# Copyright (c) 2007 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = 'Security Policy';
	freshports_Start($Title, $Title, 'FreeBSD, security, policy');

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>


<TR>
	<? echo freshports_PageBannerText($Title); ?>
</TR>
<TR><TD>

<P>
I appreciate the contributions of the security reserachers who have helped out on this
website. Please be aware that this is not a for-profit website. It is a hobby. It is something I
do in my spare time. It is run as a service to the open source community.

<p>
With that in mind, do not expect to make a living getting bounties on this website. With your consent,
I will list your contributions here. Your contribution can be anonymous if you wish. I am happy to
acknowledge the work you do and the findings you present.

</TD></TR>
</TABLE>
</TD>

  <TD class="sidebar" VALIGN="top" WIDTH="*" ALIGN="center">
  <?
  echo freshports_SideBar();
  ?>
  </td>

</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
