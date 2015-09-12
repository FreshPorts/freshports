<?php
	#
	# $Id: freshports.php,v 1.51 2013-05-12 14:47:12 dan Exp $
	#
	# Copyright (c) 1998-2007 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/ads.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../configuration/freshports.conf.php');

	if (IsSet($ShowAnnouncements)) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');
	}
#
# special HTMLified mailto to foil spam harvesters
#
DEFINE('MAILTO',                'mailto');
DEFINE('COPYRIGHTYEARS',        '2000-2014');
DEFINE('URL2LINK_CUTOFF_LEVEL', 0);
DEFINE('FAQLINK',               'faq.php');
DEFINE('PORTSMONURL',			'http://portsmon.freebsd.org/portoverview.py');
DEFINE('DISTFILESSURVEYURL',	'http://people.freebsd.org/~ehaupt/distilator/');
DEFINE('NOBORDER',              '0');
DEFINE('BORDER',                '1');

DEFINE('MESSAGE_ID_OLD_DOMAIN', '@freshports.org');
DEFINE('MESSAGE_ID_NEW_DOMAIN', '@dev.null.freshports.org');

DEFINE('UNMAINTAINTED_ADDRESS', 'ports@freebsd.org');

DEFINE('BACKGROUND_COLOUR', '#8c0707');

DEFINE('CLICKTOADD', 'Click to add this to your default watch list[s]');

DEFINE('SPONSORS', 'Servers and bandwidth provided by<br><a href="http://www.nyi.net/" TARGET="_new">New York Internet</a>, <a href="http://www.supernews.com/"  TARGET="_new">SuperNews</a>, and <a href="http://www.rootbsd.net/" TARGET="_new">RootBSD</a>');

DEFINE('FRESHPORTS_ENCODING', 'UTF-8');

if ($Debug) echo "'" . $_SERVER['DOCUMENT_ROOT'] . '/../classes/watchnotice.php<br>';

date_default_timezone_set('Europe/London');

require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watchnotice.php');

function freshports_MainTable() {
	GLOBAL $TableWidth;

	return '<table width="' . $TableWidth . '" border="0">
';
}

function freshports_Search_Depends_All($CategoryPort) {
	return '<a href="/search.php?stype=depends_all&amp;method=match&amp;query=' . htmlentities($CategoryPort) . '">' .
	      freshports_Search_Icon('search for ports that depend on this port') . '</a>';
}

function freshports_Search_For_Bugs($CategoryPort) {
  $SearchURL = "https://bugs.freebsd.org/bugzilla/buglist.cgi?component=Individual%20Port%28s%29&amp;list_id=28394&amp;product=Ports%20%26%20Packages&amp;query_format=advanced&amp;resolution=---" . 
    "&amp;short_desc=" . urlencode($CategoryPort) . "&amp;short_desc_type=allwordssubstr";

  return '<a href="' . $SearchURL . '"  rel="nofollow">' . freshports_Bugs_Find_Icon() . '</a>';
}

function freshports_Report_A_Bug($CategoryPort) {
  $SearchURL = "https://bugs.freebsd.org/bugzilla/enter_bug.cgi?component=Individual%20Port%28s%29&amp;product=Ports%20%26%20Packages&amp;short_desc=" . urlencode($CategoryPort);

  return '<a href="' . $SearchURL . '"  rel="nofollow">' . freshports_Bugs_Report_Icon() . '</a>';
}

function freshports_SanityTestFailure_Link($message_id) {
	return '<a href="/sanity_test_failures.php?message_id=' . $message_id . '">' . freshports_SanityTestFailure_Icon() . '</a>';
}

function freshports_cvsweb_Diff_Link($pathname, $previousRevision, $revision_name)
{
  $pathname = str_replace('/ports/head/', '/ports/', $pathname);
  $HTML  = '<A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $pathname . '.diff?r1=' . $previousRevision . ';r2=' . $revision_name . '">';
  $HTML .= freshports_Diff_Icon() . '</a> ';

  return $HTML;
}

function freshports_cvsweb_Annotate_Link($pathname, $revision_name)
{
  $pathname = str_replace('/ports/head/', '/ports/', $pathname);
  $HTML  = ' <A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $pathname . '?annotate=' . $revision_name . '">';
  $HTML .= freshports_Revision_Icon() . '</a>';

  return $HTML;
}

function freshports_cvsweb_Revision_Link($pathname, $revision_name)
{
  $pathname = str_replace('/ports/head/', '/ports/', $pathname);
  $HTML = '<A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $pathname . '#rev' . $revision_name . '">';

  return $HTML;
}

function freshports_svnweb_ChangeSet_Link($revision, $hostname, $path) {
  return '<a href="http://' . htmlentities($hostname) . htmlentities($path) . 
                                            '?view=revision&amp;revision=' . htmlentities($revision) .  '">' . freshports_Subversion_Icon('Revision:' . $revision) . '</a>';
}

function freshports_Search_Maintainer($Maintainer) {
	return '<a href="/search.php?stype=maintainer&amp;method=exact&amp;query=' . htmlentities($Maintainer) . '">' .
	      freshports_Search_Icon('search for ports maintained by this maintainer') . '</a>';
}

function freshports_Search_Committer($Committer) {
	return '<a href="/search.php?stype=committer&amp;method=exact&amp;query=' . htmlentities($Committer) . '">' .
	      freshports_Search_Icon('search for other commits by this committer') . '</a>';
}

function freshports_MainContentTable($Border=1, $ColSpan=1) {
	return '<table width="100%" border="' . $Border . '" cellspacing="0" cellpadding="8">' . 
		PortsFreezeStatus($ColSpan);
}

function  freshports_ErrorContentTable() {
	echo '<table width="100%" border="1" align="center" cellpadding="1" cellspacing="0">
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
<p>A <a href="http://www.freebsd.org/doc/en/articles/committers-guide/ports.html">ports freeze</a>
 means that commits will be few and far between and only by approval.
</p>
</td></tr>
';
	}

	return $result;
}


function freshports_link_to_port($CategoryName, $PortName) {

	$HTML = '';
	$HTML .= '<a href="/' . $CategoryName . '/">' . $CategoryName . '</a>/';
	$HTML .= '<a href="/' . $CategoryName . '/' . $PortName . '/">' 
	            . $PortName . '</a>';

	return $HTML;
}

function freshports_link_to_port_single($CategoryName, $PortName) {

	// This differs from freshports_link_to_port in that you get a single link, not a 
	// link to both category and port

	$HTML = '';
	$HTML .= '<a href="/' . $CategoryName . '/' . $PortName . '/">' .
	                        $CategoryName . '/' . $PortName . '</a>';

	return $HTML;
}

function freshports_link_text_to_port_single($text, $CategoryName, $PortName) {

	// This differs from freshports_link_to_port_single in the link text is not necessarily the port name.

	$HTML = '';
	$HTML .= $text . ' : <a href="/' . $CategoryName . '/' . $PortName . '/">' .
	                                   $CategoryName . '/' . $PortName . '</a>';

	return $HTML;
}

#
# These are the pages which take NOINDEX and NOFOLLOW meta tags
#


function freshports_IndexFollow($URI) {
#	$NOINDEX["/index.php"]				= 1;
	$NOINDEX["/date.php"]				= 1;

	$NOINDEX['/ports-broken.php']		= 1;
	$NOINDEX['/ports-deleted.php']		= 1;
	$NOINDEX['/ports-forbidden.php']	= 1;
	$NOINDEX['/ports-deprecated.php']	= 1;
	$NOINDEX['/ports-ignore.php']		= 1;
	$NOINDEX['/ports-new.php']			= 1;
	$NOINDEX['/search.php']				= 1;


	$NOFOLLOW["/date.php"]				= 1;
	$NOFOLLOW['/ports-deleted.php']		= 1;
	$NOFOLLOW['/graphs.php']			= 1;
	$NOFOLLOW['/ports-deleted.php']		= 1;
	$NOFOLLOW['/commit.php']			= 1;

	$NOFOLLOW['/new-user.php']			= 1;
	$NOFOLLOW['/login.php']				= 1;
	$NOFOLLOW['/search.php']			= 1;


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

return '
  <tr>
    <td height="10"></td>
  </tr>
';

}

function freshports_Subversion_Icon($Title = 'Subversion') {
	return '<img src="/images/subversion.jpg" alt="' . $Title . '" title="' . $Title . '" border="0" width="16" height="16" vspace="1">';
}

function freshports_SanityTestFailure_Icon($Title = 'Sanity Test Failure') {
	return '<img src="/images/stf.gif" alt="' . $Title . '" title="' . $Title . '" border="0" width="13" height="13" vspace="1">';
}

function freshports_Ascending_Icon($Title = 'Ascending Order') {
	return '<img src="/images/ascending.gif" alt="' . $Title . '" title="' . $Title . '" border="0" width="9" height="9" align="middle">';
}

function freshports_Descending_Icon($Title = 'Descending Order') {
	return '<img src="/images/descending.gif" alt="' . $Title . '" title="' . $Title . '" border="0" width="9" height="9" align="middle">';
}

function freshports_Search_Icon($Title = 'Search') {
	return '<img src="/images/search.jpg" alt="' . $Title . '" title="' . $Title . '" border="0" width="17" height="17" align="top">';
}

function freshports_Bugs_Find_Icon($Title = 'Find issues related to this port') {
	return '<img src="/images/bug.gif" alt="' . $Title . '" title="' . $Title . '" border="0" width="16" height="16" align="top">';
}

function freshports_Bugs_Report_Icon($Title = 'Report an issue related to this port') {
	return '<img src="/images/bug_report.gif" alt="' . $Title . '" title="' . $Title . '" border="0" width="16" height="16" align="top">';
}

function freshports_WatchListCount_Icon() {
	return '<img src="/images/sum.gif" alt="on this many watch lists" title="on this many watch lists" border="0" width="8" height="11">';
}

function freshports_WatchListCount_Icon_Link() {
	return '<a href="/' . FAQLINK . '#watchlistcount">' . freshports_WatchListCount_Icon() . '</a>';
}

function freshports_Files_Icon() {
	return '<img src="/images/logs.gif" alt="files touched by this commit" title="files touched by this commit" border="0" width="17" height="20">';
}

function freshports_Refresh_Icon() {
	return '<img src="/images/refresh.gif" alt="Refresh" title="Refresh - this port is being refreshed, or make failed to run error-free." border="0" width="15" height="18">';
}

function freshports_Refresh_Icon_Link() {
	return '<a href="/' . FAQLINK . '#refresh">' . freshports_Refresh_Icon() . '</a>';
}

function freshports_Deleted_Icon() {
	return '<img src="/images/deleted.gif" alt="Deleted" title="Deleted" border="0" width="21" height="18">';
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

	return '<img src="/images/forbidden.gif" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="20" height="20">';
}

function freshports_Forbidden_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#forbidden">' . freshports_Forbidden_Icon($HoverText) . '</a>';
}

function freshports_Broken_Icon($HoverText = '') {
	$Alt       = "Broken";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img src="/images/broken.gif" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="17" height="16">';
}

function freshports_Broken_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#broken">' . freshports_Broken_Icon($HoverText) . '</a>';
}

function freshports_Deprecated_Icon($HoverText = '') {
	$Alt       = "Deprecated";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img src="/images/deprecated.gif" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="18" height="18">';
}

function freshports_Deprecated_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#deprecated">' . freshports_Deprecated_Icon($HoverText) . '</a>';
}

function freshports_Expired_Icon($HoverText = '') {
	$Alt       = "Expired";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img src="/images/expired.gif" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="16" height="16">';
}

function freshports_Expired_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#expired">' . freshports_Expired_Icon($HoverText) . '</a>';
}

function freshports_Expiration_Icon($HoverText = '') {
	$Alt       = "Expiration Date";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img src="/images/expiration.gif" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="16" height="16">';
}

function freshports_Expiration_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#expiration">' . freshports_Expiration_Icon($HoverText) . '</a>';
}

function freshports_Restricted_Icon($HoverText = '') {
	$Alt       = "Restricted";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img src="/images/restricted.jpg" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="16" height="16">';
}

function freshports_Restricted_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#restricted">' . freshports_Restricted_Icon($HoverText) . '</a>';
}

function freshports_Is_Interactive_Icon($HoverText = '') {
	$Alt       = "Is Interactive";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img src="/images/crt.gif" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="16" height="16" align="top">';
}

function freshports_Is_Interactive_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#is_interactive">' . freshports_Is_Interactive_Icon($HoverText) . '</a>';
}

function freshports_No_CDROM_Icon($HoverText = '') {
	$Alt       = "NO CDROM";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img src="/images/no_cdrom.jpg" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="16" height="16">';
}

function freshports_No_CDROM_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#no_cdrom">' . freshports_No_CDROM_Icon($HoverText) . '</a>';
}

function freshports_Ignore_Icon($HoverText = '') {
	$Alt       = "Ignore";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img src="/images/ignored.png" alt="' . $Alt . '" title="' . $HoverText . '" border="0" width="20" height="21;">';
}

function freshports_Ignore_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#ignore">' . freshports_Ignore_Icon($HoverText) . '</a>';
}

function freshports_New_Icon() {
	return '<img src="/images/new.gif" alt="new!" title="new!" border="0" width="28" height="11" HSPACE="2">';
}

function freshports_Mail_Icon() {
	return '<img src="/images/envelope10.gif" alt="Original commit" title="Original commit message" border="0" width="32" height="18">';
}

function freshports_Commit_Icon() {
	return '<img src="/images/copy.gif" alt="Commit details" title="FreshPorts commit message" border="0" width="16" height="16">';
}

function freshports_CVS_Icon() {
	return '<img src="/images/cvs.png" alt="CVS log" title="CVS log" border="0" width="19" height="17">';
}

function freshports_Watch_Icon() {
	return '<img src="/images/watch-remove.gif" alt="Click to remove this from your default watch list[s]" title="Click to remove this from your default watch list[s]" border="0" width="16" height="16">';
}

function freshports_Watch_Icon_Add() {
	return '<img src="/images/watch-add.gif" alt="' . CLICKTOADD . '" title="' . CLICKTOADD . '" border="0" width="16" height="16">';
}

function freshports_Watch_Icon_Empty() {
	return '<img src="/images/watch-empty.gif" alt="" title="" border="0" width="16" height="1">';
}

function freshports_Encoding_Errors() {
	return '<img src="/images/error.gif" alt="Encoding Errors (not all of the commit message was ASCII)" title="Encoding Errors (not all of the commit message was ASCII)" border="0" width="16" height="16">';
}

function freshports_Encoding_Errors_Link() {
	return '<a href="/' . FAQLINK . '#encodingerrors">' . freshports_Encoding_Errors() . '<a>';
}

function freshports_VuXML_Icon() {
	return '<img src="/images/vuxml.gif" alt="This port version is marked as vulnerable." title="This port version is marked as vulnerable." border="0" width="13" height="16">';
}

function freshports_VuXML_Icon_Faded() {
	return '<img src="/images/vuxml-faded.gif" alt="An older version of this port was marked as vulnerable." title="An older version of this port was marked as vulnerable." border="0" width="13" height="16">';
}

function freshports_Revision_Icon() {
	return '<img src="/images/revision.jpg" alt="View revision" title="view revision" border="0" width="11" height="15" align="top">';
}

function freshports_Diff_Icon() {
	return '<img src="/images/diff.png" alt="View diff" title="view diff" border="0" width="15" height="11" align="top">';
}


function freshports_VuXML_Link($PackageName, $HasCurrentVulns) {
	$HTML = '<a href="/vuxml.php?package=' . $PackageName . '">';
	if ($HasCurrentVulns) {
		$HTML .= freshports_VuXML_Icon();
	} else {
		$HTML .= freshports_VuXML_Icon_Faded();
	}
	$HTML .= '</a>';

	return $HTML;
}

function freshports_Watch_Link_Add($WatchListAsk, $WatchListCount, $ElementID) {
	$HTML = '<small><a href="/watch-list.php?';
	$HTML .= 'add='  . $ElementID;

	if ($WatchListAsk == 'ask') {
		$HTML .= '&amp;ask=1';
	}

	$HTML .= '"';
	$HTML .= ' title="' . CLICKTOADD . '"';

	$HTML .= '>' . freshports_Watch_Icon_Add() . '</a></small>';

	return $HTML;
}

function freshports_Watch_Link_Remove($WatchListAsk, $WatchListCount, $ElementID) {
	$HTML = '<small><a href="/watch-list.php?';
	$HTML .= 'remove=' . $ElementID;

	if ($WatchListAsk == 'ask') {
		$HTML .= '&amp;ask=1';
	}

	$HTML .= '"';
	$HTML .= ' title="on ' . $WatchListCount . ' watch list';
	if ($WatchListCount > 1) {
		$HTML .= 's';
	}
	$HTML .= '">' . freshports_Watch_Icon() . '</a></small>';
	
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
	if (strpos($message_id, MESSAGE_ID_OLD_DOMAIN) || strpos($message_id, MESSAGE_ID_NEW_DOMAIN)) {
		$HTML = '';
	} else {
		$HTML  = '<a href="' . htmlentities($freshports_mail_archive . $message_id) . '">';
		$HTML .= freshports_Mail_Icon();
		$HTML .= '</a>';
	}

	return $HTML;
}

function freshports_Commit_Flagged_Icon($Title = 'Commit Flagged') {
	return '<img src="/images/commit-flagged.gif" alt="' . $Title . '" title="' . $Title . '" border="0" width="16" height="16" align="middle">';
}

function freshports_Commit_Flagged_Not_Icon($Title = 'Commit Not Flagged') {
	return '<img src="/images/commit-flagged-not.gif" alt="' . $Title . '" title="' . $Title . '" border="0" width="16" height="16" align="middle">';
}

function freshports_Commit_Flagged_Link($message_id) {
	$HTML  = '<a href="/commit-flag.php?message_id=' . $message_id . '&amp;action=remove">';
	$HTML .= freshports_Commit_Flagged_Icon();
	$HTML .= '</a>';

	return $HTML;
}

function freshports_Commit_Flagged_Not_Link($message_id) {
	$HTML  = '<a href="/commit-flag.php?message_id=' . $message_id . '&amp;action=add">';
	$HTML .= freshports_Commit_Flagged_Not_Icon();
	$HTML .= '</a>';

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
	$HTML  = '<a href="' . FRESHPORTS_FREEBSD_CVS_URL . $element_name . '?rev=' . $revision . '&amp;content-type=text/x-cvsweb-markup">';
	$HTML .= freshports_CVS_Icon();
	$HTML .= '</a>';

	return $HTML;
}

function freshports_Commit_Link($message_id, $LinkText = '') {
	#
	# produce a link to the commit.  by default, we provide the graphic link.
	#

	$HTML = '<a href="/commit.php?message_id=' . $message_id . '">';
	if ($LinkText == '') {
		$HTML .= freshports_Commit_Icon();
	} else {
		$HTML .= $LinkText;
	}
	$HTML .= '</a>';

	return $HTML;
}

function freshports_Commit_Link_Port_URL($MessageID, $Category, $Port) {

	$HTML = 'http://' . $_SERVER['HTTP_HOST'] . '/commit.php?category=' . $Category . '&port=' . $Port . '&files=yes&message_id=' . $MessageID;

	return $HTML;
}

function freshports_Commit_Link_Port($MessageID, $Category, $Port) {

	$HTML  = '<a href="/commit.php?category=' . $Category . '&amp;port=' . $Port . '&amp;files=yes&amp;message_id=' . $MessageID . '">';
	$HTML .= freshports_Files_Icon();
	$HTML .= '</a>';

	return $HTML;
}

function freshports_MorePortsToShow($message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow) {
	$HTML  = "(Only the first $MaxNumberPortsToShow of $NumberOfPortsInThisCommit ports in this commit are shown above. ";
	$HTML .= freshports_Commit_Link($message_id, '<img src="/images/play.gif" alt="View all ports for this commit" title="View all ports for this commit" border="0" width="13" height="13">');
	$HTML .= ")";

	return $HTML;
}

function freshports_MoreCommitMsgToShow($message_id, $NumberOfLinesShown) {
	$HTML  = "(Only the first $NumberOfLinesShown lines of the commit message are shown above ";
	$HTML .= freshports_Commit_Link($message_id, '<img src="/images/play.gif" alt="View all of this commit message" title="View all of this commit message" border="0" width="13" height="13">');
	$HTML .= ")";

	return $HTML;
}

function freshports_CookieClear() {
	SetCookie("visitor", '', 0, '/');
}

function freshportsObscureHTML($email) {
	# why obscure?  The spammers catch up.
	return htmlentities($email);
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

	$HTML = '<a href="' . MAILTO . ':' . $new_addr . '" title="committed by this person">' . $committer . '</a>';

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

	$HTML = "<a href=\"" . MAILTO . ":$new_addr?$extrabits\">$committer</a>";

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

#echo "$LocalTimeAdjustment<br>";

	$HTML = '<br>
<table width="' . $TableWidth . '" border="0" align="center">
<tr>
	<td><a href="';

	if ($_SERVER["PHP_SELF"] == "/index.php") {
		$HTML .= 'other-copyrights.php';
	} else {
		$HTML .= '/';
	}
	$HTML .= '"><img src="' . $FreshPortsLogo . '" alt="' . $FreshPortsName . ' -- ' . $FreshPortsSlogan . '" title="' . $FreshPortsName . ' -- ' . $FreshPortsSlogan . '" width="' . $FreshPortsLogoWidth . '" height="' . $FreshPortsLogoHeight . '" border="0"></a>
';

    if (defined('SHOW_ANIMATED_BUG') && SHOW_ANIMATED_BUG)
    {
	  $HTML .= '<img src="/images/notbug.gif" alt="notbug" title="notbug">';
    }
    
    $HTML .= '<span class="amazon">If you buy from Amazon USA, please support us by using <a href="http://www.amazon.com/?tag=thfrdi0c-20">this link</a>.</span>';
	
	$HTML .= '</td>';

if (date("M") == 'Nov' && date("j") <= 12) {
	$HTML .= '	<td nowrap align="center" CLASS="sans" valign="bottom"><a href="http://www.google.ca/search?q=remembrance+day"><img src="/images/poppy.gif" width="50" height="48" border="0" alt="Remember" title="Remember"><br>I remember</a></td>';
} else {
	$HTML .= '	<td>';
	$HTML .= '<div id="followus"><div class="header">Follow us</div><a href="http://news.freshports.org/">Blog</a><br><a href="https://twitter.com/freshports/">Twitter</a><br><br></div>';

	$HTML .= '</td>';
	
}

$HTML .= '
</tr>
</table>
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
	<meta http-equiv="Content-Type" content="text/html; charset=' . FRESHPORTS_ENCODING . '">
';
}

function freshports_HEAD_main_items() {
	return '
	<LINK REL="SHORTCUT ICON" HREF="/favicon.ico">
	<meta name="MSSmartTagsPreventParsing" content="TRUE">

	<link rel="alternate" type="application/rss+xml" title="FreshPorts - The Place For Ports" href="http://' . $_SERVER['HTTP_HOST'] . '/backend/rss2.0.php">

	
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
	<meta name="PhorumVersion" content="<?php echo $phorumver; ?>">
	<meta name="PhorumDB" content="<?php echo $DB->type; ?>">
	<meta name="PHPVersion" content="<?php echo phpversion(); ?>">
	
<?
}

	echo freshports_IndexFollow($_SERVER["PHP_SELF"]);

	echo "</HEAD>\n";
}

function freshports_style($Phorum=0) {

	echo '	<link rel="stylesheet" href="/css/freshports.css" type="text/css">' . "\n";

	if ($Phorum) {
		echo '	<link rel="stylesheet" href="/phorum/' . phorum_get_file_name("css") . '" type="text/css">' . "\n";
	}
}

function freshports_body() {

GLOBAL $OnLoad;
GLOBAL $Debug;

echo "\n" . '<BODY bgcolor="#FFFFFF" TEXT="#000000" ';

# should we have an onload?
if ($OnLoad) {
	echo ' onLoad="' . $OnLoad . '"';
}

echo ">\n\n";

	if ($Debug) 
	{
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

		echo '<table border="1">';
		echo '<tr><td>ShowAds</td><td>'               . $ShowAds               . '</td></tr>';
		echo '<tr><td>BannerAd</td><td>'              . $BannerAd              . '</td></tr>';
		echo '<tr><td>BannerAdUnder</td><td>'         . $BannerAdUnder         . '</td></tr>';
		echo '<tr><td>BurstFrontPage120x160</td><td>' . $BurstFrontPage120x160 . '</td></tr>';
		echo '<tr><td>BurstFrontPage125x125</td><td>' . $BurstFrontPage125x125 . '</td></tr>';
		echo '<tr><td>FrontPageAdsPayPal</td><td>'    . $FrontPageAdsPayPal    . '</td></tr>';
		echo '<tr><td>FrontPageAdsAmazon</td><td>'    . $FrontPageAdsAmazon    . '</td></tr>';
		echo '<tr><td>FrontPageDaemonNews</td><td>'   . $FrontPageDaemonNews   . '</td></tr>';
		echo '<tr><td>ShowHeaderAds</td><td>'         . $ShowHeaderAds         . '</td></tr>';
		echo '<tr><td>HeaderAdsPayPal</td><td>'       . $HeaderAdsPayPal       . '</td></tr>';
		echo '<tr><td>HeaderAdAmazon</td><td>'        . $HeaderAdAmazon        . '</td></tr>';
		echo '<tr><td>HeaderAdsBurst125x125</td><td>' . $HeaderAdsBurst125x125 . '</td></tr>';
		echo '<tr><td>HeaderAdsBurst120x160</td><td>' . $HeaderAdsBurst120x160 . '</td></tr>';
		echo '</table>';
	}
}

function freshports_Category_Name($CategoryID, $db) {
	$sql = "select name from categories where id = " . pg_escape_string($CategoryID);

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
#   echo $HTML_Temp;
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
	$sql = "select pathname_id('ports/" . pg_escape_string($category) . '/' . pg_escape_string($port) . "') as id";

	$result = pg_exec($db, $sql);
	if (pg_numrows($result)) {
		$myrow = pg_fetch_array($result, 0);
		$PortID = $myrow["id"];
	}

	return $PortID;
}

function freshports_CategoryIDFromCategory($category, $db) {
   $sql = "select categories.id from categories where categories.name = '" . pg_escape_string($category) . "'";

   $result = pg_exec($db, $sql);
   if(pg_numrows($result)) {
      $myrow = pg_fetch_array($result, 0);
      $CategoryID = $myrow["id"];
   }
   
   return $CategoryID;
}

function freshports_SideBarHTML($Self, $URL, $Label, $Title) {
   if ($Self == $URL || ($Self == '/index.php' && $URL == '/')) {
      $HTML = $Label;
   } else {
      $HTML = '<a href="' . $URL . '" title="' . $Title . '">' . $Label . '</a>';
   }

   return $HTML;
}

function freshports_SideBarHTMLParm($Self, $URL, $Parm, $Label, $Title) {
   if ($Self == $URL || ($Self == '/index.php' && $URL == '/')) {
      $HTML = $Label;
   } else {
      $HTML = '<a href="' . $URL . $Parm . '" title="' . $Title . '">' . $Label . '</a>';
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


function freshports_depends_links($dbh, $DependsList, $BranchName = BRANCH_HEAD) {
	// sometimes they have multiple spaces in the data...
	$temp = str_replace('  ', ' ', $DependsList);
      
	// split each depends up into different bits
	$depends = explode(' ', $temp);
	$Count = count($depends);
	$HTML  = '';
	foreach ($depends as $depend) {
		// split one depends into the library and the port name (/usr/ports/<category>/<port>)

		$DependsArray = explode(':', $depend);

		// now extract the port and category from this port name
		// it might look like: /usr/local/bin/perl5.16.3:/usr/local/PORTS-head/lang/perl5.16
		// try it this way
		$CategoryPort = str_replace(PATH_TO_PORTSDIR . PORTSDIR_PREFIX . BRANCH_HEAD . '/', '', $DependsArray[1]) ;
		
		// if that has no effect, try it the old way:
		// we might have old stuff stored in the db.  Which makes me think: we should store it another way in the db.
		if ($CategoryPort == $DependsArray[1]) {
		   $CategoryPort = str_replace('/usr/ports/', '', $DependsArray[1]) ;
		}
		$CategoryPortArray = explode('/', $CategoryPort);

		$HTML .= '<li>' . freshports_link_text_to_port_single(basename($DependsArray[0]), $CategoryPortArray[0], $CategoryPortArray[1]) . '</li>';
	}

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

	$HTML .= 'on ' . $PortsMoved->date . '<br>';
	$HTML .= 'REASON: ' . $PortsMoved->reason . '<br>';

	return $HTML;
}

function freshports_PortsUpdating($port, $PortsUpdating) {
	$HTML  =                    _forDisplay($PortsUpdating->date);
	$HTML .= '<pre>Affects: ' . _forDisplay($PortsUpdating->affects) . '</pre>';
	$HTML .= '<pre>Author: '  . _forDisplay($PortsUpdating->author)  . '</pre>';
	$HTML .= '<pre>Reason: '  . _forDisplay($PortsUpdating->reason)  . '</pre>';

	return $HTML;
}

function freshports_navigation_bar_top() {
}

function freshports_copyright() {
	return '<small><a href="/legal.php" target="_top" title="This material is copyrighted">Copyright</a> &copy; ' . COPYRIGHTYEARS . ' <a href="http://www.langille.org/">Dan Langille</a>. All rights reserved.</small>';
}

function FormatTime($Time, $Adjustment, $Format) {
#echo "$Time<br>";
#echo time() . "<br>";
	return date($Format, strtotime($Time) + $Adjustment);
}

function freshports_PortCommitsHeader($port) {
	# print the header for the commits for a port

	GLOBAL $User;
	
	$HTML = '';

	$HTML .= '<table border="1" width="100%" cellspacing="0" cellpadding="5">' . "\n";
	$HTML .= "<tr>\n";

	$Columns = 3;
	$HTML .= freshports_PageBannerText("Commit History - (may be incomplete: see SVNWeb link above for full details)", $Columns);

	$HTML .= '<tr><td width="180"><b>Date</b></td><td><b>By</b></td><td><b>Description</b></td>';

	$HTML .= "</tr>\n";

	return $HTML;
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

	$HTML = '';

	if (IsSet($VuXMLList[$commit->id])) {
		# yes, the most recent commit has been marked as vulnerable
		if ($port->IsSlavePort()) {
			# Yes, we have a slave port here
			# is the version for this commit, the same as the port?
			$CommitVersion = freshports_PackageVersion($commit->{'port_version'},  $commit->{'port_revision'},  $commit->{'port_epoch'});
			$PortVersion   = freshports_PackageVersion($port->{'version'},         $port->{'revision'},         $port->{'epoch'});

			if ($CommitVersion != $PortVersion) {
				$HTML .= "<p><b>NOTE</b>: This slave port may no longer be vulnerable to issues shown below because the ";
				$HTML .= '<a href="/' . $port->master_port . '/">master port</a>' . " has been updated.</p>\n";
			}
		}
	}

	return $HTML;
}

function freshports_PortCommits($port, $PageNumber = 1, $NumCommitsPerPage = 100) {
	# print all the commits for this port
	

	GLOBAL $User;

	$HTML = '';

	require_once('Pager/Pager.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_log_ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');

	$Commits = new Commit_Log_Ports($port->dbh);
	$Commits->Debug = 0;

	#	
	# get the count without excuting the whole query
	# we don't want to pull back all the data.
	#
	$NumCommits = $Commits->Count($port->id);
	$params = array(
			'mode'        => 'Sliding',
			'perPage'     => $NumCommitsPerPage,
			'delta'       => 5,
			'totalItems'  => $NumCommits,
			'urlVar'      => 'page',
			'currentPage' => $PageNumber,
			'spacesBeforeSeparator' => 1,
			'spacesAfterSeparator'  => 1,
			'append'                => false,
			'path'					=> '/' . $port->category . '/' . $port->port,
			'fileName'              => '?page=%d',
			'altFirst'              => 'First Page',
			'firstPageText'         => 'First Page',
			'altLast'               => 'Last Page',
			'lastPageText'          => 'Last Page',
		);
	$Pager = @Pager::factory($params);
	
	// Results from methods:
	if ($Commits->Debug) {
		echo '<pre>';
		echo 'getCurrentPageID()...: '; var_dump($Pager->getCurrentPageID());
		echo 'getNextPageID()......: '; var_dump($Pager->getNextPageID());
		echo 'getPreviousPageID()..: '; var_dump($Pager->getPreviousPageID());
		echo 'numItems()...........: '; var_dump($Pager->numItems());
		echo 'numPages()...........: '; var_dump($Pager->numPages());
		echo 'isFirstPage()........: '; var_dump($Pager->isFirstPage());
		echo 'isLastPage().........: '; var_dump($Pager->isLastPage());
		echo 'isLastPageComplete().: '; var_dump($Pager->isLastPageComplete());
		echo '$Pager->range........: '; var_dump($Pager->range);
		echo '</pre>';
	}

	$links = $Pager->GetLinks();

	$NumCommitsHTML = '<p align="left">Number of commits found: ' . $NumCommits;

	$Offset = 0;
	$PageLinks = $links['all'];
	$PageLinks = str_replace('&amp;page=1"', '"', $PageLinks);
	if ($PageLinks != '') {
		$offset = $Pager->getOffsetByPageId();
		$NumOnThisPage = $offset[1] - $offset[0] + 1;
		$Offset = $offset[0] - 1;
		$NumCommitsHTML .= " (showing only $NumOnThisPage on this page)";
		unset($offset);
	}

	$NumCommitsHTML .= '</p>';

	if ($PageLinks != '') {
		$PageLinksHTML .= '<p align="center">' . $PageLinks . '</p>';
	} else {
		$PageLinksHTML = '';
	}

	$HTML .= $NumCommitsHTML . $PageLinksHTML;

	if ($Commits->Debug) echo "PageNumber='$PageNumber'<br>Offset='$Offset'<br>";
	
	$Commits->LimitSet($NumCommitsPerPage);
	$Commits->OffsetSet($Offset);
	$NumRows = $Commits->FetchInitialise($port->id);
	$port->LoadVulnerabilities();

	$Commits->FetchNthCommit(0);

	$HTML .= freshports_CheckForOutdatedVulnClaim($Commits, $port, $port->VuXML_List);

	$HTML .= freshports_PortCommitsHeader($port);

	$LastVersion = '';
	for ($i = 0; $i < $NumRows; $i++) {
		$Commits->FetchNthCommit($i);
		$HTML .= freshports_PortCommitPrint($Commits, $port->category, $port->port, $port->VuXML_List);
	}

	$HTML .= freshports_PortCommitsFooter($port);
	
	$HTML .= $NumCommitsHTML . $PageLinksHTML;

	return $HTML;
}

function freshports_PortCommitPrint($commit, $category, $port, $VuXMLList) {
	GLOBAL $DateFormatDefault;
	GLOBAL $TimeFormatDefault;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
	GLOBAL $User;

	$HTML = '';

	# print a single commit for a port
	$HTML .= "<tr><td valign='top' NOWRAP>";
	

	$HTML .= $commit->commit_date . '<br>';
	// indicate if this port needs refreshing from CVS
	if ($commit->{'needs_refresh'}) {
		$HTML .= " " . freshports_Refresh_Icon_Link() . "\n";
	}
	$HTML .= freshports_Email_Link($commit->message_id);

#	$HTML .= '&nbsp;&nbsp;'. freshports_Commit_Link($commit->message_id);

	if ($commit->EncodingLosses()) {
		$HTML .= '&nbsp;'. freshports_Encoding_Errors_Link();
	}
	$HTML .= ' ';

	$HTML .= freshports_Commit_Link_Port($commit->message_id, $category, $port);

	# output the VERSION and REVISION
	$PackageVersion = freshports_PackageVersion($commit->{'port_version'},  $commit->{'port_revision'},  $commit->{'port_epoch'});
	if (strlen($PackageVersion) > 0) {
    	$HTML .= '&nbsp;&nbsp;<big><b>' . $PackageVersion . '</b></big>';
	}

	$HTML .= '<br>';

	if (isset($commit->svn_revision)) {
	  $HTML .= freshports_svnweb_ChangeSet_Link($commit->svn_revision, $commit->svn_hostname, $commit->path_to_repo);
        }

	if ($commit->stf_message != '') {
		$HTML .= '&nbsp; ' . freshports_SanityTestFailure_Link($commit->message_id);
	}

	if (IsSet($VuXMLList[$commit->id])) {
		$HTML .= '&nbsp;<a href="/vuxml.php?vid=' . $VuXMLList[$commit->id] . '">' . freshports_VuXML_Icon() . '</a>';
	}

	$HTML .= "</td>\n";
	$HTML .= '    <td valign="top">';
	$HTML .= freshports_CommitterEmailLink($commit->committer) . '&nbsp;' . freshports_Search_Committer($commit->committer);;

	$HTML .= "</td>\n";
	$HTML .= '    <td valign="top" width="*">';

	$HTML .= freshports_CommitDescriptionPrint($commit->description, $commit->encoding_losses, $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($commit->message_id, $freshports_CommitMsgMaxNumOfLinesToShow));

	$HTML .= "</td>\n";

	$HTML .= "</tr>\n";

	return $HTML;
}

function freshports_PortCommitsFooter($port) {
	# print the footer for the commits for a port
	return "</table>\n";
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
	return freshports_DescriptionPrint($description, $encoding_losses, $maxnumlines, $URL);
}

function freshports_CommitDescriptionPrint($description, $encoding_losses, $maxnumlines=0, $URL='') {
	return freshports_DescriptionPrint($description, $encoding_losses, $maxnumlines, $URL, true);
}

function freshports_DescriptionPrint($description, $encoding_losses, $maxnumlines = 0, $URL = '', $Process_PRs = false) {

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');

	$shortened = freshports_Head($description, $maxnumlines);
	$HTML  = '<PRE CLASS="code">';

	$HTML .= _forDisplay(freshports_wrap($shortened), $Process_PRs);

	$HTML .= '</PRE>';

	if (strlen($shortened) < strlen($description)) {
		$HTML .= $URL;
	}

	return $HTML;
}

function freshports_GetNextValue($sequence, $dbh) {
	$sql = "select nextval('" . pg_escape_string($sequence) . "')";

#	echo "\$sql = '$sql'<br>";

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
	return '<td align="left" bgcolor="' . BACKGROUND_COLOUR . '" height="29" COLSPAN="' . $ColSpan . ' "><FONT COLOR="#FFFFFF"><big><big>' . $Text . '</big></big></FONT></td>' . "\n";
}


function freshports_UserSendToken($UserID, $dbh) {
	#
	# send the confirmation token to the user
	#

	GLOBAL $FreshPortsSlogan;

	$sql = "select email, token 
	          from users, user_confirmations
	         where users.id = " . pg_escape_string($UserID) . "
	           and users.id = user_confirmations.user_id";

#	echo "\$sql = '$sql'<br>";

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

function freshports_ShowFooter($PhorumBottom = 0) {
	GLOBAL $TableWidth;
	GLOBAL $Statistics;
	GLOBAL $ShowPoweredBy;
	GLOBAL $ShowAds;

	$HTML = '<table width="' . $TableWidth . '" border="0" align="center">
<tr><td>';


	if ($ShowAds) {
		$HTML .= "<div align=\"center\">\n";
		if ($PhorumBottom) {
			$HTML .= Ad_728x90PhorumBottom();
		} else {
			$HTML .= Ad_728x90();
		}
		$HTML .= "</div>\n";
	}

	$HTML .= '
<HR>
<table width="98%" border="0">
';

	if (IsSet($ShowPoweredBy)) {
		$HTML .= '
<tr>

<td align="center">

<a href="http://www.freebsd.org/"><img src="/images/pbfbsd2.gif"
alt="powered by FreeBSD" border="0" width="171" height="64"></a>

&nbsp;

<a href="http://www.php.net/"><img src="/images/php-med-trans-light.gif"
alt="powered by php" border="0" width="95" height="50"></a>
&nbsp;

<a href="http://www.postgresql.org/"><img src="/images/pg-power.jpg"
alt="powered by PostgreSQL" border="0" width="164" height="59"></a>


</td></tr>
<tr><td align="center">

<a href="http://www.phorum.org/"><img src="/phorum/images/phorum.gif"
alt="powered by phorum" border="0" width="200" height="50"></a>


&nbsp;&nbsp;&nbsp;

<a href="http://www.apache.org/"><img src="/images/apache_pb.gif" 
alt="powered by apache" border="0" width="259" height="32"></a>

<HR>

</tr>
';
	}

	$HTML .= '
<tr><td>
<table width="100%">
<tr>
<td align="left"  valign="top">
<small>' . SPONSORS . '</small>
</td>
<td align="right" valign="top">
<small>
Valid 
<a href="http://validator.w3.org/check/referer" title="We like to keep our HTML valid">HTML</a>, 
<a href="http://jigsaw.w3.org/css-validator/check/referer" title="We like to have valid CSS">CSS</a>, and
<a href="http://feedvalidator.org/check.cgi?url=http://' . $_SERVER['HTTP_HOST'] . '/backend/rss2.0.php" title="Valid RSS is good too">RSS</a>.
</small>
<br>' . freshports_copyright() . '
</td></tr>
</table>
</td></tr>
</table>';

	$HTML .= '
</td></tr>
</table>
';

	if ($ShowAds) {
		$HTML .= freshports_GoogleAnalytics();
	}
	
	$HTML .= '<script src="/javascript/freshports.js" type="text/javascript"></script>';

	$Statistics->Save();

	return $HTML;
}

function freshports_GoogleAnalytics() {
	$HTML = '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-408525-1";
urchinTracker();
</script>
';

	return $HTML;
}

function freshports_SideBar() {

	GLOBAL $User;
	$ColumnWidth = 160;

	$OriginLocal = rawurlencode($_SERVER["REQUEST_URI"]);

	$HTML = '
  <table width="' . $ColumnWidth . '" border="1" cellspacing="0" cellpadding="5">
        <tr>
         <td bgcolor="' . BACKGROUND_COLOUR . '" height="30"><FONT COLOR="#FFFFFF"><big><b>Login</b></big></FONT></td>
        </tr>
        <tr>

         <td NOWRAP>';

	if (IsSet($_COOKIE["visitor"])) {
		$visitor = $_COOKIE["visitor"];
	}

	if (IsSet($visitor)) {
		GLOBAL $User;
		$HTML .= '<FONT SIZE="-1">Logged in as ' . $User->name . "</FONT><br>";

		if ($User->emailbouncecount > 0) {
			$HTML .= '<img src="/images/warning.gif" border="0" height="32" width="32"><img src="/images/warning.gif"  border="0" height="32" width="32"><img src="/images/warning.gif" border="0"height="32" width="32"><br>';
			$HTML .= '<FONT SIZE="-1">your email is <a href="/bouncing.php?origin=' . $OriginLocal. '">bouncing</a></FONT><br>';
			$HTML .= '<img src="/images/warning.gif" border="0" height="32" width="32"><img src="/images/warning.gif" border="0" height="32" width="32"><img src="/images/warning.gif" border="0" height="32" width="32"><br>';
		}
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/customize.php',        "?origin=$OriginLocal", "Customize", "Customize your settings"              ) . '</FONT><br>';

		if (preg_match("/.*@FreeBSD.org/i", $User->email)) {
			$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/committer-opt-in.php', '', "Committer Opt-in", "Committers can receive reports of Sanity Test Failures"       ) . '</FONT><br>';
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
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/logout.php',                 $args,                  "Logout",                  "Logout of the website"                  ) . '</FONT><br>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/my-flagged-commits.php',                 $args,      "My Flagged Commits",      "List of commits you have flagged"       ) . '</FONT><br>';
	} else {
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/login.php',                  "?origin=$OriginLocal", "User Login",              "Login to the website"                   ) . '</FONT><br>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/new-user.php',               "?origin=$OriginLocal", "Create account",          "Create an account"                      ) . '</FONT><br>';
	}

	$HTML .= '
   </td>
   </tr>
   </table>

' . '<div align="center">';

	$HTML .='

<p><small>' . SPONSORS  . '</small></p>
</div>';

	$HTML .= '	
<table width="' . $ColumnWidth . '" border="1" cellspacing="0" cellpadding="5">
	<tr>
		<td bgcolor="' . BACKGROUND_COLOUR . '" height="30"><FONT COLOR="#FFFFFF"><big><b>This site</b></big></FONT></td>
	</tr>
	<tr>
	<td valign="top">
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/about.php",           "What is FreshPorts?", "A bit of background on FreshPorts"    ) . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/authors.php",         "About the authors",   "Who wrote this stuff?"                ) . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/faq.php",             "FAQ",                 "Frequently Asked Questions"           ) . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/how-big-is-it.php",   "How big is it?",      "How many pages are in this website?"  ) . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/release-2004-10.php", "The latest upgrade!", "Details on the latest website upgrade") . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/privacy.php",         "Privacy",             "Our privacy statement"                ) . '</FONT><br>
	<FONT SIZE="-1"><a href="/phorum/" title="Discussion Forums">Forums</a></FONT><br>
	<FONT SIZE="-1"><a href="http://news.freshports.org/" title="All the latest FresHPorts news">Blog</a></FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/contact.php",         "Contact",             "Contact details"                      ) . '</FONT><br>
	</td>
	</tr>
</table>
<br>
<table width="' . $ColumnWidth . '" border="1" cellspacing="0" cellpadding="5">
	<tr>
		<td bgcolor="' . BACKGROUND_COLOUR . '" height="30"><FONT COLOR="#FFFFFF"><big><b>Search</b></big></FONT></td>
	</tr>
	<tr>

	<td>';

	GLOBAL $dbh;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/searches.php');


	$Searches = new Searches($dbh);
	$HTML .= $Searches->GetFormSimple('&nbsp;');

	$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/search.php', '', "more...", "Advanced Searching options") . '</FONT><br>
	</td>
</tr>
</table>

';
	if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/../dynamic/vuln-latest.html")) {
$HTML .= '<br>
<table width="' . $ColumnWidth . '" border="1" cellspacing="0" cellpadding="5">
	<tr>
		<td bgcolor="' . BACKGROUND_COLOUR . '" height="30"><FONT COLOR="#FFFFFF"><big><b>Latest Vulnerabilities</b></big></FONT></td>
	</tr>
	<tr><td>
	' . file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../dynamic/vuln-latest.html") . "\n" . '
	</td></tr>
	<tr><td align="center"><p><sup>*</sup> - modified, not new</p><p><a href="/vuxml.php?all">All vulnerabilities</a></p>
</table>
<br>';
	} else {
		$HTML .= "<br>\n";
	}

	$HTML .= '

<table width="' . $ColumnWidth . '" border="1" cellspacing="0" cellpadding="5">
	<tr>
		<td bgcolor="' . BACKGROUND_COLOUR . '" height="30"><FONT COLOR="#FFFFFF"><big><b>Ports</b></big></FONT></td>
	</tr>
	<tr>
	<td valign="top">

	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/",                     "Home",             "FreshPorts Home page"       )   . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/categories.php",       "Categories",       "List of all Port categories")   . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/ports-deleted.php",    "Deleted ports",    "All deleted ports"          )   . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/sanity_test_failures.php",    "Sanity Test Failures",    "Things that didn't go quite right..."          )   . '</FONT><br>
	<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/backend/newsfeeds.php",    "Newsfeeds",    "Newsfeeds for just about everything"          )   . '</FONT><br>
	
	</td>
	</tr>
</table>';


if (IsSet($visitor)) {


$HTML .= '<br>
<table width="' . $ColumnWidth . '" border="1" cellspacing="0" cellpadding="5">
	<tr>
		<td bgcolor="' . BACKGROUND_COLOUR . '" height="30"><FONT COLOR="#FFFFFF"><big><b>Watch Lists</b></big></FONT></td>
	</tr>
	<tr>
	<td valign="top">';

		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/pkg_upload.php',             '',                     "Upload",               "Upoad a file containing a list of ports you want to add to your watch list") . '</FONT><br>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch-categories.php',       '',                     "Categories",           "Search through categories for ports to add to your watch list"             ) . '</FONT><br>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch-list-maintenance.php', '',                     "Maintain",             "Maintain your watch list[s]"                                               ) . '</FONT><br>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch.php',                  '',                     "Ports",                "Your list of watched ports"                                                ) . '</FONT><br>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/backend/watch-list.php',     '',                     "Personal Newsfeeds",   "A list of news feeds for your watched lists"                               ) . '</FONT><br>';
		$HTML .= '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/report-subscriptions.php',   '',                     "Report Subscriptions", "Maintain your list of subscriptions"                                       ) . '</FONT><br>';

$HTML .= '		
	</td>
	</tr>
</table>';
	}


	GLOBAL $ShowAds;

	if ($ShowAds) {
		$HTML .= '<br><table border="0" cellpadding="5">
		  <tr><td align="center">
		';
		$HTML .= Ad_160x600();
		$HTML .= '</td></tr>
		  </table>
		 ';
	}

	$HTML .= '<br>

<table width="' . $ColumnWidth . '" border="1" cellspacing="0" cellpadding="5">
	<tr>
		<td COLSPAN="2" bgcolor="' . BACKGROUND_COLOUR . '" height="30"><FONT COLOR="#FFFFFF"><big><b>Statistics</b></big></FONT></td>
	</tr>
	<tr>
	<td valign="top">

' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/graphs.php",        "Graphs", "Everyone loves statistics!")   . '<br>
' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/graphs2.php",        "NEW Graphs (Javascript)", "Everyone loves statistics!")   . '<br>
' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/stats/",            "Traffic", "Traffic to this website");

	if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/../dynamic/stats.html")) {
		$HTML .= '<br>
' . file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../dynamic/stats.html") . "\n";
	}

	$HTML .= '
	</td>
	</tr>
</table>


';

	return $HTML;

}

function freshports_LinkToDate($Date, $Text = '') {
	$URL = '<a href="/date.php?date=' . date("Y/n/j", $Date) . '">';
	if ($Text != '') {
		$URL .= $Text;
	} else {
		$URL .= date("j F", $Date);
	}

	$URL .= '</a>';

	return $URL;
}

function freshports_ErrorMessage($Title, $ErrorMessage) {
	$HTML = '
<table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" border="1">
<tr><td valign=TOP>
<table width="100%">
<tr>
	' . freshports_PageBannerText($Title) . '
</tr>
<tr bgcolor="#ffffff">
<td>
  <table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr valign=top>
   <td><img src="/images/warning.gif"></td>
   <td width="100%">
  <p>' .  "WARNING: $ErrorMessage" . '</p>
 <p>If you need help, please ask in the forum. </p>
 </td>
 </tr>
 </table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br>';

	return $HTML;
}

function DisplayAnnouncements($Announcement) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');

	$HTML = '';
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
	return $HTML;

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


function freshports_DistFilesSurveyURL($Category, $Port) {
	# we have a problem with + in portnames.
	# works: http://portsmon.freebsd.org/portoverview.py?category=editors&portname=vim6%2Bruby
	# fails: http://portsmon.freebsd.org/portoverview.py?category=editors&portname=vim6+ruby
	#
	return '<a href="' . DISTFILESSURVEYURL . urlencode($Category) . '/' . urlencode($Port) . '/" title="Distfiles Availability">Distfiles Availability</a>';
}


function freshports_PortsMonitorURL($Category, $Port) {
	# we have a problem with + in portnames.
	# works: http://portsmon.freebsd.org/portoverview.py?category=editors&portname=vim6%2Bruby
	# fails: http://portsmon.freebsd.org/portoverview.py?category=editors&portname=vim6+ruby
	#
	return '<a href="' . PORTSMONURL . '?category=' . urlencode($Category) . '&amp;portname=' . urlencode($Port) . '" title="Ports Monitor">PortsMon</a>';
}


function freshports_MessageIDConvertOldToNew($message_id) {
	# Any cvs-all message before 2003-02-19 did not contain a message_id.
	# so we gave them one in the @freshports.org domain.
	# To avoid spam attempts to deliver email to those addresses,
	# the message ids have been changed to @dev.null.freshports.org
	# this code looks for @freshports.org and changes it to @dev.null.freshports.org
	#
	$message_id = str_replace(MESSAGE_ID_OLD_DOMAIN, MESSAGE_ID_NEW_DOMAIN, $message_id);

	return $message_id;
}

function freshports_RedirectPermanent($URL) {
	#
	# My thanks to nne van Kesteren who posted this solution
	# at http://annevankesteren.nl/archives/2005/01/permanent-redirect
	#

	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $URL");
}


define('LAST_MODIFIED_FORMAT', 'D, d M Y H:i:s T'); // eg Sun, 10 Jul 2005 22:49:33 GMT


function freshports_LastModified() {
	# get the last modified date of this file.
	#
	$UnixTime     = filemtime($_SERVER['SCRIPT_FILENAME']);
	$LastModified = gmdate(LAST_MODIFIED_FORMAT, $UnixTime);

	return $LastModified;
}

function freshports_LastModified_Dynamic() {
	# Use the current date/time as the modified date.
	#
	$LastModified = gmdate(LAST_MODIFIED_FORMAT);

	return $LastModified;
}

function freshports_ConditionalGetUnix($UnixTime) {
	// A PHP implementation of conditional get, see 
	//   http://fishbowl.pastiche.org/archives/001132.html
	// Based upon code from http://simon.incutio.com/archive/2003/04/23/conditionalGet

	$ETag         = gmdate('Y-m-d H:i:s', $UnixTime);
	$LastModified = gmdate(LAST_MODIFIED_FORMAT, $UnixTime);

	// Send the headers
	header('Last-Modified: ' . $LastModified);
	header('ETag: "'         . $ETag . '"');

	$if_modified_since = false;
	$if_none_match     = false;

	// See if the client has provided the required headers
	if (IsSet($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$if_modified_since = stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']);
	}

	if (IsSet($_SERVER['HTTP_IF_NONE_MATCH'])) {
		$if_none_match = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
	}

	if (!$if_modified_since && !$if_none_match) {
		return;
	}

	// At least one of the headers is there - check them
	if ($if_none_match && $if_none_match != $ETag) {
		return; // etag is there but doesn't match
	}

	if ($if_modified_since && $if_modified_since != $LastModified) {
		return; // if-modified-since is there but doesn't match
	}

	// Nothing has changed since their last request - serve a 304 and exit
	header('HTTP/1.0 304 Not Modified');
	exit;
}

function freshports_ConditionalGet($LastModified) {
	// A PHP implementation of conditional get, see 
	//   http://fishbowl.pastiche.org/archives/001132.html
	// Based upon code from http://simon.incutio.com/archive/2003/04/23/conditionalGet

	$UnixTime = strtotime($LastModified);
	freshports_ConditionalGetUnix($UnixTime);
}

#
# obtained from http://ca3.php.net/manual/en/function.is-int.php 
# on 2 August 2005. Posted by phpContrib (A T) esurfers d o t c o m
# on 06-Nov-2003 03:42

function freshports_IsInt($x) {
   return ( is_numeric ($x ) ?  intval(0+$x ) ==  $x  :  false ); 
}

function freshports_GetPortID($db, $category, $port) {
	$sql = "select Port_ID('" . pg_escape_string($category) . "', '" . pg_escape_string($port) . "')";

	$result = pg_exec($db, $sql);
	if (!$result) {
		syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error() . ' - ' . $sql);
		die('something terrible has happened!');
	}

	$myrow = pg_fetch_array($result, 0);

	return $myrow['port_id'];
}

function freshports_GetElementID($db, $category, $port) {
	$sql = "select Element_ID('" . pg_escape_string($category) . "', '" . pg_escape_string($port) . "')";

	$result = pg_exec($db, $sql);
	if (!$result) {
		echo "error " . pg_errormessage();
		exit;
	}

	$myrow = pg_fetch_array($result, 0);

	return $myrow['element_id'];
}

function freshports_OnWatchList($db, $UserID, $ElementID) {
	$sql = "select OnWatchList(" . pg_escape_string($UserID) . ", " . pg_escape_string($ElementID) . ")";

	$result = pg_exec($db, $sql);
	if (!$result) {
		echo "error " . pg_errormessage();
		exit;
	}

	$myrow = pg_fetch_array($result, 0);

	return $myrow['onwatchlist'];
}

function freshports_MessageIdToRepoName($message_id)
{
  $repo = array(
            '/\@svn.freebsd.org$/i'     => FREEBSD_REPO_SVN,
            '/\@repoman.freebsd.org$/i' => FREEBSD_REPO_CVS,
            '/\@repo.freebsd.org$/i'    => FREEBSD_REPO_SVN);

  # given a message id, figure out what repo it came from
  $RepoName = '';
  foreach($repo as $message_id_format => $reponame)
  {
    if (preg_match($message_id_format, $message_id))
    {
      $RepoName = $reponame;
    }
  }
  
  if (Empty($RepoName)) {
    $RepoName = FREEBSD_REPO_SVN;
  }

  return $RepoName;
}

function freshports_pathname_to_repo_name($WhichRepo, $pathname)
{
  # strip the repo name from the pathname
  # e.g. ports/sysutils to sysutils
  $RepoNames = array(
    FREEBSD_REPO_SVN => 'ports'
  );
  
  $AdjustPathname = array(
    FREEBSD_REPO_SVN => array('match' => '|^/?ports/|', 'replace' => '')
  );
  

  $repo_file_name = '';
  switch($WhichRepo)
  {
    case FREEBSD_REPO_SVN:
      # given ports/www/p5-App-Nopaste/Makefile, we want something like: http://svn.freebsd.org/ports/head/www/p5-App-Nopaste/Makefile
      $RepoName = $RepoNames[$WhichRepo];
      $match   = $AdjustPathname[$WhichRepo]['match'];
      $replace = $AdjustPathname[$WhichRepo]['replace'];
      $repo_file_name = $RepoName . '/head/' . preg_replace($match, $replace, $pathname);
      break;

    default:
      $repo_file_name = $pathname;
      break;
  }

  return $repo_file_name;
}

function _forDisplay($string, $flags = NULL, $encoding = FRESHPORTS_ENCODING) {
  # can't put this in the header.  See http://php.net/manual/en/functions.arguments.php
  if ($flags === NULL) {
    $flags = ENT_COMPAT | ENT_HTML401;
  }
  # does special magic for outputing stuff to a webpage.
  # e.g. htmlspecialchars($port->long_description, ENT_COMPAT | ENT_HTML401, 'ISO-8859-15')
	    
  return htmlspecialchars($string, $flags, $encoding);
}

define('EVERYTHING', 'FreshPorts has everything you want to know about <a href="http://www.freebsd.org/">FreeBSD</a> software, ports, packages,
applications, whatever term you want to use.');

openlog('FreshPorts', LOG_PID, LOG_SYSLOG);
#syslog(LOG_NOTICE, $_SERVER['SCRIPT_URL']);
