<?php
	#
	# $Id: constants.php,v 1.6 2012-12-21 18:20:53 dan Exp $
	#
	# Copyright (c) 1998-2022 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.local.php');

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
$TimeFormatDefault      = "H:i:s";

$FreshPortsTitle        = "FreshPorts";

$WatchNoticeFrequencyDaily       = "D";
$WatchNoticeFrequencyWeekly      = "W";
$WatchNoticeFrequencyFortnightly = "F";
$WatchNoticeFrequencyMonthly     = "M";
$WatchNoticeFrequencyNever       = "Z";

$UserStatusActive      = "A";
$UserStatusDisabled    = "D";
$UserStatusUnconfirmed = "U";

define('PROBLEM_SOLVER_EMAIL_ADDRESS', 'webmaster@freshports.org');

if (!defined('USER_COOKIE_NAME')) define('USER_COOKIE_NAME', "visitor");

#
# SEQUENCES
#

$Sequence_Watch_List_ID = 'watch_list_id_seq';
$Sequence_User_ID       = 'users_id_seq';

#
# external URLs
#

// path to the CVS repository
define('FRESHPORTS_FREEBSD_CVS_URL' , 'https://www.FreeBSD.org/cgi/cvsweb.cgi');

// path to the SVN repository
define('FRESHPORTS_FREEBSD_SVN_URL' , 'https://svnweb.FreeBSD.org');

// which repo do we want?
define('FREEBSD_REPO_SVN', 'svn');
define('FREEBSD_REPO_CVS', 'cvs');

// these constants came in with git
// these values are from the repo.repository table

define('FREEBSD_REPOSITORY_GIT',        'git');
define('FREEBSD_REPOSITORY_SUBVERSION', 'subversion');

// path to the ftp server
define('FRESHPORTS_FREEBSD_FTP_URL', 'ftp://ftp.freebsd.org/pub/FreeBSD/ports/i386/packages/All/');


// path to the cvs-all mailing list archive
$freshports_mail_archive = "https://www.freebsd.org/cgi/mid.cgi?db=mid&id=";

#
# max number of lines to show in a commit
#
$freshports_CommitMsgMaxNumOfLinesToShow = 15;

define('FRESHPORTS_BGCOLOR',   '#FFFFFF');
define('FRESHPORTS_TEXTCOLOR', '#000000');

define('DELETED_PORT_LINK_COLOR', 'red');

define('FRESHPORTS_VERSION_REVISION_JOINER', '_');
define('FRESHPORTS_VERSION_EPOCH_JOINER',    ',');

define('PORTSMONSHOW',        1);
define('CONFIGUREPLISTSHOW',  1);
define('DISTFILESSURVEYSHOW', 1);

define('BRANCH_HEAD',      'head');
define('BRANCH_QUARTERLY', 'quarterly');

if (!defined('PATH_TO_PORTSDIR')) define('PATH_TO_PORTSDIR', '/usr/local/');  // must have a trailing /

define('PORTSDIR_PREFIX',  'PORTS-');

define('BASEDIR', '/var/db/freshports');

define('MESSAGE_QUEUES', BASEDIR . '/message-queues');

define('MESSAGE_QUEUE_RECENT', MESSAGE_QUEUES . '/recent');

define('SIGNALS_DIRECTORY',  BASEDIR        . '/signals');    # signals / flags set by backend. some are read by webserver.

define('CACHE_DIRECTORY',    BASEDIR         . '/cache');     # caching of data for web pages
define('DAILY_DIRECTORY',    CACHE_DIRECTORY . '/daily');     # cache for date.php
define('HTML_DIRECTORY',     CACHE_DIRECTORY . '/html');      # HTML generated by FreshPorts but not cat/port stuff
define('NEWS_DIRECTORY',     CACHE_DIRECTORY . '/news');      # cached results for newsfeeds
define('PAGES_DIRECTORY',    CACHE_DIRECTORY . '/pages');     # commits.php, etc
define('PORTS_DIRECTORY',    CACHE_DIRECTORY . '/ports');     # content of category/port web page
define('SPOOLING_DIRECTORY', CACHE_DIRECTORY . '/spooling');  # cache spooling

define('DELETE_PACKAGE', 'delete-package');

define('DEFAULT_SVN_REPO', 'svnweb.freebsd.org');
define('DEFAULT_GIT_REPO', 'cgit.freebsd.org');
define('DEFAULT_GITHUB',   'github.com');
define('DEFAULT_GITLAB',   'gitlab.com');
define('DEFAULT_CODEBERG', 'codeberg.org');

define('DEFAULT_ICON_SIZE', 22);
define('FALLOUT_SMALLER_ICON_SIZE', 16);
define('FALLOUT_TITLE', 'Fallout');

define('PORT_STATUS_ACTIVE',  'A');
define('PORT_STATUS_DELETED', 'D');

# used mainly when calling freshports_LinkToDate as a sensible parameter
define('DATE_FORMAT_D_LONG_MONTH', '');

# used when invoking classes/newsfeed.php::newsfeed() from www/backend/news.php
define('NO_WATCH_LIST_ID', 0);

# number of seconds a newsfeed will remain before refreshed.
define('NEWSFEED_REFRESH_SECONDS', 3600);

define('MAINTENANCE_PAGE', 'now-in-maintenance-mode.php');
define('MAINTENANCE_MODE_RERESH_TIME_SECONDS', 180);

define('ISSUES', 'https://github.com/FreshPorts/freshports/issues');

# a git hash is 160 bytes, which is expressed as 40 characters in hex
define('MIN_GIT_HASH_LENGTH', 40);

# for packages

const PACKAGE_SETS = array('latest', 'quarterly');

# the default nubmer of commits to show
define('DEFAULT_NUMBER_OF_COMMITS', 100);

const USES_PYTHON = 'python';
const USES_PHP    = 'php';

const PYTHON_PKGNAMEPREFIX = 'PYTHON_PKGNAMEPREFIX';

# used by
# www/search.php
# classes/ports_by_pkg_plist.php

const LOGIN_QUERY = 'LOGIN';

const SEARCH_SELECT_FIELD = '
  select CL.commit_date - SystemTimeAdjust() AS last_commit_date, 
         P.id,
         E.name as port,
         C.name as category,
         C.id as category_id,
         P.version as version,
         P.revision as revision,
         P.portepoch as epoch,
         P.maintainer,
         P.short_description,
         P.package_exists,
         P.extract_suffix,
         P.homepage,
         E.status,
         P.element_id,
         P.broken,
         P.deprecated,
         P.ignore,
         PV.current as vulnerable_current,
         PV.past    as vulnerable_past,
         P.forbidden,
         P.master_port,
         P.no_package,
         P.package_name,
         P.restricted,
         P.license_restricted,
         P.manual_package_build,
         P.no_cdrom,
         P.expiration_date,
         P.license,
         P.last_commit_id,
         P.distinfo,
         element_pathname(P.element_id) as element_pathname,
         Cl.svn_revision,
         P.pkgmessage,
         P.uses  ';


# passed to pgcrypto gen_salt when creating or updating user passwords in the database
# used by
# www/new-user.php
# www/customize.php
const HASH_UPDATE_QUERY         = 'HASH_UPDATE';
const RESEND_CONFIRMATION_QUERY = 'RESEND_CONFIRMATION';

# used by FreshSource commit.php
const COMMIT_QUERY = 'COMMIT';

# used by www/confirmation.php
const TOKEN_CONFIRM_QUERY = 'TOKEN_CONFIRM_QUERY';

# used by www/logout.php
const LOGOUT_QUERY = 'LOGOUT_QUERY';

# used by www/forgotten-password.php
const FORGOTTEN_PASSWORD_QUERY1 = 'FORGOTTEN_PASSWORD_QUERY1';
const FORGOTTEN_PASSWORD_QUERY2 = 'FORGOTTEN_PASSWORD_QUERY2';
const FORGOTTEN_PASSWORD_QUERY3 = 'FORGOTTEN_PASSWORD_QUERY3';

# used by www/password-reset-via-token.php
const FORGOTTEN_PASSWORD_QUERY4 = 'FORGOTTEN_PASSWORD_QUERY4';

# used by www/repo.php
const REPO_QUERY = 'REPO_QUERY';

if (!defined('PW_HASH_METHOD')) define('PW_HASH_METHOD', 'bf');
if (!defined('PW_HASH_COST'))   define('PW_HASH_COST', 14);

define('HTML_DOCTYPE', '<!DOCTYPE html>');

define('ICON_SEPARATOR', ' &#166; ');

define('VUXML_LATEST', HTML_DIRECTORY . '/vuln-latest.html');

# re https://cgit.freebsd.org/ports/commit/?id=4010f7bbc03638d71781ce091bf40a0907fa12fe
define('LAST_SUBVERSION_COMMIT', '2021-03-31 03:12:20');
