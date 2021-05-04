<?php
	#
	# $Id: other-copyrights.php,v 1.2 2006-12-17 12:06:13 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	freshports_Start(	'Other copyrights',
					'',
					'FreeBSD, daemon copyright');

?>

	<?php echo freshports_MainTable(); ?>

  <TR>
	<td class="content">
	<P>
	The copyright on the daemon you see in the website logo is as follows:
	</P>

<BLOCKQUOTE>
	<P>
	BSD Daemon Copyright 1988 by Marshall Kirk McKusick.<BR>
	All Rights Reserved.<BR>
<BR>
	Permission to use the daemon may be obtained from:<BR>
<BLOCKQUOTE>
		Marshall Kirk McKusick<BR>
		1614 Oxford St<BR>
		Berkeley, CA 94709-1608<BR>
		USA<BR>
</BLOCKQUOTE>
	or via email at mckusick&#64;mckusick.com<BR>

</BLOCKQUOTE>
	</td>

  <td class="sidebar">
	<?
	echo freshports_SideBar();
	?>
  </td>

  </TR>

</TABLE>

<?
echo freshports_ShowFooter();
?>

</BODY>
</HTML>

