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


	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Element/FeedDate.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Element/FeedHtmlField.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Element/HtmlDescribable.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Element/FeedImage.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Element/FeedItem.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/FeedCreator.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/AtomCreator03.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/HTMLCreator.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/JSCreator.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/MBOXCreator.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/OPMLCreator.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/PIECreator01.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/RSSCreator091.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/RSSCreator10.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/Creator/RSSCreator20.php'); 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/lib/UniversalFeedCreator.php'); 
	
function newsfeed($db, $Format, $WatchListID = 0, $BranchName = BRANCH_HEAD) {

	$WatchListID = pg_escape_string($WatchListID);
	$Format      = pg_escape_string($Format);

	$PHP_SELF = $_SERVER['PHP_SELF'];

	# potential for exploitation here, with $Format
	if ($WatchListID) {
		define('NEWSFEEDCACHE', $_SERVER['DOCUMENT_ROOT'] . '/../dynamic/caching/news/news.' . $WatchListID . '.'  . $Format . '.' . $BranchName . '.xml');
	} else {
		define('NEWSFEEDCACHE', $_SERVER['DOCUMENT_ROOT'] . '/../dynamic/caching/news/news.' . $Format . '.' . $BranchName. '.xml');
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
	   AND WLE.watch_list_id = " . pg_escape_string($WatchListID) . "
	ORDER BY commit_date_sort DESC, CL.id ASC, E.name, category, version
	LIMIT 100";
	} else {
	$sql = "
SELECT PORTELEMENT.*,
       categories.name AS category
FROM (
SELECT LCPPORTS.*,
       element.name    AS port,
       element.status  AS status

FROM (
SELECT LCPCLLCP.*,
       ports.forbidden,
       ports.broken,
       ports.deprecated,
       ports.element_id                     AS element_id,
       CASE when clp_version  IS NULL then ports.version  else clp_version  END as version,
       CASE when clp_revision IS NULL then ports.revision else clp_revision END AS revision,
       ports.version                        AS ports_version,
       ports.revision                       AS ports_revision,
       ports.portepoch                      AS epoch,
       date_part('epoch', ports.date_added) AS date_added,
       ports.short_description              AS short_description,
       ports.category_id
FROM (
 SELECT LCPCL.*, 
         port_id,
         commit_log_ports.port_version  AS clp_version,
         commit_log_ports.port_revision AS clp_revision,
         commit_log_ports.needs_refresh AS needs_refresh
    FROM 
   (SELECT commit_log.id     AS commit_log_id, 
           commit_date       AS commit_date_raw,
           message_subject,
           message_id,
           committer,
           description       AS commit_description,
           to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon')  AS commit_date,
           to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI') AS commit_time,
           encoding_losses
     FROM commit_log JOIN
               (SELECT LCP.commit_log_id
                  FROM latest_commits_ports LCP JOIN commit_log_branches CLB ON LCP.commit_log_id = CLB.commit_log_id
                                     JOIN system_branch SB ON SB.branch_name = '$BranchName' AND SB.id = CLB.branch_id
              ORDER BY LCP.commit_date DESC
                 LIMIT $MaxNumberOfPorts) AS LCP
           ON commit_log.id = LCP.commit_log_id) AS LCPCL JOIN commit_log_ports
                         ON commit_log_ports.commit_log_id = LCPCL.commit_log_id
                         AND commit_log_ports.commit_log_id > latest_commits_ports_anchor()) AS LCPCLLCP JOIN ports
ON LCPCLLCP.port_id = ports.id) AS LCPPORTS JOIN element
ON LCPPORTS.element_id = element.id) AS PORTELEMENT JOIN categories
ON PORTELEMENT.category_id = categories.id
ORDER BY commit_date_raw desc, category, port 
LIMIT 30";
	}
	
#	echo "<pre>$sql</pre>";
#	exit;

	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['SERVER_NAME']);
	
	$result = pg_query($db, $sql);
	while ($myrow = pg_fetch_array($result)) {
		$item = new FeedItem();

		$CommitURL = freshports_Commit_Link_Port_URL($myrow['message_id'], $myrow['category'], $myrow['port']);

		$item->title = $myrow['category'] . '/' . $myrow["port"] . ' - ' . freshports_PackageVersion($myrow['version'], $myrow['revision'], $myrow['epoch']);
		$item->link  = $CommitURL;
		$item->description = trim($myrow['commit_description']);

		//optional
		//item->descriptionTruncSize = 500;
		$item->descriptionHtmlSyndicated = true;
	
		$item->date   = strtotime($myrow['commit_date_raw']);
		$item->source = $_SERVER['HTTP_HOST']; 
		$item->author = $myrow['committer'] . '@FreeBSD.org (' . $myrow['committer'] . ')';
		$item->guid   = $CommitURL; 

		$rss->addItem($item); 
	} 

	// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1, MBOX, OPML, ATOM0.3, HTML, JS

	return $rss->saveFeed($Format, NEWSFEEDCACHE);
}
