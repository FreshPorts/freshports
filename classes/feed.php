<?php
	#
	# Copyright (c) 1998-2026 Dan Langille
	#
	# RSS 2.0 and Atom 1.0 feeds, written by laminas-feed - re #721
	# These are served from /--/feeds/; the old feeds in /backend/ use classes/newsfeed.php
	# and UniversalFeedCreator until they are redirected here.
	#

	DEFINE('FEED_MAX_WATCH_LIST_ITEMS', 100);	# commits in a watch list feed

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	# the FreeBSD packages (www/php-laminas-feed and its dependencies) install the
	# library sources without an autoloader: Laminas\Feed\Writer\Feed is in
	# /usr/local/share/pear/Laminas/Feed/Writer/Feed.php
	spl_autoload_register(function($Class) {
		if (strncmp($Class, 'Laminas\\', 8) == 0) {
			$File = '/usr/local/share/pear/' . str_replace('\\', '/', $Class) . '.php';
			if (is_file($File)) {
				require($File);
			}
		}
	});

	use Laminas\Feed\Writer\Feed;

# the SQL from newsfeed(), less the unused needs_refresh; that copy goes away with classes/newsfeed.php
function freshports_FeedQuery($dbh, $WatchListID, $BranchName, $Flavor) {
	if ($WatchListID) {
	# this is for newfeeds based on personal watch lists
	#
	# Only the latest FEED_MAX_WATCH_LIST_ITEMS commits: without a limit, a large watch list
	# gives every commit against every port on it (89,000 rows for a 2,200 port list).
	# K finds the keys and T keeps the latest; only then are the wide columns
	# (e.g. the commit message) fetched, so the sort stays small.
	# MATERIALIZED stops the planner from walking all of commit_log by date instead.
	$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '::WatchListID
	WITH K AS MATERIALIZED (
	SELECT CLP1.commit_log_id, CLP1.port_id, CL1.commit_date
	  FROM watch_list_element WLE1
	  JOIN ports            P1   ON P1.element_id     = WLE1.element_id
	  JOIN commit_log_ports CLP1 ON CLP1.port_id      = P1.id
	  JOIN commit_log       CL1  ON CL1.id            = CLP1.commit_log_id
	 WHERE WLE1.watch_list_id = $1),
	T AS (
	SELECT * FROM K ORDER BY commit_date DESC, commit_log_id, port_id LIMIT $2)
	select E.name 			as port,
		   P.id 				as id,
	       C.name 		as category,
	       C.id 		as category_id,
	       P.version 		as version,
	       P.revision 		as revision,
	       E.id 			as element_id,
           to_char(CL.commit_date - SystemTimeAdjust(), \'DD Mon\')  AS commit_date,
           to_char(CL.commit_date - SystemTimeAdjust(), \'HH24:MI\') AS commit_time,
           CL.commit_date       AS news_date,
           CL.description       AS commit_description,
           CLP.port_epoch as epoch,
           CL.committer,
           CL.committer_name,
           CL.committer_email,
           CL.author_name,
           CL.author_email,
           CL.commit_date as commit_date_sort,
           CL.message_id,
           CL.commit_hash_short
	  FROM T
	  JOIN commit_log_ports CLP ON CLP.commit_log_id = T.commit_log_id AND CLP.port_id = T.port_id
	  JOIN commit_log       CL  ON CL.id             = T.commit_log_id
	  JOIN ports            P   ON P.id              = T.port_id
	  JOIN element          E   ON E.id              = P.element_id
	  JOIN categories       C   ON C.id              = P.category_id
	ORDER BY CL.commit_date DESC, CL.id, E.name';

	   $params = array($WatchListID, FEED_MAX_WATCH_LIST_ITEMS);

	} else {
		# no WatchListID supplied
		switch ($Flavor) {
			case 'new':
				$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '::new
  SELECT C.name    AS category,
         E.name    AS port,
         E.status  AS status,
         P.forbidden,
         P.broken,
         P.deprecated,
         P.element_id                     AS element_id,
         P.version  AS version,
         P.revision AS revision,
         P.version                        AS ports_version,
         P.revision                       AS ports_revision,
         P.portepoch                      AS epoch,
         P.date_added                     AS news_date,
         P.short_description              AS short_description,
         P.category_id
    FROM (SELECT P1.*
            FROM ports_active            P1
           WHERE P1.date_added IS NOT NULL ORDER BY P1.date_added DESC LIMIT 20) AS P
    JOIN element    E   ON P.element_id  = E.id
    JOIN categories C   ON P.category_id = C.id
ORDER BY P.date_added DESC, E.name, category, version';
				$params = array();
				break;

			case 'broken':
				$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '::broken
  SELECT C.name    AS category,
         E.name    AS port,
         E.status  AS status,
         P.forbidden,
         P.broken,
         P.deprecated,
         P.element_id                     AS element_id,
         CASE when CLP.port_version  IS NULL then P.version  else CLP.port_version  END as version,
         CASE when CLP.port_revision IS NULL then P.revision else CLP.port_revision END AS revision,
         P.version                        AS ports_version,
         P.revision                       AS ports_revision,
         P.portepoch                      AS epoch,
         date_part(\'epoch\', P.date_added) AS date_added,
         P.short_description              AS short_description,
         P.category_id,
         CLP.port_version  AS clp_version,
         CLP.port_revision AS clp_revision,
         CL.id     AS commit_log_id,
         CL.commit_date       AS news_date,
         CL.message_subject,
         CL.message_id,
         CL.commit_hash_short,
         CL.committer,
         CL.committer_name,
         CL.committer_email,
         CL.author_name,
         CL.author_email,
         CL.description       AS commit_description,
         to_char(CL.commit_date - SystemTimeAdjust(), \'DD Mon\')  AS commit_date,
         to_char(CL.commit_date - SystemTimeAdjust(), \'HH24:MI\') AS commit_time,
         CL.encoding_losses
    FROM (SELECT P1.*
            FROM ports_active            P1
           WHERE P1.broken IS NOT NULL) AS P
    JOIN commit_log           CL  ON P.last_commit_id  = CL.id
    JOIN commit_log_ports     CLP ON CLP.commit_log_id = CL.id AND P.id = CLP.port_id
    JOIN element              E   ON P.element_id      = E.id
    JOIN categories           C   ON P.category_id     = C.id
ORDER BY CL.commit_date DESC, CL.id ASC, E.name, category, version';
				$params = array();
				break;

			case 'vuln':
				$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '::vuln
  SELECT C.name    AS category,
         P.name    AS port,
         P.status  AS status,
         P.forbidden,
         P.broken,
         P.deprecated,
         P.element_id                     AS element_id,
         P.version  AS version,
         P.revision AS revision,
         P.version                        AS ports_version,
         P.revision                       AS ports_revision,
         P.portepoch                      AS epoch,
         date_part(\'epoch\', P.date_added) AS date_added,
         CL.commit_date                   AS news_date,
         P.short_description              AS short_description,
         P.category_id
         FROM ports_active            P
         JOIN ports_vulnerable PV ON PV.current    > 0            AND PV.port_id = P.id
         JOIN categories       C  ON P.category_id = C.id
         JOIN commit_log       CL ON CL.id         = P.last_commit_id
ORDER BY CL.commit_date DESC, P.name;
';
				$params = array();
				break;

			default:
			        # the goal, the latest 100 commits against ports, and one port from that commit.
			        # We start with the last 200 commits added to the system, because we're likely to get 100 ports from that.
			        # The lateral join is to get just one port from the commit_log_ports table
			        # we order by port.id so we get the same results on each query
				$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '::default
 SELECT  C.name    AS category,
         E.name    AS port,
         E.status  AS status,
         CLP.forbidden,
         CLP.broken,
         CLP.deprecated,
         CLP.element_id,
         CLP.version,
         CLP.revision,
         CLP.ports_version,
         CLP.ports_revision,
         CLP.epoch,
         CLP.date_added,
         CLP.short_description,
         CLP.category_id,
         CLP.clp_version,
         CLP.clp_revision,
         CL.id     AS commit_log_id,
         CL.commit_date       AS news_date,
         CL.message_subject,
         CL.message_id,
         CL.commit_hash_short,
         CL.committer,
         CL.committer_name,
         CL.committer_email,
         CL.author_name,
         CL.author_email,
         CL.description       AS commit_description,
         to_char(CL.commit_date - SystemTimeAdjust(), \'DD Mon\')  AS commit_date,
         to_char(CL.commit_date - SystemTimeAdjust(), \'HH24:MI\') AS commit_time,
         CL.encoding_losses
    FROM (select cl.*
            from commit_log cl
           where exists (select *
                           from commit_log_ports clp
                           join commit_log_branches clb on clp.commit_log_id = clb.commit_log_id
                           join system_branch sb on sb.id = clb.branch_id
                          where clp.commit_log_id = cl.id and sb.branch_name = $1)
           order by cl.commit_date desc limit 100 ) AS CL
    JOIN LATERAL ( select CLP1.commit_log_id, P.forbidden, P.broken, P.deprecated, P.element_id,
                          CASE when CLP1.port_version  IS NULL then P.version  else CLP1.port_version  END as version,
                          CASE when CLP1.port_revision IS NULL then P.revision else CLP1.port_revision END AS revision,
                          P.version                        AS ports_version,
                          P.revision                       AS ports_revision,
                          P.portepoch                      AS epoch,
                          date_part(\'epoch\', P.date_added) AS date_added,
                          P.short_description              AS short_description,
                          P.category_id,
                          CLP1.port_version  AS clp_version,
                          CLP1.port_revision AS clp_revision
                     from commit_log_ports CLP1, ports P
                    where CLP1.commit_log_id = CL.id
                      and CLP1.port_id   = P.id
                  ORDER BY P.id
                     LIMIT 1) AS CLP on true
    JOIN element             E   ON CLP.element_id    = E.id
    JOIN categories          C   ON CLP.category_id   = C.id
    ORDER BY CL.commit_date desc
	LIMIT 500';

			$params = array($BranchName);

		} # switch flavor
	} # WatchListID

	return array($sql, $params);
}

# Build the feed as XML. $Type is 'rss' or 'atom'; $SelfURL is the feed's own URL.
function freshports_FeedXML($dbh, $Type, $SelfURL, $WatchListID, $BranchName, $Flavor) {
	$Host = 'https://' . $_SERVER['HTTP_HOST'];

	if ($WatchListID) {
		$Title = 'FreshPorts watch list';
	} else {
		switch ($Flavor) {
			case 'new':    $Title = 'FreshPorts new ports';        break;
			case 'broken': $Title = 'FreshPorts broken ports';     break;
			case 'vuln':   $Title = 'FreshPorts vulnerable ports'; break;
			default:       $Title = 'FreshPorts news';             break;
		}
		if ($BranchName != BRANCH_HEAD) {
			$Title .= ' (' . $BranchName . ')';
		}
	}

	list($sql, $params) = freshports_FeedQuery($dbh, $WatchListID, $BranchName, $Flavor);
	$result = pg_query_params($dbh, $sql, $params);
	if (!$result) {
		syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ' sql error ' . pg_last_error($dbh));
		return false;
	}

	$feed = new Feed();
	$feed->setTitle($Title);
	$feed->setDescription('The place for ports');
	$feed->setLink($Host . '/');
	$feed->setFeedLink($SelfURL, $Type);	# atom:link rel="self" in RSS 2.0, link rel="self" in Atom
	$feed->setId($SelfURL);			# Atom: otherwise the site URL, the same for every feed
	$feed->setLanguage('en-us');
	$feed->setCopyright('Copyright ' . COPYRIGHTYEARS . ' ' . COPYRIGHTHOLDER);
	$feed->setImage(array(
		'uri'   => $Host . '/images/freshports_mini.jpg',
		'link'  => $Host . '/',
		'title' => $Title,
	));
	if ($Type == 'atom') {
		# Atom needs an author for entries which have none (new, broken, vuln).
		# RSS 2.0 has no channel author; laminas-feed would write an invalid <author> there.
		$feed->addAuthor(array('name' => 'FreshPorts', 'uri' => $Host . '/'));
	}

	$Newest = 0;
	while ($myrow = pg_fetch_array($result)) {
		switch ($Flavor) {
			case 'broken':
			case 'new':
			case 'vuln':
				$link        = freshports_Port_URL($dbh, $myrow['category'], $myrow['port'], $BranchName);
				$description = $myrow['short_description'];
				$author      = null;
				break;

			default:
				$link        = freshports_Commit_Link_Port_URL(rawurlencode($myrow['message_id']), rawurlencode($myrow['category']), rawurlencode($myrow['port']));
				$description = $myrow['commit_description'];
				$author      = array('name'  => $myrow['committer_name'] ?: $myrow['committer'],
				                     'email' => $myrow['committer'] . '@FreeBSD.org');
				break;
		}

		$entry = $feed->createEntry();
		$entry->setTitle($myrow['category'] . '/' . $myrow['port'] . ' - ' . freshports_PackageVersion($myrow['version'], $myrow['revision'], $myrow['epoch']));
		$entry->setLink($link);
		$entry->setId($link);
		$entry->setDescription(nl2br(htmlspecialchars(trim($description ?? ''))));
		if ($author) {
			$entry->addAuthor($author);
		}

		$Date = empty($myrow['news_date']) ? false : strtotime($myrow['news_date']);
		if ($Date === false) {
			$Date = time();
		}
		$entry->setDateModified($Date);
		$Newest = max($Newest, $Date);

		$feed->addEntry($entry);
	}

	$feed->setDateModified($Newest ?: time());

	return $feed->export($Type);
}

# Send the feed, from the cache if it is fresh. Exits.
#
# The cache files are in NEWS_DIRECTORY, which fp-listen empties when a commit arrives.
# The feed is held in memory and sent from there, so a cache file deleted while
# we use it does no harm (cf. the useCached() race in newsfeed()).
function freshports_Feed($dbh, $Type, $SelfURL, $WatchListID = NO_WATCH_LIST_ID, $BranchName = BRANCH_HEAD, $Flavor = '') {
	$Key       = implode('.', array($Type, $WatchListID, $BranchName, $Flavor));
	$CacheFile = NEWS_DIRECTORY . '/feed.' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $Key) . '.xml';

	$XML       = false;
	$CacheTime = @filemtime($CacheFile);
	if ($CacheTime !== false && time() - $CacheTime < NEWSFEED_REFRESH_SECONDS) {
		$XML = @file_get_contents($CacheFile);
	}

	if ($XML === false || $XML === '') {
		try {
			$XML = freshports_FeedXML($dbh, $Type, $SelfURL, $WatchListID, $BranchName, $Flavor);
		} catch (Laminas\Feed\Exception\ExceptionInterface $e) {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ' ' . $SelfURL . ' ' . $e->getMessage());
			$XML = false;
		}

		if ($XML === false) {
			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Retry-After: 60');
			exit;
		}

		# write a temporary file and rename it, so nobody reads a partial feed
		$CacheTime = time();
		$Temp      = @tempnam(NEWS_DIRECTORY, 'feed-tmp.');
		if ($Temp !== false) {
			if (@file_put_contents($Temp, $XML) === false || !@rename($Temp, $CacheFile)) {
				@unlink($Temp);
			}
		}
	}

	freshports_ConditionalGetUnix($CacheTime, '', false); # a feed has no announcements

	header('Content-Type: application/' . $Type . '+xml; charset=UTF-8');
	echo $XML;
	exit;
}
