<?

   # $Id: freshports.php,v 1.4.2.1 2001-11-25 20:50:59 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

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
$TableWidth             = "98%";
$DateFormatDefault      = "j F Y";

// path to the CVS repository
$freshports_CVS_URL = "http://www.FreeBSD.org/cgi/cvsweb.cgi";


// common things needs for all freshports php3 pages

function freshports_Start($ArticleTitle, $Description, $Keywords) {

GLOBAL $ShowAds;
GLOBAL $BannerAd;

   freshports_HTML_Start();
   freshports_Header($ArticleTitle, $Description, $Keywords);

   freshports_body();

   echo '<CENTER>
';

   if ($ShowAds) {
      freshports_BurstMediaCode();
      if ($BannerAd) {
         freshports_BurstMediaAd();
      }
   }

   echo '</CENTER>
';
   freshports_Logo();
   freshports_navigation_bar_top();
}

function freshports_Logo() {
GLOBAL $TableWidth;

echo '<BR>
<TABLE WIDTH="' . $TableWidth . '" CELLPADDING="0" CELLSPACING="0" BORDER="0">
<TR>
        <TD><A HREF="/"><IMG SRC="/images/freshports.jpg" ALT="FreshPorts.org - the place for ports" WIDTH="512" HEIGHT="110" BORDER="0"></A></TD>
        <TD ALIGN="right" CLASS="sans" VALIGN="bottom"><small>' . date("D, j M Y g:i A T") . '</small></TD>
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

function freshports_Header($ArticleTitle, $Description, $Keywords) {

	echo "<HEAD>
	<TITLE>FreshPorts";

	if ($ArticleTitle) {
		echo " -- $ArticleTitle";
	}

	echo "</TITLE>
";

//	freshports_style();

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

echo '	<meta name="MSSmartTagsPreventParsing" content="TRUE">
</HEAD>
';

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

   $sql = "select watch.id ".
          "from watch ".
          "where watch.owner_user_id = $UserID ".
          "and   watch.system        = 'FreeBSD' ".
          "and   watch.name          = 'main'";


   $result = mysql_query($sql, $db);

//   echo "freshports_MainWatchID sql = $sql<br>\n";

   if(mysql_numrows($result)) {
//      echo "results were found for that<br>\n";
      $myrow = mysql_fetch_array($result);
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

	echo "freshports_PortIDFromPortCategory SQL = $sql<BR>\n";
	$result = pg_exec($db, $sql);
	if (pg_numrows($result)) {
		$myrow = pg_fetch_array($result, 0);
		$PortID = $myrow["id"];
		echo 'freshports_PortIDFromPortCategory = ' . $PortID . "<BR>\n";
	}

	return $PortID;
}

function freshports_CategoryIDFromCategory($category, $db) {
   $sql = "select categories.id from categories where categories.name = '$category'";

   $result = mysql_query($sql, $db);
   if(mysql_numrows($result)) {
      $myrow = mysql_fetch_array($result);
      $CategoryID = $myrow["id"];
   }
   
   return $CategoryID;
}

function freshports_SideBarHTML($Self, $URL, $Title) {
//  echo "PHP_SELF = $Self and URL=$URL";
   if ($Self == $URL || ($Self == '/index.php3' && $URL == '/')) {
      $HTML = $Title;
   } else {
      $HTML = '<a href="' . $URL . '">' . $Title . '</a>';
   }

   return $HTML;
}

function freshports_SideBarHTMLParm($Self, $URL, $Parm, $Title) {
//   echo "PHP_SELF = $Self and URL=$URL";
   if ($Self == $URL || ($Self == '/index.php3' && $URL == '/')) {
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


function freshports_PortDetails($myrow, $ShowDeletedDate, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription) {
//
// This php3 fragment does the basic port information for a single port.
// I'd show you what code is expected in $myrow, but I can't be bothered
// right now
//
   GLOBAL $freshports_CVS_URL;

   $MarkedAsNew = "N";
   $HTML .= "<DL>\n";

   $HTML .= "<DT><b>" . $myrow["port"];
   if (strlen($myrow["version"]) > 0) {
      $HTML .= ' ' . $myrow["version"];
   }

   $HTML .= "</b>";

   // indicate if this port needs refreshing from CVS
   if ($myrow["status"] == "D") {
      $HTML .= ' <font size="-1">[deleted - port removed from ports tree]';
      if ($ShowDeletedDate == "Y") {
         $HTML .= ' on ' . $myrow["updated"];
      }
      $HTML .= '</font>';
   }
   if ($myrow["needs_refresh"]) {
      $HTML .= ' <font size="-1">[refresh]</font>';
   }

   if (!$HideCategory) {
      $URL_Category = "category.php3?category=" . $myrow["category_id"];
      $HTML .= ' <font size="-1"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></font>';
   }

//   $HTML .= $myrow["date_created_formatted"] ."::". $myrow["updated"] ."--". $DaysMarkedAsNew;
   if ($myrow["date_created"] > Time() - 3600 * 24 * $DaysMarkedAsNew) {
      $MarkedAsNew = "Y";
      $HTML .= " <img src=\"/images/new.gif\" width=28 height=11 alt=\"new!\" hspace=2 align=absmiddle>";
   }

   if ($MarkedAsNew == "Y" || $ShowPortCreationDate) {
      if ($myrow["date_created_formatted"] != $myrow["updated"] || !($ShowLastChange == "Y" || $ShowEverything) || $ShowPortCreationDate) {
         $HTML .= ' <font size="-1">(' . $myrow["date_created_formatted"] . ")</font>";
      }
   }

   $HTML .= "</DT>\n<DD>";
   # show forbidden and broken
   if ($myrow["forbidden"]) {
      $HTML .= '<img src="images/forbidden.gif" alt="Forbidden" width="20" height="20" hspace="2">' . $myrow["forbidden"] . "<br><br>";

   }
   if ($myrow["broken"]) {
      $HTML .= '<img src="images/broken.gif" alt="Broken" width="17" height="16" hspace="2">' . $myrow["broken"] . "<br><br>"; ;
   }

   // description
   if ($myrow["short_description"] && ($ShowShortDescription == "Y" || $ShowEverything)) {
      $HTML .= $myrow["short_description"];
      $HTML .= "<br>\n";
   }

   // maintainer
   if ($myrow["maintainer"] && ($ShowMaintainedBy == "Y" || $ShowEverything)) {
      $HTML .= '<i>';
      if ($myrow["status"] == 'A') {
         $HTML .= 'Maintained';
      } else {
         $HTML .= 'was maintained'; 
      }
      $HTML .= ' by:</i> <a href="mailto:' . $myrow["maintainer"];
      $HTML .= '?cc=ports@FreeBSD.org&amp;subject=FreeBSD%20Port:%20' . $myrow["port"] . "-" . $myrow["version"] . '">';
      $HTML .= $myrow["maintainer"] . '</a></br>' . "\n";
  }

   // there are only a few places we want to show the last change.
   // such places set $GlobalHideLastChange == "Y"
   if ($GlobalHideLastChange != "Y") {
      if ($ShowLastChange == "Y" || $ShowEverything) {
         if ($myrow["last_change_log_id"] != 0) {
            $HTML .= 'last change committed by ' . $myrow["committer"];  // separate lines in case committer is null
 
            $HTML .= ' on <font size="-1">' . $myrow["updated"] . '</font><br>' . "\n";
 
            $HTML .= $myrow["update_description"] . "<br>" . "\n";
         } else {
            $HTML .= "no changes recorded in FreshPorts<br>\n";
         }
      }
   }

   if ($myrow["categories"]) {
      // remove the primary category
      $Categories = str_replace($myrow["category"], '', $myrow["categories"]);
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
               $HTML .= '<a href="category.php3?category=' . $CategoryID . '">' . $Category . '</a>';
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
echo 'build = ' . $myrow["depends_build"] . "<br>\n";
echo 'run   = ' . $myrow["depends_run"] . "<br>\n";
*/

if ($ShowDepends) {
   if ($myrow["depends_build"]) {
      $HTML .= "<i>required to build:</i> ";

      // sometimes they have multiple spaces in the data...
      $temp = str_replace('  ', ' ', $myrow["depends_build"]);
      
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

          $HTML .= '<a href="port-description.php3?port=' . $DependsPortID . '">' . $CategoryPortArray[1]. '</a>';
          if ($i < $Count - 1) {
             $HTML .= ", ";
          }
      }
      $HTML .= "<br>\n";
   }

   if ($myrow["depends_run"]) {
      $HTML .= "<i>required to run:</i> ";
      // sometimes they have multiple spaces in the data...
      $temp = str_replace('  ', ' ', $myrow["depends_run"]);

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

          $HTML .= '<a href="port-description.php3?port=' . $DependsPortID . '">' . $CategoryPortArray[1]. '</a>';
          if ($i < $Count - 1) {
             $HTML .= ", ";
          }
      }
      $HTML .= "<br>\n";
   }

}

   if (!$HideDescription && ($ShowDescriptionLink == "Y" || $ShowEverything)) {
      // Long descripion
      $HTML .= '<a HREF="port-description.php3?port=' . $myrow["id"] .'">Description</a>';

      $HTML .= ' <b>:</b> ';
   }

   if ($ShowChangesLink == "Y" || $ShowEverything) {
      // changes
      $HTML .= '<a HREF="' . $freshports_CVS_URL .
               $myrow["category"] . '/' .  $myrow["port"] . '">Changes</a>';
   }

   // download
   if ($myrow["status"] == "A" && ($ShowDownloadPortLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="ftp://ftp5.FreeBSD.org/pub/FreeBSD/branches/-current/ports/' .
               $myrow["category"] . '/' .  $myrow["port"] . '">Download Port</a>';
   }

   if ($myrow["package_exists"] == "Y" && ($ShowPackageLink == "Y" || $ShowEverything)) {
      // package
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="ftp://ftp5.FreeBSD.org/pub/FreeBSD/FreeBSD-stable/packages/' .
               $myrow["category"] . '/' .  $myrow["port"] . "-" . $myrow["version"] . '.tgz">Package</a>';
   }

   if ($myrow["homepage"] && ($ShowHomepageLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="' . $myrow["homepage"] . '">Homepage</a>';
   }

   $HTML .= "</DD></DL>\n";

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


?>
