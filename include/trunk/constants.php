<?php
	#
	# $Id: constants.php,v 1.6 2012-12-21 18:20:53 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');

#
# colours for the banners (not really banners, but headings)
#

$BannerBackgroundColour = "#FFCC33";
$BannerTextColour       = "#000000";
$BannerCellSpacing      = "0";
$BannerCellPadding      = "2";
$BannerBorder           = "1";
$BannerFontSize         = "+1";

$BannerWidth            = "100%";
$TableWidth             = "100%";
$DateFormatDefault      = "j M Y";
$TimeFormatDefault		= "H:i:s";

$FreshPortsTitle		= "FreshPorts";

$WatchNoticeFrequencyDaily			= "D";
$WatchNoticeFrequencyWeekly			= "W";
$WatchNoticeFrequencyFortnightly	= "F";
$WatchNoticeFrequencyMonthly		= "M";
$WatchNoticeFrequencyNever			= "Z";

$UserStatusActive      = "A";
$UserStatusDisabled    = "D";
$UserStatusUnconfirmed = "U";

$ProblemSolverEmailAddress	= "webmaster@freshports.org";


#
# SEQUENCES
#

$Sequence_Watch_List_ID			= 'watch_list_id_seq';
$Sequence_User_ID				= 'users_id_seq';

#
# external URLs
#

// path to the CVS repository
define('FRESHPORTS_FREEBSD_CVS_URL' , 'http://www.FreeBSD.org/cgi/cvsweb.cgi');

// path to the SVN repository
define('FRESHPORTS_FREEBSD_SVN_URL' , 'http://svnweb.FreeBSD.org');

// which repo do we want?
define('FREEBSD_REPO_SVN', 'svn');
define('FREEBSD_REPO_CVS', 'cvs');

// path to the ftp server
define('FRESHPORTS_FREEBSD_FTP_URL', 'ftp://ftp.freebsd.org/pub/FreeBSD/ports/i386/packages/All/');


// path to the cvs-all mailing list archive
$freshports_mail_archive = "http://www.freebsd.org/cgi/mid.cgi?db=mid&id=";

#
# max number of lines to show in a commit
#
$freshports_CommitMsgMaxNumOfLinesToShow = 15;

define('FRESHPORTS_BGCOLOR',   '#FFFFFF');
define('FRESHPORTS_TEXTCOLOR', '#000000');

define('FRESHPORTS_VERSION_REVISION_JOINER', '_');
define('FRESHPORTS_VERSION_EPOCH_JOINER',    ',');

define('PORTSMONSHOW',        1);
define('DISTFILESSURVEYSHOW', 1);

define('BRANCH_HEAD', 'head');
define('PATH_TO_PORTSDIR', '/usr/local/');
define('PORTSDIR_PREFIX',  'PORTS-');
