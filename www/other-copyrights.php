<?php
	#
	# $Id: other-copyrights.php,v 1.2 2006-12-17 12:06:13 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	# not just this file's mtime: the version shown below changes when other files do
	freshports_ConditionalGetUnix(freshports_VersionLastModified());

	freshports_Start(	_('Other copyrights'),
					'',
					'FreeBSD, daemon copyright');

?>

	<?php echo freshports_MainTable(); ?>

  <tr>
	<td class="content">
	<P>
	<?php echo _('The copyright on the daemon you see in the website logo is as follows:'); ?>
	</P>

<blockquote>
	<P>
	BSD Daemon Copyright 1988 by Marshall Kirk McKusick.<br>
	All Rights Reserved.<br>
<br>
	Permission to use the daemon may be obtained from:<br>
<blockquote>
		Marshall Kirk McKusick<br>
		1614 Oxford St<br>
		Berkeley, CA 94709-1608<br>
		USA<br>
</blockquote>
	or via email at mckusick&#64;mckusick.com<br>

</blockquote>
	</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

  </tr>

</table>

<?php
echo freshports_ShowFooter();
echo freshports_PackageVersionComment();
?>

</BODY>
</HTML>
