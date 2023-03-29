<?php
	#
	# $Id: watch-list.php,v 1.3 2007-01-18 13:35:55 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/newsfeed.php');

	$Debug = 0;

	# NOTE: login not required to access this page; a valid token is required

	if (IN_MAINTENANCE_MODE) {
		# usually we redirect to the maintenance page but this is an
		# RSS feed.
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 300');//300 seconds
	}

function DisplayNewsFeed($db, $format, $token) {
	$Debug = 0;

	GLOBAL $FreshPortsSlogan;
	GLOBAL $FreshPortsName;

	$wlid  = freshports_WatchListVerifyToken($db, $token);
	if ($wlid == '') {
		syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . 
			' watch list token requested by ' . $_SERVER['REMOTE_ADDR'] . 
			' not found ' . "'$token'");
		header('HTTP/1.1 404 NOT FOUND');
		exit; 
	}
	
	echo newsfeed($db, strtoupper($format), $wlid);
}

function DisplayWatchListNewsFeeds($db, $UserID) {
	$Debug = 0;

	$WatchLists = new WatchLists($db);
	$NumRows = $WatchLists->Fetch($UserID);

	if ($Debug) {
		echo "$NumRows rows found!<br>";
		echo "selected = '$selected'<br>";
	}

	$HTML = '';
	$Protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';

	if ($NumRows) {
		for ($i = 0; $i < $NumRows; $i++) {
			$WatchList = $WatchLists->FetchNth($i);
			$URL = $Protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?id=' . $WatchList->token . '&format=rss0.91';
			$HTML .= '<a href="' . $URL . '">' . $WatchList->name . '</a><br>';
		}
	}

	$HTML .= "<p>You can use these formats:
	
	<ul>
	<li>rss0.91 (default)</li>
	<li>rss1.0</li>
	<li>rss2.0</li>
	</ul>";

	echo $HTML;	
}

	if (IsSet($_REQUEST['id'])) {
		$token = pg_escape_string($db, $_REQUEST['id']);
	}

	if (IsSet($_REQUEST['format'])) {
		$format = pg_escape_string($db, $_REQUEST['format']);
	}

	# validate incoming format	
	switch (strtolower($format ?? '')) {
		case 'rss1.0':
		case 'rss2.0':
			# all good.
			break;

		default:
			$format = 'rss0.91';
			break;
	}

	if (IsSet($token)) {
		DisplayNewsFeed($db, $format, $token);
	} else {
		// if we don't know who they are, we'll make sure they login first
		if (!$visitor) {
			header("Location: /login.php");
			exit;  /* Make sure that code below does not get executed when we redirect. */
		}

		$Title = "Watch List Feeds";
		freshports_Start($Title, $Title, 'FreeBSD, index, applications, ports');
		echo freshports_MainTable();
		echo '<tr><td class="content">';
		echo freshports_MainContentTable();
		echo '<tr>';
		echo freshports_PageBannerText('These are your news feeds');
		echo '</tr><tr><td class="textcontent">';

		DisplayWatchListNewsFeeds($db, $User->id);
		?>
	</td></tr>
</table>
</td>

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
	} ?>