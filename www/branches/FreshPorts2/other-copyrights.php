<?php
	#
	# $Id: other-copyrights.php,v 1.1.4.12 2005-01-22 14:48:52 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start(	$ArticleTitle,
					'',
					'FreeBSD, daemon copyright');

?>

	<?php echo freshports_MainTable(); ?>

  <TR>
	<TD VALIGN="top" WIDTH="100%">
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

</BODY>
</HTML>

