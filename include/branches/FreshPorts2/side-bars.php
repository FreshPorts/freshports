<?php
	#
	# $Id: side-bars.php,v 1.4.2.56 2004-12-01 22:56:06 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	$ColumnWidth = 155;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/burstmedia.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/searches.php');

?>


  <TABLE WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5">
        <TR>
         <TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Login</B></BIG></FONT></TD>
        </TR>
        <TR>

         <TD NOWRAP><?php
   switch (basename($_SERVER["PHP_SELF"])) {
//      case "watch.php":
//      case "watch-categories.php":
//      case "customize.php":
//      case "port-watch.php":
//         $OriginLocal = '/';
//         break;

      default:
         $OriginLocal = rawurlencode($_SERVER["REQUEST_URI"]);
         break;
   }

if (IsSet($_COOKIE["visitor"])) {
	$visitor = $_COOKIE["visitor"];
}

//echo "OriginLocal = '$OriginLocal'<BR>\n";
if (IsSet($visitor)) {
	GLOBAL $User;
   echo '<FONT SIZE="-1">Logged in as ', $User->name, "</FONT><BR>";

   if ($User->emailbouncecount > 0) {
      echo '<IMG SRC="/images/warning.gif"><IMG SRC="/images/warning.gif"><IMG SRC="/images/warning.gif"><BR>';
      echo '<FONT SIZE="-1">your email is <A HREF="bouncing.php?origin=' . $OriginLocal. '">bouncing</A></FONT><BR>';
      echo '<IMG SRC="/images/warning.gif"><IMG SRC="/images/warning.gif"><IMG SRC="/images/warning.gif"><BR>';
   }
   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/customize.php',        "?origin=$OriginLocal", "Customize"              ) . '</FONT><BR>';

   if (eregi(".*@FreeBSD.org", $User->email)) {
      echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/committer-opt-in.php', '', "Committer Opt-in"       ) . '</FONT><BR>';
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
   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/logout.php',                 $args,                  "Logout"                  ) . '</FONT><BR>';


   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/pkg_upload.php',             '',                     "Watch list - upload"     ) . '</FONT><BR>';
   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch-categories.php',       '',                     "Watch list - Categories" ) . '</FONT><BR>';
   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch-list-maintenance.php', '',                     "Watch lists - Maintain"  ) . '</FONT><BR>';
   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/watch.php',                  '',                     "Your watched ports"      ) . '</FONT><BR>';
   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/report-subscriptions.php',   '',                     "Report Subscriptions"    ) . '</FONT><BR>';
  } else {
   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/login.php',                  "?origin=$OriginLocal", "User Login"              ) . '</FONT><BR>';
   echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/new-user.php',               "?origin=$OriginLocal", "Create account"          ) . '</FONT><BR>';
  }
?>
	<A HREF="/phorum/">Forums</A>
   </TD>
   </TR>
   </TABLE>

<P>

<SMALL>Server and bandwidth provided by <A HREF="http://www.bchosting.com/" TARGET="_new">BChosting.com</A></SMALL>

</P>

<TABLE WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Search</B></BIG></FONT></TD>
	</TR>
	<TR>

	<TD>
<?php
	GLOBAL $dbh;

	$Searches = new Searches($dbh);
	echo $Searches->GetFormSimple('&nbsp;');
?>
	<? echo '<FONT SIZE="-1">' . freshports_SideBarHTMLParm($_SERVER["PHP_SELF"], '/search.php', '', "more...") . '</FONT><BR>'; ?>
	</TD>
</TR>
</TABLE>

<BR>

<?
	GLOBAL $ShowAds;

	if ($ShowAds) {
		echo '<TABLE BORDER="0" CELLPADDING="5">
		  <TR><TD ALIGN="center">
		 ';

		BurstSkyscraperAd();
		echo '</TD></TR>
		  </TABLE>
		 ';
	}
?>


<BR>

<TABLE WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD COLSPAN="2" BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Statistics</B></BIG></FONT></TD>
	</TR>
	<TR>
	<TD VALIGN="top">

<?php
	echo freshports_SideBarHTML($_SERVER["PHP_SELF"], "/graphs.php",        "Graphs")      . '<BR>';
	echo freshports_SideBarHTML($_SERVER["PHP_SELF"], "/stats/",            "Traffic")     . '<BR>';

	@readfile($_SERVER["DOCUMENT_ROOT"] . "/../dynamic/stats.html");
?>
	</TD>
	</TR>
</TABLE>


<BR>

<TABLE WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Ports</B></BIG></FONT></TD>
	</TR>
	<TR>
	<TD VALIGN="top">
<?
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/",                  "Home")            . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/categories.php",    "Categories")      . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/ports-deleted.php", "Deleted ports")   . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/ports-broken.php",  "Broken ports")   . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/ports-new.php",     "New ports")       . '</FONT><BR>';
?>
	</TD>
	</TR>
</TABLE>



<BR>

<TABLE WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>This site</B></BIG></FONT></TD>
	</TR>
	<TR>
	<TD VALIGN="top">
<?
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/about.php",              "What is FreshPorts?") . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/authors.php",            "About the Authors")   . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/faq.php",                "FAQ")                 . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/how-big-is-it.php",      "How big is it?")      . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/release-2004-10.php", "The latest upgrade!") . '</FONT><BR>';
	echo '<FONT SIZE="-1">' . freshports_SideBarHTML($_SERVER["PHP_SELF"], "/privacy.php",            "Privacy")             . '</FONT><BR>';
?>
	</TD>
	</TR>
</TABLE>


<BR>

<SCRIPT TYPE="text/javascript">
   function addNetscapePanel() {
      if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function"))
      {
         window.sidebar.addPanel ("FreshPorts",
         "http://www.FreshPorts.org/sidebar.php","");
      }
      else
      {
         var rv = window.confirm ("This page is enhanced for use with Netscape 6.  " + "Would you like to upgrade now?");
         if (rv)
            document.location.href = "http://home.netscape.com/download/index.html";
      }
   }
//-->

</SCRIPT>

<CENTER>
<A NAME="button_image"></A><A HREF="javascript:addNetscapePanel();"><IMG SRC="/images/sidebar-add-button.gif" BORDER="0" HEIGHT="45" WIDTH="100" ALT="Add tab to Netscape 6"></A>
</CENTER>

