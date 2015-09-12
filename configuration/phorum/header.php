<?php if ( !defined( "_COMMON_PHP" ) ) return; ?>
<?php
	#
	# $Id: header.php,v 1.2 2006-12-17 11:39:37 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#

	require($_SERVER['DOCUMENT_ROOT'] . "/../include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/../include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/../include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/../include/getvalues.php");

	freshports_Start('', '', '', 1);

function custom_BannerForum($ForumName, $article_id) {
	$TableWidth = "100%";

	echo '<TABLE WIDTH="' . $TableWidth . '" ALIGN="center"  cellspacing="0">';
echo "
  <TR>
    <TD>
    <div class=\"section\">$ForumName</div>
    </TD>
  </TR>
";
	echo '</TABLE>';
}

  if ($BannerAd) {
    echo "\n<CENTER>\n";
    echo Ad_728x90PhorumTop();
    echo "</CENTER>\n\n";
    }

?>


<TABLE ALIGN="center" WIDTH="<? echo $TableWidth; ?>" CELLPADDING="<? echo $BannerCellPadding; ?>" CELLSPACING="<? echo $BannerCellSpacing; ?>" BORDER="0">
<TR>
    <!-- first column in body -->
    <TD WIDTH="100%" VALIGN="top" ALIGN="center">

