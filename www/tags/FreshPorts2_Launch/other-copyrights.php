<?
	# $Id: other-copyrights.php,v 1.1.4.4 2002-05-22 04:30:26 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	freshports_Start(	$ArticleTitle,
					"",
					"FreeBSD, daemon copyright");

?>

<TABLE WIDTH="<? echo $TableWidth; ?>%" ALIGN="center" BORDER="0">
  <TR>
	<TD VALIGN="top">
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

  <td valign="top" width="*">
   <? include($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php") ?>
  </TD>
  </TR>

</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>

