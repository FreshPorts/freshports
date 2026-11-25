<?php
	#
	# Copyright (c) 1998-2026 Dan Langille
	#
	# A watch list, as RSS 2.0 (default) or Atom 1.0 (format=atom) - re #721
	#
	# id=<watch list token>; no login needed, the token is enough
	# without id=: the list of your watch list feeds; login needed
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/feed.php');

# The page listing the logged-in user's watch lists, with a link to each one's feed
function freshports_WatchListFeedsPage($db, $visitor, $User) {
	if (empty($visitor)) {
		header('Location: /login.php');
		exit;
	}

	$Title = _('Watch List Feeds');
	freshports_Start($Title, $Title, 'FreeBSD, index, applications, ports');
	echo freshports_MainTable();
	echo '<tr><td class="content">';
	echo freshports_MainContentTable();
	echo '<tr>';
	echo freshports_PageBannerText(_('These are your news feeds'));
	echo '</tr><tr><td class="textcontent">';

	$WatchLists = new WatchLists($db);
	$NumRows = $WatchLists->Fetch($User->id);

	for ($i = 0; $i < $NumRows; $i++) {
		$WatchList = $WatchLists->FetchNth($i);
		$URL = 'https://' . $_SERVER['HTTP_HOST'] . '/--/feeds/watch-list.php?id=' . rawurlencode($WatchList->token);
		echo '<a href="' . htmlspecialchars($URL) . '">' . htmlspecialchars($WatchList->name) . '</a>'
		   . ' [ <a href="' . htmlspecialchars($URL . '&format=atom') . '">Atom</a> ]<br>';
	}

	echo "<p>" . _('You can use these formats:') . "

	<ul>
	<li>" . sprintf(_('%s (default)'), 'RSS 2.0') . "</li>
	<li>Atom 1.0 (format=atom)</li>
	</ul>";

	echo '</td></tr>
</table>
</td>

  <td class="sidebar">
	' . freshports_SideBar() . '
  </td>

</tr>
</table>

' . freshports_ShowFooter() . '

</body>
</html>
';
}

	if (IN_MAINTENANCE_MODE) {
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Retry-After: 300');
		exit;
	}

	$Token = $_GET['id'] ?? '';
	if (!is_string($Token) || $Token === '') {
		# no feed asked for: show the list of the user's watch list feeds
		# (this page used to be /backend/watch-list.php, which now redirects here - re #724)
		freshports_WatchListFeedsPage($db, $visitor ?? null, $User ?? null);
		exit;
	}

	$WatchListID = freshports_WatchListVerifyToken($db, pg_escape_string($db, $Token));
	if ($WatchListID == '') {
		# Unknown token: usually a feed reader still polling a watch list that was deleted
		# or whose token was regenerated. 410 Gone (not 404) tells feed readers to drop the
		# feed; many retry a 404 forever. It is the client's problem, not ours, so NOTICE.
		syslog(LOG_NOTICE, __FILE__ . '::' . __LINE__ .
			' watch list token requested by ' . $_SERVER['REMOTE_ADDR'] .
			' not found ' . "'$Token'");
		header('HTTP/1.1 410 Gone');
		exit;
	}

	$Type = (($_GET['format'] ?? '') === 'atom') ? 'atom' : 'rss';

	$SelfURL = 'https://' . $_SERVER['HTTP_HOST'] . '/--/feeds/watch-list.php?id=' . rawurlencode($Token) . ($Type == 'atom' ? '&format=atom' : '');

	freshports_Feed($db, $Type, $SelfURL, $WatchListID);
