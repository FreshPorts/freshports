<?php
	#
	# $Id: freshports.php,v 1.4.2.216 2005-02-21 01:18:01 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/burstmedia.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../configuration/freshports.conf.php');

	if (IsSet($ShowAnnouncements)) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');
	}
#
# special HTMLified mailto to foil spam harvesters
#
DEFINE('MAILTO',                '&#109;&#97;&#105;&#108;&#116;&#111;');
DEFINE('COPYRIGHTYEARS',        '2000-2005');
DEFINE('URL2LINK_CUTOFF_LEVEL', 0);
DEFINE('FAQLINK',               'faq.php');
DEFINE('PORTSMONURL',			'http://portsmon.firepipe.net/portoverview.py');
DEFINE('NOBORDER',              '0');
DEFINE('BORDER',                '1');

if ($Debug) echo "'" . $_SERVER['DOCUMENT_ROOT'] . '/../classes/watchnotice.php<BR>';

require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watchnotice.php');

function freshports_MainTable() {
	GLOBAL $TableWidth;

	return '<TABLE WIDTH="' . $TableWidth . '" BORDER="0">
';
}

function freshports_MainContentTable($Border=1, $ColSpan=1) {
	return '<TABLE WIDTH="100%" border="' . $Border . '" CELLSPACING="0" CELLPADDING="8">
' . PortsFreezeStatus($ColSpan);
}

function  freshports_ErrorContentTable() {
	echo '<TABLE WIDTH="100%" BORDER="1" ALIGN="center" CELLPADDING="1" CELLSPACING="0">
';
}


function PortsFreezeStatus($ColSpan=1) {
	#
	# this function checks to see if there is a port freeze on.
	# if there is, it returns text that indicates same.
	# otherwise, it returns an empty string.
	#
	$result = '';

	if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/../dynamic/PortsFreezeIsOn")) {
		$result = '
<tr>' . freshports_PageBannerText('There is a PORTS FREEZE in effect!', $ColSpan) . '</tr>
<tr><td';
		if ($ColSpan > 1) {
			$result .= ' colspan="' . $ColSpan . '"';
		}
		$result .= '>
<p>A <a href="http://www.freebsd.org/doc/en/articles/committers-guide/ports.html#AEN1390">ports freeze</a>
 means that commits will be few and far between and only by approval.
</p>
</td></tr>
';
	}

	return $result;
}


function freshports_link_to_port($CategoryName, $PortName) {

	$HTML .= '<a href="/' . $CategoryName . '/">' . $CategoryName . '</a>/';
	$HTML .= '<a href="/' . $CategoryName . '/' . $PortName . '/">' 
	            . $PortName . '</a>';

	return $HTML;
}

#
# These are the pages which take NOINDEX and NOFOLLOW meta tags
#


function freshports_IndexFollow($URI) {
	$NOINDEX["/index.php"]				= 1;
	$NOINDEX["/date.php"]				= 1;

	$NOINDEX['/ports-broken.php']		= 1;
	$NOINDEX['/ports-deleted.php']		= 1;
	$NOINDEX['/ports-forbidden.php']	= 1;
	$NOINDEX['/ports-deprecated.php']	= 1;
	$NOINDEX['/ports-ignore.php']		= 1;
	$NOINDEX['/ports-new.php']			= 1;

	$NOFOLLOW["/date.php"]				= 1;
	$NOFOLLOW['/ports-deleted.php']		= 1;
	$NOFOLLOW['/graphs.php']			= 1;
	$NOFOLLOW['/ports-deleted.php']		= 1;
	$NOFOLLOW['/commit.php']		= 1;

	# well, OK, so it may not be a URI... but it's close

	$HTML = '';

	GLOBAL $g_NOFOLLOW;
	GLOBAL $g_NOINDEX;

	$l_NoFollow = 0;
	$l_NoIndex  = 0;

	if (IsSet($NOFOLLOW[$URI]) || $g_NOFOLLOW) $l_NoFollow = 1;
	if (IsSet($NOINDEX[$URI])  || $g_NOINDEX)  $l_NoIndex  = 1;

	if ($l_NoFollow || $l_NoIndex) {
		$HTML .= '	<meta name="robots" content="';
		if ($l_NoIndex) {
			$HTML .= 'noindex';
			if ($l_NoFollow) {
				$HTML .= ',';
			}
		}

		if ($l_NoFollow) {
			$HTML .= 'nofollow';
		}

		$HTML .= '">' . "\n";
	}

	return $HTML;
}

function freshports_BannerSpace() {

return "
  <TR>
    <TD height=\"10\"></TD>
  </TR>
";

}


function freshports_WatchListCount_Icon() {
	return '<IMG SRC="/images/sum.gif" ALT="on this many watch lists" TITLE="on this many watch lists" BORDER="0" WIDTH="12" HEIGHT="17" ALIGN="middle">';
}

function freshports_WatchListCount_Icon_Link() {
	return '<a href="/' . FAQLINK . '#watchlistcount">' . freshports_WatchListCount_Icon() . '</a>';
}

function freshports_Files_Icon() {
	return '<IMG SRC="/images/logs.gif" ALT="files touched by this commit" TITLE="files touched by this commit" BORDER="0" WIDTH="17" HEIGHT="20">';
}

function freshports_Refresh_Icon() {
	return '<IMG SRC="/images/refresh.gif" ALT="Refresh" TITLE="Refresh - this port is being refreshed, or make failed to run error-free." BORDER="0" WIDTH="15" HEIGHT="18">';
}

function freshports_Refresh_Icon_Link() {
	return '<a href="/' . FAQLINK . '#refresh">' . freshports_Refresh_Icon() . '</a>';
}

function freshports_Deleted_Icon() {
	return '<IMG SRC="/images/deleted.gif" ALT="Deleted" TITLE="Deleted" BORDER="0" WIDTH="21" HEIGHT="18">';
}

function freshports_Deleted_Icon_Link() {
	return '<a href="/' . FAQLINK . '#deleted">' . freshports_Deleted_Icon() . '</a>';
}

function freshports_HoverTextCleaner($Prefix, $HoverText) {
	if ($HoverText == '') {
		$HoverText = $Prefix;
	} else {
		# use the text provided, but remove any quotes,
		# which will mess up the HTML
		$HoverText = $Prefix . ': ' . htmlspecialchars($HoverText);
	}

	return $HoverText;
}

function freshports_Forbidden_Icon($HoverText = '') {
	$Alt       = "Forbidden";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<IMG SRC="/images/forbidden.gif" ALT="' . $Alt . '" TITLE="' . $HoverText . '" BORDER="0" WIDTH="20" HEIGHT="20">';
}

function freshports_Forbidden_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#forbidden">' . freshports_Forbidden_Icon($HoverText) . '</a>';
}

function freshports_Broken_Icon($HoverText = '') {
	$Alt       = "Broken";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<IMG SRC="/images/broken.gif" ALT="' . $Alt . '" TITLE="' . $HoverText . '" BORDER="0" WIDTH="17" HEIGHT="16">';
}

function freshports_Broken_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#broken">' . freshports_Broken_Icon($HoverText) . '</a>';
}

function freshports_Deprecated_Icon($HoverText = '') {
	$Alt       = "Deprecated";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<IMG SRC="/images/deprecated.gif" ALT="' . $Alt . '" TITLE="' . $HoverText . '" BORDER="0" WIDTH="18" HEIGHT="18">';
}

function freshports_Deprecated_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#deprecated">' . freshports_Deprecated_Icon($HoverText) . '</a>';
}

function freshports_Restricted_Icon($HoverText = '') {
	$Alt       = "Restricted";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<IMG SRC="/images/restricted.jpg" ALT="' . $Alt . '" TITLE="' . $HoverText . '" BORDER="0" WIDTH="16" HEIGHT="16">';
}

function freshports_Restricted_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#restricted">' . freshports_Restricted_Icon($HoverText) . '</a>';
}

function freshports_No_CDROM_Icon($HoverText = '') {
	$Alt       = "NO CDROM";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<IMG SRC="/images/no_cdrom.jpg" ALT="' . $Alt . '" TITLE="' . $HoverText . '" BORDER="0" WIDTH="16" HEIGHT="16">';
}

function freshports_No_CDROM_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#no_cdrom">' . freshports_No_CDROM_Icon($HoverText) . '</a>';
}





function freshports_Ignore_Icon($HoverText = '') {
	$Alt       = "Ignore";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<IMG SRC="/images/ignored.png" ALT="' . $Alt . '" TITLE="' . $HoverText . '" BORDER="0" WIDTH="20" HEIGHT="21;">';
}

function freshports_Ignore_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#ignore">' . freshports_Ignore_Icon($HoverText) . '</a>';
}

function freshports_New_Icon() {
	return '<IMG SRC="/images/new.gif" ALT="new!" TITLE="new!" BORDER="0" WIDTH="28" HEIGHT="11" HSPACE="2">';
}

function freshports_Mail_Icon() {
	return '<IMG SRC="/images/envelope10.gif" ALT="Original commit" TITLE="Original commit message" BORDER="0" WIDTH="25" HEIGHT="14">';
}

function freshports_Commit_Icon() {
	return '<IMG SRC="/images/copy.gif" ALT="Commit details" TITLE="FreshPorts commit message" BORDER="0" WIDTH="16" HEIGHT="16">';
}

function freshports_CVS_Icon() {
	return '<IMG SRC="/images/cvs.png" ALT="CVS log" TITLE="CVS log" BORDER="0" WIDTH="19" HEIGHT="17">';
}

function freshports_Watch_Icon() {
	return '<IMG SRC="/images/watch.gif" ALT="Item is on one of your default watch lists" TITLE="Item is on one of your default watch lists" BORDER="0" WIDTH="23" HEIGHT="22">';
}

function freshports_Watch_Icon_Add() {
	return '<IMG SRC="/images/watch-add.gif" ALT="Add item to your default watch lists" TITLE="Add item to your default watch lists" BORDER="0" WIDTH="13" HEIGHT="13">';
}

function freshports_Security_Icon() {
	return '<IMG SRC="/images/security.gif"  ALT="This commit addresses a security issue" TITLE="This commit addresses a security issue" BORDER="0" WIDTH="20" HEIGHT="20">';
}

function freshports_Encoding_Errors() {
	return '<IMG SRC="/images/error.gif" ALT="Encoding Errors (not all of the commit message was ASCII)" TITLE="Encoding Errors (not all of the commit message was ASCII)" BORDER="0" WIDTH="16" HEIGHT="16">';
}

function freshports_Encoding_Errors_Link() {
	return '<a href="/' . FAQLINK . '#encodingerrors">' . freshports_Encoding_Errors() . '<a>';
}

function freshports_VuXML_Icon() {
	return '<IMG SRC="/images/vuxml.gif" ALT="This port version is marked as vulnerable." TITLE="This port version is marked as vulnerable." BORDER="0" WIDTH="13" HEIGHT="16">';
}

function freshports_VuXML_Icon_Faded() {
	return '<IMG SRC="/images/vuxml-faded.gif" ALT="An older version of this port was marked as vulnerable." TITLE="An older version of this port was marked as vulnerable." BORDER="0" WIDTH="13" HEIGHT="16">';
}

function freshports_Revision_Icon() {
	return '<IMG SRC="/images/revision.jpg" ALT="View revision" TITLE="view revision" BORDER="0" WIDTH="11" HEIGHT="15" ALIGN="middle">';
}

function freshports_Watch_Link_Add($WatchListAsk, $WatchListCount, $ElementID) {
	$HTML = '<SMALL><A HREF="/watch-list.php?';
	$HTML .= 'add='  . $ElementID;

	if ($WatchListAsk == 'ask') {
		$HTML .= '&amp;ask=1';
	}

	$HTML .= '"';
	$HTML .= ' TITLE="add to watch list';

	$HTML .= '">' . freshports_Watch_Icon_Add() . '</A></SMALL>';

	return $HTML;
}

function freshports_Watch_Link_Remove($WatchListAsk, $WatchListCount, $ElementID) {
	$HTML = '<SMALL><A HREF="/watch-list.php?';
	$HTML .= 'remove=' . $ElementID;

	if ($WatchListAsk == 'ask') {
		$HTML .= '&amp;ask=1';
	}

	$HTML .= '"';
	$HTML .= ' TITLE="on ' . $WatchListCount . ' watch list';
	if ($WatchListCount > 1) {
		$HTML .= 's';
	}
	$HTML .= '">' . freshports_Watch_Icon() . '</A></SMALL>';
	
	return $HTML;
}

function freshports_Email_Link($message_id) {
	#
	# produce a link to the email
	#
	GLOBAL $freshports_mail_archive;

	#
	# if the message id is for freshports, then it's an old message which does not contain
	# a valid message id which can be found in the mailing list archive.
	#
	if (strpos($message_id, "@freshports.org")) {
		$HTML = '';
	} else {
		$HTML  = '<A HREF="' . htmlentities($freshports_mail_archive . $message_id) . '">';
		$HTML .= freshports_Mail_Icon();
		$HTML .= '</A>';
	}

	return $HTML;
}

function freshports_CVS_Link($element_name, $revision) {
	#
	# produce a link to the FreeBSD CVS log
	#

	# make sure the element name starts with a /
	if (substr($element_name, 0, 1) != '/') {
		$element_name = '/' . $element_name;
	}
	$HTML  = '<A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $element_name . '?rev=' . $revision . '&amp;content-type=text/x-cvsweb-markup">';
	$HTML .= freshports_CVS_Icon();
	$HTML .= '</A>';

	return $HTML;
}
function freshports_Commit_Link($message_id, $LinkText = '') {
	#
	# produce a link to the commit.  by default, we provide the graphic link.
	#

	$HTML = '<A HREF="/commit.php?message_id=' . $message_id . '">';
	if ($LinkText == '') {
		$HTML .= freshports_Commit_Icon();
	} else {
		$HTML .= $LinkText;
	}
	$HTML .= '</A>';

	return $HTML;
}

function freshports_MorePortsToShow($message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow) {
	$HTML  = "(Only the first $MaxNumberPortsToShow of $NumberOfPortsInThisCommit ports in this commit are shown above. ";
	$HTML .= freshports_Commit_Link($message_id, '<IMG SRC="/images/play.gif" ALT="View all ports for this commit" TITLE="View all ports for this commit" BORDER="0" WIDTH="13" HEIGHT="13">');
	$HTML .= ")";

	return $HTML;
}

function freshports_MoreCommitMsgToShow($message_id, $NumberOfLinesShown) {
	$HTML  = "(Only the first $NumberOfLinesShown lines of the commit message are shown above ";
	$HTML .= freshports_Commit_Link($message_id, '<IMG SRC="/images/play.gif" ALT="View all of this commit message" TITLE="View all of this commit message" BORDER="0" WIDTH="13" HEIGHT="13">');
	$HTML .= ")";

	return $HTML;
}

function freshports_CookieClear() {
	SetCookie("visitor", '', 0, '/');
}

function freshportsObscureHTML($HTML) {
	$new_HTML = '';
	for ($i = 0; $i <strlen($HTML); $i++) {
		$new_HTML .= ("&#".ord($HTML[$i]).";");
	}
	
	return $new_HTML;
}

function freshports_CommitterEmailLink($committer) {
	#
	# in an attempt to reduce spam, encode the mailto
	# so the spambots get rubbish, but it works OK in
	# the browser.
	#

	$new_addr = "";
	$addr = $committer . "@FreeBSD.org";

	$new_addr = freshportsObscureHTML($addr);

	$HTML = '<A HREF="' . MAILTO . ':' . $new_addr . '" TITLE="committed by this person">' . $committer . '</A>';

	return $HTML;
}

#
# this function not yet used
#
function freshports_CommitterEmailLinkExtra($committer, $extrabits) {
	#
	# in an attempt to reduce spam, encode the mailto
	# so the spambots get rubbish, but it works OK in
	# the browser.
	#

	$new_addr = "";
	$addr = $committer . "@FreeBSD.org";

	$new_addr = freshportsObscureHTML($addr);

	$HTML = "<A HREF=\"" . MAILTO . ":$new_addr?$extrabits\">$committer</A>";

	return $HTML;
}




// common things needs for all freshports php3 pages

function freshports_Start($ArticleTitle, $Description, $Keywords, $Phorum=0) {

GLOBAL $ShowAds;
GLOBAL $BannerAd;
GLOBAL $ShowAnnouncements;

   freshports_HTML_Start();
   freshports_Header($ArticleTitle, $Description, $Keywords, $Phorum);

   freshports_body();

   if ($ShowAds) {
      if ($BannerAd) {
		echo "\n<CENTER>\n";
		echo BurstMediaAd();
		echo "</CENTER>\n\n";
      }
   }

   echo freshports_Logo();
   freshports_navigation_bar_top();

	if (IsSet($ShowAnnouncements)) {

		GLOBAL $db;
		$Announcement = new Announcement($db);

		$NumRows = $Announcement->FetchAllActive();
		if ($NumRows > 0) {
			echo DisplayAnnouncements($Announcement);
		}
	}

}

function freshports_Logo() {
GLOBAL $TableWidth;
GLOBAL $LocalTimeAdjustment;
GLOBAL $FreshPortsName;
GLOBAL $FreshPortsLogo;
GLOBAL $FreshPortsSlogan;
GLOBAL $FreshPortsLogoWidth;
GLOBAL $FreshPortsLogoHeight;

#echo "$LocalTimeAdjustment<BR>";

	$HTML = '<BR>
<TABLE WIDTH="' . $TableWidth . '" BORDER="0" ALIGN="center">
<TR>
	<TD><A HREF="';

	if ($_SERVER["PHP_SELF"] == "/index.php") {
		$HTML .= 'other-copyrights.php';
	} else {
		$HTML .= '/';
	}
	$HTML .= '"><IMG SRC="' . $FreshPortsLogo . '" ALT="' . $FreshPortsName . ' -- ' . $FreshPortsSlogan . ' " WIDTH="' . $FreshPortsLogoWidth . '" HEIGHT="' . $FreshPortsLogoHeight . '" BORDER="0"></A></TD>
';

if (date("M") == 'Nov' && date("j") <= 12) {
	$HTML .= '	<TD ALIGN="right" CLASS="sans" VALIGN="bottom"><a href="http://www.google.ca/search?q=remembrance+day"><img src="/images/poppy.gif" width="50" height="48" border="0" alt="Remember" TITLE="Remember"><br>I remember</a></TD>';
} else {
	$HTML .= '	<TD ALIGN="right" CLASS="sans" VALIGN="bottom">' . FormatTime(Date("D, j M Y g:i A T"), $LocalTimeAdjustment, "D, j M Y g:i A T") . '</TD>';
}

$HTML .= '
</TR>
</TABLE>
';

	return $HTML;
}


function freshports_HTML_Start() {
GLOBAL $Debug;

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
';
}

function freshports_HEAD_charset() {
	return '
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
';
}

function freshports_HEAD_main_items() {
	return '
	<LINK REL="SHORTCUT ICON" HREF="/favicon.ico">
	<meta name="MSSmartTagsPreventParsing" content="TRUE">

	<META http-equiv="Pragma"              CONTENT="no-cache">
	<META HTTP-EQUIV="Cache-Control"       CONTENT="no-cache">
	<META HTTP-EQUIV="Pragma-directive"    CONTENT="no-cache">
	<META HTTP-EQUIV="cache-directive"     CONTENT="no-cache">
	<META HTTP-EQUIV="Expires"             CONTENT="0">
	<META NAME="ROBOTS"                    CONTENT="NOARCHIVE">
';
}



function freshports_HEAD_Title($ArticleTitle) {
	echo "
	<TITLE>FreshPorts";

	if ($ArticleTitle) {
		echo " -- $ArticleTitle";
	}

	echo "</TITLE>
	";
}


function freshports_Header($ArticleTitle, $Description, $Keywords, $Phorum=0) {

	GLOBAL $FreshPortsName;

	echo "<HEAD>
	<TITLE>" . $FreshPortsName;

	if ($ArticleTitle) {
		echo " -- $ArticleTitle";

		if ($Phorum) {
			GLOBAL $ForumName;

			if(isset($ForumName)) echo " - $ForumName";
		}
	}

	echo "</TITLE>
";

	freshports_style($Phorum);
	
	echo freshports_HEAD_charset();

	echo "
	<META NAME=\"description\" CONTENT=\"";

	if ($Description) {
		echo $Description;
	} else {
		echo $ArticleTitle;
	}

	echo "\">
	<META NAME=\"keywords\"    CONTENT=\"$Keywords\">
";

	echo freshports_HEAD_main_items();

if ($Phorum) {
	GLOBAL $phorumver;
	GLOBAL $DB;
	GLOBAL $ForumName;

?>
	<meta name="Phorum Version" content="<?php echo $phorumver; ?>">
	<meta name="Phorum DB" content="<?php echo $DB->type; ?>">
	<meta name="PHP Version" content="<?php echo phpversion(); ?>">
	
<?
}

	echo freshports_IndexFollow($_SERVER["PHP_SELF"]);

	echo "</HEAD>\n";
}

function freshports_style($Phorum=0) {

	if ($Phorum) {
		?>
		<LINK REL="STYLESHEET" TYPE="text/css" HREF="<?php echo phorum_get_file_name("css"); ?>">
		<?
	}

	if (2==2) {
		echo '	<link rel="stylesheet" href="/css/freshports.css" type="text/css">' . "\n";
	}
}

function freshports_body() {

GLOBAL $OnLoad;
GLOBAL $Debug;

echo "\n" . '<BODY BGCOLOR="#FFFFFF" TEXT="#000000" ';

# should we have an onload?
if ($OnLoad) {
	echo ' onLoad="' . $OnLoad . '"';
}

echo ">\n\n";

	if ($Debug) {
		GLOBAL $ShowAds;
		GLOBAL $BannerAd;
		GLOBAL $BannerAdUnder;
		GLOBAL $BurstFrontPage120x160;
		GLOBAL $BurstFrontPage125x125;
		GLOBAL $FrontPageAdsPayPal;
		GLOBAL $FrontPageAdsAmazon;
		GLOBAL $FrontPageDaemonNews;
		GLOBAL $ShowHeaderAds;
		GLOBAL $HeaderAdsPayPal;
		GLOBAL $HeaderAdAmazon;
		GLOBAL $HeaderAdsBurst125x125;
		GLOBAL $HeaderAdsBurst120x160;

		if ($BannerAd == 1) echo 'banner is on';

		echo '<TABLE BORDER="1">';
		echo '<TR><TD>ShowAds</TD><TD>'               . $ShowAds               . '</TD></TR>';
		echo '<TR><TD>BannerAd</TD><TD>'              . $BannerAd              . '</TD></TR>';
		echo '<TR><TD>BannerAdUnder</TD><TD>'         . $BannerAdUnder         . '</TD></TR>';
		echo '<TR><TD>BurstFrontPage120x160</TD><TD>' . $BurstFrontPage120x160 . '</TD></TR>';
		echo '<TR><TD>BurstFrontPage125x125</TD><TD>' . $BurstFrontPage125x125 . '</TD></TR>';
		echo '<TR><TD>FrontPageAdsPayPal</TD><TD>'    . $FrontPageAdsPayPal    . '</TD></TR>';
		echo '<TR><TD>FrontPageAdsAmazon</TD><TD>'    . $FrontPageAdsAmazon    . '</TD></TR>';
		echo '<TR><TD>FrontPageDaemonNews</TD><TD>'   . $FrontPageDaemonNews   . '</TD></TR>';
		echo '<TR><TD>ShowHeaderAds</TD><TD>'         . $ShowHeaderAds         . '</TD></TR>';
		echo '<TR><TD>HeaderAdsPayPal</TD><TD>'       . $HeaderAdsPayPal       . '</TD></TR>';
		echo '<TR><TD>HeaderAdAmazon</TD><TD>'        . $HeaderAdAmazon        . '</TD></TR>';
		echo '<TR><TD>HeaderAdsBurst125x125</TD><TD>' . $HeaderAdsBurst125x125 . '</TD></TR>';
		echo '<TR><TD>HeaderAdsBurst120x160</TD><TD>' . $HeaderAdsBurst120x160 . '</TD></TR>';
		echo '</TABLE>';
	}
}

function freshports_Category_Name($CategoryID, $db) {
	$sql = "select name from categories where id = $CategoryID";

//	echo $sql;

	$result = pg_exec($db, $sql);
	if (!$result) {
		echo "error " . pg_errormessage();
		exit;
	}

	$myrow = pg_fetch_array($result, 0);

//	echo $myrow["name"];

	return $myrow["name"];
}


function freshports_echo_HTML($text) {
//   echo $text;
   return $text;
}

function freshports_echo_HTML_flush() {
   echo $HTML_Temp;
}

function freshports_in_array($value, $array) {
  $Count = count($array);
  for ($i = 0; $i < $Count; $i++) {
     if ($array[$i] == $value) {
         return 1;
     }
  }

  return 0;
}

function freshports_PortIDFromPortCategory($category, $port, $db) {
	$sql = "select pathname_id('ports/$category/$port') as id";

	$result = pg_exec($db, $sql);
	if (pg_numrows($result)) {
		$myrow = pg_fetch_array($result, 0);
		$PortID = $myrow["id"];
	}

	return $PortID;
}

function freshports_CategoryIDFromCategory($category, $db) {
   $sql = "select categories.id from categories where categories.name = '$category'";

   $result = pg_exec($db, $sql);
   if(pg_numrows($result)) {
      $myrow = pg_fetch_array($result, 0);
      $CategoryID = $myrow["id"];
   }
   
   return $CategoryID;
}

function freshports_SideBarHTML($Self, $URL, $Label, $Title) {
   if ($Self == $URL || ($Self == '/index.php' && $URL == '/')) {
      $HTML = $Title;
   } else {
      $HTML = '<a href="' . $URL . '" TITLE="' . $Title . '">' . $Label . '</a>';
   }

   return $HTML;
}

function freshports_SideBarHTMLParm($Self, $URL, $Parm, $Label, $Title) {
   if ($Self == $URL || ($Self == '/index.php' && $URL == '/')) {
      $HTML = $Label;
   } else {
      $HTML = '<a href="' . $URL . $Parm . '" TITLE="' . $Title . '">' . $Label . '</a>';
   }
      
   return $HTML;
}

function freshports_YNToCheckbox($Value) {
// this function takes a Y/N value and converts it to
// HTML suitable for a checkbox.
   $HTML = 'value="ON"';
   if ($Value == "Y") {
      $HTML .= " checked";
   }

   return $HTML;
}

function freshports_ONToYN($Value) {
   if ($Value == "ON") {
      $YN = "Y";
   } else {
      $YN = "N";
   }

   return $YN;
}


function freshports_depends_links($dbh, $DependsList) {
	// sometimes they have multiple spaces in the data...
	$temp = str_replace('  ', ' ', $DependsList);
      
	// split each depends up into different bits
	$depends = explode(' ', $temp);
	$Count = count($depends);
	$HTML  = '';
	for ($i = 0; $i < $Count; $i++) {
		// split one depends into the library and the port name (/usr/ports/<category>/<port>)

		$DependsArray = explode(':', $depends[$i]);

		// now extract the port and category from this port name
		$CategoryPort      = str_replace('/usr/ports/', '', $DependsArray[1]) ;
		$CategoryPortArray = explode('/', $CategoryPort);
#		$DependsPortID     = freshports_PortIDFromPortCategory($CategoryPortArray[0], $CategoryPortArray[1], $dbh);

		$HTML .= '<A HREF="/' . $CategoryPortArray[0] . '/' . $CategoryPortArray[1] . '/">' . $CategoryPortArray[0] . '/' . $CategoryPortArray[1]. '</a>';
		if ($i < $Count - 1) {
			$HTML .= ", ";
		}
	}

	return $HTML;
}


function freshports_PortDetails($port, $db, $ShowDeletedDate, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, $LinkToPort = 0, $AddRemoveExtra = '', $ShowCategory = 1, $ShowDateAdded = "N", $IndicateWatchListStatus = 1, $ShowMasterSites = 0, $ShowWatchListCount = 0, $ShowMasterSlave = 0) {
//
// This fragment does the basic port information for a single port.
// It really needs to be fixed up.
//
	GLOBAL $ShowDepends;
	GLOBAL $FreshPortsWatchedPortPrefix;
	GLOBAL $FreshPortsWatchedPortSuffix;
	GLOBAL $FreshPortsWatchedPortNotPrefix;
	GLOBAL $FreshPortsWatchedPortNotSuffix;
	GLOBAL $User;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');

	$MarkedAsNew = "N";
	$HTML  = "<DL>\n";

	$HTML .= "<DT>";

	$HTML .= '<BIG><B>';

	if ($LinkToPort) {
		$HTML .= "<A HREF=\"/$port->category/$port->port/\">$port->port</A>";
	} else {
		$HTML .= $port->port;
	}

	$PackageVersion = freshports_PackageVersion($port->{'version'}, $port->{'revision'}, $port->{'epoch'});
	if (strlen($PackageVersion) > 0) {
		$HTML .= ' ' . $PackageVersion;
	}

	if (IsSet($port->category_looking_at)) {
		if ($port->category_looking_at != $port->category) {
			$HTML .= '<sup>*</sup>';
		}
	}

	$HTML .= "</B></BIG>";

	if ($ShowCategory) {
		$HTML .= ' / <A HREF="/' . $port->category . '/" TITLE="The category for this port">' . $port->category . '</A>';
	}

	if ($User->id && $IndicateWatchListStatus) {
		if ($port->{'onwatchlist'}) {
			$HTML .= ' '. freshports_Watch_Link_Remove($User->watch_list_add_remove, $port->onwatchlist, $port->{'element_id'});
		} else {
			$HTML .= ' '. freshports_Watch_Link_Add   ($User->watch_list_add_remove, $port->onwatchlist, $port->{'element_id'});
		}
	}

	// indicate if this port has been removed from cvs
	if ($port->{'status'} == "D") {
		$HTML .= " " . freshports_Deleted_Icon_Link() . "\n";
	}

	// indicate if this port needs refreshing from CVS
	if ($port->{'needs_refresh'}) {
		$HTML .= " " . freshports_Refresh_Icon_Link() . "\n";
	}

	if ($port->{'date_added'} > Time() - 3600 * 24 * $DaysMarkedAsNew) {
		$MarkedAsNew = "Y";
		$HTML .= freshports_New_Icon() . "\n";
	}

	if ($ShowWatchListCount) {
		$HTML .= '&nbsp; ' . freshPorts_WatchListCount_Icon_Link() . '=' . $port->WatchListCount();
	}

	if ($port->IsVulnerable()) {
		$HTML .= '&nbsp;' . freshports_VuXML_Icon();
	} else {
		if ($port->WasVulnerable()) {
			$HTML .= '&nbsp;' . freshports_VuXML_Icon_Faded();
		}
	}

	$HTML .= "</DT>\n<DD>";
	# show forbidden and broken
	if ($port->forbidden) {
		$HTML .= freshports_Forbidden_Icon_Link($port->forbidden)   . ' FORBIDDEN: '  . htmlify(htmlspecialchars($port->forbidden))  . "<br>";
	}

	if ($port->broken) {
		$HTML .= freshports_Broken_Icon_Link($port->broken)         . ' BROKEN: '     . htmlify(htmlspecialchars($port->broken))     . "<br>"; ;
	}

	if ($port->deprecated) {
		$HTML .= freshports_Deprecated_Icon_Link($port->deprecated) . ' DEPRECATED: ' . htmlify(htmlspecialchars($port->deprecated)) . "<br>"; ;
	}

	if ($port->ignore) {
		$HTML .= freshports_Ignore_Icon_Link($port->ignore)         . ' IGNORE: '     . htmlify(htmlspecialchars($port->ignore))     . "<br>"; ;
	}

	# we do not show vulnerabilities here.  We are showing detail.

	if ($port->restricted) {
		$HTML .= freshports_Restricted_Icon_Link($port->restricted) . ' RESTRICTED: '     . htmlify(htmlspecialchars($port->restricted)) . "<br>"; ;
	}

	if ($port->no_cdrom) {
		$HTML .= freshports_No_CDROM_Icon_Link($port->no_cdrom)      . ' NO CDROM: '     . htmlify(htmlspecialchars($port->no_cdrom))   . "<br>"; ;
	}

   // description
   if ($port->short_description && ($ShowShortDescription == "Y" || $ShowEverything)) {
      $HTML .= htmlify(htmlspecialchars($port->short_description));
      $HTML .= "<br>\n";
   }

   // maintainer
   if ($port->maintainer && ($ShowMaintainedBy == "Y" || $ShowEverything)) {
      $HTML .= '<i>';
      if ($port->status == 'A') {
         $HTML .= 'Maintained';
      } else {
         $HTML .= 'was maintained'; 
      }

      $HTML .= ' by:</i> <A HREF="' . MAILTO . ':' . freshportsObscureHTML($port->maintainer);
      $HTML .= freshportsObscureHTML('?cc=ports@FreeBSD.org') . '&amp;subject=FreeBSD%20Port:%20' . $port->port . '-' . freshports_PackageVersion($port->version, $port->revision, $port->epoch) . '" TITLE="email the maintainer">';
      $HTML .= freshportsObscureHTML($port->maintainer) . "</A><BR>";
  }

   // there are only a few places we want to show the last change.
   // such places set $GlobalHideLastChange == "Y"
   if ($GlobalHideLastChange != "Y") {
      if ($ShowLastChange == "Y" || $ShowEverything) {
         if ($port->updated != 0) {
            $HTML .= 'last change committed by ' . freshports_CommitterEmailLink($port->committer);  // separate lines in case committer is null
 
            $HTML .= ' on <font size="-1">' . $port->updated . '</font>' . "\n";

				$HTML .= freshports_Email_Link($port->message_id);

				if ($port->EncodingLosses()) {
					$HTML .= '&nbsp;' . freshports_Encoding_Errors_Link();
				}

				$HTML .= ' ' . freshports_Commit_Link($port->message_id);
				$HTML .= ' ' . freshports_CommitFilesLink($port->message_id, $port->category, $port->port);

 				$HTML .= freshports_PortDescriptionPrint($port->update_description, $port->encoding_losses, 
 				 				$freshports_CommitMsgMaxNumOfLinesToShow, 
 				 				freshports_MoreCommitMsgToShow($port->message_id,
 				 				       $freshports_CommitMsgMaxNumOfLinesToShow));
         } else {
            $HTML .= "no changes recorded in FreshPorts<br>\n";
         }
      }
   }

	# show the date added, if asked

	if ($ShowDateAdded == "Y" || $ShowEverything) {
		$HTML .= 'port added: <font size="-1">';
		if ($port->date_added) {
			$HTML .= $port->date_added;
		} else {
			$HTML .= "unknown";
		}
		$HTML .= '</font><BR>' . "\n";
	}

	if (IsSet($port->no_package) && $port->no_package != '') {
		$HTML .= '<p><b>No package is available:</b> ' . $port->no_package . '</p>';
	} else {
		$HTML .= '<p><b>To add the package:</b> <code class="code">pkg_add -r ' . $port->latest_link . '</code></p>';
	}


	$HTML .= PeopleWatchingThisPortAlsoWatch($db, $port->element_id);

   if ($port->categories) {
      // remove the primary category and remove any double spaces or trailing/leading spaces
		// this ensures that explode gives us the right stuff
      if (IsSet($port->category_looking_at)) {
         $CategoryToRemove = $port->category_looking_at;
      } else {
         $CategoryToRemove = $port->category;
      }
      $Categories = str_replace($CategoryToRemove, '', $port->categories);
      $Categories = str_replace('  ', ' ', $Categories);
		$Categories = trim($Categories);
      if ($Categories) {
         $HTML .= "<i>Also listed in:</i> ";
         $CategoriesArray = explode(" ", $Categories);
         $Count = count($CategoriesArray);
         for ($i = 0; $i < $Count; $i++) {
            $Category = $CategoriesArray[$i];
            $CategoryID = freshports_CategoryIDFromCategory($Category, $db);
            if ($CategoryID) {
               // this is a real category
               $HTML .= '<a href="/' . $Category . '/">' . $Category . '</a>';
            } else {
               $HTML .= $Category;
            }
            if ($i < $Count - 1) {
               $HTML .= " ";
            }
         }
      $HTML .= "<br>\n";
      }
   }

if ($ShowDepends) {
   if ($port->depends_build) {
      $HTML .= "<i>required to build:</i> ";
      $HTML .= freshports_depends_links($db, $port->depends_build);

      $HTML .= "<br>\n";
   }

   if ($port->depends_run) {
      $HTML .= "<i>required to run:</i> ";
      $HTML .= freshports_depends_links($db, $port->depends_run);
      $HTML .= "<BR>\n";
   }

   if ($port->depends_lib) {
      $HTML .= "<i>required libraries:</i> ";
      $HTML .= freshports_depends_links($db, $port->depends_lib);

      $HTML .= "<br>\n";
	}

}

	if ($ShowMasterSites) {
		$HTML .= '<dl><dt><i>master sites:</i></dt>' . "\n";

		$MasterSites = explode(' ', $port->master_sites);
		foreach ($MasterSites as $Site) {
			$HTML .= '<dd>' . htmlify(htmlspecialchars($Site)) . "</dd>\n";
		}

		$HTML .= "</dl>\n";

		$HTML .= '<br>';
	}

   if (!$HideDescription && ($ShowDescriptionLink == "Y" || $ShowEverything)) {
      // Long descripion
      $HTML .= '<A HREF="/' . $port->category . '/' . $port->port .'/">Description</a>';

      $HTML .= ' <b>:</b> ';
   }

   if ($ShowChangesLink == "Y" || $ShowEverything) {
      // changes
      $HTML .= '<a HREF="' . FRESHPORTS_FREEBSD_CVS_URL . '/ports/' .
               $port->category . '/' .  $port->port . '/" TITLE="The CVS Repository">CVSWeb</a>';
   }

   // download
   if ($port->status == "A" && ($ShowDownloadPortLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="http://www.freebsd.org/cgi/pds.cgi?ports/' .
               $port->category . '/' .  $port->port . '" TITLE="The source code">Sources</a>';
   }

	if ($port->PackageExists() && ($ShowPackageLink == "Y" || $ShowEverything)) {
		// package
		$HTML .= ' <b>:</b> ';
		$HTML .= '<A HREF="' . FRESHPORTS_FREEBSD_FTP_URL . '/' . freshports_PackageVersion($port->version, $port->revision, $port->epoch);
		$HTML .= '.tgz">Package</A>';
	}

   if ($port->homepage && ($ShowHomepageLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="' . htmlspecialchars($port->homepage) . '" TITLE="Main web site for this port">Main Web Site</a>';
   }

	if (defined('PORTSMONSHOW')) {
		$HTML .= ' <b>:</b> ' . freshports_PortsMonitorURL($port->category, $port->port);
	}

	if ($ShowMasterSlave) {
		#
		# Display our master port
		#

		if ($port->IsSlavePort()) {
			$HTML .= '<dl><dt><b>Master port:</b> ';
			list($MyCategory, $MyPort) = explode('/', $port->master_port);
			$HTML .= freshports_link_to_port($MyCategory, $MyPort);
			$HTML .= "</dt>\n";
			$HTML .= "</dl>\n";
		}
	
		#
		# Display our slave ports
		#

		$MasterSlave = new MasterSlave($port->dbh);
		$NumRows = $MasterSlave->FetchByMaster($port->category . '/' . $port->port);
		if ($NumRows > 0) {
			$HTML .= '<dl><dt><b>Slave ports</b>' . "</dt>\n";
			for ($i = 0; $i < $NumRows; $i++) {
				$MasterSlave->FetchNth($i);
				$HTML .= '<dd>' . freshports_link_to_port($MasterSlave->slave_category_name, $MasterSlave->slave_port_name);
				$HTML .= "</dd>\n";
			}
			$HTML .= "</dl>\n";
		}
	}
	
   $HTML .= "\n</DD>\n";
   $HTML .= "</DL>\n";

   return $HTML;
}

function freshports_PortsMoved($port, $PortsMoved) {
	$HTML = '';

	if ($PortsMoved->port == '') {
		$HTML .= "port deleted ";
	} else {
		if ($PortsMoved->from_port_id == $PortsMoved->to_port_id) {
			$HTML .= ' resurrected ';
		} else {
			if ($PortsMoved->from_port_id == $port->id) {
				$HTML .= "port moved to ";
			} else {
				$HTML .= "port moved here from ";
			}
			$HTML .= '<a href="/' . $PortsMoved->category .                                 '/">' . $PortsMoved->category . '</a>';
			$HTML .= '/';
			$HTML .= '<a href="/' . $PortsMoved->category . '/'   . $PortsMoved->port     . '/">' . $PortsMoved->port     . '</a> ';
		}
	}

	$HTML .= 'on ' . $PortsMoved->date . "<br>";
	$HTML .= 'REASON: ' . $PortsMoved->reason . '<br>';

	return $HTML;
}

function freshports_PortsUpdating($port, $PortsUpdating) {
	$HTML  =                    htmlspecialchars($PortsUpdating->date);
	$HTML .= '<pre>Affects: ' . htmlspecialchars($PortsUpdating->affects) . '</pre>';
	$HTML .= '<pre>Author: '  . htmlspecialchars($PortsUpdating->author)  . '</pre>';
	$HTML .= '<pre>Reason: '  . htmlspecialchars($PortsUpdating->reason)  . '</pre>';

	return $HTML;
}

function freshports_navigation_bar_top() {
}

function freshports_copyright() {
	return '<SMALL><A HREF="/legal.php" target="_top" TITLE="This material is copyrighted">Copyright</A> &copy; ' . COPYRIGHTYEARS . ' <A HREF="http://www.dvl-software.com/">DVL Software Limited</A>. All rights reserved.</SMALL>';
}

function FormatTime($Time, $Adjustment, $Format) {
#echo "$Time<BR>";
#echo time() . "<BR>";
	return date($Format, strtotime($Time) + $Adjustment);
}

function freshports_PortCommitsHeader($port) {
	# print the header for the commits for a port

	GLOBAL $User;

	echo '<TABLE BORDER="1" width="100%" CELLSPACING="0" CELLPADDING="5">' . "\n";
	echo "<TR>\n";

	$Columns = 3;
	if ($User->IsTaskAllowed(FRESHPORTS_TASKS_SECURITY_NOTICE_ADD)) {
		$Columns++;
	}
	echo freshports_PageBannerText("Commit History - (may be incomplete: see CVSWeb link above for full details)", $Columns);

	echo '<TR><TD WIDTH="180"><b>Date</b></td><td><b>By</b></td><td><b>Description</b></td>';
	if ($User->IsTaskAllowed(FRESHPORTS_TASKS_SECURITY_NOTICE_ADD)) {
		echo '<td><b>Security</b></td>';
	}

	echo "</tr>\n";
}

function freshports_PackageVersion($PortVersion, $PortRevision, $PortEpoch) {
	$PackageVersion = '';

	if (strlen($PortVersion) > 0) {
    	$PackageVersion .= $PortVersion;
		if (strlen($PortRevision) > 0 && $PortRevision != "0") {
    		$PackageVersion .= FRESHPORTS_VERSION_REVISION_JOINER . $PortRevision;
		}

		if (strlen($PortEpoch) > 0 && $PortEpoch != "0") {
    		$PackageVersion .= FRESHPORTS_VERSION_EPOCH_JOINER . $PortEpoch;
		}
	}

	return $PackageVersion;
}

function freshports_CheckForOutdatedVulnClaim($commit, $port, $VuXMLList) {
	# This is a situation where the most recent commit listed
	# for a port may not accurately reflect the current state
	# of the port.  It is thought this would happen only for slave ports
	# where the master port has been updated.  Such a commit would not
	# appear under the slave port, thereby giving a false impression
	# that the slave port was still vulnerable.
	#
	if (IsSet($VuXMLList[$commit->id])) {
		# yes, the most recent commit has been marked as vulnerable
		if ($port->IsSlavePort()) {
			# Yes, we have a slave port here
			# is the version for this commit, the same as the port?
			$CommitVersion = freshports_PackageVersion($commit->{'port_version'},  $commit->{'port_revision'},  $commit->{'port_epoch'});
			$PortVersion   = freshports_PackageVersion($port->{'version'},         $port->{'revision'},         $port->{'epoch'});

			if ($CommitVersion != PortVersion) {
				echo "<p><b>NOTE</b>: This slave port may no longer be vulnerable to issues shown below because the ";
				echo '<a href="/' . $port->master_port . '/">master port</a>' . " has been updated.</p>\n";
			}
		}
	}
}

function freshports_PortCommits($port) {
	# print all the commits for this port

	GLOBAL $User;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_log_ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');

	$Commits = new Commit_Log_Ports($port->dbh);
	$NumRows = $Commits->FetchInitialise($port->id);

	$port->LoadVulnerabilities();

	$Commits->FetchNthCommit(0);

	freshports_CheckForOutdatedVulnClaim($Commits, $port, $port->VuXML_List);

	freshports_PortCommitsHeader($port);

	$LastVersion = '';
	for ($i = 0; $i < $NumRows; $i++) {
		$Commits->FetchNthCommit($i);
		freshports_PortCommitPrint($Commits, $port->category, $port->port, $port->VuXML_List);
	}

	freshports_PortCommitsFooter($port);
}

function freshports_CommitFilesLink($MessageID, $Category, $Port) {

#	echo "freshports_CommitFilesLink gets $MesssageID, $Category, $Port<BR>";

	$HTML  = '<A HREF="/' . $Category . '/' . $Port . '/files.php?message_id=' . $MessageID . '">';
	$HTML .= freshports_Files_Icon();
	$HTML .= '</A>';

	return $HTML;
}

function freshports_PortCommitPrint($commit, $category, $port, $VuXMLList) {
	GLOBAL $DateFormatDefault;
	GLOBAL $TimeFormatDefault;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
	GLOBAL $User;

	# print a single commit for a port
	echo "<TR><TD VALIGN='top' NOWRAP>";
	

	echo $commit->commit_date . '<BR>';
	// indicate if this port needs refreshing from CVS
	if ($commit->{'needs_refresh'}) {
		echo " " . freshports_Refresh_Icon_Link() . "\n";
	}
	echo freshports_Email_Link($commit->message_id);

	echo '&nbsp;&nbsp;'. freshports_Commit_Link($commit->message_id);

	if ($commit->EncodingLosses()) {
		echo '&nbsp;'. freshports_Encoding_Errors_Link();
	}

	echo ' ';

	echo freshports_CommitFilesLink($commit->message_id, $category, $port);
	if (IsSet($commit->security_notice_id)) {
		echo ' <a href="/security-notice.php?message_id=' . $commit->message_id . '">' . freshports_Security_Icon() . '</a>';
	}

	# ouput the VERSION and REVISION
	$PackageVersion = freshports_PackageVersion($commit->{'port_version'},  $commit->{'port_revision'},  $commit->{'port_epoch'});
	if (strlen($PackageVersion) > 0) {
    	echo '&nbsp;&nbsp;&nbsp;<BIG><B>' . $PackageVersion . '</B></BIG>';
	}

	if (IsSet($VuXMLList[$commit->id])) {
		echo '&nbsp;<a href="/vuxml.php?vid=' . $VuXMLList[$commit->id] . '">' . freshports_VuXML_Icon() . '</a>';
	}

	echo "</TD>\n";
	echo '    <TD VALIGN="top">';
	echo freshports_CommitterEmailLink($commit->committer);

	echo "</TD>\n";
	echo '    <TD VALIGN="top" WIDTH="*">';

	echo freshports_PortDescriptionPrint($commit->description, $commit->encoding_losses, $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($commit->message_id, $freshports_CommitMsgMaxNumOfLinesToShow));

	echo "</TD>\n";

	if ($User->IsTaskAllowed(FRESHPORTS_TASKS_SECURITY_NOTICE_ADD)) {
		echo '<TD ALIGN="center" VALIGN="top"><a href="/security-notice.php?message_id=' . $commit->message_id . '">Edit</a></td>';
	}

	echo "</TR>\n";
}

function freshports_PortCommitsFooter($port) {
	# print the footer for the commits for a port
	echo "</TABLE>\n";
}











function freshports_CommitsHeader($element_record) {
	# print the header for the commits for an element

	GLOBAL $User;

	echo '<TABLE BORDER="1" width="100%" CELLSPACING="0" CELLPADDING="5">' . "\n";
	echo "<TR>\n";

	$Columns = 3;
	if ($User->IsTaskAllowed(FRESHPORTS_TASKS_SECURITY_NOTICE_ADD)) {
		$Columns++;
	}
	echo freshports_PageBannerText("Commit History - (may be incomplete: see CVSWeb link above for full details)", $Columns);

	echo '<TR><TD WIDTH="180"><b>Date</b></td><td><b>By</b></td><td><b>Description</b></td>';
	if ($User->IsTaskAllowed(FRESHPORTS_TASKS_SECURITY_NOTICE_ADD)) {
		echo '<td><b>Security</b></td>';
	}

	echo "</tr>\n";
}

function freshports_CommitsFooter($element_record) {
	# print the footer for the commits for a port
	echo "</TABLE>\n";
}















function freshports_Commits($element_record) {
	# print all the commits for this port

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_log_elements.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');

	freshports_CommitsHeader($element_record);

	$Commits = new Commit_Log_Elements($element_record->dbh);
	$NumRows = $Commits->FetchInitialise($element_record->id);

	$LastVersion = '';
	for ($i = 0; $i < $NumRows; $i++) {
		$Commits->FetchNthCommit($i);
		freshports_CommitPrint($element_record, $Commits);
	}

	freshports_CommitsFooter($element_record);
}




function freshports_CommitPrint($element_record, $commit) {
	GLOBAL $DateFormatDefault;
	GLOBAL $TimeFormatDefault;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
	GLOBAL $User;

	# print a single commit for a port
	echo "<TR><TD VALIGN='top' NOWRAP>";
	

	echo $commit->commit_date . '<BR>';
	echo freshports_Email_Link($commit->message_id);

	echo '&nbsp;&nbsp;'. freshports_Commit_Link($commit->message_id);

	if ($commit->EncodingLosses()) {
		echo '&nbsp;'. freshports_Encoding_Errors_Link();
	}

	echo ' ';

	echo freshports_CVS_Link($element_record->element_pathname, $commit->revision_name);
	if (IsSet($commit->security_notice_id)) {
		echo ' <a href="/security-notice.php?message_id=' . $commit->message_id . '">' . freshports_Security_Icon() . '</a>';
	}

	# ouput the REVISION
	if (strlen($commit->{'revision_name'}) > 0) {
    	echo '&nbsp;&nbsp;&nbsp;<BIG><B>' . $commit->{'revision_name'} . '</B></BIG>';
	}

	echo "</TD>\n";
	echo '    <TD VALIGN="top">';
	echo freshports_CommitterEmailLink($commit->committer);

	echo "</TD>\n";
	echo '    <TD VALIGN="top" WIDTH="*">';

	echo freshports_PortDescriptionPrint($commit->description, $commit->encoding_losses, $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($commit->message_id, $freshports_CommitMsgMaxNumOfLinesToShow));

	echo "</TD>\n";

	if ($User->IsTaskAllowed(FRESHPORTS_TASKS_SECURITY_NOTICE_ADD)) {
		echo '<TD ALIGN="center" VALIGN="top"><a href="/security-notice.php?message_id=' . $commit->message_id . '">Edit</a></td>';
	}

	echo "</TR>\n";
}










function freshports_Head($string, $n, $FudgeFactor = 10) {
	#
	# We don't want to display very long commit messages or very long lists of
	# ports.  So we use this function to shorten things.
	#
	# return the first $n lines of $string, but only if the entire string
	# is within $FudgeFactor.  
	#

	#
	# ensure valid parameters
	#
	if (!is_int($n) || $n <= 0 || !is_int($FudgeFactor) || $FudgeFactor < 0) {
		return $string;
	}

	$pos = -1;
	for ($i = 0; $i < $n ; $i++) {
		$pos = strpos($string, "\n", $pos+1);
		if ($pos === false) {
			break;
		}
	}

	if ($pos === false) {
		# we got to the end of the string before we found
		# $n lines.
	} else {
		#
		# we know we have at least $n lines.
		# but if we wind up saving only a few lines, let's show them all.
		#
		$ShortVersion = $pos;

		for ($i = $n; $i < ($n + $FudgeFactor); $i++) {
			$pos = strpos($string, "\n", $pos+1);
			if ($pos === false) {
				break;
			}
		}

		if ($pos == false) {
			# hmmm, the string is shorter than our fudge factor
		} else {
			# ahh, the string is longer than our fudge factor allows
			$string = substr($string, 0, $ShortVersion);
		}
	}

	return $string;
}

function freshports_PortDescriptionPrint($description, $encoding_losses, $maxnumlines=0, $URL='') {

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');

	$shortened = freshports_Head($description, $maxnumlines);
	$HTML  = '<PRE CLASS="code">';

	$HTML .= htmlify(htmlspecialchars(freshports_wrap($shortened)));

	$HTML .= '</PRE>';

	if (strlen($shortened) < strlen($description)) {
		$HTML .= $URL;
	}

	return $HTML;
}

function freshports_GetNextValue($sequence, $dbh) {
	$sql = "select nextval('$sequence')";

#	echo "\$sql = '$sql'<BR>";

	$result = pg_exec($dbh, $sql);
	if ($result && pg_numrows($result)) {
		$row       = pg_fetch_array($result,0);
		$NextValue = $row[0];
	} else {
		pg_errormessage() . ' sql = $sql';
	}

	return $NextValue;
}

if (!defined('WRAPCOMMITSATCOLUMN')) {
	  define('WRAPCOMMITSATCOLUMN', 80);
}

function freshports_wrap($text, $length = WRAPCOMMITSATCOLUMN) {
	#
	# split the text into lines based on \n
	#
	$lines = explode("\n", $text);

	#
	# for each line, wrap them at 72 chars...
	#
	for ($i = 0; $i < count($lines); $i++) {
		$lines[$i] = wordwrap($lines[$i], $length, "\n");
	}

	#
	# put the array back into a single text string with \n
	# as the glue.
	#
	return implode("\n", $lines);
}

function freshports_PageBannerText($Text, $ColSpan=1) {
	return '<TD ALIGN="left" BGCOLOR="#AD0040" HEIGHT="29" COLSPAN="' . $ColSpan . ' "><FONT COLOR="#FFFFFF"><BIG><BIG>' . $Text . '</BIG></BIG></FONT></TD>' . "\n";
}


function freshports_UserSendToken($UserID, $dbh) {
	#
	# send the confirmation token to the user
	#

	GLOBAL $FreshPortsSlogan;

	$sql = "select email, token 
	          from users, user_confirmations
	         where users.id = $UserID
	           and users.id = user_confirmations.user_id";

#	echo "\$sql = '$sql'<BR>";

	$result = pg_exec($dbh, $sql);
	if ($result && pg_numrows($result)) {
		$row   = pg_fetch_array($result,0);
		$email = $row[0];
		$token = $row[1];
	} else {
		pg_errormessage() . ' sql = $sql';
	}

	if (IsSet($token)) {
		OpenLog("FreshPorts", LOG_PID, LOG_SYSLOG);
		SysLog(LOG_NOTICE, "User Token Sent: UID=$UserID, email=$email");
		CloseLog();

		$message =  "Someone, perhaps you, supplied your email address as their\n".
					"FreshPorts login. If that wasn't you, and this message becomes\n".
				    "a nuisance, please forward this message to webmaster@" . $_SERVER["HTTP_HOST"] . "\n".
					"and we will take care of it for you.\n".
                    " \n".
	                "Your token is: $token\n".
    	            "\n".
        	        "Please point your browser at\n".
					"http://" . $_SERVER["HTTP_HOST"] . "/confirmation.php?token=$token\n" .
	                "\n".
    	            "the request came from " . $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"] ."\n".
					"\n".
					"-- \n".
					"FreshPorts - http://" . $_SERVER["HTTP_HOST"] . "/ -- $FreshPortsSlogan";

		$result = mail($email, "FreshPorts - user registration", $message,
					"From: webmaster@" . $_SERVER["HTTP_HOST"] . "\nReply-To: webmaster@" . $_SERVER["HTTP_HOST"] . "\nX-Mailer: PHP/" . phpversion());
	} else {
		$result = 0;
	}

	return $result;
}

function freshports_ShowFooter() {
	GLOBAL $TableWidth;
	GLOBAL $Statistics;
	GLOBAL $ShowPoweredBy;
	GLOBAL $ShowAds;

	$HTML = '<TABLE WIDTH="' . $TableWidth . '" BORDER="0" ALIGN="center">
<TR><TD>

<HR>
<TABLE WIDTH="98%" BORDER="0">
';

	if (IsSet($ShowPoweredBy)) {
		$HTML .= '
<TR>

<TD align="center">

<A HREF="http://www.freebsd.org/"><IMG SRC="/images/pbfbsd2.gif"
ALT="powered by FreeBSD" BORDER="0" WIDTH="171" HEIGHT="64"></A>

&nbsp;

<A HREF="http://www.php.net/"><IMG SRC="/images/php-med-trans-light.gif"
ALT="powered by php" BORDER="0" WIDTH="95" HEIGHT="50"></A>
&nbsp;

<A HREF="http://www.postgresql.org/"><IMG SRC="/images/pg-power.jpg"
ALT="powered by PostgreSQL" BORDER="0" WIDTH="164" HEIGHT="59"></A>


</TD></TR>
<TR><TD align="center">

<A HREF="http://www.phorum.org/"><IMG SRC="/phorum/images/phorum.gif"
ALT="powered by phorum" BORDER="0" WIDTH="200" HEIGHT="50"></A>


&nbsp;&nbsp;&nbsp;

<A HREF="http://www.apache.org/"><IMG SRC="/images/apache_pb.gif" 
ALT="powered by apache" BORDER="0" WIDTH="259" HEIGHT="32"></A>

<HR>

</TR>
';
	}

	$HTML .= '
<TR><TD>
<table width="100%">
<tr>
<td align="left"  valign="top">
<SMALL>Server and bandwidth provided by <A HREF="http://www.bchosting.com/" TARGET="_new" TITLE="Our major sponsor">BChosting.com</A></SMALL>
<br>
<small>This page created in ' . round($Statistics->ElapsedTime(), 3) . ' seconds.</small>
</td>
<td align="right" valign="top">
<small>
Valid 
<a href="http://validator.w3.org/check/referer" TITLE="We like to keep our HTML valid">HTML</a>, 
<a href="http://jigsaw.w3.org/css-validator/check/referer" TITLE="We like to have valid CSS">CSS</a>, and
<a href="http://feeds.archive.org/validator/check?url=http://' . $_SERVER['HTTP_HOST'] . '/news.php" TITLE="Valid RSS is good too">RSS</a>.
</small>
<BR>' . freshports_copyright() . '

</td></tr>
</table>
</TD></TR>
</TABLE>';

	if ($ShowAds) {
		$HTML .= "<div align=\"center\">\n";
		$HTML .= '<br>';
		$HTML .= Burst_468x60_Below();
		$HTML .= "</div>\n";
	}

	$HTML .= '
</TD></TR>
</TABLE>
';

	$Statistics->Save();

	return $HTML;
}

function freshports_SideBar() {

	GLOBAL $User;
	$ColumnWidth = 155;

#	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/side-bars.php');

	$OriginLocal = rawurlencode($_SERVER["REQUEST_URI"]);

	$HTML = '
  <TABLE WIDTH="' . $ColumnWidth . '" BORDER="1" CELLSPACING="0" CELLPADDING="5">
        <TR>
         <TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Login</B></BIG></FONT></TD>
        </TR>
        <TR>

         <TD NOWRAP>';

	if (IsSet($_COOKIE["visitor"])) {
		$visitor = $_COOKIE["visitor"];
	}

	if (IsSet($visitor)) {
		GLOBAL $User;
		$HTML .= '<FONT SIZE="-1">Logged in as ' . $User->name . "</FONT><BR>";

		if ($User->emailbouncecount > 0) {
			$HTML .= '<IMG SRC="/images/warning.gif" BORDER="0" HEIGHT="32" WIDTH="32"><IMG SRC="/images/warning.gif"  BORDER="0" HEIGHT="32" WIDTH="32"><IMG SRC="/images/warning.gif" BORDER="0"HEIGHT="32" WIDTH="32"><BR>';
			$HTML .= '<FONT SIZE="-1">your email is <A HREF="bouncing.php?origin=' . $OriginLocal. '">bouncing</A></FONT><BR>';
			$HTML .= '<IMG SRC="/images/warning.gif" BORDER="0" HEIGHT="32" WIDTH="32"><IMG SRC="/images/warning.gif" BORDER="0" HEIGHT="32" WIDTH="32"><IMG SRC="/images/warning.gif" BORDER="0" HEIGHT="32" WIDTH="32"><BR>';
		}
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/customize.php',        "?origin=$OriginLocal", "Customize", "Customize your settings"              ) . '</FONT><BR>';

		if (eregi(".*@FreeBSD.org", $User->email)) {
			$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/committer-opt-in.php', '', "Committer Opt-in", "Committers can receive reports of Sanity Test Failures"       ) . '</FONT><BR>';
		}


		# for a logout, where we go depends on where we are now
		#
		switch ($_SERVER["PHP_SELF"]) {
			case "customize.php":
			case "watch-categories.php":
			case "watch.php":
				$args = "?origin=$OriginLocal";
				break;

			default:
				$args = '';
		}
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/logout.php',                 $args,                  "Logout", "Logout of the website"                  ) . '</FONT><BR>';

		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/pkg_upload.php',             '',                     "Watch list - upload",     "Upoad a file containing a list of ports you want to add to your watch list") . '</FONT><BR>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch-categories.php',       '',                     "Watch list - Categories", "Search through categories for ports to add to your watch list"             ) . '</FONT><BR>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch-list-maintenance.php', '',                     "Watch lists - Maintain",  "Maintain your watch list[s]"                                               ) . '</FONT><BR>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch.php',                  '',                     "Your watched ports",      "Your list of watched ports"                                                ) . '</FONT><BR>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/report-subscriptions.php',   '',                     "Report Subscriptions",    "Maintain your list of subscriptions"                                       ) . '</FONT><BR>';
	} else {
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/login.php',                  "?origin=$OriginLocal", "User Login",              "Login to the website"                                                      ) . '</FONT><BR>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/new-user.php',               "?origin=$OriginLocal", "Create account",          "Create an account"                                                         ) . '</FONT><BR>';
	}

	$HTML .= '
	<A HREF="/phorum/" TITLE="Discussion Forums">Forums</A>
   </TD>
   </TR>
   </TABLE>

<P>

<SMALL>Server and bandwidth provided by <A HREF="http://www.bchosting.com/" TARGET="_new" TITLE="Our major sponsor">BChosting.com</A></SMALL>

</P>

<TABLE WIDTH="' . $ColumnWidth . '" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Search</B></BIG></FONT></TD>
	</TR>
	<TR>

	<TD>';

	GLOBAL $dbh;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/searches.php');


	$Searches = new Searches($dbh);
	$HTML .= $Searches->GetFormSimple('&nbsp;');

	$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/search.php', '', "more...", "Advanced Searching options") . '</FONT><BR>
	</TD>
</TR>
</TABLE>

<BR>';

	GLOBAL $ShowAds;

	if ($ShowAds) {
		$HTML .= '<TABLE BORDER="0" CELLPADDING="5">
		  <TR><TD ALIGN="center">
		 ';

		$HTML .= BurstSkyscraperAd();
		$HTML .= '</TD></TR>
		  </TABLE>
		 ';
	}

	$HTML .= '<BR>

<TABLE WIDTH="' . $ColumnWidth . '" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD COLSPAN="2" BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Statistics</B></BIG></FONT></TD>
	</TR>
	<TR>
	<TD VALIGN="top">

' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/graphs.php",        "Graphs", "Everyone loves statistics!")   . '<BR>
' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/stats/",            "Traffic", "Traffic to this website");

	if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/../dynamic/stats.html")) {
		$HTML .= '<BR>
' . file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../dynamic/stats.html") . "\n";
	}

	$HTML .= '
	</TD>
	</TR>
</TABLE>


<BR>

<TABLE WIDTH="' . $ColumnWidth . '" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Ports</B></BIG></FONT></TD>
	</TR>
	<TR>
	<TD VALIGN="top">

	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/",                     "Home",             "FreshPorts Home page"       )   . '</FONT><BR>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/categories.php",       "Categories",       "List of all Port categories")   . '</FONT><BR>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/ports-deleted.php",    "Deleted ports",    "All deleted ports"          )   . '</FONT><BR>
	</TD>
	</TR>
</TABLE>



<BR>

<TABLE WIDTH="' . $ColumnWidth .'" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>This site</B></BIG></FONT></TD>
	</TR>
	<TR>
	<TD VALIGN="top">
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/about.php",           "What is FreshPorts?", "A bit of background on FreshPorts"    ) . '</FONT><BR>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/authors.php",         "About the Authors",   "Who wrote this stuff?"                ) . '</FONT><BR>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/faq.php",             "FAQ",                 "Frequently Asked Questions"           ) . '</FONT><BR>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/how-big-is-it.php",   "How big is it?",      "How many pages are in this website?"  ) . '</FONT><BR>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/release-2004-10.php", "The latest upgrade!", "Details on the latest website upgrade") . '</FONT><BR>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/privacy.php",         "Privacy",             "Our privacy statement"                ) . '</FONT><BR>
	</TD>
	</TR>
</TABLE>


<BR>

<SCRIPT TYPE="text/javascript">
   function addNetscapePanel() {
      if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function"))
      {
         window.sidebar.addPanel ("FreshPorts",
         "http://www.FreshPorts.org/sidebar.php","");
      }
      else
      {
         var rv = window.confirm ("This page is enhanced for use with Netscape 6.  " + "Would you like to upgrade now?");
         if (rv)
            document.location.href = "http://home.netscape.com/download/index.html";
      }
   }
//-->

</SCRIPT>

<CENTER>
<A NAME="button_image"></A><A HREF="javascript:addNetscapePanel();"><IMG SRC="/images/sidebar-add-button.gif" BORDER="0" HEIGHT="45" WIDTH="100" ALT="Add tab to Netscape 6"></A>
</CENTER>';

	return $HTML;

}

function freshports_LinkToDate($Date, $Text = '') {
	$URL = '<a href="/date.php?date=' . date("Y/n/j", $Date) . '">';
	if ($Text != '') {
		$URL .= $Text;
	} else {
		$URL .= date("D, j M Y", $Date);
	}

	$URL .= '</a>';

	return $URL;
}

function freshports_ErrorMessage($Title, $ErrorMessage) {
	$HTML = '
<TABLE WIDTH="100%" BORDER="1" ALIGN="center" CELLPADDING=1 CELLSPACING=0 BORDER="1">
<TR><TD VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
	' . freshports_PageBannerText($Title) . '
</TR>
<TR BGCOLOR="#ffffff">
<TD>
  <TABLE WIDTH="100%" CELLPADDING=0 CELLSPACING=0 BORDER=0>
  <TR valign=top>
   <TD><img src="/images/warning.gif"></TD>
   <TD WIDTH="100%">
  <p>' .  "WARNING: $ErrorMessage" . '</p>
 <p>If you need help, please ask in the forum. </p>
 </TD>
 </TR>
 </TABLE>
</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>
<BR>';

	return $HTML;
}

function DisplayAnnouncements($Announcement) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');

	$HTML .= '<table width="100%"cellpadding="4" cellspacing="0" border="0">' . "\n";

	$NumRows = $Announcement->NumRows();

	for ($i = 0; $i < $NumRows; $i++) {
		$Announcement->FetchNth($i);
		$HTML .= '<tr>' . "\n";
		$HTML .= '<td>' . $Announcement->TextGet() . '</td>';
      $HTML .= '</tr>' . "\n";
	}
	$HTML .= '</table>' . "\n";

	return $HTML;
}

function PeopleWatchingThisPortAlsoWatch($dbh, $element_id) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_list_also_watched.php');

	$HTML = '';

	$AlsoWatched = new WatchListAlsoWatched($dbh);
	$numrows = $AlsoWatched->WatchersAlsoWatch($element_id);
	if ($numrows) {
		$HTML .= '<p><b>People watching this port, also watch:</b> ';
		for ($i = 0; $i < $numrows; $i++) {
			$AlsoWatched->FetchNth($i);
			$HTML .= $AlsoWatched->URL;
			if (($i + 1) < $numrows) {
				$HTML .= ', ';
			}
		}
		$HTML .= '</p>';
	}

	return $HTML;

}

function freshports_PortsMonitorURL($Category, $Port) {
	return '<a href="' . PORTSMONURL . '?category=' . $Category . '&amp;portname=' . $Port . '" TITLE="Ports Monitor">PortsMon</a>';
}

openlog('FreshPorts', LOG_PID | LOG_PERROR, LOG_LOCAL0);

?>
