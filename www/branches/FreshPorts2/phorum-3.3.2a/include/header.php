<?
	# $Id: header.php,v 1.1.2.7 2002-05-22 04:34:54 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	freshports_Start("the place for ports",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports",
					1);

?>

<TABLE ALIGN="center" WIDTH="<? echo $TableWidth; ?>" CELLPADDING="<? echo $BannerCellPadding; ?>" CELLSPACING="<? echo $BannerCellSpacing; ?>" BORDER="0">
<TR>
    <!-- first column in body -->
    <TD WIDTH="100%" VALIGN="top" ALIGN="center">
<TABLE BORDER="0" WIDTH="100%" CELLPADDING="1" ALIGN="center">
<TR>
    <? freshports_PageBannerText($ForumName); ?>
</TR>
<TR><TD VALIGN="top" ALIGN="center">

