<?php
	#
	# $Id: watch-list.php,v 1.3 2007-01-18 13:35:55 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
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
			# the feeds in /--/feeds/; this script still serves the old ones - re #721
			$URL = 'https://' . $_SERVER['HTTP_HOST'] . '/--/feeds/watch-list.php?id=' . rawurlencode($WatchList->token);
			$HTML .= '<a href="' . htmlspecialchars($URL) . '">' . htmlspecialchars($WatchList->name) . '</a>'
			       . ' [ <a href="' . htmlspecialchars($URL . '&format=atom') . '">Atom</a> ]<br>';
		}
	}

	$HTML .= "<p>" . _('You can use these formats:') . "

	<ul>
	<li>" . sprintf(_('%s (default)'), 'RSS 2.0') . "</li>
	<li>Atom 1.0 (format=atom)</li>
	</ul>";

	echo $HTML;	
}

	if (IsSet($_REQUEST['id'])) {
		$token = pg_escape_string($db, $_REQUEST['id']);
	}

	if (IsSet($_REQUEST['format'])) {
		$format = pg_escape_string($db, $_REQUEST['format']);
	}

	# no token and not logged in: the list of feeds needs a login
	if (empty($visitor) && empty($token)) {
		header('Location: /login.php');  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}


# validate incoming format
	switch (strtolower($format ?? '')) {
		case 'rss0.91':
		case 'rss1.0':
		case 'rss2.0':
			# all good.
			break;

		default:
			$format = 'rss2.0';
			break;
	}

	if (IsSet($token)) {
		DisplayNewsFeed($db, $format, $token);
	} else {
		// if we don't know who they are, we'll make sure they login first

		$Title = _('Watch List Feeds');
		freshports_Start($Title, $Title, 'FreeBSD, index, applications, ports');
		echo freshports_MainTable();
		echo '<tr><td class="content">';
		echo freshports_MainContentTable();
		echo '<tr>';
		echo freshports_PageBannerText(_('These are your news feeds'));
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