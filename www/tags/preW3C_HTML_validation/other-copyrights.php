<?
	# $Id: other-copyrights.php,v 1.1.4.2 2002-02-21 06:42:20 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("./include/getvalues.php");

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
	</P>

</BLOCKQUOTE>
	</TD>

  <td valign="top" width="*">
   <? include("./include/side-bars.php") ?>
  </TD>
  </TR>

</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>

