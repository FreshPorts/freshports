<?
	# $Id: header.php,v 1.1.2.2 2002-02-16 23:51:20 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require("../include/common.php");
	require("../include/freshports.php");
	require("../include/databaselogin.php");

	require("../include/getvalues.php");

	freshports_Start("the place for ports",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports",
					1);

?>

<TABLE VALIGN="top" ALIGN="center" WIDTH="<? echo $TableWidth; ?>" CELLPADDING="<? echo $BannerCellPadding; ?>" CELLSPACING="<? echo $BannerCellSpacing; ?>" BORDER="0">
<TR>
    <!-- first column in body -->
    <TD WIDTH="100%" VALIGN="top" ALIGN="center">
<TABLE BORDER="0" WIDTH="100%" CELLPADDING="0" ALIGN="center">
<TR><TD VALIGN="top">

<div class="PhorumForumTitle"><b><?php echo $ForumName; ?></b></div>