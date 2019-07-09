<?php
	#
	# $Id: newsfeed.php,v 1.7 2013-02-15 02:09:22 dan Exp $
	#
	# Copyright (c) 1998-2007 DVL Software Limited
	#

	DEFINE('MAX_PORTS', 20);
	define('TIME_ZONE', 'UTC');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');


	require_once('/usr/local/share/UniversalFeedCreator/lib/Element/FeedDate.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Element/FeedHtmlField.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Element/HtmlDescribable.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Element/FeedImage.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Element/FeedItem.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/FeedCreator.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/AtomCreator03.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/HTMLCreator.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/JSCreator.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/MBOXCreator.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/OPMLCreator.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/PIECreator01.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/RSSCreator091.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/RSSCreator10.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/Creator/RSSCreator20.php'); 
	require_once('/usr/local/share/UniversalFeedCreator/lib/UniversalFeedCreator.php'); 
	
function newsfeed($db, $Format, $WatchListID = 0, $BranchName = BRANCH_HEAD, $Flavor = '') { # $OrderBy = '', $Where = '') {

	$WatchListID = pg_escape_string($WatchListID);
	$Format      = pg_escape_string($Format);
	$Flavor      = pg_escape_string($Flavor);

	$PHP_SELF = $_SERVER['PHP_SELF'];

	# potential for exploitation here, with $WatchListID, $BranchName, $Format & $Flavor
	if ($WatchListID) {
		define('NEWSFEEDCACHE', NEWS_DIRECTORY . '/news.' . $WatchListID . '.'  . $Format . '.' . $BranchName . '.xml');
	} else {
		if (empty($Flavor)) {
			define('NEWSFEEDCACHE', NEWS_DIRECTORY . '/news.' . $Format . '.' . $BranchName . '.xml');
		} else {
			define('NEWSFEEDCACHE', NEWS_DIRECTORY . '/news.' . $Format . '.' . $BranchName . '.' . $Flavor . '.xml');
		}
	}

	$MaxNumberOfPorts = pg_escape_string(MAX_PORTS);

	$rss = new UniversalFeedCreator(); 

	# NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE
	#
	# this next call may wind up using the cached and the 
	# rest of the function may never be use executed.
	#
	# NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE NOTE

	$rss->useCached($Format, NEWSFEEDCACHE, NEWSFEED_REFRESH_SECONDS);

	$rss->title          = 'FreshPorts news'; 
	$rss->description    = 'The place for ports'; 
	$rss->syndicationURL = $_SERVER['HTTP_HOST'] . '/' .  $PHP_SELF;

	$rss->editor    = 'editor@freshports.org (The Editor)';
	$rss->webmaster = 'webmaster@freshports.org (The Webmaster)';
	$rss->language  = 'en-us';
	$rss->copyright = 'Copyright ' . COPYRIGHTYEARS . ' ' . COPYRIGHTHOLDER;

	//optional
	//$rss->descriptionTruncSize = 500;
	//$rss->descriptionHtmlSyndicated = true;
	//$rss->xslStyleSheet = 'http://feedster.com/rss20.xsl';

	$rss->link    = 'http://' . $_SERVER['HTTP_HOST']; 
	$rss->feedURL = 'http://' . $_SERVER['HTTP_HOST'] . '/' .  $PHP_SELF; 

	$image = new FeedImage(); 
	$image->title       = 'FreshPorts news'; 
	$image->url         = 'http://' . $_SERVER['HTTP_HOST'] .'/images/freshports_mini.jpg'; 
	$image->link        = 'http://' . $_SERVER['HTTP_HOST']; ; 
	$image->description = 'Feed provided by FreshPorts. Click to visit.'; 

	//optional
	$image->descriptionTruncSize      = 500;
	$image->descriptionHtmlSyndicated = true;

	$rss->image = $image;

	$MyMaxArticles = 10;
	if (!IsSet($MaxArticles) || !$MaxArticles || $MaxArticles < 1 || $MaxArticles > $MyMaxArticles) {
	    $MaxArticles = $MyMaxArticles;
	}

	if ($WatchListID) {
	# this is for newfeeds based on personal watch lists
	$sql = "
	select E.name 			as port, 
		   P.id 				as id, 
	       C.name 		as category, 
	       C.id 		as category_id, 
	       P.version 		as version, 
	       P.revision 		as revision, 
	       E.id 			as element_id,
           to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon')  AS commit_date,
           to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI') AS commit_time,
           commit_date          AS commit_date_raw,
           CL.description       AS commit_description,
           CLP.port_epoch as epoch,
           CL.committer,
           CL.commit_date as commit_date_sort,
           CL.message_id
	  FROM watch_list_element WLE, element E, categories C, ports P,
           commit_log CL, commit_log_ports CLP
	 WHERE CLP.commit_log_id = CL.id
       AND CLP.port_id       = P.id
       AND P.element_id      = WLE.element_id
	   AND P.element_id      = E.id
	   AND P.category_id     = C.id 
	   AND WLE.watch_list_id = " . pg_escape_string($WatchListID) .  ' ';
	   
	} else {
		if ($BranchName == BRANCH_HEAD) {
			$BranchExpression = pg_escape_literal('/ports/' . BRANCH_HEAD .'/%');
		} else {
			$BranchExpression =  pg_escape_literal('/ports/branches/' . $BranchName . '/%');
		}

		switch ($Flavor) {
			case 'new':
				$sql = "
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
         date_part('epoch', P.date_added) AS date_added,
         P.short_description              AS short_description,
         P.category_id
    FROM (SELECT P1.* 
            FROM ports            P1
            JOIN element_pathname EP ON P1.element_id = EP.element_id AND EP.pathname LIKE '/ports/head/%'
           WHERE P1.date_added IS NOT NULL ORDER BY P1.date_added DESC LIMIT 20) AS P
    JOIN element    E   ON P.element_id  = E.id
    JOIN categories C   ON P.category_id = C.id
ORDER BY P.date_added DESC, E.name, category, version";
				break;

			case 'broken':
				$sql = "
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
         date_part('epoch', P.date_added) AS date_added,
         P.short_description              AS short_description,
         P.category_id,
         CLP.port_version  AS clp_version,
         CLP.port_revision AS clp_revision,
         CLP.needs_refresh AS needs_refresh,
         CL.id     AS commit_log_id, 
         CL.commit_date       AS commit_date_raw,
         CL.message_subject,
         CL.message_id,
         CL.committer,
         CL.description       AS commit_description,
         to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon')  AS commit_date,
         to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI') AS commit_time,
         CL.encoding_losses
    FROM (SELECT P1.* 
            FROM ports            P1
            JOIN element_pathname EP ON P1.element_id = EP.element_id AND EP.pathname LIKE '/ports/head/%'
           WHERE P1.broken IS NOT NULL
             AND P1.status = 'A') AS P
    JOIN commit_log           CL  ON P.last_commit_id  = CL.id 
    JOIN commit_log_ports     CLP ON CLP.commit_log_id = CL.id AND P.id = CLP.port_id
    JOIN element              E   ON P.element_id      = E.id
    JOIN categories           C   ON P.category_id     = C.id
ORDER BY CL.commit_date DESC, CL.id ASC, E.name, category, version";
				break;
				
                        case 'vuln':
                                $sql = "
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
         date_part('epoch', P.date_added) AS date_added,
         P.short_description              AS short_description,
         P.category_id
         FROM ports            P
         JOIN ports_vulnerable PV ON PV.current    > 0            AND PV.port_id = P.id
         JOIN element_pathname EP ON EP.element_id = P.element_id AND EP.pathname like '/ports/head/%'
         JOIN element          E  ON P.element_id  = E.id         AND E.status = 'A'
         JOIN categories       C  ON P.category_id = C.id
         JOIN commit_log       CL ON CL.id         = P.last_commit_id
ORDER BY CL.commit_date;
";
                                break;

			default:
				$sql = "
 SELECT C.name    AS category,
         E.name    AS port,
         E.status  AS status,
         P.forbidden,
         P.broken,
         P.deprecated,
         P.element_id                     AS element_id,
         CASE when CL.port_version  IS NULL then P.version  else CL.port_version  END as version,
         CASE when CL.port_revision IS NULL then P.revision else CL.port_revision END AS revision,
         P.version                        AS ports_version,
         P.revision                       AS ports_revision,
         P.portepoch                      AS epoch,
         date_part('epoch', P.date_added) AS date_added,
         P.short_description              AS short_description,
         P.category_id,
         CL.port_version  AS clp_version,
         CL.port_revision AS clp_revision,
         CL.needs_refresh AS needs_refresh,
         CL.id     AS commit_log_id, 
         CL.commit_date       AS commit_date_raw,
         CL.message_subject,
         CL.message_id,
         CL.committer,
         CL.description       AS commit_description,
         to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon')  AS commit_date,
         to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI') AS commit_time,
         CL.encoding_losses
    FROM (SELECT CL1.*, CLP.port_version, CLP.port_revision, CLP.needs_refresh, CLP.port_id
            FROM commit_log CL1
            JOIN commit_log_ports    CLP ON CL1.id            = CLP.commit_log_id
            JOIN commit_log_branches CLB ON CLP.commit_log_id = CLB.commit_log_id
            JOIN system_branch       SB  ON SB.branch_name    = " . pg_escape_literal($BranchName) . " AND SB.id = CLB.branch_id
        ORDER BY CL1.id DESC
           LIMIT 100) CL
    JOIN ports                P   ON CL.port_id        = P.id
    JOIN element              E   ON P.element_id      = E.id
    JOIN categories           C   ON P.category_id     = C.id
ORDER BY CL.commit_date DESC, CL.id ASC, E.name, category, version LIMIT 20";
		} # switch flavor
	} # WatchListID	

#	echo "<pre>$sql</pre>";

#	exit;

	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['HTTP_HOST']);
	
	# get the results
	$result = pg_query($db, $sql);
	if (!$result) {
		syslog(LOG_ERR, 'sql error ' . pg_result_error($result));

		die('We broke the SQL, sorry');
	}

	# build the information for the feed.
	while ($myrow = pg_fetch_array($result)) {
		$item = new FeedItem();

		switch ($Flavor) {
			case 'broken':
			case 'new':
			case 'vuln':
				# this is a relative link
				$link        = freshports_Port_URL($myrow['category'], $myrow['port'], $BranchName);;
				$date        = $myrow['date_added'];
				$author      = $myrow['maintainer'];
				$description = $myrow['short_description'];
				break;
				
			default:
				$link        = freshports_Commit_Link_Port_URL($myrow['message_id'], $myrow['category'], $myrow['port']);
				$date        = $myrow['commit_date_raw'];
				$author      = $myrow['committer'] . '@FreeBSD.org (' . $myrow['committer'] . ')';
				$description = $myrow['commit_description'];
				break;
		}

		$item->title = $myrow['category'] . '/' . $myrow["port"] . ' - ' . freshports_PackageVersion($myrow['version'], $myrow['revision'], $myrow['epoch']);
		$item->link  = $link;
		$item->description = trim($description);

		//optional
		//item->descriptionTruncSize = 500;
		$item->descriptionHtmlSyndicated = true;
	
		$item->date   = strtotime($date);
		$item->source = $_SERVER['HTTP_HOST']; 
		$item->author = $author;
		$item->guid   = $link; 

		$rss->addItem($item); 
	} 

	// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1, MBOX, OPML, ATOM0.3, HTML, JS

	return $rss->saveFeed($Format, NEWSFEEDCACHE);
}
