<?php
	#
	# $Id: getvalues.php,v 1.1.2.25 2003-04-27 20:33:21 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user.php');
	
GLOBAL $User;
$User = new User($db);

$Debug = 0;

$FormatDateDefault		= "%W, %b %e";
$FormatTimeDefault		= "%H:%i";
$DaysMarkedAsNewDefault	= 10;
$DefaultPageSize			= 50;


// there are only a few places we want to show the last change.
// such places set $GlobalHideLastChange == "Y"   
$GlobalHideLastChange   = "Y";

$DaysToShow  = 20;
$MaxArticles = 40;
$DaysNew     = 10;

/*
GLOBAL $MaxNumberOfPorts;
GLOBAL $ShowShortDescription;
GLOBAL $ShowMaintainedBy;
GLOBAL $ShowLastChange;
GLOBAL $ShowDescriptionLink;
GLOBAL $ShowChangesLink;
GLOBAL $ShowDownloadPortLink;
GLOBAL $ShowPackageLink;
GLOBAL $ShowHomepageLink;
GLOBAL $FormatDate;
GLOBAL $FormatTime;
GLOBAL $DaysMarkedAsNew;
GLOBAL $EmailBounceCount;
GLOBAL $CVSTimeAdjustment;
GLOBAL $UserName;
GLOBAL $visitor;
GLOBAL $db;
GLOBAL $NumberOfDays;
GLOBAL $WatchListAsk;
*/

$MaxNumberOfPorts			= 100;
$ShowShortDescription	= "Y";
$ShowMaintainedBy			= "Y";
$ShowLastChange			= "Y";
$ShowDescriptionLink		= "Y";
$ShowChangesLink			= "Y";
$ShowDownloadPortLink	= "Y";
$ShowPackageLink			= "Y";
$ShowHomepageLink			= "Y";
$FormatDate					= $FormatDateDefault;
$FormatTime					= $FormatTimeDefault;
$DaysMarkedAsNew			= $DaysMarkedAsNewDefault;
$EmailBounceCount			= 0;
$CVSTimeAdjustment		= -10800;	# this is number of seconds the web server is relative to the cvs server.
									# a value of -10800 means the web server is three hours east of the cvs server.
									# we can override that for a particular user.

$LocalTimeAdjustment	= 0;		# This can be used to display the time the webpage was loaded.
$NumberOfDays			= 9;
$WatchListAsk			= 1;

#
# flags for showing various port parts.
#
$ShowEverything			= 0;
$ShowPortCreationDate	= 0;

$UserName	= "";
$User->id	= "";

// This is used to determine whether or not the cach can be used.
$DefaultMaxArticles = $MaxArticles;

$visitor = $_COOKIE["visitor"];
if (!empty($visitor)) {
	
	if ($User->FetchByCookie($visitor) != 1) {
		if ($Debug) echo "we didn't find anyone with that login... " . pg_errormessage() . "\n<br>";
		if ($Debug) echo ' no cookie found for that person ';
		# we were given a cookie which didn't refer to a cookie we found.
		freshports_CookieClear();
		unset($visitor);

	} else {
		if ($Debug) echo "we found a result there...\n<br>";
		if ($User->status == $UserStatusDisabled) {
			#
			# the account has become disabled after they have
			# logged in.  Let's just leave them a simple
			# message for them to contact us.
			#

			freshports_CookieClear();
			echo 'Database error: Account details corrupted.  Please contact ' . $ProblemSolverEmailAddress . '.<BR>';
			echo 'You have been logged out.';
			exit;
		}
/*
		$UserName					= $myrow["name"];
		$User->id					= $myrow["id"];
		$emailsitenotices_yn		= $myrow["emailsitenotices_yn"];
		$email						= $myrow["email"];

		$WatchNotice = new WatchNotice($db);
		$WatchNotice->FetchByID($myrow["watch_notice_id"]);

		$watchnotifyfrequency	= $WatchNotice->frequency;


//		$MaxNumberOfPorts			= $myrow["max_number_of_ports"];
		$ShowShortDescription	= $myrow["show_short_description"];
		$ShowMaintainedBy			= $myrow["show_maintained_by"];
		$ShowLastChange			= $myrow["show_last_change"];
		$ShowDescriptionLink		= $myrow["show_description_link"];
		$ShowChangesLink			= $myrow["show_changes_link"];
		$ShowDownloadPortLink	= $myrow["show_download_port_link"];
		$ShowPackageLink			= $myrow["show_package_link"];
		$ShowHomepageLink			= $myrow["show_homepage_link"];

		if ($emailsitenotices_yn == "t") {
			$emailsitenotices_yn = "ON";
		} else {
			$emailsitenotices_yn = "";
		}

		$EmailBounceCount	= $myrow["emailbouncecount"];

		$NumberOfDays		= $myrow["number_of_days"];
*/

		if ($Debug) echo "we found a row there...\n<br>";
		// record their last login
		$sql = "update users set lastlogin = current_timestamp where id = $User->id";
//		echo $sql, "<br>";
		$result = pg_exec($db, $sql);

	}
	if ($Debug) {
		echo "UserName = $User->name\n<br>UserID=$User->id<br>\n";
	}
} else {
	if ($Debug) echo "we have no \$visitor\n<BR>";
}
?>
