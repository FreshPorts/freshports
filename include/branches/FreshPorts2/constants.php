<?php
	#
	# $Id: constants.php,v 1.1.2.11 2004-08-09 22:30:04 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

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
# These values are used when specifying add/remove on a port
#
$FreshPortsWatchedPortPrefix	= "<SMALL><A HREF=\"/watch-list.php?remove=";
$FreshPortsWatchedPortSuffix	= '">' . freshports_Watch_Icon() . '</A></SMALL>';
$FreshPortsWatchedPortNotPrefix	= "<SMALL><A HREF=\"/watch-list.php?add=";
$FreshPortsWatchedPortNotSuffix	= '">' . freshports_Watch_Icon_Add() . '</A></SMALL>';

#
# These are similar to the above but are using in SQL queries
#
#$FreshPortsWatchedPort		= "<SMALL><A HREF=\"/watch-list.php?remove=' || commits_latest_ports.element_id || '\">Remove</A></SMALL>";
#$FreshPortsWatchedPortNot	= "<SMALL><A HREF=\"/watch-list.php?add='    || commits_latest_ports.element_id || '\">Add</A></SMALL>";




#
# SEQUENCES
#

$Sequence_Watch_List_ID			= 'watch_list_id_seq';
$Sequence_User_ID				= 'users_id_seq';
$Sequence_Security_Notice_ID	= 'security_notice_id_seq';

#
# external URLs
#

// path to the CVS repository
define('FRESHPORTS_FREEBSD_CVS_URL' , 'http://www.FreeBSD.org/cgi/cvsweb.cgi');

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


?>