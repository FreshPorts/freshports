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
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-port.php');

#
# tell the robots not to follow links from this page.
# see include/freshports.php for more information
#
GLOBAL $g_NOFOLLOW;

$g_NOFOLLOW = 1;

const CachePortPart1    = 'CachePortPart1';
const CachePortPart2    = 'CachePortPart2';
const CachePortPackages = 'CachePortPackages';

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

function freshports_PortDisplay($db, $category, $port, $branch) {
	return _freshports_PortDisplayHelper($db, $category, $port, $branch);
}

function freshports_PortDisplayNotOnBranch($db, $category, $port, $branch) {
	return _freshports_PortDisplayHelper($db, $category, $port, $branch, false);
}

function _freshPorts_GetPortDisplay($MyPort, $WhichPart, &$LastModified) {

	# convert $WhichPart

	# allowing the code to bypass and/or not update the cache is only permitted
	# with FRESHPORTS_LOG_CACHE_ACTIVITY set
	$BypassCache  = 0; # by default, we do not bypass the cache
	$RefreshCache = 1; # by default, we refresh the cache

	# if allowed, look to see if we are allowed to change the default values.
	# this prevents abuse by non-developers.
	if (defined('FRESHPORTS_LOG_CACHE_ACTIVITY')) {
		if ($Debug) echo 'checking for cache instructions<br>';
		if (IsSet($url_args['bypasscache'])  && $url_args['bypasscache']  == '1') $BypassCache  = 1;
		if (IsSet($url_args['refreshcache']) && $url_args['refreshcache'] == '0') $RefreshCache = 0;
	} else {
		if ($Debug) echo 'cache instructions are not enabled<br>';
	}

	if ($Debug) {
		echo "\$BypassCache='$BypassCache'<br>";
		echo "\$RefreshCache='$RefreshCache'<br>";
	}

	$port_display = new port_display($db, $User, $branch);
	switch($WhichPart) {
		case CachePort::CachePortPart1:
			$port_display->SetDetailsBeforePackages();
			break;
			
		case CachePort::CachePortPart2:
			$port_display->SetDetailsAfterPackages();
			break;

		default:
			exit('unknown WhichPart passed to ' . __FUNCTION__ . ' in ' . __FILE__  . $WhichPart);
	}
	$port_display->SetDetailsFull();

	$Cache = new CachePort();
	$Cache->PageSize = $User->page_size;
	if (!$BypassCache) {
		$result = $Cache->RetrievePort($category, $port, CACHE_PORT_DETAIL, $PageNumber, $branch, $WhichPart);
		if ($Debug) {
			if (!$result) {
				if ($Debug) echo "found something from the cache<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port' on $branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
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
		$ElementID = intval(substr($HTML, 0, $EndOfFirstLine));
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

		# now we extract the short description
		$EndOfFirstLine = strpos($HTML, "\n");
		if ($EndOfFirstLine == false) {
			die('Internal error: I was expecting a short description and found nothing');
		}

		# short description should be short
		$ShortDescription = substr($HTML, 0, $EndOfFirstLine);
		if (empty($ShortDescription) || strlen($ShortDescription) > 100) {
			syslog(LOG_ERR, "Extract of ShortDescription from cache failed.  Is cache corrupt/deprecated? port was $category/$port");
			die('sorry, I encountered a problem with the cache.  Please send the URL and this message to the webmaster.');
		}
		$HTML = substr($HTML, $EndOfFirstLine + 1);
	} else {
		$HTML = '';
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		$port_id = freshports_GetPortID($db, $category, $port, $HasCommitsOnBranch ? $branch : BRANCH_HEAD);
		if (!IsSet($port_id)) {
			if ($Debug) echo "$category/$port is not a port according to freshports_GetPortID<br>\n";

			return -1;
		}

		if ($Debug) echo "$category/$port $port_id found by freshports_GetPortID on $branch<br>";

		$port_display->SetPort($MyPort);
	
		$HTML .= $port_display->Display();
		
		$HTML .= "</TD></TR>\n</TABLE>\n\n";

		if ($HasCommitsOnBranch) {
			# we are displaying the 
			$HTML .= DisplayPortCommits($MyPort, $PageNumber);
		} else {
			$HTML .= "<h2>There are no commits on branch $branch for this port</h2>";
		}

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			#
			# we prepend the element_id and short description for use when we pull that back from the cache
			# the element_id is used with the user's watch lists to indictate if the port is on or off a watch list
			# the short description is used in the page title.
			#
			$Cache->CacheDataSet($MyPort->{'element_id'} . "\n" . $MyPort->{'short_description'} . "\n" . $HTML);
			$Cache->AddPort($MyPort->category, $MyPort->port, CACHE_PORT_DETAIL, $PageNumber, $branch, $Cache::CachePartOne);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}

	}
}

function _freshports_PortDisplayHelper($db, $category, $port, $branch, $HasCommitsOnBranch = true) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;
	GLOBAL $User;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-packages-display.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');

	$Debug = 0;
	if ($Debug) echo 'into ' . __FILE__ . ' now' . "<br>\n";
#	if ($Debug) phpinfo();

	$PageNumber = 1;
	if (IsSet($_SERVER['REQUEST_URI'])) {
		$url_query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		parse_str($url_query, $url_args);
        } else {
           $url_args = null;
        }

	if ($Debug) {
	  echo 'query parts';
	  echo '<pre>' . var_export($url_args, true) . '</pre>';
	}

	if (IsSet($url_args['page'])  && Is_Numeric($url_args['page'])) {
		$PageNumber = intval($url_args['page']);
		if ($PageNumber != $url_args['page'] || $PageNumber < 1) {
			$PageNumber = 1;
		}
	}
	$MyPort = new Port($db);
	$MyPort->FetchByID($port_id, $User->id);

	$ElementID        = $MyPort->{'element_id'};
	$OnWatchList      = $MyPort->{'onwatchlist'};
	$ShortDescription = $MyPort->{'short_description'};


	$HTML_Part1    = $this->_freshPorts_GetPortDisplay($MyPort, CachePort::CachePortPart1, $LastModified);
#	$HTML_Packages = $this->_freshPorts_GetPortDisplay($MyPort, CachePortPackages);
	$HTML_Part2    = $this->_freshPorts_GetPortDisplay($MyPort, CachePort::CachePortPart2, $LastModified);

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

	$HTML_Part1 = $port_display->ReplaceAdvertismentToken($HTML_Part1, $HTML_For_Ad);

	freshports_ConditionalGetUnix($Cache->LastModifiedGet());

	header("HTTP/1.1 200 OK");

	$Title = $category . "/" . $port . ': ' . $ShortDescription;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	
	$ExtraScript = "
<script type=\"text/javascript\">
var sheet = document.createElement('style')
sheet.innerHTML = \".more {display: none;}\";
document.body.appendChild(sheet);
</script>
";

	freshports_Start($Title,
	        		"$FreshPortsTitle - new ports, applications",
					"FreeBSD, index, applications, ports", 0, $ExtraScript);

?>


<?php echo freshports_MainTable(); ?>

<tr><TD VALIGN="top" width="100%">

<?php echo freshports_MainContentTable(); ?>

<TR>
<?php echo freshports_PageBannerText("Port details" . ($branch != BRANCH_HEAD ? ' on branch ' . htmlspecialchars($branch) : '')); ?>
</TR>

<tr><td valign="top" width="100%">

<?php
	echo $HTML;
?>

</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
  <?php
  echo freshports_SideBar();
  ?>
  </td>
</TR>

</TABLE>

<?php
	echo freshports_ShowFooter();
?>

</body>
</html>

<?php

return 0;

}
