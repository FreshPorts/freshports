<?php
	#
	# $Id: newsfeed.php,v 1.7 2013-02-15 02:09:22 dan Exp $
	#
	# Copyright (c) 1998-2007 DVL Software Limited
	#

	DEFINE('MAX_PORTS', 20);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');


	require_once($_SERVER['DOCUMENT_ROOT'] . '/../feedcreator/feedcreator.class.php'); 
	
function newsfeed($db, $Format) {

	$PHP_SELF = $_SERVER['PHP_SELF'];

	# potential for exploitation here, with $Format

	define('NEWSFEEDCACHE', $_SERVER['DOCUMENT_ROOT'] . '/../dynamic/caching/news/news.' . $Format . '.xml');

	$MaxNumberOfPorts = MAX_PORTS;

	$rss = new UniversalFeedCreator(); 

	# this next call may wind up using the cached and the 
	# rest of the function may never be use executed.
	#
	$rss->useCached($Format, NEWSFEEDCACHE, time());

	$rss->title          = 'FreshPorts news'; 
	$rss->description    = 'The place for ports'; 
	$rss->syndicationURL = $_SERVER['HTTP_HOST'] . '/' .  $PHP_SELF;

	$rss->editor    = 'editor@freshports.org (The Editor)';
	$rss->webmaster = 'webmaster@freshports.org (The Webmaster)';
	$rss->language  = 'en-us';
	$rss->copyright = 'Copyright 1998-2013 DVL Software Limited';

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
               (SELECT latest_commits_ports.commit_log_id
                   FROM latest_commits_ports
               ORDER BY latest_commits_ports.commit_date DESC
                 LIMIT $MaxNumberOfPorts) AS LCP
           ON commit_log.id = LCP.commit_log_id) AS LCPCL JOIN commit_log_ports
                         ON commit_log_ports.commit_log_id = LCPCL.commit_log_id
                         AND commit_log_ports.commit_log_id > latest_commits_ports_anchor()) AS LCPCLLCP JOIN ports
ON LCPCLLCP.port_id = ports.id) AS LCPPORTS JOIN element
ON LCPPORTS.element_id = element.id) AS PORTELEMENT JOIN categories
ON PORTELEMENT.category_id = categories.id
ORDER BY commit_date_raw desc, category, port 
LIMIT 30";


	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['SERVER_NAME']);
	
	$result = pg_query($db, $sql);
	while ($myrow = pg_fetch_array($result)) {
		$item = new FeedItem();

		$CommitURL = freshports_Commit_Link_Port_URL($myrow['message_id'], $myrow['category'], $myrow['port']);

		$item->title = $myrow["category"] . '/' . $myrow["port"] . ' - ' . freshports_PackageVersion($myrow["version"], $myrow["revision"], $myrow["epoch"]);
		$item->link  = $CommitURL;
		if ($Format == 'rss0.91') {
			$item->description = trim($myrow["commit_description"]);
		} else {
			$item->description = htmlentities(trim($myrow["commit_description"]));
		}

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

?>