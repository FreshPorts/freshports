<?php
	#
	# $Id: missing-port.php,v 1.6 2012-08-07 13:13:24 dan Exp $
	#
	# Copyright (c) 2001-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/master_slave.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports_updating.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-port.php');

#
# tell the robots not to follow links from this page.
# see include/freshports.php for more information
#
GLOBAL $g_NOFOLLOW;

$g_NOFOLLOW = 1;

function DisplayPortCommits($port, $PageNumber) {
	$HTML = '';
	
	$PortsUpdating   = new PortsUpdating($port->dbh);
	$NumRowsUpdating = $PortsUpdating->FetchInitialise($port->id);

	$HTML .= freshports_UpdatingOutput($NumRowsUpdating, $PortsUpdating, $port);
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports_moved.php');

	$PortsMovedFrom = new PortsMoved($port->dbh);
	$NumRowsFrom    = $PortsMovedFrom->FetchInitialiseFrom($port->id);

	$PortsMovedTo   = new PortsMoved($port->dbh);
	$NumRowsTo      = $PortsMovedTo->FetchInitialiseTo($port->id);

	if ($NumRowsFrom + $NumRowsTo > 0) {
		$HTML .= '<TABLE BORDER="1" width="100%" CELLSPACING="0" CELLPADDING="5">' . "\n";
		$HTML .= "<TR>\n";
		$HTML .= freshports_PageBannerText("Port Moves", 1);
		$HTML .= "<tr><td>\n";
		$HTML .= "<ul>\n";
	}

	for ($i = 0; $i < $NumRowsFrom; $i++) {
		$PortsMovedFrom->FetchNth($i);
		$HTML .= '<li>' . freshports_PortsMoved($port, $PortsMovedFrom);
		if ($i + 1 != $NumRowsFrom) {
			$HTML .= '<br>';
		}
		$HTML .= "</li>\n";
	}

	for ($i = 0; $i < $NumRowsTo; $i++) {
		$PortsMovedTo->FetchNth($i);
		$HTML .= '<li>' . freshports_PortsMoved($port, $PortsMovedTo);
		if ($i + 1 != $NumRowsTo) {
			$HTML .= '<br>';
		}
		$HTML .= "</li>\n";
	}
	
	if ($NumRowsFrom + $NumRowsTo > 0) {
		$HTML .= "</ul>\n";
		$HTML .= "</td></tr>\n";
		$HTML .= "</table>\n";
	}


	GLOBAL $User;
	$HTML .= freshports_PortCommits($port, $PageNumber, $User->page_size);
	
	// end of caching
	
	return $HTML;
}

function freshports_PortDisplay($db, $category, $port) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;
	GLOBAL $User;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');

	# these two options must be the last on the line.  And as such are mutually exclusive
	define('BYPASSCACHE',  'bypasscache=1');  # do not read the cache for display
	define('REFRESHCACHE', 'refreshcache=1'); # refresh the cache

	$BypassCache  = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["REQUEST_URI"]) - strlen(BYPASSCACHE))  == BYPASSCACHE;
	$RefreshCache = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["REQUEST_URI"]) - strlen(REFRESHCACHE)) == REFRESHCACHE;

	$Debug = 0;
	if ($Debug) echo 'into ' . __FILE__ . ' now' . "<br>\n";

	$PageNumber = 1;
	if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
	   parse_str($_SERVER['REDIRECT_QUERY_STRING'], $query_parts);
        } else {
           $query_parts = null;
        }

	if ($Debug) echo print_r($query_parts, true);
	if (IsSet($query_parts['page'])  && Is_Numeric($query_parts['page'])) {
		$PageNumber = intval($query_parts['page']);
		if ($PageNumber != $query_parts['page'] || $PageNumber < 1) {
			$PageNumber = 1;
		}
	}

	$port_display = new port_display($db, $User);
	$port_display->SetDetailsFull();

	$Cache = new CachePort();
	$Cache->PageSize = $User->page_size;
	$result = $Cache->RetrievePort($category, $port, CACHE_PORT_DETAIL, $PageNumber);
	if (!$result && !$BypassCache && !$RefreshCache) {
		if ($Debug) echo "found something from the cache<br>\n";
		$HTML = $Cache->CacheDataGet();
		#
		# we need to know the element_id of this port
		# and the whether or not it is on the person's watch list
		# let's create a special function for that!
		#
		$EndOfFirstLine = strpos($HTML, "\n");
		if ($EndOfFirstLine == false) {
			die('Internal error: I was expecting an ElementID and found nothing');
		}
		# extract the ElementID from the cache
		$ElementID  = intval(substr($HTML, 0, $EndOfFirstLine));
		if ($ElementID == 0) {
			syslog(LOG_ERR, "Extract of ElementID from cache failed.  Is cache corrupt/deprecated? port was $category/$port");
			die('sorry, I encountered a problem with the cache.  Please send the URL and this message to the webmaster.');
		}

		if ($User->id) {
			$OnWatchList = freshports_OnWatchList($db, $User->id, $ElementID);
		} else {
			$OnWatchList = 0;
		}

		$HTML = substr($HTML, $EndOfFirstLine + 1);
	} else {
		if ($Debug) echo "found NOTHING in cache for '$category/$port'<br>\n";
		$HTML = '';
		$port_id = freshports_GetPortID($db, $category, $port);
		if (!IsSet($port_id)) {
			if ($Debug) echo "$category/$port is not a port according to freshports_GetPortID<br>\n";

			return -1;
		}

		if ($Debug) echo "$category/$port found by freshports_GetPortID<br>";

		$MyPort = new Port($db);
		$MyPort->FetchByID($port_id, $User->id);

		$port_display->port = $MyPort;
	
		$HTML .= $port_display->Display();
		
		$HTML .= "</TD></TR>\n</TABLE>\n\n";

		$HTML .= DisplayPortCommits($MyPort, $PageNumber);

		# If we are not reading 
		if (!$BypassCache || $RefreshCache) {
			$Cache->CacheDataSet($MyPort->{'element_id'} . "\n" . $HTML);
			$Cache->AddPort($MyPort->category, $MyPort->port, CACHE_PORT_DETAIL, $PageNumber);
		}

		$ElementID   = $MyPort->{'element_id'};
		$OnWatchList = $MyPort->{'onwatchlist'};
	}
	
	# At this point, we have the port detail HTML

	$HTML = $port_display->ReplaceWatchListToken($OnWatchList, $HTML, $ElementID);
	GLOBAL $ShowAds, $BannerAd;

	GLOBAL $ShowAds;
	GLOBAL $BannerAd;

	if ($ShowAds && $BannerAd) {
		$HTML_For_Ad = "<hr><center>\n" . Ad_728x90PortDescription() . "\n</center>\n<hr>\n";
	} else{
		$HTML_For_Ad = '';
	}

	$HTML = $port_display->ReplaceAdvertismentToken($HTML, $HTML_For_Ad);

	freshports_ConditionalGetUnix($Cache->LastModifiedGet());

	header("HTTP/1.1 200 OK");

	$Title = $category . "/" . $port;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start($Title,
	        		"$FreshPortsTitle - new ports, applications",
					"FreeBSD, index, applications, ports");

?>


<?php echo freshports_MainTable(); ?>

<tr><TD VALIGN="top" width="100%">

<?php echo freshports_MainContentTable(); ?>

<TR>
<? echo freshports_PageBannerText("Port details"); ?>
</TR>

<tr><td valign="top" width="100%">

<?
	echo $HTML;
?>

</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
  <?
  echo freshports_SideBar();
  ?>
  </td>
</TR>

</TABLE>

<?
	echo freshports_ShowFooter();
?>

</body>
</html>

<?

return 0;

}

