<script language="php">

	# $Id: getvalues.php,v 1.1.2.7 2002-01-06 16:58:45 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

$Debug = 0;

$FormatDateDefault	= "%W, %b %e";
$FormatTimeDefault	= "%H:%i";
$DaysMarkedAsNewDefault	= 10;


// there are only a few places we want to show the last change.
// such places set $GlobalHideLastChange == "Y"   
$GlobalHideLastChange   = "Y";

$DaysToShow  = 20;
$MaxArticles = 40;
$DaysNew     = 10;

$MaxNumberOfPorts		= 100;
$ShowShortDescription	= "Y";
$ShowMaintainedBy		= "Y";
$ShowLastChange			= "Y";
$ShowDescriptionLink	= "Y";
$ShowChangesLink		= "Y";
$ShowDownloadPortLink	= "Y";
$ShowPackageLink		= "Y";
$ShowHomepageLink		= "Y";
$FormatDate				= $FormatDateDefault;
$FormatTime				= $FormatTimeDefault;
$DaysMarkedAsNew		= $DaysMarkedAsNewDefault;
$EmailBounceCount		= 0;
$CVSTimeAdjustment		= -10800;	# this is number of seconds the web server is relative to the cvs server.
									# a value of -10800 means the web server is three hours east of the cvs server.
									# we can override that for a particular user.

$LocalTimeAdjustment	= 0;		# This can be used to display the time the webpage was loaded.


#
# flags for showing various port parts.
#
$ShowEverything			= 0;
$ShowPortCreationDate	= 0;

$UserName		= "";
$UserID			= "";

// This is used to determine whether or not the cach can be used.
$DefaultMaxArticles = $MaxArticles;

if (!empty($visitor)) {
	$sql = "select * from users ".
         "where cookie = '$visitor'";

	if ($Debug) {
		echo "sql=$sql<br>\n";
	}


	$result = pg_exec($db, $sql) or die("getvalues query failed " . pg_errormessage());

	if ($result) {
		if ($Debug) echo "we found a result there...\n<br>";
		$numrows = pg_numrows($result);
		if ($numrows) {
			$myrow = pg_fetch_array ($result, 0);
			if ($myrow) {
				if ($Debug) echo "we found a row there...\n<br>";

				$UserName				= $myrow["name"];
				$UserID					= $myrow["id"];
				$emailsitenotices_yn	= $myrow["emailsitenotices_yn"];
				$email					= $myrow["email"];

				$WatchNotice = new WatchNotice($db);
				$WatchNotice->FetchByID($myrow["watch_notice_id"]);

				$watchnotifyfrequency	= $WatchNotice->frequency;

//				$MaxNumberOfPorts		= $myrow["max_number_of_ports"];
				$ShowShortDescription	= $myrow["show_short_description"];
				$ShowMaintainedBy		= $myrow["show_maintained_by"];
				$ShowLastChange			= $myrow["show_last_change"];
				$ShowDescriptionLink	= $myrow["show_description_link"];
				$ShowChangesLink		= $myrow["show_changes_link"];
				$ShowDownloadPortLink	= $myrow["show_download_port_link"];
				$ShowPackageLink		= $myrow["show_package_link"];
				$ShowHomepageLink		= $myrow["show_homepage_link"];

/*
				if ($myrow["days_marked_as_new"]) {
					$DaysMarkedAsNew	= $myrow["days_marked_as_new"];
				} else {
					$DaysMarkedAsNew	= $DaysMarkedAsNewDefault;
				}
*/

/*
				if ($myrow["format_date"]) {
					$FormatDate			= $myrow["format_date"];
				}

				if ($myrow["format_time"]) {
					$FormatTime			= $myrow["format_time"];
				}
*/
				if ($emailsitenotices_yn == "t") {
					$emailsitenotices_yn = "ON";
				} else {
					$emailsitenotices_yn = "";
				}
/*
				$SampleFormatDate	= $myrow["sample_date"];
				$SampleFormatTime	= $myrow["sample_time"];
*/

				$EmailBounceCount	= $myrow["emailbouncecount"];
 
//				echo "visitor = $visitor<br>";

				// record their last login
				$sql = "update users set lastlogin = current_timestamp where id = $UserID";
//				echo $sql, "<br>";
				$result = pg_exec($db, $sql);
			}
		} else {
			if ($Debug) echo "we didn't find anyone with that login... " . pg_errormessage() . "\n<br>";
			if ($Debug) echo ' no cookie found for that person ';
			# we were given a cookie which didn't refer to a cookie we found.
			freshports_CookieClear();
			unset($visitor);
		}
	}
	if ($Debug) {
		echo "UserName = $UserName\n<br>UserID=$UserID<br>\n";
	}
}
</script>
