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

	$Title = 'Contact';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>


<TR>
	<? echo freshports_PageBannerText('Contact'); ?>
</TR>
<TR><TD>

<P>
This is a pretty big website.  Roughly 600,000 pages as of Oct 2007.
And 1.8 million as of June 2020.

<p>
If you need help with a particular port, please go through the
FreeBSD mailing lists.

<p>
If you see a problem with the website (incorrect information, 
errors, etc), please let us know.  The best place for that is via a 
<a href="https://github.com/FreshPorts/freshports/issues">GitHub Issue</a>.

<p>
If your needs do not fall into the above categories, you can try
email: dan (at) langille.org.
</TD></TR>
</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
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
