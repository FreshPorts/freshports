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
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-port-packages.php');

#
# tell the robots not to follow links from this page.
# see include/freshports.php for more information
#
GLOBAL $g_NOFOLLOW;

$g_NOFOLLOW = 1;

function DisplayPortCommits($port, $PageNumber):string {
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
		$HTML .= '<table class="ports-moved fullwidth bordered">' . "\n";
		$HTML .= "<tr>\n";
		$HTML .= freshports_PageBannerText("Port Moves");
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

#
# If during freshports_PortDisplay(), we do not have the port fetched from the database and we need it
# this function gets invoked. Sometimes you have some cached items but not others
#
function _GetThatPort($db, $Debug, $category, $port, $branch, $UserID) {
	$MyPort = null;

	$port_id = freshports_GetPortID($db, $category, $port, $branch);
	if (!IsSet($port_id)) {
		if ($Debug) echo "$category/$port is not a port according to freshports_GetPortID<br>\n";
		return $MyPort;
	}

	if ($Debug) echo "$category/$port $port_id found by freshports_GetPortID on $branch<br>\n";

	$MyPort = new Port($db);
	$MyPort->FetchByID($port_id, $UserID);

	return $MyPort;
}

function freshports_PortDisplay($db, $category, $port, $branch, $HasCommitsOnBranch = true)
{
	global $User;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');

	$Debug = 0;

	$MyPort = null;

	# If a port has no commits on a branch, we can't display that port.
	# In that circumstance, we display from head.
	$ReadFromThisBranch = $HasCommitsOnBranch ? $branch : BRANCH_HEAD;

	if ($Debug) echo 'into ' . __FILE__ . ' now' . "<br>\n";
#	if ($Debug) phpinfo();

	$PageNumber = 1;
	$url_args = array();
	if (isset($_SERVER['REQUEST_URI'])) {
		$url_query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

		if (!empty($url_query)) {
			parse_str($url_query, $url_args);
		}
	}

	if ($Debug) {
		echo 'query parts';
		echo '<pre>' . var_export($url_args, true) . '</pre>';
	}

	# allowing the code to bypass and/or not update the cache is only permitted
	# with FRESHPORTS_LOG_CACHE_ACTIVITY set
	$BypassCache = 0; # by default, we do not bypass the cache
	$RefreshCache = 1; # by default, we refresh the cache

	# if allowed, look to see if we are allowed to change the default values.
	# this prevents abuse by non-developers.
	if (defined('FRESHPORTS_LOG_CACHE_ACTIVITY')) {
		if ($Debug) echo 'checking for cache instructions<br>';
		if (isset($url_args['bypasscache']) && $url_args['bypasscache'] == '1') $BypassCache = 1;
		if (isset($url_args['refreshcache']) && $url_args['refreshcache'] == '0') $RefreshCache = 0;
	} else {
		if ($Debug) echo 'cache instructions are not enabled<br>';
	}

	if ($Debug) {
		echo "\$BypassCache='$BypassCache'<br>";
		echo "\$RefreshCache='$RefreshCache'<br>";
	}

	if (isset($url_args['page']) && Is_Numeric($url_args['page'])) {
		$PageNumber = intval($url_args['page']);
		if ($PageNumber != $url_args['page'] || $PageNumber < 1) {
			$PageNumber = 1;
		}
	}

	$port_display = new port_display($db, $User, $branch);
	$port_display->SetDetailsFull();


	################################################################################################
	### Port part 1 ################################################################################
	################################################################################################

	$Cache = new CachePort();
	$Cache->PageSize = $User->page_size;
	if (!$BypassCache) {
		# returns zero if found
		$result = $Cache->RetrievePort($category, $port, CACHE_PORT_DETAIL, $PageNumber, $branch, CachePort::CachePartOne);
		if ($Debug) {
			if (!$result) {
				echo 'found something from the cache for ' . CachePort::CachePartOne . "<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port'" . CachePort::CachePartOne . " on $branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
		$HTMLPortPart1 = $Cache->CacheDataGet();
	} else {
		$HTMLPortPart1 = '';

		//
		// When processing cache, there might be race conditions where one part of the cache exists, but others do not
		// Therefore, we read from the database only if we must.
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		if (empty($MyPort)) {
			$MyPort = _GetThatPort($db, $Debug, $category, $port, $HasCommitsOnBranch ? $branch : BRANCH_HEAD, $User->id);
			if (!$MyPort) {
				syslog(LOG_ERR, 'Fatal error: Could not fetch that port from the database on ' . __LINE__ . ' of ' . __FILE__);
				return -1;
			}
		}

		$HTMLPortPart1 .= $MyPort->long_description;
		if (empty($HTMLPortPart1)) {
			$HTMLPortPart1 = 'a long description could not be found for this port';
		}

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			$Cache->CacheDataSet($HTMLPortPart1);
			$Cache->AddPort($MyPort->category, $MyPort->port, CACHE_PORT_DETAIL, $PageNumber, $branch, $Cache::CachePartOne);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}
	}

	# At this point, we have the port detail HTML part 1

	################################################################################################
	### Port part 2 ################################################################################
	################################################################################################

	$Cache = new CachePort();
	$Cache->PageSize = $User->page_size;
	if (!$BypassCache) {
		# returns zero if found
		$result = $Cache->RetrievePort($category, $port, CACHE_PORT_DETAIL, $PageNumber, $branch, CachePort::CachePartTwo);
		if ($Debug) {
			if (!$result) {
				echo 'found something from the cache for ' . CachePort::CachePartTwo . "<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port'" . CachePort::CachePartTwo . " on $branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
		$HTMLPortPart2 = $Cache->CacheDataGet();
		#
		# this code is similar to that around line 81 in www/commit.php
		# we need to know the element_id of this port
		# and if it is on the person's watch list
		#
		$EndOfFirstLine = strpos($HTMLPortPart2, "\n");
		# XXX debug
#		$EndOfFirstLine = false; 
		if ($EndOfFirstLine == false) {
			syslog(LOG_ERR, "Internal error: I was expecting an ElementID and found nothing for $category/$port");
			die("Internal error: I was expecting an ElementID and found nothing for $category/$port");
		}
		# extract the ElementID from the cache
		$ElementID = intval(substr($HTMLPortPart2, 0, $EndOfFirstLine));
		# XXX debug
#		$ElementID = 0;
		if ($ElementID == 0) {
			syslog(LOG_ERR, "Extract of ElementID from cache failed.  Is cache corrupt/deprecated? port was $category/$port");
			die("Extract of ElementID from cache failed.  Is cache corrupt/deprecated? port was $category/$port. Please send the URL and this message to the webmaster.");
		}

		if ($User->id) {
			$OnWatchList = freshports_OnWatchList($db, $User->id, $ElementID);
		} else {
			$OnWatchList = 0;
		}

		$HTMLPortPart2 = substr($HTMLPortPart2, $EndOfFirstLine + 1);

		# now we extract the short description
		$EndOfFirstLine = strpos($HTMLPortPart2, "\n");
		# XXX debug
#		$EndOfFirstLine = false;
		if ($EndOfFirstLine == false) {
			syslog(LOG_ERR, "Internal error: I was expecting a short description and found nothing for $category/$port");
			die("Internal error: I was expecting a short description and found nothing for $category/$port");
		}

		# short description should be short
		$ShortDescription = substr($HTMLPortPart2, 0, $EndOfFirstLine);
		# XXX debug
#		unset($ShortDescription);
		if (empty($ShortDescription) || strlen($ShortDescription) > 130) {
			syslog(LOG_ERR, "Internal error: Extract of ShortDescription from cache failed.  Is cache corrupt/deprecated? port was $category/$port");
			die("Internal error: Extract of ShortDescription from cache failed.  Is cache corrupt/deprecated? port was $category/$port. Please send the URL and this message to the webmaster.");
		}
		$HTMLPortPart2 = substr($HTMLPortPart2, $EndOfFirstLine + 1);
	} else {
		$HTMLPortPart2 = '';

		//
		// When processing cache, there might be race conditions where one part of the cache exists, but others do not
		// Therefore, we read from the database only if we must.
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		if (empty($MyPort)) {
			$MyPort = _GetThatPort($db, $Debug, $category, $port, $ReadFromThisBranch, $User->id);
			if (!$MyPort) {
				syslog(LOG_ERR, 'Fatal error: Could not fetch that port from the database on ' . __LINE__ . ' of ' . __FILE__);
				return -1;
			}
		}

		$port_display->SetPort($MyPort);
		$port_display->SetDetailsBeforePackages();

		$HTMLPortPart2 .= $port_display->Display();

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			#
			# we prepend the element_id and short description for use when we pull that back from the cache
			# the element_id is used with the user's watch lists to indictate if the port is on or off a watch list
			# the short description is used in the page title.
			#
			$myShortDescription = $MyPort->{'short_description'};
			if (empty($myShortDescription)) {
				$myShortDescription = 'A short description could not be found for this port';
			}
			$Cache->CacheDataSet($MyPort->{'element_id'} . "\n" . $myShortDescription . "\n" . $HTMLPortPart2);
			$Cache->AddPort($MyPort->category, $MyPort->port, CACHE_PORT_DETAIL, $PageNumber, $branch, $Cache::CachePartTwo);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}

		$ElementID = $MyPort->{'element_id'};
		$OnWatchList = $MyPort->{'onwatchlist'};
		$ShortDescription = $MyPort->{'short_description'};
	}

	# At this point, we have the port detail HTML part 2

	$HTMLPortPart2 = $port_display->ReplaceWatchListToken($OnWatchList, $HTMLPortPart2, $ElementID);

	global $ShowAds, $BannerAd;

	if ($ShowAds && $BannerAd) {
		$HTML_For_Ad = "<hr><center>\n" . Ad_728x90PortDescription() . "\n</center>\n<hr>\n";
	} else {
		$HTML_For_Ad = '';
	}

	# we take this off the first cache item, for no particular reason.
	freshports_ConditionalGetUnix($Cache->LastModifiedGet());


	################################################################################################
	### Port part 2 ################################################################################
	################################################################################################

	if (!$BypassCache) {
		# returns zero if found
		$result = $Cache->RetrievePort($category, $port, CACHE_PORT_DETAIL, $PageNumber, $branch, CachePort::CachePartThree);
		if ($Debug) {
			if (!$result) {
				echo 'found something from the cache for ' . CachePort::CachePartThree . "<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port'" . CachePort::CachePartThree . " on $branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
		$HTMLPortPart3 = $Cache->CacheDataGet();
	} else {
		$HTMLPortPart3 = '';

		//
		// When processing cache, there might be race conditions where one part of the cache exists, but others do not
		// Therefore, we read from the database only if we must.
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		if (empty($MyPort)) {
			$MyPort = _GetThatPort($db, $Debug, $category, $port, $ReadFromThisBranch, $User->id);
			if (!$MyPort) {
				syslog(LOG_ERR, 'Fatal error: Could not fetch that port from the database on ' . __LINE__ . ' of ' . __FILE__);
				return -1;
			}
		}

		$port_display->SetPort($MyPort);
		$port_display->SetDetailsAfterPackages();

		$HTMLPortPart3 .= $port_display->Display();

		$HTMLPortPart3 .= "</TD></tr>\n</table>\n\n";

		if ($HasCommitsOnBranch) {
			# we are displaying the 
			$HTMLPortPart3 .= DisplayPortCommits($MyPort, $PageNumber);
		} else {
			$HTMLPortPart3 .= "<h2>There are no commits on branch $branch for this port</h2>";
		}

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			#
			# we prepend the element_id and short description for use when we pull that back from the cache
			# the element_id is used with the user's watch lists to indictate if the port is on or off a watch list
			# the short description is used in the page title.
			#
			$Cache->CacheDataSet($HTMLPortPart3);
			$Cache->AddPort($MyPort->category, $MyPort->port, CACHE_PORT_DETAIL, $PageNumber, $branch, $Cache::CachePartThree);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}
	}

	# the ad is always in the second part
	$HTMLPortPart3 = $port_display->ReplaceAdvertismentToken($HTMLPortPart3, $HTML_For_Ad);

	freshports_ConditionalGetUnix($Cache->LastModifiedGet());

	$Title = $category . "/" . $port . ': ' . $ShortDescription;


	################################################################################################
	### Port packages ##############################################################################
	################################################################################################

	$CachePackages = new CachePortPackages();
	if (!$BypassCache) {
		# returns zero if found
		$result = $CachePackages->RetrievePortPackages($category, $port);
		if ($Debug) {
			if (!$result) {
				echo "found something from the cache for packages<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port' packages on $branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
		$HTMLPortPackages = $CachePackages->CacheDataGet();
	} else {
		$HTMLPortPackages = '';

		//
		// When processing cache, there might be race conditions where one part of the cache exists, but others do not
		// Therefore, we read from the database only if we must.
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		if (empty($MyPort)) {
			$MyPort = _GetThatPort($db, $Debug, $category, $port, $ReadFromThisBranch, $User->id);
			if (!$MyPort) {
				$msg = 'Fatal error: Could not fetch that port from the database on ' . __LINE__ . ' of ' . __FILE__;
				syslog(LOG_ERR, $msg);
				return -1;
			}
		}

		$port_display->SetPort($MyPort);
		$port_display->SetDetailsPackages();

		$HTMLPortPackages .= $port_display->Display();

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			#
			# we prepend the element_id and short description for use when we pull that back from the cache
			# the element_id is used with the user's watch lists to indictate if the port is on or off a watch list
			# the short description is used in the page title.
			#
			$CachePackages->CacheDataSet($HTMLPortPackages);
			$CachePackages->AddPortPackages($MyPort->category, $MyPort->port);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}
	}

	header("HTTP/1.1 200 OK");

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$ExtraScript = "
<script>
var sheet = document.createElement('style')
sheet.innerHTML = \".more {display: none;}\";
document.body.appendChild(sheet);
</script>
";

	freshports_Start($Title,
		$HTMLPortPart1,
		"FreeBSD, index, applications, ports", 0, $ExtraScript);

	?>


	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

			<?php echo freshports_MainContentTable(); ?>

	<tr>
		<?php echo freshports_PageBannerText("Port details" . ($branch != BRANCH_HEAD ? ' on branch ' . htmlspecialchars($branch) : '')); ?>
	</tr>

	<tr><td class="content">

			<?php
			echo $HTMLPortPart2 . $HTMLPortPackages . $HTMLPortPart3;
			?>

		</TD>
		<td class="sidebar">
			<?php
			echo freshports_SideBar();
			?>
		</td>
	</tr>

	</table>

	<?php
	echo freshports_ShowFooter();
	?>

	</body>
	</html>

	<?php
}

function freshports_PortDisplayNew($db, $MyPort, $category, $port, $url_args, $Branch, $HasCommitsOnBranch)
{
	global $User;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');

	# If a port has no commits on a branch, we can't display that port.
	# In that circumstance, we display from head.
	$ReadFromThisBranch = $HasCommitsOnBranch ? $Branch : BRANCH_HEAD;

	$Debug = 0;

	if ($Debug) echo 'into ' . __FILE__ . ' now' . "<br>\n";
#	if ($Debug) phpinfo();

	$PageNumber = 1;

	if ($Debug) {
		echo "looking at '$category/$port on branch $Branch which ";
		if ($HasCommitsOnBranch) {
			echo "should have commits";
		} else {
			echo "should not have commits";
		}
		echo '<br>';
		echo 'query parts';
		echo '<pre>' . var_export($url_args, true) . '</pre>';

	}

	# allowing the code to bypass and/or not update the cache is only permitted
	# with FRESHPORTS_LOG_CACHE_ACTIVITY set
	$BypassCache = 0; # by default, we do not bypass the cache
	$RefreshCache = 1; # by default, we refresh the cache

	# if allowed, look to see if we are allowed to change the default values.
	# this prevents abuse by non-developers.
	if (defined('FRESHPORTS_LOG_CACHE_ACTIVITY')) {
		if ($Debug) echo 'checking for cache instructions<br>';
		if (isset($url_args['bypasscache']) && $url_args['bypasscache'] == '1') $BypassCache = 1;
		if (isset($url_args['refreshcache']) && $url_args['refreshcache'] == '0') $RefreshCache = 0;
	} else {
		if ($Debug) echo 'cache instructions are not enabled<br>';
	}

	if ($Debug) {
		echo "\$BypassCache='$BypassCache'<br>";
		echo "\$RefreshCache='$RefreshCache'<br>";
	}

	if (isset($url_args['page']) && Is_Numeric($url_args['page'])) {
		$PageNumber = intval($url_args['page']);
		if ($PageNumber != $url_args['page'] || $PageNumber < 1) {
			$PageNumber = 1;
		}
	}

	$port_display = new port_display($db, $User, $Branch);
	$port_display->SetDetailsFull();


	################################################################################################
	### Port part 1 ################################################################################
	################################################################################################

	$Cache = new CachePort();
	$Cache->PageSize = $User->page_size;
	if (!$BypassCache) {
		# returns zero if found
		$result = $Cache->RetrievePort($category, $port, CACHE_PORT_DETAIL, $PageNumber, $Branch, CachePort::CachePartOne);
		if ($Debug) {
			if (!$result) {
				if ($Debug) echo 'found something from the cache for ' . CachePort::CachePartOne . "<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port'" . CachePort::CachePartOne . " on $Branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
		$HTMLPortPart1 = $Cache->CacheDataGet();
	} else {
		$HTMLPortPart1 = '';

		//
		// When processing cache, there might be race conditions where one part of the cache exists, but others do not
		// Therefore, we read from the database only if we must.
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		if (empty($MyPort)) {
			$MyPort = _GetThatPort($db, $Debug, $category, $port, $ReadFromThisBranch, $User->id);
			if (!$MyPort) {
				syslog(LOG_ERR, 'Fatal error: Could not fetch that port from the database on ' . __LINE__ . ' of ' . __FILE__);
				return -1;
			}
		}

		$HTMLPortPart1 .= $MyPort->long_description;
		if (empty($HTMLPortPart1)) {
			$HTMLPortPart1 = 'a long description could not be found for this port';
		}

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			$Cache->CacheDataSet($HTMLPortPart1);
			$Cache->AddPort($MyPort->category, $MyPort->port, CACHE_PORT_DETAIL, $PageNumber, $Branch, $Cache::CachePartOne);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}
	}

	# At this point, we have the port detail HTML part 1

	################################################################################################
	### Port part 2 ################################################################################
	################################################################################################

	$Cache = new CachePort();
	$Cache->PageSize = $User->page_size;
	if (!$BypassCache) {
		# returns zero if found
		$result = $Cache->RetrievePort($category, $port, CACHE_PORT_DETAIL, $PageNumber, $Branch, CachePort::CachePartTwo);
		if ($Debug) {
			if (!$result) {
				echo 'found something from the cache for ' . CachePort::CachePartTwo . "<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port'" . CachePort::CachePartTwo . " on $Branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
		$HTMLPortPart2 = $Cache->CacheDataGet();
		#
		# this code is similar to that around line 81 in www/commit.php
		# we need to know the element_id of this port
		# and if it is on the person's watch list
		#
		$EndOfFirstLine = strpos($HTMLPortPart2, "\n");
		# XXX debug
#		$EndOfFirstLine = false; 
		if ($EndOfFirstLine == false) {
			$msg = "Internal error: I was expecting an ElementID and found nothing for $category/$port";
			syslog(LOG_ERR, $msg);
			die($msg);
		}
		# extract the ElementID from the cache
		$ElementID = intval(substr($HTMLPortPart2, 0, $EndOfFirstLine));
		# XXX debug
#		$ElementID = 0;
		if ($ElementID == 0) {
			$msg = "Extract of ElementID from cache failed.  Is cache corrupt/deprecated? port was $category/$port";
			syslog(LOG_ERR, $msg);
			die($msg);
		}

		if ($User->id) {
			$OnWatchList = freshports_OnWatchList($db, $User->id, $ElementID);
		} else {
			$OnWatchList = 0;
		}

		$HTMLPortPart2 = substr($HTMLPortPart2, $EndOfFirstLine + 1);

		# now we extract the short description
		$EndOfFirstLine = strpos($HTMLPortPart2, "\n");
		# XXX debug
#		$EndOfFirstLine = false;
		if ($EndOfFirstLine == false) {
			$msg = "Internal error: I was expecting a short description and found nothing for $category/$port";
			syslog(LOG_ERR, $msg);
			die($msg);
		}

		# short description should be short
		$ShortDescription = substr($HTMLPortPart2, 0, $EndOfFirstLine);
		# XXX debug
#		unset($ShortDescription);
		if (empty($ShortDescription) || strlen($ShortDescription) > 130) {
			$msg = "Internal error: Extract of ShortDescription from cache failed.  Is cache corrupt/deprecated? port was $category/$port";
			syslog(LOG_ERR, $msg);
			die($msg);
		}
		$HTMLPortPart2 = substr($HTMLPortPart2, $EndOfFirstLine + 1);
	} else {
		$HTMLPortPart2 = '';

		//
		// When processing cache, there might be race conditions where one part of the cache exists, but others do not
		// Therefore, we read from the database only if we must.
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		if (empty($MyPort)) {
			$MyPort = _GetThatPort($db, $Debug, $category, $port, $ReadFromThisBranch, $User->id);
			if (!$MyPort) {
				$msg = 'Fatal error: Could not fetch that port from the database on ' . __LINE__ . ' of ' . __FILE__;
				syslog(LOG_ERR, $msg);
				return -1;
			}
		}

		$port_display->SetPort($MyPort);
		$port_display->SetDetailsBeforePackages();

		$HTMLPortPart2 .= $port_display->Display();

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			#
			# we prepend the element_id and short description for use when we pull that back from the cache
			# the element_id is used with the user's watch lists to indictate if the port is on or off a watch list
			# the short description is used in the page title.
			#
			$myShortDescription = $MyPort->{'short_description'};
			if (empty($myShortDescription)) {
				$myShortDescription = 'A short description could not be found for this port';
			}
			$Cache->CacheDataSet($MyPort->{'element_id'} . "\n" . $myShortDescription . "\n" . $HTMLPortPart2);
			$Cache->AddPort($MyPort->category, $MyPort->port, CACHE_PORT_DETAIL, $PageNumber, $Branch, $Cache::CachePartTwo);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}

		$ElementID = $MyPort->{'element_id'};
		$OnWatchList = $MyPort->{'onwatchlist'};
		$ShortDescription = $MyPort->{'short_description'};
	}

	# At this point, we have the port detail HTML part 2

	$HTMLPortPart2 = $port_display->ReplaceWatchListToken($OnWatchList, $HTMLPortPart2, $ElementID);

	global $ShowAds, $BannerAd;

	if ($ShowAds && $BannerAd) {
		$HTML_For_Ad = "<hr><center>\n" . Ad_728x90PortDescription() . "\n</center>\n<hr>\n";
	} else {
		$HTML_For_Ad = '';
	}

	# we take this off the first cache item, for no particular reason.
	freshports_ConditionalGetUnix($Cache->LastModifiedGet());


	################################################################################################
	### Port part 2 ################################################################################
	################################################################################################

	if (!$BypassCache) {
		# returns zero if found
		$result = $Cache->RetrievePort($category, $port, CACHE_PORT_DETAIL, $PageNumber, $Branch, CachePort::CachePartThree);
		if ($Debug) {
			if (!$result) {
				echo 'found something from the cache for ' . CachePort::CachePartThree . "<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port'" . CachePort::CachePartThree . " on $Branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
		$HTMLPortPart3 = $Cache->CacheDataGet();
	} else {
		$HTMLPortPart3 = '';

		//
		// When processing cache, there might be race conditions where one part of the cache exists, but others do not
		// Therefore, we read from the database only if we must.
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		if (empty($MyPort)) {
			$MyPort = _GetThatPort($db, $Debug, $category, $port, $ReadFromThisBranch, $User->id);
			if (!$MyPort) {
				$msg = 'Fatal error: Could not fetch that port from the database on ' . __LINE__ . ' of ' . __FILE__;
				syslog(LOG_ERR, $msg);
				return -1;
			}
		}

		$port_display->SetPort($MyPort);
		$port_display->SetDetailsAfterPackages();

		$HTMLPortPart3 .= $port_display->Display();

		$HTMLPortPart3 .= "</TD></tr>\n</table>\n\n";

		if ($HasCommitsOnBranch) {
			# we are displaying the commits from the branch
			$HTMLPortPart3 .= DisplayPortCommits($MyPort, $PageNumber);
		} else {
			$HTMLPortPart3 .= "<h2>There are no commits on branch $Branch for this port</h2>";
		}

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			#
			# we prepend the element_id and short description for use when we pull that back from the cache
			# the element_id is used with the user's watch lists to indictate if the port is on or off a watch list
			# the short description is used in the page title.
			#
			$Cache->CacheDataSet($HTMLPortPart3);
			$Cache->AddPort($MyPort->category, $MyPort->port, CACHE_PORT_DETAIL, $PageNumber, $Branch, $Cache::CachePartThree);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}
	}

	# the ad is always in the second part
	$HTMLPortPart3 = $port_display->ReplaceAdvertismentToken($HTMLPortPart3, $HTML_For_Ad);

	freshports_ConditionalGetUnix($Cache->LastModifiedGet());

	$Title = $category . "/" . $port . ': ' . $ShortDescription;


	################################################################################################
	### Port packages ##############################################################################
	################################################################################################

	$CachePackages = new CachePortPackages();
	if (!$BypassCache) {
		# returns zero if found
		$result = $CachePackages->RetrievePortPackages($category, $port);
		if ($Debug) {
			if (!$result) {
				echo "found something from the cache for packages<br>\n";
			} else {
				echo "found NOTHING in cache for '$category/$port' packages on $Branch<br>\n";
			}
		}
	} else {
		$result = -1;
	}

	if (!$result) {
		$HTMLPortPackages = $CachePackages->CacheDataGet();
	} else {
		$HTMLPortPackages = '';

		//
		// When processing cache, there might be race conditions where one part of the cache exists, but others do not
		// Therefore, we read from the database only if we must.
		//
		// sometimes they want to see a port on a branch, but there have been no commits against that port on that branch
		// therefore, we display head. We display head because that's what will be on the branch by default, given no
		// commits.
		//
		if (empty($MyPort)) {
			$MyPort = _GetThatPort($db, $Debug, $category, $port, $ReadFromThisBranch, $User->id);
			if (!$MyPort) {
				$msg = 'Fatal error: Could not fetch that port from the database on ' . __LINE__ . ' of ' . __FILE__;
				syslog(LOG_ERR, $msg);
				return -1;
			}
		}

		$port_display->SetPort($MyPort);
		$port_display->SetDetailsPackages();

		$HTMLPortPackages .= $port_display->Display();

		# only save if we are supposed to save... usually for debugging
		if ($RefreshCache) {
			if ($Debug) echo 'saving to cache<br>';
			#
			# we prepend the element_id and short description for use when we pull that back from the cache
			# the element_id is used with the user's watch lists to indictate if the port is on or off a watch list
			# the short description is used in the page title.
			#
			$CachePackages->CacheDataSet($HTMLPortPackages);
			$CachePackages->AddPortPackages($MyPort->category, $MyPort->port);
		} else {
			if ($Debug) echo 'not saving to cache, as instructed<br>';
		}
	}



	header("HTTP/1.1 200 OK");

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$ExtraScript = "
<script>
var sheet = document.createElement('style')
sheet.innerHTML = \".more {display: none;}\";
document.body.appendChild(sheet);
</script>
";

	freshports_Start($Title,
		$HTMLPortPart1,
		"FreeBSD, index, applications, ports", 0, $ExtraScript);

	?>


	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

			<?php echo freshports_MainContentTable(); ?>

	<tr>
		<?php echo freshports_PageBannerText("Port details" . ($Branch != BRANCH_HEAD ? ' on branch ' . htmlspecialchars($Branch) : '')); ?>
	</tr>

	<tr><td class="content">

			<?php
			echo $HTMLPortPart2 . $HTMLPortPackages . $HTMLPortPart3;
			?>

		</TD>
		<td class="sidebar">
			<?php
			echo freshports_SideBar();
			?>
		</td>
	</tr>

	</table>

	<?php
	echo freshports_ShowFooter();
	?>

	</body>
	</html>

	<?php

	# If we get this far, we have a port. No further processing is done.
	exit;
}

