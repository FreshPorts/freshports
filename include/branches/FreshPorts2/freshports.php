<?php
	#
	# $Id: freshports.php,v 1.4.2.145 2003-05-16 02:44:13 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/burstmedia.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/announcements.php');
#
# special HTMLified mailto to foil spam harvesters
#
DEFINE('MAILTO',                '&#109;&#97;&#105;&#108;&#116;&#111;');
DEFINE('COPYRIGHTYEARS',        '2000-2003');
DEFINE('URL2LINK_CUTOFF_LEVEL', 70);


if ($Debug) echo "'" . $_SERVER['DOCUMENT_ROOT'] . '/../classes/watchnotice.php<BR>';

require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watchnotice.php');

#
# These are the pages which take NOINDEX and NOFOLLOW meta tags
#


function freshports_IndexFollow($URI) {
	$NOINDEX{'/index.php'}				= 1;
	$NOINDEX{'/date.php'}				= 1;
	$NOINDEX{'/ports-deleted.php'}	= 1;
	$NOINDEX{'/ports-new.php'}			= 1;
	$NOINDEX{'/ports-forbidden.php'}	= 1;
	$NOFOLLOW{'/date.php'}				= 1;
	$NOFOLLOW{'/graphs.php'}			= 1;
	$NOFOLLOW{'/ports-deleted.php'}	= 1;

	# well, OK, so it may not be a URI... but it's close

	if ($NOINDEX{$URI} || $NOFOLLOW{$URI}) {
		echo '	<meta name="robots" content="';
		if ($NOINDEX{$URI}) {
			echo 'noindex';
			if ($NOFOLLOW{$URI}) {
				echo ',';
			}
		}

		if ($NOFOLLOW{$URI}) {
			echo 'nofollow';
		}

		echo '">' . "\n";
	}

}

function freshports_BannerSpace() {

return "
  <TR>
    <TD height=\"10\"></TD>
  </TR>
";

}


function freshports_Files_Icon() {
	return '<IMG SRC="/images/logs.gif" ALT="files touched by this commit" TITLE="files touched by this commit" BORDER="0" WIDTH="17" HEIGHT="20">';
}

function freshports_Refresh_Icon() {
	return '<IMG SRC="/images/refresh.gif" ALT="Refresh" TITLE="Refresh" BORDER="0" WIDTH="15" HEIGHT="18">';
}

function freshports_Deleted_Icon() {
	return '<IMG SRC="/images/deleted.gif" ALT="Deleted" TITLE="Deleted" BORDER="0" WIDTH="15" HEIGHT="15">';
}

function freshports_Forbidden_Icon() {
	return '<IMG SRC="/images/forbidden.gif" ALT="Forbidden" TITLE="Forbidden" WIDTH="20" HEIGHT="20">';
}

function freshports_Broken_Icon() {
	return '<IMG SRC="/images/broken.gif" ALT="Broken" TITLE="Broken" WIDTH="17" HEIGHT="16">';
}

function freshports_New_Icon() {
	return '<IMG SRC="/images/new.gif" ALT="new!" TITLE="new!" WIDTH=28 HEIGHT=11 HSPACE=2>';
}

function freshports_Mail_Icon() {
	return '<IMG SRC="/images/envelope10.gif" ALT="Original commit" TITLE="Original commit message" BORDER="0" WIDTH="25" HEIGHT="14">';
}

function freshports_Commit_Icon() {
	return '<IMG SRC="/images/copy.gif" ALT="Commit details" TITLE="FreshPorts commit message" BORDER="0" WIDTH="16" HEIGHT="16">';
}

function freshports_Watch_Icon() {
	return '<IMG SRC="/images/watch.gif" ALT="Item is on one of your default watch lists" TITLE="Item is on one of your default watch lists" BORDER="0" WIDTH="23" HEIGHT="22">';
}

function freshports_Watch_Icon_Add() {
	return '<IMG SRC="/images/watch-add.gif" ALT="Add item to your default watch lists" TITLE="Add item to your default watch lists" BORDER="0" WIDTH="13" HEIGHT="13">';
}

function freshports_Security_Icon() {
	return '<IMG SRC="/images/security.gif"  ALT="This commit addresses a security issue" TITLE="This commit addresses a security issue" WIDTH="20" HEIGHT="20" BORDER="0">';
}

function freshports_Encoding_Errors() {
	return '<IMG SRC="/images/error.gif" ALT="Encoding Errors (not all of the commit message was ASCII)" TITLE="Encoding Errors (not all of the commit message was ASCII)" BORDER="0" WIDTH="16" HEIGHT="16">';
}

function freshports_Watch_Link_Add($WatchListAsk, $WatchListCount, $ElementID) {
	$HTML = '<SMALL><A HREF="/watch-list.php?';
	$HTML .= 'add='  . $ElementID;

	if ($WatchListAsk == 'ask') {
		$HTML .= '&ask=1';
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
		$HTML .= '&ask=1';
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
		$HTML .= '';
	} else {
		$HTML .= '<A HREF="' . htmlentities($freshports_mail_archive . $message_id) . '">';
		$HTML .= freshports_Mail_Icon();
		$HTML .= '</A>';
	}

	return $HTML;
}

function freshports_Commit_Link($message_id, $LinkText = '') {
	#
	# produce a link to the commit.  by default, we provide the graphic link.
	#

	$HTML .= '<A HREF="/commit.php?message_id=' . $message_id . '">';
	if ($LinkText == '') {
		$HTML .= freshports_Commit_Icon();
	} else {
		$HTML .= $LinkText;
	}
	$HTML .= '</A>';

	return $HTML;
}

function freshports_MorePortsToShow($message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow) {
	$HTML .= "(Only the first $MaxNumberPortsToShow of $NumberOfPortsInThisCommit ports in this commit are shown above. ";
	$HTML .= freshports_Commit_Link($message_id, '<IMG SRC="/images/play.gif" ALT="View all ports for this commit" BORDER="0" WIDTH="13" HEIGHT="13">');
	$HTML .= ")";

	return $HTML;
}

function freshports_MoreCommitMsgToShow($message_id, $NumberOfLinesShown) {
	$HTML .= "(Only the first $NumberOfLinesShown lines of the commit message are shown above ";
	$HTML .= freshports_Commit_Link($message_id, '<IMG SRC="/images/play.gif" ALT="View all ports for this commit" BORDER="0" WIDTH="13" HEIGHT="13">');
	$HTML .= ")";

	return $HTML;
}

function freshports_CookieClear() {
#	echo " clearing the cookie";
	SetCookie("visitor", '', 0, '/');
}

function freshportsObscureHTML($HTML) {
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

	$HTML = "<A HREF=\"" . MAILTO . ":$new_addr\">$committer</A>";

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

   freshports_HTML_Start();
   freshports_Header($ArticleTitle, $Description, $Keywords, $Phorum);

   freshports_body();

   if ($ShowAds) {
      BurstMediaCode();
      if ($BannerAd) {
		echo "\n<CENTER>\n";
		BurstMediaAd();
		echo "</CENTER>\n\n";
      }
   }

   freshports_Logo();
   freshports_navigation_bar_top();

	GLOBAL $db;
	$Announcement = new Announcement($db);

	$NumRows = $Announcement->FetchAllActive();
	if ($NumRows > 0) {
		echo DisplayAnnouncements($Announcement);
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

echo '<BR>
<TABLE WIDTH="' . $TableWidth . '" BORDER="0" ALIGN="center">
<TR>
	<TD><A HREF="';

	if ($_SERVER["PHP_SELF"] == "/index.php") {
		echo 'other-copyrights.php';
	} else {
		echo '/';
	}
        echo '"><IMG SRC="' . $FreshPortsLogo . '" ALT="' . $FreshPortsName . ' -- ' . $FreshPortsSlogan . ' " WIDTH="' . $FreshPortsLogoWidth . '" HEIGHT="' . $FreshPortsLogoHeight . '" BORDER="0"></A></TD>
        <TD ALIGN="right" CLASS="sans" VALIGN="bottom">' . FormatTime(Date("D, j M Y g:i A T"), $LocalTimeAdjustment, "D, j M Y g:i A T") . '</TD>
</TR>
</TABLE>
';


}


function freshports_HTML_start() {
GLOBAL $Debug;

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
';
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
			echo initvar("title");
		}
	}

	echo "</TITLE>
";

	freshports_style($Phorum);

	echo "
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">
	<META NAME=\"description\" CONTENT=\"";

	if ($Description) {
		echo $Description;
	} else {
		echo $ArticleTitle;
	}

	echo "\">
	<META NAME=\"keywords\"    CONTENT=\"$Keywords\">
	<META http-equiv=\"Pragma\"              content=\"no-cache\">
";

	echo '	<meta name="MSSmartTagsPreventParsing" content="TRUE">' . "\n";

?>

	<LINK REL="SHORTCUT ICON" HREF="http://www.freshports.org/favicon.ico">
	<meta name="MSSmartTagsPreventParsing" content="TRUE">
	<META http-equiv="Pragma"              CONTENT="no-cache">
	<META HTTP-EQUIV="Expires"             CONTENT="0">
	<META HTTP-EQUIV="Cache-Control"       CONTENT="no-cache">
	<META HTTP-EQUIV="Pragma-directive"    CONTENT="no-cache">
	<META HTTP-EQUIV="cache-directive"     CONTENT="no-cache">
	<META NAME="ROBOTS"                    CONTENT="NOARCHIVE">

<?php

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
	echo "\n        <STYLE TYPE=\"text/css\">\n";

if (2==2) {
?>
BODY, TD, TR, P, UL, OL, LI, INPUT, SELECT, DL, DD, DT, FONT
{
    font-family: Helvetica, Verdana, Arial, Clean, sans-serif;
    font-size: 12px;
}
<?
}
	echo "
                CODE.code { color: #461b7e}
                PRE.code {  color: #461b7e}
                BLOCKQUOTE.code { color: #461b7e}
                TD.sans { font-size: smaller; }
                P.white { color: white; }
                P.blackhead { color: black; font-weight: 900; }
                P.whitehead { color: white; font-weight: 900; }
                P.yellow { color: #FFCC33; }
                A:hover { color: #666666; }
                A.white { color: white; text-decoration: none; font-size: smaller; }
                A.black { color: black; text-decoration: none; font-size: smaller; }
                A.white:hover { text-decoration: underline; }
        </STYLE>\n";

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

function freshports_SideBarHTML($Self, $URL, $Title) {
   if ($Self == $URL || ($Self == '/index.php' && $URL == '/')) {
      $HTML = $Title;
   } else {
      $HTML = '<a href="' . $URL . '">' . $Title . '</a>';
   }

   return $HTML;
}

function freshports_SideBarHTMLParm($Self, $URL, $Parm, $Title) {
   if ($Self == $URL || ($Self == '/index.php' && $URL == '/')) {
      $HTML = $Title;
   } else {
      $HTML = '<a href="' . $URL . $Parm . '">' . $Title . '</a>';
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


function freshports_PortDetails($port, $db, $ShowDeletedDate, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, $LinkToPort = 0, $AddRemoveExtra = '', $ShowCategory = 1, $ShowDateAdded = "N", $IndicateWatchListStatus = 1, $ShowMasterSites = 0, $ShowWatchListCount = 0) {
//
// This fragment does the basic port information for a single port.
// It really needs to be fixed up.
//
	GLOBAL $freshports_CVS_URL;
	GLOBAL $freshports_FTP_URL;
	GLOBAL $ShowDepends;
	GLOBAL $FreshPortsWatchedPortPrefix;
	GLOBAL $FreshPortsWatchedPortSuffix;
	GLOBAL $FreshPortsWatchedPortNotPrefix;
	GLOBAL $FreshPortsWatchedPortNotSuffix;
	GLOBAL $User;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;

	$MarkedAsNew = "N";
	$HTML .= "<DL>\n";

	$HTML .= "<DT>";

	$HTML .= '<BIG><B>';

	if ($LinkToPort) {
		$HTML .= "<A HREF=\"/$port->category/$port->port/\">$port->port";
	} else {
		$HTML .= $port->port;
	}

	if (strlen($port->{version}) > 0) {
    	$HTML .= ' ' . $port->{version};
		if (strlen($port->{revision}) > 0 && $port->{revision} != "0") {
    		$HTML .= '-' . $port->{revision};
		}
	}

	if ($LinkToPort) {
		$HTML .= '</A>';
	}

	$HTML .= "</B></BIG>";

	if ($ShowCategory) {
		$HTML .= ' / <A HREF="/' . $port->category . '/">' . $port->category . '</A>';
	}

	if ($User->id && $IndicateWatchListStatus) {
		if ($port->{onwatchlist}) {
			$HTML .= ' '. freshports_Watch_Link_Remove($User->watch_list_add_remove, $port->onwatchlist, $port->{element_id});
		} else {
			$HTML .= ' '. freshports_Watch_Link_Add   ($User->watch_list_add_remove, $port->onwatchlist, $port->{element_id});
		}
	}

	// indicate if this port has been removed from cvs
	if ($port->{status} == "D") {
		$HTML .= " " . freshports_Deleted_Icon() . "\n";
	}

	// indicate if this port needs refreshing from CVS
	if ($port->{needs_refresh}) {
		$HTML .= " " . freshports_Refresh_Icon() . "\n";
	}

	if ($port->{date_added} > Time() - 3600 * 24 * $DaysMarkedAsNew) {
		$MarkedAsNew = "Y";
		$HTML .= freshports_New_Icon() . "\n";
	}

#	if ($ShowWatchListCount) {
		$HTML .= ' <a href="/faq.php">WLC</a>=' . $port->WatchListCount() . '<br>';
#	}

	$HTML .= "</DT>\n<DD>";
	# show forbidden and broken
	if ($port->forbidden) {
		$HTML .= freshports_Forbidden_Icon() . ' FORBIDDEN: ' . htmlify(htmlspecialchars($port->forbidden)) . "<br>";
	}

	if ($port->broken) {
		$HTML .= freshports_Broken_Icon() . ' BROKEN: ' . htmlify(htmlspecialchars($port->broken)) . "<br>"; ;
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
      $HTML .= freshportsObscureHTML('?cc=ports@FreeBSD.org&subject=FreeBSD%20Port:%20' . $port->port . '-' . $port->version) . '">';
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

				if ($port->encoding_losses == 't') {
					$HTML .= '&nbsp;' . freshports_Encoding_Errors();
				}

				$HTML .= ' ' . freshports_Commit_Link($port->message_id);
				$HTML .= ' ' . freshports_CommitFilesLink($port->message_id, $port->category, $port->port);

#				$HTML .= freshports_PortDescriptionPrint($port->update_description, $port->encoding_losses);
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

   if ($port->categories) {
      // remove the primary category
      $Categories = str_replace($port->category, '', $port->categories);
      $Categories = str_replace('  ', ' ', $Categories);
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

/*
echo 'build = ' . $port->depends_build . "<br>\n";
echo 'run   = ' . $port->depends_run . "<br>\n";
*/

if ($ShowDepends) {
   if ($port->depends_build) {
      $HTML .= "<i>required to build:</i> ";

      // sometimes they have multiple spaces in the data...
      $temp = str_replace('  ', ' ', $port->depends_build);
      
      // split each depends up into different bits
      $depends = explode(' ', $temp);
      $Count = count($depends);
      for ($i = 0; $i < $Count; $i++) {
          // split one depends into the library and the port name (/usr/ports/<category>/<port>)

          $DependsArray = explode(':', $depends[$i]);

          // now extract the port and category from this port name
          $CategoryPort      = str_replace('/usr/ports/', '', $DependsArray[1]) ;
          $CategoryPortArray = explode('/', $CategoryPort);
          $DependsPortID     = freshports_PortIDFromPortCategory($CategoryPortArray[0], $CategoryPortArray[1], $db);

          $HTML .= '<A HREF="/' . $CategoryPortArray[0] . '/' . $CategoryPortArray[1] . '/">' . $CategoryPortArray[0] . '/' . $CategoryPortArray[1]. '</a>';
          if ($i < $Count - 1) {
             $HTML .= ", ";
          }
      }
      $HTML .= "<br>\n";
   }

   if ($port->depends_run) {
      $HTML .= "<i>required to run:</i> ";
      // sometimes they have multiple spaces in the data...
      $temp = str_replace('  ', ' ', $port->depends_run);

      // split each depends up into different bits
      $depends = explode(' ', $temp);
      $Count = count($depends);
      for ($i = 0; $i < $Count; $i++) {
          // split one depends into the library and the port name (/usr/ports/<category>/<port>)

          $DependsArray = explode(':', $depends[$i]);

          // now extract the port and category from this port name
          $CategoryPort      = str_replace('/usr/ports/', '', $DependsArray[1]) ;
          $CategoryPortArray = explode('/', $CategoryPort);
          $DependsPortID     = freshports_PortIDFromPortCategory($CategoryPortArray[0], $CategoryPortArray[1], $db);

          $HTML .= '<A HREF="/' . $CategoryPortArray[0] . '/' . $CategoryPortArray[1] . '/">' . $CategoryPortArray[0] . '/' . $CategoryPortArray[1]. '</a>';
          if ($i < $Count - 1) {
             $HTML .= ", ";
          }
      }
      $HTML .= "<BR>\n";
   }

}

	if ($ShowMasterSites) {
		$HTML .= '<dl><dt><i>master sites:</i></dt>' . "\n";

		$MasterSites = explode(' ', $port->master_sites);
		foreach ($MasterSites as $Site) {
			$HTML .= '<dd>' . htmlify(htmlspecialchars($Site)) . "</dd>\n";
		}

		$HTML .= "</dl>\n";
	}

   if (!$HideDescription && ($ShowDescriptionLink == "Y" || $ShowEverything)) {
      // Long descripion
      $HTML .= '<A HREF="/' . $port->category . '/' . $port->port .'/">Description</a>';

      $HTML .= ' <b>:</b> ';
   }

   if ($ShowChangesLink == "Y" || $ShowEverything) {
      // changes
      $HTML .= '<a HREF="' . $freshports_CVS_URL . '/ports/' .
               $port->category . '/' .  $port->port . '/">CVSWeb</a>';
   }

   // download
   if ($port->status == "A" && ($ShowDownloadPortLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="http://www.freebsd.org/cgi/pds.cgi?ports/' .
               $port->category . '/' .  $port->port . '">Sources</a>';
   }

   if ($port->package_exists == "Y" && ($ShowPackageLink == "Y" || $ShowEverything)) {
      // package
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="ftp://ftp5.FreeBSD.org/pub/FreeBSD/FreeBSD-stable/packages/' .
               $port->category . '/' .  $port->port . "-" . $port->version . '.tgz">Package</a>';
   }

   if ($port->homepage && ($ShowHomepageLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="' . $port->homepage . '">Homepage</a>';
   }

	$HTML .= ' <b>:</b> ';
	$HTML .= '<A HREF="' . $freshports_FTP_URL . $port->category . '/' . $port->port . '/">' . 'FTP</A>';

   $HTML .= "\n</DD>\n</DL>\n";

   return $HTML;
}

function freshports_navigation_bar_top() {
#GLOBAL $TableWidth;
#
#echo '<TABLE BGCOLOR="#663333" WIDTH="' . $TableWidth . '" CELLPADDING="3" CELLSPACING="0" BORDER="1">
#<TR>
#        <TD ALIGN="center"><P CLASS="yellow">[ <A CLASS="white" HREF="/">HOME</A> | <A CLASS="white" HREF="/topics.php">TOPICS</A> | <A CLASS="white" HREF="/chronological.php">INDEX</A> | <A CLASS="white" HREF="/help.php">WEB RESOURCES</A> | <A CLASS="white" HREF="/booksmags.php">BOOKS/MAGS</A> | <A CLASS="white" HREF="/contribute.php">CONTRIBUTE</A> | <A CLASS="white" HREF="/search.php">SEARCH</A> | <A CLASS="white" HREF="/feedback.php">FEEDBACK</A> | <A CLASS="white" HREF="/faq.php">FAQ</A> | <A CLASS="white" HREF="/phorum/">FORUMS</A> ]</P>
#</TD>
#</TR>
#</TABLE>
#';

}

function freshports_copyright() {
	return '<SMALL><A HREF="/legal.php" target="_top">Copyright</A> &copy; ' . COPYRIGHTYEARS . ' <A HREF="http://www.dvl-software.com/">DVL Software Limited</A>. All rights reserved.</SMALL>';
}

function FormatTime($Time, $Adjustment, $Format) {
#echo "$Time<BR>";
#echo time() . "<BR>";
	return date($Format, strtotime($Time) + $Adjustment);
}

#
# The code below was donated by Steve Kacsmark <stevek@guide.chi.il.us>.
# With modifications by Marcin Gryszkalis <mgryszkalis@cerint.pl>.
#


function freshports_IsEmailValid($email) {
	# see also convertMail
	if (eregi("^[a-z0-9\._+-]+@[a-z0-9\._-]+$", $email)) {
		return TRUE;
	} else {
		return FALSE;
	}
}



function pr2link($Arr) {
	return preg_replace("/((\w+\/)?\d+)/", 
					"<A HREF=\"http://www.FreeBSD.org/cgi/query-pr.cgi?pr=\\1\">\\1</A>",
					$Arr[0]);  
}

function mail2link($Arr) {
	#
	# in an attempt to reduce spam, encode the mailto
	# so the spambots get rubbish, but it works OK in
	# the browser.
	#

	$addr     = $Arr[0];
	$new_addr = "";

	for ($i=0; $i<strlen($addr); $i++) {
		$new_addr .= ("&#".ord($addr[$i]).";");
	}

	$addr = "<A HREF=\"" . MAILTO . ":$new_addr\">$new_addr</A>";

	return $addr;
}

function url2link($Arr) {
	#
	# URLs will be truncated if they are too long. But only
	# the visible part
	#
	$html = $Arr[1];

#	if (URL2LINK_CUTOFF_LEVEL > 0 && strlen($html) > URL2LINK_CUTOFF_LEVEL) {
#		$vhtml = substr($html, 0, URL2LINK_CUTOFF_LEVEL - 5) . "(...)";
#	} else {
		$vhtml = $html;
#	}

	$html  = preg_replace("/@/", "&#64", $html);
	$vhtml = preg_replace("/@/", "&#64", $vhtml);
	
	return "<A HREF=\"$html\">$vhtml</A>" . $Arr[3];
}

function url_shorten($Arr) {
	#
	# URLs will be truncated if they are too long. But only
	# the visible part
	#
	$html = $Arr[1];
#	syslog(LOG_NOTICE, "start");
#	syslog(LOG_NOTICE, "0 - $Arr[0]");
#	syslog(LOG_NOTICE, "1 - $Arr[1]");
#	syslog(LOG_NOTICE, "2 - $Arr[2]");
#	syslog(LOG_NOTICE, "3 - $Arr[3]");
#	syslog(LOG_NOTICE, "4 - $Arr[4]");
#	syslog(LOG_NOTICE, "5 - $Arr[5]");
#	syslog(LOG_NOTICE, "6 - $Arr[6]");
#	syslog(LOG_NOTICE, "finish");

	$URL = $Arr[5];
	if (URL2LINK_CUTOFF_LEVEL > 0 && strlen($URL) > URL2LINK_CUTOFF_LEVEL) {
		$URL = substr($URL, 0, URL2LINK_CUTOFF_LEVEL - 5) . "(...)";
	}

	return $Arr[1] . '">' . $URL . '</a>';
}

function htmlify($String) {
	$del_t = array("&quot;","&#34;","&gt;","&#62;","\/\.\s","\)","'","\s","$");
	$delimiters = "(".join("|",$del_t).")";

	$String = preg_replace_callback("/((http|ftp|https):\/\/.*?)($delimiters)/i",                    url2link,    $String);
	$String = preg_replace_callback("/(<a href=(\"|')(http|ftp|https):\/\/.*?)(\">|'>)(.*?)<\/a>/i", url_shorten, $String);
	$String = preg_replace_callback("/([\w+=\-.!]+@[\w\-]+(\.[\w\-]+)+)/",                           mail2link,   $String);
	$String = preg_replace_callback("/(\bPR[:\#]?)\s*(((\w+\/)?\d+)(,\s*((\w+\/)?\d+))*)/",          pr2link,     $String);
 
	return $String;
}


#
# The code above was donated by Steve Kacsmark <stevek@guide.chi.il.us>.
# With modifications by Marcin Gryszkalis <mgryszkalis@cerint.pl>.
#


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

function freshports_PortCommits($port) {
	# print all the commits for this port

	GLOBAL $User;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_log_ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');

#	echo ' *************** into freshports_PortCommits ***************';
	freshports_PortCommitsHeader($port);

	$Commits = new Commit_Log_Ports($port->dbh);
	$NumRows = $Commits->FetchInitialise($port->id);

#	echo "freshports_PortCommits \$NumRows='$NumRows'";

	$LastVersion = '';
	for ($i = 0; $i < $NumRows; $i++) {
		$Commits->FetchNthCommit($i);
		freshports_PortCommitPrint($Commits, $port->category, $port->port);
	}

	freshports_PortCommitsFooter($port);
}

function freshports_CommitFilesLink($MessageID, $Category, $Port) {

#	echo "freshports_CommitFilesLink gets $MesssageID, $Category, $Port<BR>";

	$HTML .= '<A HREF="/' . $Category . '/' . $Port . '/files.php?message_id=' . $MessageID . '">';
	$HTML .= freshports_Files_Icon();
	$HTML .= '</A>';

	return $HTML;
}

function freshports_PortCommitPrint($commit, $category, $port) {
	GLOBAL $DateFormatDefault;
	GLOBAL $TimeFormatDefault;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
	GLOBAL $User;

	# print a single commit for a port
	echo "<TR><TD VALIGN='top' NOWRAP>";
	

	echo $commit->commit_date . '<BR>';
	// indicate if this port needs refreshing from CVS
	if ($commit->{needs_refresh}) {
		echo " " . freshports_Refresh_Icon() . "\n";
	}
	echo freshports_Email_Link($commit->message_id);

	echo '&nbsp;&nbsp;'. freshports_Commit_Link($commit->message_id);

	if ($commit->encoding_losses == 't') {
		echo '&nbsp;'. freshports_Encoding_Errors();
	}

	echo ' ';

	echo freshports_CommitFilesLink($commit->message_id, $category, $port);
	if (IsSet($commit->security_notice_id)) {
		echo ' <a href="/security-notice.php?message_id=' . $commit->message_id . '">' . freshports_Security_Icon() . '</a>';
	}

	# ouput the VERSION and REVISION
	if (strlen($commit->{port_version}) > 0) {
    	echo '&nbsp;&nbsp;&nbsp;<BIG><B>' . $commit->{port_version};
		if (strlen($commit->{port_revision}) > 0 && $commit->{port_revision} != "0") {
    		echo '-' . $commit->{port_revision};
		}
		echo '</B></BIG>';
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

function freshports_Head($string, $n) {
	if (!is_int($n) || $n <= 0) {
		return $string;
	}
	$pos = -1;
	for ($i = 0; $i < $n ; $i++) {
		$pos = strpos($string, "\n", $pos+1);
		if ($pos === false) {
#			echo 'break';
			break;
		}
#		echo "$pos='$pos'<BR>";
	}
	if ($pos === false) {
		# not found
	} else {
		$string = substr($string, 0, $pos);
	}

	return $string;
}

function freshports_PortDescriptionPrint($description, $encoding_losses, $maxnumlines=0, $URL='') {
	$shortened = freshports_Head($description, $maxnumlines);
	$HTML .= '<PRE CLASS="code">';

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
		$retval    = true;
		$row       = pg_fetch_array($result,0);
		$NextValue = $row[0];
	} else {
		pg_errormessage() . ' sql = $sql';
	}

	return $NextValue;
}

function freshports_wrap($text, $length = 80) {
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
		$retval	= true;
		$row	= pg_fetch_array($result,0);
		$email	= $row[0];
		$token	= $row[1];
	} else {
		pg_errormessage() . ' sql = $sql';
	}

	if (IsSet($token)) {
		OpenLog("FreshPorts", LOG_PID, LOG_SYSLOG);
		SysLog(LOG_NOTICE, "User Creation: UID=$UserID, email=$email");
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

echo '<TABLE WIDTH="' . $TableWidth . '" BORDER="0" ALIGN="center">
<TR><TD>
';

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/footer.php');

echo '
</TD></TR>
</TABLE>
';
}

function freshports_SideBar() {

	GLOBAL $User;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/side-bars.php');

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

	$HTML .= '<table width="100%"cellpadding="4" cellspacing="0" border="0">' . "\n";

	$NumRows = $Announcement->NumRows();

	for ($i = 0; $i < $NumRows; $i++) {
		$Announcement->FetchNth($i);
		$HTML .= '<tr>' . "\n";
		$HTML .= '<td>' . $Announcement->TextGet()      . '</td>';
      $HTML .= '</tr>' . "\n";
	}
	$HTML .= '</table>' . "\n";

	return $HTML;
}



openlog('FreshPorts', LOG_PID | LOG_PERROR, LOG_LOCAL0);

?>
