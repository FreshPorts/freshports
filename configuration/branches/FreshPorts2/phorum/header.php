<?php if ( !defined( "_COMMON_PHP" ) ) return; ?>
<?php
	#
	# $Id: header.php,v 1.1.2.1 2003-09-04 15:01:59 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	freshports_HTML_start();
	echo "<HEAD>\n";
	echo freshports_HEAD_Title($ForumName);

	echo freshports_HEAD_charset();

	freshports_style();

	echo '	<link rel="STYLESHEET" type="text/css" href="' . phorum_get_file_name("css") . '">';

	echo freshports_HEAD_main_items();
#	freshports_Start("the place for ports",
#					"$FreshPortsName - new ports, applications",
#					"FreeBSD, index, applications, ports",
#					1);

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

?>


	<meta name="PhorumVersion" content="<?php echo $phorumver;   ?>">
	<meta name="PhorumDB"      content="<?php echo $DB->type;    ?>">
	<meta name="PHPVersion"    content="<?php echo phpversion(); ?>">

</HEAD>

<?php
	freshports_body();
	if ($ShowAds) {
		BurstMediaCode();
		if ($BannerAd) {
			echo "<div align=\"center\">\n";
			if ($UsePHPAdsNew) {
				require_once($_SERVER['DOCUMENT_ROOT'] . '/phpPgAds/phpadsnew.inc.php');
				if (!isset($phpAds_context)) $phpAds_context = array();
				$what = 'top,+728x90,n=top';
				$phpAds_raw = view_raw($what, 0, '', '', '0', $phpAds_context);
				echo $phpAds_raw['html'];
			} else {
				BurstMediaAd();
			}
			echo "</div>\n";
		}
	}
	freshports_Logo();
	freshports_navigation_bar_top();
?>


<TABLE ALIGN="center" WIDTH="<? echo $TableWidth; ?>" CELLPADDING="<? echo $BannerCellPadding; ?>" CELLSPACING="<? echo $BannerCellSpacing; ?>" BORDER="0">
<TR>
    <!-- first column in body -->
    <TD WIDTH="100%" VALIGN="top" ALIGN="center">

