<?php
	#
	# $Id: freshports.php,v 1.51 2013-05-12 14:47:12 dan Exp $
	#
	# Copyright (c) 1998-2022 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	if ($ShowAds) require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/ads.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../configuration/freshports.conf.php');

	if (IsSet($ShowAnnouncements)) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');
	}

	require_once('/usr/local/share/phpmailer/PHPMailer.php');
	require_once('/usr/local/share/phpmailer/SMTP.php');

#
# special HTMLified mailto to foil spam harvesters
#
DEFINE('MAILTO',                'mailto');
DEFINE('COPYRIGHTYEARS',        '2000-' . date('Y'));
DEFINE('COPYRIGHTHOLDER',       'Dan Langille');
DEFINE('COPYRIGHTHOLDERURL',    'https://www.langille.org/');
DEFINE('URL2LINK_CUTOFF_LEVEL', 0);
DEFINE('FAQLINK',               'faq.php');
DEFINE('PORTSMONURL',           'http://portsmon.freebsd.org/portoverview.py');
DEFINE('NOBORDER',              'borderless');
DEFINE('BORDER',                'bordered');

DEFINE('UNMAINTAINTED_ADDRESS', 'ports@freebsd.org');

DEFINE('CLICKTOADD', 'Click to add this to your default watch list[s]');

DEFINE('SPONSORS', 'Servers and bandwidth provided by <br><a href="https://www.nyi.net/" rel="noopener noreferrer" TARGET="_blank">New York Internet</a>, <a href="https://www.ixsystems.com/"  rel="noopener noreferrer" TARGET="_blank">iXsystems</a>, and <a href="https://www.rootbsd.net/" rel="noopener noreferrer" TARGET="_blank">RootBSD</a>');

DEFINE('FRESHPORTS_ENCODING', 'UTF-8');
DEFINE('FRESHPORTS_TIMEZONE', 'UTC');

if ($Debug) echo "'" . $_SERVER['DOCUMENT_ROOT'] . '/../classes/watchnotice.php<br>';

date_default_timezone_set(FRESHPORTS_TIMEZONE);

require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watchnotice.php');

function freshports_MainTable() {
	return '<table class="fullwidth borderless">
';
}

function freshports_Search_Depends_All($CategoryPort) {
	return '<a href="/search.php?stype=depends_all&amp;method=match&amp;query=' . htmlentities($CategoryPort) . '">' .
	      freshports_Search_Icon('search for ports that depend on this port') . '</a>';
}

function freshports_Search_For_Bugs($CategoryPort) {
  $SearchURL = "https://bugs.freebsd.org/bugzilla/buglist.cgi?component=Individual%20Port%28s%29&amp;list_id=28394&amp;product=Ports%20%26%20Packages&amp;query_format=advanced&amp;resolution=---" . 
    "&amp;short_desc=" . urlencode($CategoryPort) . "&amp;short_desc_type=allwordssubstr";

  return '<a href="' . $SearchURL . '"  rel="nofollow noopener noreferrer">' . freshports_Bugs_Find_Icon() . '</a>';
}

function freshports_Report_A_Bug($CategoryPort) {
  $SearchURL = "https://bugs.freebsd.org/bugzilla/enter_bug.cgi?component=Individual%20Port%28s%29&amp;product=Ports%20%26%20Packages&amp;short_desc=" . urlencode($CategoryPort);

  return '<a href="' . $SearchURL . '"  rel="noopener noreferrer nofollow">' . freshports_Bugs_Report_Icon() . '</a>';
}

function freshports_SanityTestFailure_Link($message_id) {
	return '<a href="/sanity_test_failures.php?message_id=' . $message_id . '">' . freshports_SanityTestFailure_Icon() . '</a>';
}

function freshports_cvsweb_Diff_Link($pathname, $previousRevision, $revision_name)
{
  $pathname = str_replace('/ports/head/', '/ports/', $pathname);
  $HTML  = '<a href="' . FRESHPORTS_FREEBSD_CVS_URL . $pathname . '.diff?r1=' . $previousRevision . ';r2=' . $revision_name . ' " rel="noopener noreferrer">';
  $HTML .= freshports_Diff_Icon() . '</a> ';

  return $HTML;
}

function freshports_Convert_Subversion_Path_To_Git($pathname, $branch = BRANCH_HEAD)
{
  # the pathnames in the FreshPorts database reflect the physical pathname on disk
  # yeah, repo changes means different paths. Hopefully, we can do that all
  # external to the database instead of in the database.
  if ($branch == BRANCH_HEAD) {
    return str_replace('/ports/head/',             '', $pathname);
  } else {
    return str_replace("/ports/branches/$branch/", '', $pathname);
  }
}

function freshports_cvsweb_Annotate_Link($pathname, $revision_name)
{
  $pathname = str_replace('/ports/head/', '/ports/', $pathname);
  $HTML  = ' <a href="' . FRESHPORTS_FREEBSD_CVS_URL . $pathname . '?annotate=' . $revision_name . ' " rel="noopener noreferrer">';
  $HTML .= freshports_Revision_Icon() . '</a>';

  return $HTML;
}

function freshports_cvsweb_Revision_Link($pathname, $revision_name)
{
  $pathname = str_replace('/ports/head/', '/ports/', $pathname);
  $HTML = '<a href="' . FRESHPORTS_FREEBSD_CVS_URL . $pathname . '#rev' . $revision_name . '" rel="noopener noreferrer">';

  return $HTML;
}

function freshports_git_commit_Link_freebsd($revision, $hostname, $path) {
  return '<a href="https://' . htmlentities($hostname) . $path . '/commit/?id=' . htmlentities($revision) .  '">' . freshports_Git_Icon('commit hash:' . $revision) . '</a>';
}

function freshports_git_commit_Link_github($revision, $hostname, $path) {
  return '<a href="https://github.com/FreeBSD/freebsd-ports/commit/' . htmlentities($revision) .  '">' . freshports_GitHub_Icon('commit hash:' . $revision) . '</a>';
}

function freshports_git_commit_Link_gitlab($revision, $hostname, $path) {
  return '<a href="https://gitlab.com/FreeBSD/freebsd-ports/-/commit/' . htmlentities($revision) .  '">' . freshports_GitLab_Icon('commit hash:' . $revision) . '</a>';
}

function freshports_git_commit_Link_codeberg($revision, $hostname, $path) {
  return '<a href="https://codeberg.org/FreeBSD/freebsd-ports/commit/' . htmlentities($revision) .  '">' . freshports_Codeberg_Icon('commit hash:' . $revision) . '</a>';
}

function freshports_git_commit_Link_diff($revision, $hostname, $path) {
  return '<a href="https://' . htmlentities($hostname) . $path . '/commit/?id=' . htmlentities($revision) .  '">' . freshports_Diff_Icon() . '</a>';
}

function freshports_git_commit_Link_Hash($hash, $link_text, $hostname, $path) {
  return '<a href="https://' . htmlentities($hostname) . $path . '/commit/?id=' . htmlentities($hash) .  '" class="hash">' . $link_text . '</a>';
}

function freshports_Fallout_Link($category, $port) {
  # re https://github.com/FreshPorts/freshports/issues/181
  return '<a href="https://portsfallout.com/fallout?port=' . rawurlencode($category . '/' . $port . '$') . '" rel="noopener noreferrer">' . freshports_Fallout_Icon(FALLOUT_TITLE, FALLOUT_SMALLER_ICON_SIZE) . '</a>';
}

function freshports_svnweb_ChangeSet_Link($revision, $hostname) {
  return '<a href="https://' . htmlentities($hostname) . '/changeset/ports/' . htmlentities($revision) .  '" rel="noopener noreferrer">' . freshports_Subversion_Icon('Revision:' . $revision) . '</a>';
}

function freshports_svnweb_ChangeSet_Link_Text($revision, $hostname) {
  return '<a href="https://' . htmlentities($hostname) . '/changeset/ports/' . htmlentities($revision) .  '" rel="noopener noreferrer">' . $revision . '</a>';
}

function freshports_Search_Maintainer($Maintainer) {
	return '<a href="/search.php?stype=maintainer&amp;method=exact&amp;query=' . urlencode($Maintainer) . '">' .
	      freshports_Search_Icon('search for ports maintained by this maintainer') . '</a>';
}

function freshports_Search_Committer($Committer) {
	return '<a href="/search.php?stype=committer&amp;method=exact&amp;query=' . urlencode($Committer) . '">' .
	      freshports_Search_Icon('search for other commits by this committer') . '</a>';
}

function freshports_MainContentTable($Classes=BORDER) {
	return '<table class="maincontent fullwidth ' . $Classes . '">' . PortsFreezeStatus();
}

function  freshports_ErrorContentTable() {
	echo '<table class="fullwidth bordered centered">
';
}


function PortsFreezeStatus() {
	#
	# this function checks to see if there is a port freeze on.
	# if there is, it returns text that indicates same.
	# otherwise, it returns an empty string.
	#
	$result = '';

	if (file_exists(SIGNALS_DIRECTORY . "/PortsFreezeIsOn")) {
		$result = '
<tr>' . freshports_PageBannerText('There is a PORTS FREEZE in effect!') . '</tr>
<tr><td>';
		$result .= '
<p>A <a href="https://www.freebsd.org/doc/en/articles/committers-guide/ports.html" rel="noopener noreferrer">ports freeze</a>
 means that commits will be few and far between and only by approval.
</p>
</td></tr>
';
	}

	return $result;
}


function freshports_strip_port_suffix($PortName) {
	# a dependency might look like:
	# devel/py-setuptools@py27
	# devel/py-setuptools:configure
	#
	# but we can't link to that, so we remove the suffix

	$PortName = strtok($PortName, "@:");

	return $PortName;
}

function freshports_link_to_port($CategoryName, $PortName, $BranchName = BRANCH_HEAD) {

	# see also freshports_Port_URL

	$HTML = '';

	// create link to category, perhaps on a branch
	//
	$HTML .= '<a href="/' . $CategoryName . '/';
	if ($BranchName != BRANCH_HEAD) {
	  $HTML .= '?branch=' . htmlentities($BranchName);
	}
	$HTML .= '">' . $CategoryName . '</a>/';

	// create link to port, perhaps on a branch
	//
	$HTML .= '<a href="/' . $CategoryName . '/' . freshports_strip_port_suffix($PortName) . '/';
	if ($BranchName != BRANCH_HEAD) {
	  $HTML .= '?branch=' . htmlentities($BranchName);
	}

	$HTML .= '">' . $PortName . '</a>';

	return $HTML;
}

function freshports_Port_URL($dbh, $CategoryName, $PortName, $BranchName = BRANCH_HEAD) {

	# see also freshports_link_to_port

	$HTML = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $CategoryName . '/' . $PortName . '/';
	if ($BranchName != BRANCH_HEAD) {
		$HTML .= '?branch=' . pg_escape_string($dbh, $BranchName);
	}

	return $HTML;
}

function freshports_link_to_port_single($CategoryName, $PortName, $BranchName = BRANCH_HEAD, $class = '' ) {

	// This differs from freshports_link_to_port in that you get a single link, not a 
	// link to both category and port

	$HTML = '';
	$HTML .= '<a href="/' . $CategoryName . '/' . freshports_strip_port_suffix($PortName) . '/';
	if ($BranchName != BRANCH_HEAD) {
	  $HTML .= '?branch=' . htmlentities($BranchName);
	}

	$HTML .= '"';

	if ($class) {
		$HTML .= ' class="' . $class . '"';
	}

	$HTML .= '>' . $CategoryName . '/' . $PortName . '</a>';

	return $HTML;
}

function freshports_link_text_to_port_single($text, $CategoryName, $PortName, $BranchName = BRANCH_HEAD) {

	// This differs from freshports_link_to_port_single in the link text is not necessarily the port name.

	$HTML = '';
	$HTML .= htmlentities($text) . ' : <a href="/' . $CategoryName . '/' . freshports_strip_port_suffix($PortName) . '/';
	if ($BranchName != BRANCH_HEAD) {
	  $HTML .= '?branch=' . htmlentities($BranchName);
	}

	$HTML .= '">' . $CategoryName . '/' . $PortName . '</a>';

	return $HTML;
}

#
# These are the pages which take NOINDEX and NOFOLLOW meta tags
#


function freshports_IndexFollow($URI) {
#	$NOINDEX["/index.php"]             = 1;
	$NOINDEX["/date.php"]              = 1;

	$NOINDEX['/ports-broken.php']      = 1;
	$NOINDEX['/ports-deleted.php']     = 1;
	$NOINDEX['/ports-forbidden.php']   = 1;
	$NOINDEX['/ports-deprecated.php']  = 1;
	$NOINDEX['/ports-ignore.php']      = 1;
	$NOINDEX['/ports-new.php']         = 1;
	$NOINDEX['/search.php']            = 1;


	$NOFOLLOW["/date.php"]             = 1;
	$NOFOLLOW['/ports-deleted.php']    = 1;
	$NOFOLLOW['/graphs.php']           = 1;
	$NOFOLLOW['/ports-deleted.php']    = 1;
	$NOFOLLOW['/commit.php']           = 1;

	$NOFOLLOW['/new-user.php']         = 1;
	$NOFOLLOW['/login.php']            = 1;
	$NOFOLLOW['/search.php']           = 1;


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

function freshports_Fallout_Icon($Title = FALLOUT_TITLE, $size = DEFAULT_ICON_SIZE) {
	return '<img class="icon fallout" src="/images/fallout.svg" alt="pkg-fallout" title="pkg-fallout" width="' . $size . '" height="' . $size . '">';
}

function freshports_Codeberg_Icon($Title = 'Codeberg', $size = DEFAULT_ICON_SIZE) {
	return '<img class="icon" src="/images/codeberg.svg" alt="' . $Title . '" title="' . $Title . '" width="' . $size . '" height="' . $size . '">';
}

function freshports_Subversion_Icon($Title = 'Subversion') {
	return '<img class="icon" src="/images/subversion.png" alt="' . $Title . '" title="' . $Title . '" width="32" height="32">';
}

function freshports_Subversion_Icon_Greyed($Title = 'Subversion') {
	return '<img class="icon" src="/images/subversion-greyed.png" alt="' . $Title . '" title="' . $Title . '" width="32" height="32">';
}

function freshports_Git_Icon($Title = 'git', $size = DEFAULT_ICON_SIZE) {
	return '<img class="icon" src="/images/git.png" alt="' . $Title . '" title="' . $Title . '" width="' . $size . '" height="' . $size . '">';
}

function freshports_GitHub_Icon($Title = 'git', $size = DEFAULT_ICON_SIZE) {
	return '<img class="icon" src="/images/github.svg" alt="' . $Title . '" title="' . $Title . '" width="' . $size . '" height="' . $size . '">';
}

function freshports_GitLab_Icon($Title = 'git', $size = DEFAULT_ICON_SIZE) {
	return '<img class="icon" src="/images/gitlab.svg" alt="' . $Title . '" title="' . $Title . '" width="' . $size . '" height="' . $size . '">';
}

function freshports_Homepage_Icon($Title = 'Homepage', $size = DEFAULT_ICON_SIZE + 2) {
	return '<img class="icon" src="/images/home.svg" alt="' . $Title . '" title="' . $Title . '" width="24" height="24">';
}

function freshports_SanityTestFailure_Icon($Title = 'Sanity Test Failure') {
	return '<img class="icon" src="/images/stf.gif" alt="' . $Title . '" title="' . $Title . '" width="13" height="13">';
}

function freshports_Ascending_Icon($Title = 'Ascending Order') {
	return '<img class="icon" src="/images/ascending.gif" alt="' . $Title . '" title="' . $Title . '" width="9" height="9">';
}

function freshports_Descending_Icon($Title = 'Descending Order') {
	return '<img class="icon" src="/images/descending.gif" alt="' . $Title . '" title="' . $Title . '" width="9" height="9">';
}

function freshports_Search_Icon($Title = 'Search') {
	return '<img class="icon" src="/images/search.jpg" alt="' . $Title . '" title="' . $Title . '" width="17" height="17">';
}

function freshports_Bugs_Find_Icon($Title = 'Find issues related to this port') {
	return '<img class="icon" src="/images/bug.gif" alt="' . $Title . '" title="' . $Title . '" width="16" height="16">';
}

function freshports_Bugs_Report_Icon($Title = 'Report an issue related to this port') {
	return '<img class="icon" src="/images/bug_report.gif" alt="' . $Title . '" title="' . $Title . '" width="16" height="16">';
}

function freshports_WatchListCount_Icon() {
	return '<img class="icon" src="/images/sum.gif" alt="on this many watch lists" title="on this many watch lists" width="8" height="11">';
}

function freshports_WatchListCount_Icon_Link() {
	return '<a href="/' . FAQLINK . '#watchlistcount">' . freshports_WatchListCount_Icon() . '</a>';
}

function freshports_Files_Icon() {
	return '<img class="icon" src="/images/logs.gif" alt="files touched by this commit" title="files touched by this commit" width="17" height="20">';
}

function freshports_Refresh_Icon() {
	return '<img class="icon" src="/images/refresh.gif" alt="Refresh" title="Refresh - this port is being refreshed, or make failed to run error-free." width="15" height="18">';
}

function freshports_Refresh_Icon_Link() {
	return '<a href="/' . FAQLINK . '#refresh">' . freshports_Refresh_Icon() . '</a>';
}

function freshports_Deleted_Icon() {
	return '<img class="icon" src="/images/deleted.gif" alt="Deleted" title="Deleted" width="21" height="18">';
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

	return '<img class="icon" src="/images/forbidden.gif" alt="' . $Alt . '" title="' . $HoverText . '" width="20" height="20">';
}

function freshports_Forbidden_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#forbidden">' . freshports_Forbidden_Icon($HoverText) . '</a>';
}

function freshports_Broken_Icon($HoverText = '') {
	$Alt       = "Broken";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img class="icon" src="/images/broken.gif" alt="' . $Alt . '" title="' . $HoverText . '" width="17" height="16">';
}

function freshports_Broken_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#broken">' . freshports_Broken_Icon($HoverText) . '</a>';
}

function freshports_Deprecated_Icon($HoverText = '') {
	$Alt       = "Deprecated";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img class="icon" src="/images/deprecated.gif" alt="' . $Alt . '" title="' . $HoverText . '" width="18" height="18">';
}

function freshports_Deprecated_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#deprecated">' . freshports_Deprecated_Icon($HoverText) . '</a>';
}

function freshports_Expired_Icon($HoverText = '') {
	$Alt       = "Expired";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img class="icon" src="/images/expired.gif" alt="' . $Alt . '" title="' . $HoverText . '" width="16" height="16">';
}

function freshports_Expired_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#expired">' . freshports_Expired_Icon($HoverText) . '</a>';
}

function freshports_Expiration_Icon($HoverText = '') {
	$Alt       = "Expiration Date";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img class="icon" src="/images/expiration.gif" alt="' . $Alt . '" title="' . $HoverText . '" width="16" height="16">';
}

function freshports_Expiration_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#expiration">' . freshports_Expiration_Icon($HoverText) . '</a>';
}

function freshports_Restricted_Icon($HoverText = '') {
	$Alt       = "Restricted";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img class="icon" src="/images/restricted.jpg" alt="' . $Alt . '" title="' . $HoverText . '" width="16" height="16">';
}

function freshports_Restricted_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#restricted">' . freshports_Restricted_Icon($HoverText) . '</a>';
}

function freshports_Is_Interactive_Icon($HoverText = '') {
	$Alt       = "Is Interactive";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img class="icon" src="/images/crt.gif" alt="' . $Alt . '" title="' . $HoverText . '" width="16" height="16">';
}

function freshports_Is_Interactive_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#is_interactive">' . freshports_Is_Interactive_Icon($HoverText) . '</a>';
}

function freshports_No_CDROM_Icon($HoverText = '') {
	$Alt       = "NO CDROM";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img class="icon" src="/images/no_cdrom.jpg" alt="' . $Alt . '" title="' . $HoverText . '" width="16" height="16">';
}

function freshports_No_CDROM_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#no_cdrom">' . freshports_No_CDROM_Icon($HoverText) . '</a>';
}

function freshports_Ignore_Icon($HoverText = '') {
	$Alt       = "Ignore";
	$HoverText = freshports_HoverTextCleaner($Alt, $HoverText);

	return '<img class="icon" src="/images/ignored.png" alt="' . $Alt . '" title="' . $HoverText . '" width="20" height="21">';
}

function freshports_Ignore_Icon_Link($HoverText = '') {
	return '<a href="/' . FAQLINK . '#ignore">' . freshports_Ignore_Icon($HoverText) . '</a>';
}

function freshports_New_Icon() {
	return '<img class="icon" src="/images/new.gif" alt="new!" title="new!" width="28" height="11">';
}

function freshports_Mail_Icon() {
	return '<img class="icon" src="/images/envelope10.gif" alt="Original commit" title="Original commit message" width="32" height="18">';
}

function freshports_Commit_Icon() {
	return '<img class="icon" src="/images/copy.gif" alt="Commit details" title="FreshPorts commit message" width="16" height="16">';
}

function freshports_CVS_Icon() {
	return '<img class="icon" src="/images/cvs.png" alt="CVS log" title="CVS log" width="19" height="17">';
}

function freshports_Watch_Icon() {
	return '<img class="icon" src="/images/watch-remove.gif" alt="Click to remove this from your default watch list[s]" title="Click to remove this from your default watch list[s]" width="16" height="16">';
}

function freshports_Watch_Icon_Add() {
	return '<img class="icon" src="/images/watch-add.gif" alt="' . CLICKTOADD . '" title="' . CLICKTOADD . '" width="16" height="16">';
}

function freshports_Watch_Icon_Empty() {
	return '<img class="icon" src="/images/watch-empty.gif" alt="" title="" width="16" height="1">';
}

function freshports_Encoding_Errors() {
	return '<img class="icon" src="/images/error.gif" alt="Encoding Errors (not all of the commit message was ASCII)" title="Encoding Errors (not all of the commit message was ASCII)" width="16" height="16">';
}

function freshports_Encoding_Errors_Link() {
	return '<a href="/' . FAQLINK . '#encodingerrors">' . freshports_Encoding_Errors() . '</a>';
}

function freshports_Repology_Icon() {
	return '<img class="icon" src="/images/repology.png" alt="View this port on Repology." title="View this port on Repology." width="16" height="16">';
}

function freshports_VuXML_Icon() {
	return '<img class="icon" src="/images/vuxml.gif" alt="This port version is marked as vulnerable." title="This port version is marked as vulnerable." width="13" height="16">';
}

function freshports_VuXML_Icon_Faded() {
	return '<img class="icon" src="/images/vuxml-faded.gif" alt="An older version of this port was marked as vulnerable." title="An older version of this port was marked as vulnerable." width="13" height="16">';
}

function freshports_Revision_Icon() {
	return '<img class="icon" src="/images/revision.jpg" alt="View revision" title="view revision" width="11" height="15">';
}

function freshports_Annotate_Icon() {
	return '<img class="icon" src="/images/annotate.png" alt="Annotate / Blame" title="Annotate / Blame" width="20" height="20">';
}

function freshports_Diff_Icon() {
	return '<img class="icon" src="/images/diff.png" alt="View diff" title="view diff" width="15" height="11">';
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

function freshports_Repology_Link($CategoryNamePortName) {
	$HTML = '<a href="https://repology.org/tools/project-by?repo=freebsd&amp;name_type=srcname&amp;target_page=project_versions&amp;name=' . $CategoryNamePortName . '" rel="noopener noreferrer">';
	$HTML .= freshports_Repology_Icon();
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
	$HTML  = '<a href="' . htmlentities($freshports_mail_archive . $message_id) . '" rel="noopener noreferrer">';
	$HTML .= freshports_Mail_Icon();
	$HTML .= '</a>';

	return $HTML;
}

function freshports_Commit_Flagged_Icon($Title = 'Commit Flagged') {
	return '<img class="icon" src="/images/commit-flagged.gif" alt="' . $Title . '" title="' . $Title . '" width="16" height="16">';
}

function freshports_Commit_Flagged_Not_Icon($Title = 'Commit Not Flagged') {
	return '<img class="icon" src="/images/commit-flagged-not.gif" alt="' . $Title . '" title="' . $Title . '" width="16" height="16">';
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
	$HTML  = '<a href="' . FRESHPORTS_FREEBSD_CVS_URL . $element_name . '?rev=' . $revision . '&amp;content-type=text/x-cvsweb-markup" rel="noopener noreferrer">';
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

	$HTML = 'https://' . $_SERVER['HTTP_HOST'] . '/commit.php?category=' . $Category . '&port=' . $Port . '&files=yes&message_id=' . $MessageID;

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
	$HTML .= freshports_Commit_Link($message_id, '<img class="icon" src="/images/play.gif" alt="View all ports for this commit" title="View all ports for this commit" width="13" height="13">');
	$HTML .= ")";

	return $HTML;
}

function freshports_MoreCommitMsgToShow($message_id, $NumberOfLinesShown) {
	$HTML  = "(Only the first $NumberOfLinesShown lines of the commit message are shown above ";
	$HTML .= freshports_Commit_Link($message_id, '<img class="icon" src="/images/play.gif" alt="View all of this commit message" title="View all of this commit message" width="13" height="13">');
	$HTML .= ")";

	return $HTML;
}

function freshports_CookieClear() {
	SetCookie(USER_COOKIE_NAME, '', array(
		'expires'  => 1, // 0 makes it a session cookie, we actually want it to delete soon
		'path'     => '/',
		'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
		'httponly' => TRUE,
		'samesite' => 'Lax',
	));
}

function freshportsObscureHTML($email) {
	# why obscure?  The spammers catch up.
	return htmlentities($email);
}

#
# this is the link used when commiter_name is not present
# in the commit_log table - such commits are typically
# svn or cvs - with git, we have commiter_name & commiter_email
#
function freshports_CommitterEmailLink_Old($committer) {
	#
	# in an attempt to reduce spam, encode the mailto
	# so the spambots get rubbish, but it works OK in
	# the browser.
	#

	$new_addr = "";
	# Sometimes we see 'marck (doc committer)' or 'dumbbell (src committer)'
	$addr = strtok($committer, ' ') . "@FreeBSD.org";

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
	# Sometimes we see 'marck (doc committer)' or 'dumbbell (src committer)'
	$addr = strtok($committer, ' ') . "@FreeBSD.org";

	$new_addr = freshportsObscureHTML($addr);

	$HTML = "<a href=\"" . MAILTO . ":$new_addr?$extrabits\">$committer</a>";

	return $HTML;
}


function freshports_AuthorEmailLink($author_name, $author_email) {
	#
	# in an attempt to reduce spam, encode the mailto
	# so the spambots get rubbish, but it works OK in
	# the browser.
	#

	$new_addr = "";
	$addr = $author_email;

	$new_addr = freshportsObscureHTML($addr);

	$HTML = '<a href="' . MAILTO . ':' . $new_addr . '" title="authored by this person">' . $author_name . '</a>';

	return $HTML;
}

function freshports_CommitterEmailLink($committer_name, $committer_email) {
	#
	# in an attempt to reduce spam, encode the mailto
	# so the spambots get rubbish, but it works OK in
	# the browser.
	#

	$new_addr = "";
	$addr = $committer_email;

	$new_addr = freshportsObscureHTML($addr);

	$HTML = '<a href="' . MAILTO . ':' . $new_addr . '" title="committed by this person">' . $committer_name . '</a>';

	return $HTML;
}



// common things needs for all freshports php3 pages

function freshports_Start($ArticleTitle, $Description, $Keywords, $Phorum = 0, $ExtraScript = null) {

GLOBAL $ShowAds;
GLOBAL $BannerAd;
GLOBAL $ShowAnnouncements;

	header('X-Accel-Buffering: no');

	freshports_HTML_Start();
	freshports_Header($ArticleTitle, $Description, $Keywords, $Phorum);

	freshports_body($ExtraScript);

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

	if (ob_get_level() > 0) {
		ob_flush();
	}
	flush();
}

function freshports_Logo() {
GLOBAL $LocalTimeAdjustment;
GLOBAL $FreshPortsName;
GLOBAL $FreshPortsLogo;
GLOBAL $FreshPortsSlogan;
GLOBAL $FreshPortsLogoWidth;
GLOBAL $FreshPortsLogoHeight;

#echo "$LocalTimeAdjustment<br>";

	$HTML = '<br>
<table class="fullwidth borderless">
<tr>
	<td><span class="logo"><a href="';

	if ($_SERVER["PHP_SELF"] == "/index.php") {
		$HTML .= 'other-copyrights.php';
	} else {
		$HTML .= '/';
	}
	$HTML .= '"><img id="fp-logo" src="' . $FreshPortsLogo . '" alt="' . $FreshPortsName . ' -- ' . $FreshPortsSlogan . '" title="' . $FreshPortsName . ' -- ' . $FreshPortsSlogan . '" width="' . $FreshPortsLogoWidth . '" height="' . $FreshPortsLogoHeight . '"></a></span>';
    define('HEAD_FILE', $_SERVER['DOCUMENT_ROOT'] . '/../.git/HEAD');

    if (file_exists(HEAD_FILE)) {
      # taken from https://stackoverflow.com/questions/7447472/how-could-i-display-the-current-git-branch-name-at-the-top-of-the-page-of-my-de
      $HTML .= '<span class="branch">The git branch used by this host is <span class="file">' . implode('/', array_slice(explode('/', file_get_contents(HEAD_FILE)), 2)) . '</span></span><br>';
    }
    if (defined('SHOW_ANIMATED_BUG') && SHOW_ANIMATED_BUG)
    {
	  $HTML .= '<img src="/images/notbug.gif" width="56" height="50" alt="notbug" title="notbug">';
    }

    if (defined('SHOW_IPV6_LOGO') && SHOW_IPV6_LOGO && filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
    	$HTML .= "

<!-- IPv6-test.com button BEGIN -->
<a href='https://ipv6-test.com/validate.php?url=referer' rel='noopener noreferrer'><img src='/images/button-ipv6-big.png' alt='ipv6 ready' title='ipv6 ready'></a>
<!-- IPv6-test.com button END -->
";
	}

	$HTML .= '<span class="amazon">As an Amazon Associate I earn from qualifying purchases.<br>Want a good read? Try <a target="_blank" rel="noopener noreferrer" href="https://www.amazon.com/gp/product/B07PVTBWX7/ref=as_li_tl?ie=UTF8&amp;camp=1789&amp;creative=9325&amp;creativeASIN=B07PVTBWX7&amp;linkCode=as2&amp;tag=thfrdi0c-20&amp;linkId=a5cb3ac309f59900d44401e24a169f05">FreeBSD Mastery: Jails (IT Mastery Book 15)</a></span>';
	$HTML .= '<span class="timezone">All times are UTC</span>';

	$HTML .= '</td>';

if (date("M") == 'Nov' && date("j") <= 12) {
	$HTML .= '	<td class="sans nowrap vbottom hcentered"><a href="https://www.google.ca/search?q=remembrance+day" rel="noopener noreferrer"><img src="/images/poppy.gif" width="50" height="48" alt="Remember" title="Remember"><br>I remember</a></td>';
} elseif (defined('UKRAINE') && UKRAINE) {
	$HTML .= '	<td class="sans nowrap vbottom hcentered"><img src="/images/ukraine.png" width="133" height="100" alt="Ukraine" title="Ukraine"></td>';
} else {
	$HTML .= '	<td>';
	$HTML .= '<div id="followus"><div class="header">Follow us</div><a href="https://news.freshports.org/" rel="noopener noreferrer">Blog</a><br><a href="https://twitter.com/freshports/" rel="noopener noreferrer">Twitter</a><br><a href="https://freshports.wordpress.com/" rel="noopener noreferrer">Status page</a><br></div><a rel="me" href="https://bsd.network/@dvl">Mastodon</a>';

	$HTML .= '</td>';
	
}

$HTML .= '
</tr>
</table>
';

	return $HTML;
}

function freshports_detect_holidays($now) {
	$month = date("n", $now);

	// June is LGBTQ+ Pride Month
	if ($month == "06") return "pride";

	return '';
}

function freshports_HTML_Start() {
GLOBAL $Debug;

$holiday = freshports_detect_holidays(time());

echo HTML_DOCTYPE . '
<html lang="en"' . ($holiday ? ' class="holiday ' . $holiday . '"' : '') . '>
';
}

function freshports_HEAD_charset() {
	return '
	<meta http-equiv="Content-Type" content="text/html; charset=' . FRESHPORTS_ENCODING . '">
';
}

function freshports_HEAD_main_items() {
	return '
	<LINK REL="SHORTCUT ICON" href="/favicon.ico">

	<link rel="alternate" type="application/rss+xml" title="FreshPorts - The Place For Ports" href="https://' . $_SERVER['HTTP_HOST'] . '/backend/rss2.0.php">

	<link rel="apple-touch-icon" sizes="57x57" href="/images/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/images/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/images/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/images/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/images/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/images/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/images/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/images/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="/images/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/images/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
	<link rel="manifest" href="/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/images/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">
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

	}

	echo "</TITLE>
";

	freshports_style($Phorum);
	
	echo freshports_HEAD_charset();

	echo "
	<META NAME=\"description\" CONTENT=\"";

	if ($Description) {
		echo htmlspecialchars($Description);
	} else {
		echo htmlspecialchars($ArticleTitle);
	}

	echo "\">
	<META NAME=\"keywords\"    CONTENT=\"" . htmlspecialchars($Keywords) . "\">
";

	echo freshports_HEAD_main_items();

	echo freshports_IndexFollow($_SERVER["PHP_SELF"]);

	echo "</HEAD>\n";
}

function freshports_style($Phorum=0) {
	$version = substr(hash_file('sha1', $_SERVER['DOCUMENT_ROOT'] . '/css/freshports.css'), 0, 8);
	echo '	<link rel="stylesheet" href="/css/freshports.css?v=' . $version . '" type="text/css">' . "\n";
}

function freshports_body($ExtraScript = null) {

GLOBAL $Debug;

echo "\n" . '<BODY>';

# most often used for page setup, hiding elements, etc
if (!empty($ExtraScript)) {
  echo $ExtraScript;
}

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

		echo '<table class="bordered">';
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

function freshports_Category_Name($CategoryID, $dbh) {
	$sql = "select name from categories where id = " . pg_escape_string($dbh, $CategoryID);

//	echo $sql;

	$result = pg_exec($dbh, $sql);
	if (!$result) {
		echo "error " . pg_last_error($dbh);
		exit;
	}

	$myrow = pg_fetch_array($result, 0);

//	echo $myrow["name"];

	return $myrow["name"];
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

function freshports_PortIDFromPortCategory($category, $port, $dbh) {
	$sql = "select pathname_id('ports/" . pg_escape_string($dbh, $category) . '/' . pg_escape_string($dbh, $port) . "') as id";

	$result = pg_exec($dbh, $sql);
	if (pg_num_rows($result)) {
		$myrow = pg_fetch_array($result, 0);
		$PortID = $myrow["id"];
	}

	return $PortID;
}

function freshports_CategoryIDFromCategory($category, $dbh) {
   $sql = "select categories.id from categories where categories.name = '" . pg_escape_string($dbh, $category) . "'";

   $result = pg_exec($dbh, $sql);
   if(pg_num_rows($result)) {
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

   return $HTML . '<br>';
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
	$Debug = 0;

	// sometimes they have multiple spaces in the data...
	$temp = str_replace('  ', ' ', $DependsList);
      
	// split each depends up into different bits
	$depends = explode(' ', $temp);
	$Count = count($depends);
	$HTML  = '';
	foreach ($depends as $depend) {
		// split one depends into the library and the port name (/usr/ports/<category>/<port>)
		if ($Debug) echo "depends is $depend<br>";

		$DependsArray = explode(':', $depend);

		// now extract the port and category from this port name
		// it might look like: /usr/local/bin/perl5.16.3:/usr/local/PORTS-head/lang/perl5.16
		//                 or: yasm:/usr/local/repos/PORTS-2016Q1/devel/yasm
		// try it this way
		$full_path = PATH_TO_PORTSDIR . PORTSDIR_PREFIX . $BranchName . '/';
		if ($Debug) echo "full_path=$full_path<br>";

		$CategoryPort = str_replace($full_path, '', $DependsArray[1]) ;

		if ($Debug) echo "CategoryPort='$CategoryPort'<br>";

		// if that has no effect, try it the old way:
		// we might have old stuff stored in the db.  Which makes me think: we should store it another way in the db.
		if ($CategoryPort == $DependsArray[1]) {
		   $CategoryPort = str_replace('/usr/ports/', '', $DependsArray[1]) ;
		}
		$CategoryPortArray = explode('/', $CategoryPort);

		$HTML .= '<li>' . freshports_link_text_to_port_single(basename($DependsArray[0]), $CategoryPortArray[0], $CategoryPortArray[1], $BranchName) . '</li>';
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
	return '<small><a href="/legal.php" target="_top" title="This material is copyrighted">Copyright</a> &copy; ' . COPYRIGHTYEARS . ' <a href="' . COPYRIGHTHOLDERURL . '" rel="noopener noreferrer">' . COPYRIGHTHOLDER . '</a>. All rights reserved.</small>';
}

function FormatTime($Time, $Adjustment, $Format) {
#echo "$Time<br>";
#echo time() . "<br>";
	return date($Format, strtotime($Time) + $Adjustment);
}

function freshports_UpdatingOutput($NumRowsUpdating, $PortsUpdating, $port) {
	$HTML = '';

	if ($NumRowsUpdating > 0) {
		$HTML .= '<table class="ports-updating fullwidth bordered">' . "\n";
		$HTML .= "<tr>\n";
		$HTML .= freshports_PageBannerTextWithID('Notes from UPDATING', 'updating');
		$HTML .= "<tr><td><dl>\n";
		$HTML .= "<dt>These upgrade notes are taken from <a href=\"/UPDATING\">/usr/ports/UPDATING</a></dt>";
		$HTML .= "<dd><ul>\n";

		$Hiding = false;
		for ($i = 0; $i < $NumRowsUpdating; $i++) {
			$PortsUpdating->FetchNth($i);
			if ($i == 1) {
				$Hiding = true;
				# end the old list, start a new list
				$HTML .= "</ul></dd>\n";
				$HTML .= '<dt><a href="#" id="UPDATING-Extra-show" class="showLink" onclick="showHide(\'UPDATING-Extra\');return false;">Expand this list (' . ($NumRowsUpdating - 1) . ' items)</a></dt>';
				$HTML .= '<dd id="UPDATING-Extra" class="more UPDATING">';

				# start the new list of all hidden items
				$HTML .= "<ul>\n";
			}

			$HTML .= '<li>' . freshports_PortsUpdating($port, $PortsUpdating) . "</li>\n";
		}
		if ($Hiding) {
			$HTML .= '<li class="nostyle"><a href="#" id="UPDATING-Extra-hide2" class="hideLink" onclick="showHide(\'UPDATING-Extra\');return false;">Collapse this list.</a></li>';
		}

		$HTML .= "</ul></dd>";
		$HTML .= "</dl></td></tr>\n";
		$HTML .= "</table>\n";
	}

	return $HTML;
}

function freshports_PortCommitsHeader($port) {
	# print the header for the commits for a port

	GLOBAL $User;
	
	$HTML = '';

	$HTML .= '<table class="commit-list fullwidth bordered">' . "\n";
	$HTML .= "<tr>\n";

	$Columns = 3;

	$HTML .= freshports_PageBannerTextColSpan("Commit History - (may be incomplete: for full details, see links to repositories near top of page)", $Columns);

	if ($port->IsSlavePort()) {
		$HTML .= '<tr><td colspan="' . $Columns . '">'; 
		$HTML .= 'This is a slave port.  You may also want to view the commits to the master port: ';
		list($MyCategory, $MyPort) = explode('/', $port->master_port);
		$HTML .= freshports_link_to_port_single($MyCategory, $MyPort);
		$HTML .= '</td></tr>';
	}

	$HTML .= '<tr><th>Commit</th><th>Credits</th><th>Log message</th>';

	$HTML .= "</tr>\n";

	return $HTML;
}

function freshports_PackageVersion($PortVersion, $PortRevision, $PortEpoch) {
	$PackageVersion = '';

	if (IsSet($PortVersion) && strlen($PortVersion) > 0) {
		$PackageVersion .= $PortVersion;
	        
		if (IsSet($PortRevision) && strlen($PortRevision) > 0 && $PortRevision != "0") {
    			$PackageVersion .= FRESHPORTS_VERSION_REVISION_JOINER . $PortRevision;
		}

		if (!empty($PortEpoch)) {
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
			'path'			=> '/' . $port->category . '/' . $port->port,
			'fileName'              => '?page=%d#history', 	# there are two places #history is set.  This is #1
			'altFirst'              => 'First Page',
			'firstPageText'         => 'First Page',
			'altLast'               => 'Last Page',
			'lastPageText'          => 'Last Page',
		);

	# use @ to suppress: Non-static method Pager::factory() should not be called statically
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

	# the opening <p> is prepended later
	$NumCommitsHTML = 'Number of commits found: ' . $NumCommits;

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
		$PageLinksHTML = '<p class="pagination">' . $PageLinks . '</p>';
	} else {
		$PageLinksHTML = '';
	}

	# this is the 1st of 2 places where NumCommitsHTML is used.
	$HTML .= '<p id="history">' . $NumCommitsHTML . $PageLinksHTML;

	if ($Commits->Debug) echo "PageNumber='$PageNumber'<br>Offset='$Offset'<br>";
	
	$Commits->LimitSet($NumCommitsPerPage);
	$Commits->OffsetSet($Offset);
	$NumRows = $Commits->FetchInitialise($port->id);
	# if no commits on this branch, don't fetch
	if ($NumRows > 0) {
		$port->LoadVulnerabilities();

		$Commits->FetchNthCommit(0);

		$HTML .= freshports_CheckForOutdatedVulnClaim($Commits, $port, $port->VuXML_List);

		$HTML .= freshports_PortCommitsHeader($port);

		$LastVersion = '';
		for ($i = 0; $i < $NumRows; $i++) {
			$Commits->FetchNthCommit($i);
			$HTML .= freshports_PortCommitPrint($Commits, $port->category, $port->port, $port->VuXML_List);
		}
	}

	$HTML .= freshports_PortCommitsFooter($port);
	
	# this is the 2nd of 2 places where NumCommitsHTML is used.
	# no id=history here
	$HTML .= '<p>' . $NumCommitsHTML . $PageLinksHTML;

	return $HTML;
}

function freshports_PortCommitPrint($commit, $category, $port, $VuXMLList) {
	GLOBAL $DateFormatDefault;
	GLOBAL $TimeFormatDefault;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
	GLOBAL $User;


	# if the message_id does not contain freebsd.org, it's a git commit
	#
	$GitCommit = strpos($commit->message_id, 'freebsd.org') == false;
	$HTML = '';

	# print a single commit for a port
	$HTML .= "<tr><td class=\"commit-details\">";
	

	# output the VERSION and REVISION
	$PackageVersion = freshports_PackageVersion($commit->{'port_version'},  $commit->{'port_revision'},  $commit->{'port_epoch'});
	if (strlen($PackageVersion) > 0) {
		$HTML .= '<span class="element-details">' . $PackageVersion . '</span><br>';
	}

	$HTML .= $commit->commit_date . '<br>';
	if ($GitCommit) {
		$HTML .= freshports_git_commit_Link_freebsd ($commit->message_id, $commit->repo_hostname, $commit->path_to_repo);
		$HTML .= freshports_git_commit_Link_codeberg($commit->message_id, $commit->repo_hostname, $commit->path_to_repo);
		$HTML .= freshports_git_commit_Link_github  ($commit->message_id, $commit->repo_hostname, $commit->path_to_repo);
		$HTML .= freshports_git_commit_Link_gitlab  ($commit->message_id, $commit->repo_hostname, $commit->path_to_repo);
	} else {
		if (isset($commit->svn_revision)) {
			$HTML .= freshports_svnweb_ChangeSet_Link($commit->svn_revision, $commit->repo_hostname);
	        }
	}

	// indicate if this port needs refreshing from CVS
	if ($commit->{'needs_refresh'}) {
		$HTML .= " " . freshports_Refresh_Icon_Link() . "\n";
	}

	if (!$GitCommit) {
		$HTML .= freshports_Email_Link($commit->message_id);
	}

	if ($commit->EncodingLosses()) {
		$HTML .= '&nbsp;'. freshports_Encoding_Errors_Link();
	}
	$HTML .= '&nbsp;';

	$HTML .= freshports_Commit_Link_Port($commit->message_id, $category, $port);

	if ($commit->stf_message != '') {
		$HTML .= '&nbsp;' . freshports_SanityTestFailure_Link($commit->message_id);
	}

	if (IsSet($VuXMLList[$commit->id])) {
		$HTML .= '&nbsp;<a href="/vuxml.php?vid=' . urlencode($VuXMLList[$commit->id]) . '">' . freshports_VuXML_Icon() . '</a>';
	}

	if (IsSet($commit->branch) && $commit->branch != BRANCH_HEAD) {
		$HTML .= '<br>' . urlencode($commit->branch);
	}

	$HTML .= "</td>\n";
	$HTML .= '    <td class="commit-details">';

	#
	# THIS CODE IS SIMILAR TO THAT IN classes/display_commit.php & classes/port-display.php
	#
	#
	# the commmiter may not be the author
	# committer name and author name came into the database with git.
	# For other commits, such as git or cvs, those fields will not be present.
	# committer will always be present.
	#
	$CommitterIsNotAuthor = !empty($commit->author_name) && !empty($commit->committer_name) && $commit->author_name != $commit->committer_name;

	# if no author name, it's an older commit, and we have only committer
	if (empty($commit->committer_name)) {
		$HTML .= freshports_CommitterEmailLink_Old($commit->committer);
        } else {
		$HTML .= freshports_AuthorEmailLink($commit->committer_name, $commit->committer_email);
		# display the committer id, just because
		$HTML .= '&nbsp;(' . $commit->committer . ')';
	}

	# after the committer, display a search-by-commiter link
	$HTML .= '&nbsp;' . freshports_Search_Committer($commit->committer);

	if ($CommitterIsNotAuthor) {
		$HTML .= '<br>Author:&nbsp;' . freshports_AuthorEmailLink($commit->author_name, $commit->author_email);
	}

	$HTML .= "</td>\n";
	$HTML .= '    <td class="commit-details">';

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
	$HTML  = '<pre class="code">';

	$HTML .= htmlify(_forDisplay(freshports_wrap($shortened)), $Process_PRs);

	$HTML .= '</pre>';

	if (strlen($shortened) < strlen($description)) {
		$HTML .= $URL;
	}

	return $HTML;
}

function freshports_GetNextValue($sequence, $dbh) {
	$sql = "select nextval('" . pg_escape_string($dbh, $sequence) . "')";

#	echo "\$sql = '$sql'<br>";

	$result = pg_exec($dbh, $sql);
	if ($result && pg_num_rows($result)) {
		$row       = pg_fetch_array($result,0);
		$NextValue = $row[0];
	} else {
		pg_last_error($dbh) . ' sql = $sql';
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

function freshports_PageBannerText($Text) {
	return freshports_PageBannerTextColSpan($Text, 1);
}


function freshports_PageBannerTextWithID($Text, $ID) {
	return freshports_PageBannerTextColSpanWithID($Text, 1, $ID);
}


function freshports_PageBannerTextColSpan($Text, $ColSpan) {
	return freshports_PageBannerTextColSpanWithID($Text, $ColSpan, null);
}


function freshports_PageBannerTextColSpanWithID($Text, $ColSpan, $ID) {
	$HTML = '<td class="accent" colspan="' . $ColSpan . '"><span>';
	if (!empty($ID)) {
	  $HTML .= '<a id="' . htmlentities($ID) . '">';
	}
	if (!empty($Text)) {
		$HTML .= htmlentities($Text);
	}
	if (!empty($ID)) {
	  $HTML .= '</a>';
        }
        $HTML .= '</span></td>' . "\n";

        return $HTML;
}


function freshports_UserSendToken($UserID, $dbh) {
	#
	# send the confirmation token to the user
	#

	GLOBAL $FreshPortsSlogan;

	$sql = "select email, token 
	          from users, user_confirmations
	         where users.id = " . pg_escape_string($dbh, $UserID) . "
	           and users.id = user_confirmations.user_id";

#	echo "\$sql = '$sql'<br>";

	$result = pg_exec($dbh, $sql);
	if ($result && pg_num_rows($result)) {
		$row   = pg_fetch_array($result,0);
		$email = $row[0];
		$token = $row[1];
	} else {
		pg_last_error($dbh) . ' sql = $sql';
	}

	if (IsSet($token)) {
		OpenLog("FreshPorts", LOG_PID, LOG_SYSLOG);
		SysLog(LOG_NOTICE, "User Token Sent: UID=$UserID, email=$email");
		CloseLog();

		$message =  "Someone, perhaps you, supplied your email address as their\n".
		            "FreshPorts login. If that wasn't you, and this message becomes\n".
		            "a nuisance, please forward this message to " . PROBLEM_SOLVER_EMAIL_ADDRESS . "\n".
		            "and we will take care of it for you.\n".
                            " \n".
	                    "Your token is: $token\n".
                            "\n".
                            "Please point your browser at\n". "https://" . $_SERVER["HTTP_HOST"] . "/confirmation.php?token=$token\n" .
	                    "\n".
                            "The request came from " . $_SERVER["REMOTE_ADDR"] ."\n".
		            "\n".
		            "-- \n".
		           "FreshPorts - https://" . $_SERVER["HTTP_HOST"] . "/ -- $FreshPortsSlogan";

                try {
                  $mail = new PHPMailer\PHPMailer\PHPMailer;

                  // Settings
                  $mail->IsSMTP();
                  $mail->Host       = MAIL_SERVER;                   // SMTP server
                  $mail->Port       = 25;                            // set the SMTP port for the smtp server
                  $mail->SMTPDebug  = 0;                             // enables SMTP debug information (for testing)

                  // Content
                  $mail->ContentType = 'text/plain';
                  $mail->Subject     = 'FreshPorts - user registration';
                  $mail->Body        = $message;

                  $mail->setFrom   (PROBLEM_SOLVER_EMAIL_ADDRESS, 'FreshPorts');
                  $mail->addReplyTo(PROBLEM_SOLVER_EMAIL_ADDRESS, 'FreshPorts');

                  $mail->addAddress($email);

                  if ($mail->send()) {
                  } else {
                    syslog(LOG_ERR, "freshports_UserSendToken send() failed with: " . $mail->ErrorInfo);
                  }
                } catch (phpmailerException $e) {
                  syslog(LOG_ERR, "freshports_UserSendToken has this error with PHPMailer: " . $e->errorMessage());
                }
	} else {
		$result = 0;
	}

	return $result;
}

function freshports_ShowFooter($PhorumBottom = 0) {
	GLOBAL $Statistics;
	GLOBAL $ShowPoweredBy;
	GLOBAL $ShowAds;

	$HTML = '<table class="footer fullwidth borderless">
<tr><td>';


	if ($ShowAds) {
		$HTML .= "<div>\n";
		$HTML .= Ad_728x90();
		$HTML .= "</div>\n";
	}

	$HTML .= '
<HR>
<table class="borderless">
';

	if (IsSet($ShowPoweredBy)) {
		$HTML .= '
<tr>

<td>

<a href="https://www.freebsd.org/" rel="noopener noreferrer"><img src="/images/pbfbsd2.gif"
alt="powered by FreeBSD" width="171" height="64">/</a>

&nbsp;

<a href="https://www.php.net/" rel="noopener noreferrer"><img src="/images/php-med-trans-light.gif"
alt="powered by php" width="95" height="50"></a>
&nbsp;

<a href="https://www.postgresql.org/" rel="noopener noreferrer"><img src="/images/pg-power.jpg"
alt="powered by PostgreSQL" width="164" height="59"></a>


</td></tr>
<tr><td>

<a href="https://www.nginx.org/" rel="noopener noreferrer"><img src="/images/nginx.gif" 
alt="powered by nginx" width="121" height="32"></a>

<HR>

</tr>
';
	}

	$URIBase = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
	$URI = trim(urlencode($URIBase . $_SERVER['REQUEST_URI']));

	$HTML .= '
<tr><td>
<table class="fullwidth">
<tr>
<td class="sponsors">
<small>' . SPONSORS . '</small>
</td>
<td class="copyright">
<small>
Valid 
<a href="https://validator.w3.org/check?uri=' . $URI . '" title="We like to keep our HTML valid" target="_blank" rel="noopener noreferrer">HTML</a>,
<a href="https://jigsaw.w3.org/css-validator/validator?uri=' .  $URI . '" title="We like to have valid CSS" rel="noopener noreferrer">CSS</a>, and
<a href="https://validator.w3.org/feed/check.cgi?url=' . rawurlencode("{$URIBase}/backend/rss2.0.php") . '" title="Valid RSS is good too" rel="noopener noreferrer">RSS</a>.
</small>
' . freshports_copyright() . '
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
	
	$HTML .= '<script src="/javascript/freshports.js" defer></script>';

	$Statistics->Save();

	return $HTML;
}

function freshports_GoogleAnalytics() {
	$HTML = '<script src="https://www.google-analytics.com/urchin.js">
</script>
<script>
_uacct = "UA-408525-1";
urchinTracker();
</script>
';

	return $HTML;
}

function freshports_SideBar() {

	GLOBAL $User;

	$HTML = '
  <table class="bordered">
        <tr>
         <th class="accent">Login</th>
        </tr>
        <tr>

         <td>';

	if (IsSet($_COOKIE[USER_COOKIE_NAME])) {
		$visitor = $_COOKIE[USER_COOKIE_NAME];
	}

	if (IsSet($visitor)) {
		GLOBAL $User;

		$HTML .= 'Logged in as ' . htmlentities($User->name) . "<br>";

		if ($User->emailbouncecount > 0) {
			$HTML .= '<img src="/images/warning.gif" border="0" height="32" width="32"><img src="/images/warning.gif" border="0" height="32" width="32"><img src="/images/warning.gif" border="0" height="32" width="32"><br>';
			$HTML .= 'your email is <a href="/bouncing.php">bouncing</a><br>';
			$HTML .= '<img src="/images/warning.gif" border="0" height="32" width="32"><img src="/images/warning.gif" border="0" height="32" width="32"><img src="/images/warning.gif" border="0" height="32" width="32"><br>';
		}
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/customize.php', "Your Account", "Your account");

		if (preg_match("/.*@FreeBSD.org/i", $User->email)) {
			$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/committer-opt-in.php', "Committer Opt-in", "Committers can receive reports of Sanity Test Failures");
		}


		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/logout.php',             "Logout",                  "Logout of the website"            );
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/my-flagged-commits.php', "My Flagged Commits",      "List of commits you have flagged" );
	} else {
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/login.php',              "User Login",              "Login to the website"             );
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/new-user.php',           "Create account",          "Create an account"                );
	}

	$HTML .= '
   </td>
   </tr>
   </table>

' . '<div>';

	$HTML .='

<p><small>' . SPONSORS  . '</small></p>
</div>';

	$HTML .= '	
<table class="bordered">
	<tr>
		<th class="accent">This site</th>
	</tr>
	<tr>
	<td>
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/about.php",           "What is FreshPorts?", "A bit of background on FreshPorts"               ) . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/authors.php",         "About the authors",   "Who wrote this stuff?"                           ) . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], ISSUES,                 "Issues",              "Report a website problem"                        ) . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/faq.php",             "FAQ",                 "Frequently Asked Questions"                      ) . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/how-big-is-it.php",   "How big is it?",      "How many pages are in this website?"             ) . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/security-policy.php", "Security Policy",     "Are you a security researcher? Please read this.") . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/privacy.php",         "Privacy",             "Our privacy statement"                           ) . '
	<a href="https://news.freshports.org/" title="All the latest FreshPorts news" rel="noopener noreferrer">Blog</a><br>
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/contact.php",         "Contact",             "Contact details"                                 ) . '
	</td>
	</tr>
</table>
<br>
<table class="bordered">
	<tr>
		<th class="accent">Search</th>
	</tr>
	<tr>

	<td>';

	GLOBAL $dbh;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/searches.php');


	$Searches = new Searches($dbh);
	$HTML .= $Searches->GetFormSimple('&nbsp;', IsSet($User) && $User->set_focus_search);

	if ($_SERVER["PHP_SELF"] != '/search.php') {
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/search.php', "more...", "Advanced Searching options");
	}
	$HTML .= '
	</td>
</tr>
</table>

';
	if (file_exists(VUXML_LATEST)) {
$HTML .= '<br>
<table class="bordered">
	<tr>
		<th class="accent">Latest Vulnerabilities</th>
	</tr>
	<tr><td>
	' . file_get_contents(VUXML_LATEST) . "\n" . '
	</td></tr>
	<tr><td>
		<p><sup>*</sup> - modified, not new</p><p><a href="/vuxml.php?all">All vulnerabilities</a></p>
		<p>Last processed:<br>' . date('Y-m-d H:i:s T', filemtime(VUXML_LATEST)) . '</p>
	</td></tr>
</table>
<br>';
	} else {
		$HTML .= "<br>\n";
	}

	$HTML .= '

<table class="bordered">
	<tr>
		<th class="accent">Ports</th>
	</tr>
	<tr>
	<td>

	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/",                         "Home",                 "FreshPorts Home page"                ) . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/categories.php",           "Categories",           "List of all Port categories"         ) . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/ports-deleted.php",        "Deleted ports",        "All deleted ports"                   ) . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/sanity_test_failures.php", "Sanity Test Failures", "Things that didn't go quite right...") . '
	' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/backend/newsfeeds.php",    "Newsfeeds",            "Newsfeeds for just about everything" ) . '
	
	</td>
	</tr>
</table>';


if (IsSet($visitor)) {


$HTML .= '<br>
<table class="bordered">
	<tr>
		<th class="accent">Watch Lists</th>
	</tr>
	<tr>
	<td>';

		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/pkg_upload.php',             "Upload",               "Upoad a file containing a list of ports you want to add to your watch list");
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/watch-categories.php',       "Categories",           "Search through categories for ports to add to your watch list"             );
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/watch-list-maintenance.php', "Maintain",             "Maintain your watch list[s]"                                               );
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/watch.php',                  "Ports",                "Your list of watched ports"                                                );
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/backend/watch-list.php',     "Personal Newsfeeds",   "A list of news feeds for your watched lists"                               );
		$HTML .= freshports_SideBarHTML($_SERVER["PHP_SELF"], '/report-subscriptions.php',   "Report Subscriptions", "Maintain your list of subscriptions"                                       );

$HTML .= '		
	</td>
	</tr>
</table>';
	}


	GLOBAL $ShowAds;

	if ($ShowAds) {
		$HTML .= '<br><table class="borderless">
		  <tr><td class="vcentered">
		';
		$HTML .= Ad_160x600();
		$HTML .= '</td></tr>
		  </table>
		 ';
	}

	$HTML .= '<br>

<table class="bordered">
	<tr>
		<th class="accent">Statistics</th>
	</tr>
	<tr>
	<td>

' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/graphs.php",  "Graphs",                  "Everyone loves statistics!") . '
' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/graphs2.php", "NEW Graphs (Javascript)", "Everyone loves statistics!");

	if (file_exists(HTML_DIRECTORY . '/stats.html')) {
		$HTML .= file_get_contents(HTML_DIRECTORY . '/stats.html') . "\n";
	}

	$HTML .= '
	</td>
	</tr>
</table>


';

	return $HTML;

}

function freshports_LinkToDate($Date, $Text = '', $BranchName = BRANCH_HEAD) {
	$URL = '<a href="/date.php?date=' . date("Y/n/j", $Date);
	if ($BranchName != BRANCH_HEAD) {
		$URL .= '&amp;branch=' . htmlspecialchars($BranchName);
	}
	$URL .= '">';
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
<table class="fullwidth bordered vcentered">
<tr><td class="vtop">
<table class="fullwidth">
<tr>
	' . freshports_PageBannerText($Title) . '
</tr>
<tr>
<td>
  <table class="fullwidth borderless" cellpadding="0">
  <tr class="vtop">
   <td><img src="/images/warning.gif"></td>
   <td width="100%">
  <p>' .  "WARNING: $ErrorMessage" . '</p>
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
	$HTML .= '<table class="fullwidth borderless" cellpadding="4">' . "\n";

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
		$HTML .= '<dt><b>People watching this port, also watch:</b>: ';
		for ($i = 0; $i < $numrows; $i++) {
			$AlsoWatched->FetchNth($i);
			$HTML .= $AlsoWatched->URL;
			if (($i + 1) < $numrows) {
				$HTML .= ', ';
			}
		}
		$HTML .= '</dt>';
	}

	return $HTML;

}


function freshports_RedirectPermanent($URL) {
	#
	# My thanks to nne van Kesteren who posted this solution
	# at https://annevankesteren.nl/archives/2005/01/permanent-redirect
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
	//   https://fishbowl.pastiche.org/archives/001132.html
	// Based upon code from https://simon.incutio.com/archive/2003/04/23/conditionalGet

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
	//   https://fishbowl.pastiche.org/archives/001132.html
	// Based upon code from https://simon.incutio.com/archive/2003/04/23/conditionalGet

	if (!empty($LastModified)) {
		$UnixTime = strtotime($LastModified);
		freshports_ConditionalGetUnix($UnixTime);
	}
}

#
# obtained from https://ca3.php.net/manual/en/function.is-int.php 
# on 2 August 2005. Posted by phpContrib (A T) esurfers d o t c o m
# on 06-Nov-2003 03:42

function freshports_IsInt($x) {
   return ( is_numeric ($x ) ?  intval(0+$x ) ==  $x  :  false ); 
}

function freshports_GetPortID($dbh, $category, $port, $branch) {
	$Debug = 0;
	$sql = "select Port_ID('" . pg_escape_string($dbh, $category) . "', '" . pg_escape_string($dbh, $port) . "')";

	$sql = "select id AS port_id from ports where element_id = pathname_id('/ports";

	if ($branch != BRANCH_HEAD) {
	  $sql .= '/branches';
	}

	$sql .= '/' . pg_escape_string($dbh, $branch) . '/' .  pg_escape_string($dbh, $category) . '/' . pg_escape_string($dbh, $port) . "')";

	if ($Debug) echo $sql . '<br>';
	$result = pg_exec($dbh, $sql);
	if (!$result) {
		syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh) . ' - ' . $sql);
		die('something terrible has happened!');
	}

	if (pg_num_rows($result)) {
	  $myrow = pg_fetch_array($result, 0);
	  $port_id = $myrow['port_id'];
	} else {
	  $port_id = null;
	}

	return $port_id;
}

function freshports_GetElementID($dbh, $category, $port) {
	$sql = "select Element_ID('" . pg_escape_string($dbh, $category) . "', '" . pg_escape_string($dbh, $port) . "')";

	$result = pg_exec($dbh, $sql);
	if (!$result) {
		echo "error " . pg_last_error($dbh);
		exit;
	}

	$myrow = pg_fetch_array($result, 0);

	return $myrow['element_id'];
}

function freshports_OnWatchList($dbh, $UserID, $ElementID) {
	$sql = "select OnWatchList(" . pg_escape_string($dbh, $UserID) . ", " . pg_escape_string($dbh, $ElementID) . ")";

	$result = pg_exec($dbh, $sql);
	if (!$result) {
		echo "error " . pg_last_error($dbh);
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
      # given ports/www/p5-App-Nopaste/Makefile, we want something like: https://svn.freebsd.org/ports/head/www/p5-App-Nopaste/Makefile
      $RepoName = $RepoNames[$WhichRepo];
      $match    = $AdjustPathname[$WhichRepo]['match'];
      $replace  = $AdjustPathname[$WhichRepo]['replace'];
      $repo_file_name = $RepoName . '/head/' . preg_replace($match, $replace, $pathname);
      break;

    default:
      $repo_file_name = $pathname;
      break;
  }

  return $repo_file_name;
}

function _forDisplay($string, $flags = NULL, $encoding = FRESHPORTS_ENCODING) {
  # can't put this in the header.  See https://php.net/manual/en/functions.arguments.php
  if ($flags === NULL) {
    $flags = ENT_IGNORE | ENT_QUOTES | ENT_HTML5;
  }
  # does special magic for outputing stuff to a webpage.
  # e.g. htmlspecialchars($port->long_description, ENT_COMPAT | ENT_HTML401, 'ISO-8859-15')
	    
  $encoded = htmlspecialchars($string ?? '', $flags, $encoding);

  # if htmlspecialchars() fails, you get an empty string.
  # Report that, but only if the original was not empty.
  if (empty($encoded) && !empty($string)) {
     syslog(LOG_ERR, "htmlspecialchars in _forDisplay() with \$encoding='$encoding' failed for '$string'");
     $encoded = $string;
  }

  return $encoded;
}

define('EVERYTHING', 'FreshPorts has everything you want to know about <a href="https://www.freebsd.org/" rel="noopener noreferrer">FreeBSD</a> software, ports, packages,
applications, whatever term you want to use.');

openlog('FreshPorts', LOG_PID, LOG_LOCAL3);
#syslog(LOG_NOTICE, $_SERVER['SCRIPT_URL']);

function NormalizeBranch($Branch = BRANCH_HEAD) {
  # this function converts 'quarterly' to something like 2019Q2
  # from https://secure.php.net/manual/en/function.date.php
  # n Numeric representation of a month, without leading zeros 1 through 12

  if ($Branch == BRANCH_QUARTERLY) {
    $Branch = date('Y') . 'Q' . (floor((date('n') - 1) / 3) + 1);
  }

  if ($Branch != BRANCH_HEAD && !preg_match("/^\d{4}Q[1-4]$/", $Branch)) {
     # if not head or YYYYQN, then default to branch
     $Branch = BRANCH_HEAD;
  }
  return $Branch;
}

function BranchSuffix($Branch = BRANCH_HEAD) {
  if ($Branch == BRANCH_HEAD) {
    $BranchSuffix = '';
  } else {
    $BranchSuffix = '?branch=' . htmlspecialchars($Branch);
  }

  return $BranchSuffix;
}

function getLoginDetails($dbh, $statementName, $UserID, $Password) {

  $sql = 'select *, password_hash not like \'$2_$' . PW_HASH_COST . '$%\' as insecure_hash ' .
    'from users where lower(name) = lower($1) and password_hash = crypt($2, password_hash)';
  if (IsSet($Debug) && $Debug || 0) {
    echo '<pre>' . htmlentities($sql) . '<pre>';
  }

  $result = pg_prepare($dbh, $statementName, $sql) or die('query failed ' . pg_last_error($dbh));
  if ($result) {
    $result = pg_execute($dbh, $statementName, array($UserID, $Password))  or die('query failed ' . pg_last_error($dbh));
  }

  return $result;
}

function checkAcceptedMaxLoad($PageName) {
  switch($PageName) {
    case '/search.php':
      $MaxLoad = defined('MAX_LOAD_SEARCH') ? MAX_LOAD_SEARCH : 0.5 ;
      break;

    case '/commit.php':
      $MaxLoad = defined('MAX_LOAD_COMMIT') ? MAX_LOAD_COMMIT : 0.4;
      break;

    default:
      $MaxLoad = 0;
      break;
  }

  return $MaxLoad;
}

function checkLoadBeforeProceeding() {
  GLOBAL $User;
  
  if ($User->id) {
    return;
  }
  $MaxLoad = checkAcceptedMaxLoad($_SERVER['PHP_SELF']);
  if ($MaxLoad) {
    $load = sys_getloadavg();
    if ($load[0] > $MaxLoad) {
      header('HTTP/1.1 503 Too busy, try again later. You should never see this mesasge if you are logged in.');
      die('Server too busy. Please try again later.  You should never see this message if you are logged in.');
    }
  }
}
