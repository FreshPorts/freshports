<?

   # $Id: freshports.php,v 1.4.2.56 2002-04-02 04:45:24 dan Exp $
   #
   # Copyright (c) 1998-2002 DVL Software Limited

GLOBAL $DOCUMENT_ROOT;


#
# colours for the banners (not really banners, but headings)
#
if ($Debug) echo "'" . $DOCUMENT_ROOT . "/../classes/watchnotice.php'<BR>";

require_once($DOCUMENT_ROOT . "/../classes/watchnotice.php");

$BannerBackgroundColour = "#FFCC33";
$BannerTextColour       = "#000000";
$BannerCellSpacing      = "0";
$BannerCellPadding      = "2";
$BannerBorder           = "1";
$BannerFontSize         = "+1";

$BannerWidth            = "100%";
$TableWidth             = "98%";
$DateFormatDefault      = "j M Y";
$TimeFormatDefault		= "H:i:s";

$FreshPortsTitle		= "FreshPorts";

$WatchNoticeFrequencyDaily			= "D";
$WatchNoticeFrequencyWeekly			= "W";
$WatchNoticeFrequencyFortnightly	= "F";
$WatchNoticeFrequencyMonthly		= "M";
$WatchNoticeFrequencyNever			= "Z";

$UserStatusActive	   = "A";
$UserStatusDisabled    = "D";
$UserStatusUnconfirmed = "U";

$ProblemSolverEmailAddress	= "webmaster@freshports.org";

#
# These values are used when specifying add/remove on a port
#
$FreshPortsWatchedPortPrefix	= "<SMALL><A HREF=\"/watch-list.php?remove=";
$FreshPortsWatchedPortSuffix	= "\">Remove</A></SMALL>";
$FreshPortsWatchedPortNotPrefix	= "<SMALL><A HREF=\"/watch-list.php?add=";
$FreshPortsWatchedPortNotSuffix	= "\">Add</A></SMALL>";

#
# These are similar to the above but are using in SQL queries
#
#$FreshPortsWatchedPort		= "<SMALL><A HREF=\"/watch-list.php?remove=' || commits_latest.element_id || '\">Remove</A></SMALL>";
#$FreshPortsWatchedPortNot	= "<SMALL><A HREF=\"/watch-list.php?add='    || commits_latest.element_id || '\">Add</A></SMALL>";




#
# SEQUENCES
#

$Sequence_Watch_List_ID	= 'watch_list_id_seq';
$Sequence_User_ID		= 'users_id_seq';

// path to the CVS repository
$freshports_CVS_URL = "http://www.FreeBSD.org/cgi/cvsweb.cgi";

// path to the ftp server
$freshports_FTP_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports/";

// path to the cvs-all mailing list archive
$freshports_mail_archive = " http://www.freebsd.org/cgi/mid.cgi?db=mid&id=";

function freshports_Email_Link($message_id) {
	#
	# produce a link to the email
	#
	GLOBAL $freshports_mail_archive;

	$HTML .= '<A HREF="' . $freshports_mail_archive . $message_id . '">';
	$HTML .= '<IMG SRC="/images/envelope10.gif" ALT="Original commit message" BORDER="0" WIDTH="25" HEIGHT="14">';
	$HMTL .= '</A>';

	return $HTML;
}

function freshports_CookieClear() {
#	echo " clearing the cookie";
	SetCookie("visitor", '', 0, '/');
}


// common things needs for all freshports php3 pages

function freshports_Start($ArticleTitle, $Description, $Keywords, $Phorum=0) {

GLOBAL $ShowAds;
GLOBAL $BannerAd;

   freshports_HTML_Start();
   freshports_Header($ArticleTitle, $Description, $Keywords, $Phorum);

   freshports_body();

   echo '<CENTER>
';

   if ($ShowAds) {
      freshports_BurstMediaCode();
      if ($BannerAd) {
		echo '<CENTER>';
		freshports_BurstMediaAd();
		echo '</CENTER>';
      }
   }

   echo '</CENTER>
';
   freshports_Logo();
   freshports_navigation_bar_top();
}

function freshports_Logo() {
GLOBAL $TableWidth;
GLOBAL $LocalTimeAdjustment;
GLOBAL $PHP_SELF;

#echo "$LocalTimeAdjustment<BR>";

echo '<BR>
<TABLE WIDTH="' . $TableWidth . '" BORDER="0" ALIGN="center">
<TR>
	<TD><A HREF="';

	if ($PHP_SELF == "/index.php") {
		echo 'other-copyrights.php';
	} else {
		echo '/';
	}
        echo '"><IMG SRC="/images/freshports.jpg" ALT="FreshPorts.org - the place for ports" WIDTH="512" HEIGHT="110" BORDER="0"></A></TD>
        <TD ALIGN="right" CLASS="sans" VALIGN="bottom">' . FormatTime(Date("D, j M Y g:i A T"), $LocalTimeAdjustment, "D, j M Y g:i A T") . '</TD>
</TR>
</TABLE>
';


}


function freshports_HTML_start() {
GLOBAL $Debug;

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<HTML>
';
}

function freshports_Header($ArticleTitle, $Description, $Keywords, $Phorum=0) {

	echo "<HEAD>
	<TITLE>FreshPorts";

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


if ($Phorum) {
	GLOBAL $phorumver;
	GLOBAL $DB;
	GLOBAL $ForumName;

?>
	<meta name="Phorum Version" content="<?php echo $phorumver; ?>" />
	<meta name="Phorum DB" content="<?php echo $DB->type; ?>" />
	<meta name="PHP Version" content="<?php echo phpversion(); ?>" />
<?
}

	echo "</HEAD>\n";

}

function freshports_style($Phorum=0) {

	echo "\n        <STYLE>\n";

if (2==2) {
?>
BODY, TD, TR, P, UL, OL, LI, INPUT, SELECT, DL, DD, DT, FONT
{
    font-family: Verdana, Arial, Clean, Helvetica, sans-serif;
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

	if ($Phorum) {
		?>
		<link rel="STYLESHEET" type="text/css" href="<?php echo phorum_get_file_name("css"); ?>" />
		<?
	}
}

function freshports_BurstMediaCode() {
#
# This is required on all pages which contain Burst Ads.  It's the base code.
#

echo '
<!-- BEGIN RICH-MEDIA BURST! CODE -->
<script language="JavaScript">
<!-- /* © 1997-2001 BURST! Media, LLC. All Rights Reserved.*/
function ShowBurstAd(adcode, width, height) {
 var bN = navigator.appName;
 var bV = parseInt(navigator.appVersion);
 var base=\'http://www.burstnet.com/\';
 var Tv=\'\';
 var agt=navigator.userAgent.toLowerCase();
 if (bV>=4)
 {ts=window.location.pathname+window.location.search;
  i=0; Tv=0; while (i< ts.length)
    { Tv=Tv+ts.charCodeAt(i); i=i+1; } Tv="/"+Tv;}
  else   {Tv=escape(window.location.pathname);
  if( Tv.charAt(0)!=\'/\' ) Tv="/"+Tv;
    else if (Tv.charAt(1)=="/")
 Tv="";
 if( Tv.charAt(Tv.length-1) == "/")
   Tv = Tv + "_";}
 if (bN==\'Netscape\'){
  if ((bV>=4)&&(agt.indexOf("mac")==-1))
 { document.write(\'<s\'+\'cript src="\'+
  base+\'cgi-bin/ads/\'+adcode+\'.cgi/RETURN-CODE/JS\'
  +Tv+\'">\');
  document.write(\'</\'+\'script>\');
 }
   else if (bV>=3) {document.write(\'<\'+\'a href="\'+base+\'ads/\' +
  adcode + \'-map.cgi\'+Tv+\'"target=_top>\');
  document.write(\'<img src="\' + base + \'cgi-bin/ads/\' +
  adcode + \'.cgi\' + Tv + \'" width="\' + width + \'" height="\' + height + \'"\' +
  \' border="0" alt="Click Here"></a>\');}
}
if (bN==\'Microsoft Internet Explorer\')
document.write(\'<ifr\'+\'ame id="BURST" src="\'+base+\'cgi-bin/ads/\'
+
adcode + \'.cgi\' + Tv + \'/RETURN-CODE" width="\' + width + \'" height="\' + height + \'"\' +
\'marginwidth="0" marginheight="0" hspace="0" vspace="0" \' +
\'frameborder="0" scrolling="no"></ifr\'+\'ame>\');
}
//-->
</script>
<!-- END BURST CODE -->
';
}

function freshports_BurstMediaAd() {
#
# This goes at the top of each article and show the ad, the graphic, and the links
#
GLOBAL $AddressForAds;

echo '
        <!-- BEGIN RICH-MEDIA BURST! CODE -->
        <script language="JavaScript">
        <!--
        ShowBurstAd(\'ad4556a\',\'468\',\'60\');
        // --></script>
        <noscript><a href="http://www.burstnet.com/ads/ad4556a-
        map.cgi/ns" target="_top"><img src="http://www.burstnet.com/cgi-
        bin/ads/ad4556a.cgi/ns" <width="468" height="60"
        border="0" alt="Click Here"></a>
        </noscript>
        <!-- END BURST CODE -->
';

#<BR>
#
#<small>Your ad here.&nbsp; Please <a href="mailto:" . $AddressForAds . "?subject=your ad here">contact us</A> for deta

}


function freshports_body() {

GLOBAL $OnLoad;
GLOBAL $Debug;

echo '
<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0"';

# should we have an onload?
if ($OnLoad) {
	echo ' onLoad="' . $OnLoad . '"';
}

echo '>';
#<CENTER>
#';


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


function freshports_MainWatchID($UserID, $db) {

	$sql = "select watch_list.id ".
	       "  from watch_list ".
	       " where watch_list.user_id = $UserID ".
	       "   and watch_list.name    = 'main'";

	$result = pg_exec ($db, $sql);

//	echo "freshports_MainWatchID sql = $sql<br>\n";

	if(pg_numrows($result)) {
//		echo "results were found for that<br>\n";
		$myrow = pg_fetch_array ($result, 0);
		$WatchID = $myrow["id"];
	}

	return $WatchID;
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
/*
	echo "category = $category<br>\n";
	echo "port     = $port<br>\n";
*/
	$sql = "select pathname_id('ports/$category/$port') as id";

#	echo "freshports_PortIDFromPortCategory SQL = $sql<BR>\n";
	$result = pg_exec($db, $sql);
	if (pg_numrows($result)) {
		$myrow = pg_fetch_array($result, 0);
		$PortID = $myrow["id"];
#		echo 'freshports_PortIDFromPortCategory = ' . $PortID . "<BR>\n";
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
//  echo "PHP_SELF = $Self and URL=$URL";
   if ($Self == $URL || ($Self == '/index.php' && $URL == '/')) {
      $HTML = $Title;
   } else {
      $HTML = '<a href="' . $URL . '">' . $Title . '</a>';
   }

   return $HTML;
}

function freshports_SideBarHTMLParm($Self, $URL, $Parm, $Title) {
//   echo "PHP_SELF = $Self and URL=$URL";
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


function freshports_PortDetails($port, $db, $ShowDeletedDate, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, $LinkToPort=0, $AddRemoveExtra='', $ShowCategory=1) {
//
// This php3 fragment does the basic port information for a single port.
//
	GLOBAL $freshports_CVS_URL;
	GLOBAL $freshports_FTP_URL;
	GLOBAL $ShowDepends;
	GLOBAL $FreshPortsWatchedPortPrefix;
	GLOBAL $FreshPortsWatchedPortSuffix;
	GLOBAL $FreshPortsWatchedPortNotPrefix;
	GLOBAL $FreshPortsWatchedPortNotSuffix;
	GLOBAL $WatchListID;

	$MarkedAsNew = "N";
	$HTML .= "<DL>\n";

	$HTML .= "<DT>";

	$HTML .= '<BIG><B>';

	if ($ShowCategory) {
		$HTML .= '<A HREF="/' . $port->category . '/">' . $port->category . '</A>/';
	}

	if ($LinkToPort) {
		$HTML .= "<A HREF=\"/$port->category/$port->port/\">$port->port</A>";
	} else {
		$HTML .= $port->port;
	}

	if (strlen($port->{version}) > 0) {
    	$HTML .= ' ' . $port->{version};
		if (strlen($port->{revision}) > 0 && $port->{revision} != "0") {
    		$HTML .= '-' . $port->{revision};
		}
	}

	$HTML .= "</B></BIG>";

	// indicate if this port needs refreshing from CVS
	if ($port->status == "D") {
		$HTML .= ' <font size="-1">[deleted - port removed from ports tree]';
		if ($ShowDeletedDate == "Y") {
			$HTML .= ' on ' . $port->updated;
		}
		$HTML .= '</font>';
	}

   if ($port->needs_refresh) {
      $HTML .= ' <font size="-1">[refresh]</font>';
   }

   if ($port->date_added > Time() - 3600 * 24 * $DaysMarkedAsNew) {
      $MarkedAsNew = "Y";
      $HTML .= " <img src=\"/images/new.gif\" width=28 height=11 alt=\"new!\" hspace=2 align=absmiddle>";
   }

#   if ($MarkedAsNew == "Y" || $ShowPortCreationDate) {
#      if ($port->date_added != $port->updated || !($ShowLastChange == "Y" || $ShowEverything) || $ShowPortCreationDate) {
#         $HTML .= ' <font size="-1">(' . date("j M Y H:i", $port->date_added) . ")</font>";
#      }
#   }

#	$HTML .= "onwatchlist = '" . $port->{onwatchlist} . "'";

	if ($WatchListID) {
		if ($port->{onwatchlist}) {
			$HTML .= ' ' . $FreshPortsWatchedPortPrefix    . $port->{element_id} . $AddRemoveExtra . $FreshPortsWatchedPortSuffix;
		} else {
			$HTML .= ' ' . $FreshPortsWatchedPortNotPrefix . $port->{element_id} . $AddRemoveExtra . $FreshPortsWatchedPortNotSuffix;
		}
	}

   $HTML .= "</DT>\n<DD>";
   # show forbidden and broken
   if ($port->forbidden) {
      $HTML .= '<img src="/images/forbidden.gif" alt="Forbidden" width="20" height="20" hspace="2"> FORBIDDEN: ' . $port->forbidden . "<br>";

   }
   if ($port->broken) {
      $HTML .= '<img src="/images/broken.gif" alt="Broken" width="17" height="16" hspace="2"> BROKEN: ' . $port->broken . "<br>"; ;
   }



   // description
   if ($port->short_description && ($ShowShortDescription == "Y" || $ShowEverything)) {
      $HTML .= $port->short_description;
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
      $HTML .= ' by:</i> <A HREF="mailto:' . $port->maintainer;
      $HTML .= htmlspecialchars('?cc=ports@FreeBSD.org?subject=FreeBSD%20Port:%20' . $port->port . '-' . $port->version) . '">';
      $HTML .= $port->maintainer . "</A><BR>";
  }

   // there are only a few places we want to show the last change.
   // such places set $GlobalHideLastChange == "Y"
   if ($GlobalHideLastChange != "Y") {
      if ($ShowLastChange == "Y" || $ShowEverything) {
         if ($port->updated != 0) {
            $HTML .= 'last change committed by ' . $port->committer;  // separate lines in case committer is null
 
            $HTML .= ' on <font size="-1">' . $port->updated . '</font>' . "\n";

			$HTML .= freshports_PortDescriptionPrint($port->update_description);
 
         } else {
            $HTML .= "no changes recorded in FreshPorts<br>\n";
         }
      }
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
          $CategoryPort = str_replace('/usr/ports/', '', $DependsArray[1]) ;
          $CategoryPortArray = explode('/', $CategoryPort);
          $DependsPortID = freshports_PortIDFromPortCategory($CategoryPortArray[0], $CategoryPortArray[1], $db);

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
          $CategoryPort = str_replace('/usr/ports/', '', $DependsArray[1]) ;
          $CategoryPortArray = explode('/', $CategoryPort);
          $DependsPortID = freshports_PortIDFromPortCategory($CategoryPortArray[0], $CategoryPortArray[1], $db);

          $HTML .= '<A HREF="/' . $CategoryPortArray[0] . '/' . $CategoryPortArray[1] . '/">' . $CategoryPortArray[0] . '/' . $CategoryPortArray[1]. '</a>';
          if ($i < $Count - 1) {
             $HTML .= ", ";
          }
      }
      $HTML .= "<BR>\n";
   }

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

#GLOBAL $TableWidth;
#
#echo '<TABLE WIDTH="' . $TableWidth . '" CELLPADDING="3" CELLSPACING="0">
#<TR>
#<TD ALIGN="right"><SMALL><A HREF="/legal.php">&copy;</A> 1997 - 2001 <A HREF="http://www.dvl-software.com/">DVL Software Ltd.</A><BR>All rights reserved.<SMALL></TD>
#</TR>
#</TABLE>
#';

}

function diary_ads_Random() {

echo '  <P ALIGN="center">
        <a href="http://magazine.daemonnews.org/" target="_top"><img src="/ads/daemonnews.gif" width="468" height="60" border="0" alt="Daemon News - Bringing BSD Together"></a>
        </noscript>
        <!-- END BURST CODE -->
        </P>
';

}


function FormatTime($Time, $Adjustment, $Format) {
#echo "$Time<BR>";
#echo time() . "<BR>";
	return date($Format, strtotime($Time) + $Adjustment);
}




#
# The following code was obtained from http://www.zend.com/codex.php?id=199&single=1
# 
#

function convertURLS($text) {
	$text = eregi_replace("((ht|f)tp://www\.|www\.)([a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})((/|\?)[a-z0-9~#%&\\/'_\+=:\?\.-]*)*)", "http://www.\\3", $text);
	$text = eregi_replace("((ht|f)tp://)((([a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}))|(([0-9]{1,3}\.){3}([0-9]{1,3})))((/|\?)[a-z0-9~#%&'_\+=:\?\.-]*)*)", "<a href=\"\\0\">\\0</a>", $text);
	return $text;
}

function convertMail($text) {
	$text = eregi_replace("([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}))", "<a href='mailto:\\0'>\\0</a>", $text);
	return $text;
}

function convertAllLinks($text) {
	$text = convertURLS($text);
	$text= convertMail($text);
	return $text;
}

#
#
# The above code was obtained from http://www.zend.com/codex.php?id=199&single=1
#

function freshports_PortCommitsHeader($port) {
	# print the header for the commits for a port

	echo '<TABLE BORDER="1" width="100%" CELLSPACING="0" CELLPADDING="5"bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">' . "\n";

	freshports_PageBannerText("Commit History - (may be incomplete: see CVSWeb link above for full details)", 3);

	echo "<TR><TD WIDTH=\"180\"><b>Date</b></td><td><b>Committer</b></td><td><b>Description</b></td></tr>\n";
}

function freshports_PortCommits($port) {
	# print all the commits for this port
	GLOBAL $DOCUMENT_ROOT;

	require($DOCUMENT_ROOT . "/../classes/commit_log_ports.php");

#	echo ' *************** into freshports_PortCommits ***************';
	freshports_PortCommitsHeader($port);

	$Commits = new Commit_Log_Ports($port->dbh);
	$NumRows = $Commits->FetchInitialise($port->id);

#	echo "freshports_PortCommits \$NumRows='$NumRows'";

	$i = 0;
	for ($i = 0; $i < $NumRows; $i++) {
		$Commits->FetchNthCommit($i);
		freshports_PortCommitPrint($Commits, $port->category, $port->port);
	}

	freshports_PortCommitsFooter($port);
}

function freshports_CommitFilesLink($CommitID, $Category, $Port) {

#	echo "freshports_CommitFilesLink gets $CommitID, $Category, $Port<BR>";

	$HTML .= '<A HREF="/' . $Category . '/' . $Port . '/files.php?' . $CommitID . '">';
	$HTML .= '<IMG SRC="/images/logs.gif" ALT="Files within this port affected by this commit" ';
	$HTML .= 'BORDER="0" WIDTH="17" HEIGHT="20" HSPACE="2"></A>';

	return $HTML;
}

function freshports_PortCommitPrint($commit, $category, $port) {
	GLOBAL  $DateFormatDefault;
	GLOBAL  $TimeFormatDefault;

	# print a single commit for a port
	echo "<TR><TD VALIGN='top'>" . $commit->commit_date . '<BR>' . freshports_Email_Link($commit->message_id);
	echo "</TD>\n";
	echo '    <TD VALIGN="top">';
    echo $commit->committer;
	echo '<BR>';

	$CommitID = $commit->id;
	echo freshports_CommitFilesLink($CommitID, $category, $port);
	echo "</TD>\n";
	echo '    <TD VALIGN="top" WIDTH="*">';


	echo freshports_PortDescriptionPrint($commit->description);

	echo "</TD></TR>\n";
}

function freshports_PortCommitsFooter($port) {
	# print the footer for the commits for a port
	echo "</TABLE>\n";
}

function freshports_PortDescriptionPrint($description) {
	$HTML .= '<PRE CLASS="code">';

	$HTML .= convertAllLinks(htmlspecialchars(freshports_wrap($description)));

	$HTML .= '</PRE>';

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
	echo '<TD BGCOLOR="#AD0040" HEIGHT="29" COLSPAN="' . $ColSpan . ' "><FONT COLOR="#FFFFFF"><BIG><BIG>' . $Text . '</BIG></BIG></FONT></TD>' . "\n";
}

function freshports_IsEmailValid($email) {
	if (eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,3}$", $email)) {
		return TRUE;
	} else {
		return FALSE;
	}
}


function freshports_UserSendToken($UserID, $dbh) {
	#
	# send the confirmation token to the user
	#

	GLOBAL	$REMOTE_ADDR;
	GLOBAL	$REMOTE_PORT;
	GLOBAL	$HTTP_HOST;

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
				    "a nuisance, please forward this message to webmaster@$HTTP_HOST\n".
					"and we will take care of it for you.\n".
                    " \n".
	                "Your token is: $token\n".
    	            "\n".
        	        "Please point your browser at\n".
					"http://$HTTP_HOST/confirmation.php?token=$token\n" .
	                "\n".
    	            "the request came from $REMOTE_ADDR:$REMOTE_PORT\n".
					"\n".
					"-- \n".
					"FreshPorts - http://$HTTP_HOST/ - the place for ports";

		$result = mail($email, "FreshPorts - user registration", $message,
					"From: webmaster@$HTTP_HOST\nReply-To: webmaster@$HTTP_HOST\nX-Mailer: PHP/" . phpversion());
	} else {
		$result = 0;
	}

	return $result;
}

?>
