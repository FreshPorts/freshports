<?
	# $Id: show-help.php,v 1.1.2.1 2002-06-12 03:06:34 dan Exp $
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

<TABLE WIDTH="<? echo $TableWidth; ?>" ALIGN="center" BORDER="0">
  <TR>
	<TD VALIGN="top" WIDTH="100%">
	This is where you'd see a bit of help on the topic you selected.
	</TD>

  <?
  freshports_SideBar();
  ?>

  </TR>

</TABLE>


<?
freshports_ShowFooter();
?>

</BODY>
</HTML>

