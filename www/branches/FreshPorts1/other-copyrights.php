<?
	# $Id: other-copyrights.php,v 1.1.2.1 2002-02-07 22:06:48 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	freshports_Start(	$ArticleTitle,
					"",
					"FreeBSD, daemon copyright");

?>

<TABLE WIDTH="98%" ALIGN="center">
  <TR>
	<TD>
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
	or via email at mckusick@mckusick.com<BR>
	</P>

</BLOCKQUOTE>
	</TD>
  </TR>

<?
#	freshports_BannerSpace();
?>

</TABLE>

<?
	include("./include/footer.php")
?>

</body>
</html>

