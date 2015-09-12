<?php
	#
	# $Id: news.php,v 1.3 2010-09-16 15:46:48 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#
	
	DEFINE('MAX_PORTS', 20);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$Debug = 0;

	GLOBAL $FreshPortsSlogan;
	GLOBAL $FreshPortsName;

	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['SERVER_NAME']);

	$HTML  = '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' . "\n";
	$HTML .= '        "http://my.netscape.com/publish/formats/rss-0.91.dtd">'      . "\n";
	$HTML .= '<rss version="0.91">'                                                . "\n";

	$HTML .= "\n";

	$HTML .= '<channel>'                                                                        . "\n";
	$HTML .= '  <title>' . "$FreshPortsName -- $FreshPortsSlogan" . '</title>'                  . "\n";
	$HTML .= '  <link>http://' . $ServerName . '/</link>'                                       . "\n";
	$HTML .= '  <description>The easiest place to find ports</description>'                     . "\n";
	$HTML .= '  <language>en-us</language>'                                                     . "\n";
	$HTML .= '  <copyright>Copyright ' . COPYRIGHTYEARS . ', DVL Software Limited.</copyright>' . "\n";

	$HTML .= "\n";

	$HTML .= '  <image>'                                                                       . "\n";
	$HTML .= '    <title>FreshPorts - The place for ports</title>'                             . "\n";
	$HTML .= '    <url>http://'  . $_SERVER["HTTP_HOST"] . '/images/freshports_mini.jpg</url>' . "\n";
	$HTML .= '    <link>http://' . $_SERVER["HTTP_HOST"] . '/</link>'                          . "\n";
	$HTML .= '    <width>128</width>'                                                          . "\n";
	$HTML .= '    <height>28</height>'                                                         . "\n";
	$HTML .= '    <description>FreshPorts - The place for ports</description>'                 . "\n";
	$HTML .= '  </image>'                                                                      . "\n";

	$sort ="commit_log.commit_date desc, commit_log.id asc, element.name, category, version";

	$MaxArticles = MAX_PORTS;
	$date        = 1;
	$committer   = 1;
	$time        = 1;

#	if (IsSet($_REQUEST['MaxArticles'])) $MaxArticles = pg_escape_string($_REQUEST['MaxArticles']);
#	if (IsSet($_REQUEST['date']))        $date        = pg_escape_string($_REQUEST['date']);
#	if (IsSet($_REQUEST['committer']))   $committer   = pg_escape_string($_REQUEST['committer']);
#	if (IsSet($_REQUEST['time']))        $time        = pg_escape_string($_REQUEST['time']);

#	phpinfo();
#	exit;

	if (!$MaxArticles || $MaxArticles < 1 || $MaxArticles > MAX_PORTS) {
		$MaxArticles = MAX_PORTS;
	}
	
	$MaxNumberOfPorts = $MaxArticles;

	$NewsAddenda = $_SERVER['DOCUMENT_ROOT'] . "/news.addenda";
	if (file_exists($NewsAddenda)) {
		# include any highlights here
		$fp = fopen($NewsAddenda, "r");
		if ($fp) {
			$NewsAddendaContents = fread($fp, filesize ($NewsAddenda));
			fclose($fp);
		}
		$HTML .= $NewsAddendaContents;
		$MaxNumberOfPorts--;
	}


$sql = "
SELECT PEC.*
FROM (
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
on LCPCLLCP.port_id = ports.id) AS LCPPORTS JOIN element
on LCPPORTS.element_id = element.id) AS PORTELEMENT JOIN categories
on PORTELEMENT.category_id = categories.id) AS PEC
order by commit_date_raw desc, category, port 
limit 30";

	if ($Debug) {
		echo $sql;
	}

	$result = pg_exec ($db, $sql);
	if (!$result) {
		echo '<pre>' . $sql . '</pre>error = ' . pg_errormessage();
		exit;
	}

	$numrows = pg_numrows($result);
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array ($result, $i);
		$HTML .= "\n";
		$HTML .= '  <item>' . "\n";

		$HTML .= '    <title>';
		if ($date == 1) {
			$HTML .= '[' . htmlentities($myrow['commit_date']) . '] ';
		}

		if ($time == 1) {
			$HTML .= '[' . htmlentities($myrow['commit_time']);
		}

		if ($committer == 1) {
			if ($time != 1) {
				$HTML .= '[';
			} else {
				$HTML .= ' ';
			}

			$HTML .= htmlentities($myrow['committer']);
		}

		if ($time == 1 || $committer == 1) {
			$HTML .= '] ';
		}

		$HTML .= htmlentities($myrow["category"]) . '/' . htmlentities($myrow["port"]) . ' - ' . htmlentities(freshports_PackageVersion($myrow["version"], $myrow["revision"], $myrow["epoch"]));

		$HTML .= '</title>'                                                                                       . "\n";

		$HTML .= '    <link>http://' . $ServerName . '/' . htmlentities($myrow["category"] . '/' . $myrow["port"]) . '/</link>' . "\n";
		$HTML .= '    <description>' . htmlspecialchars(trim($myrow["commit_description"])) . '</description>'    . "\n";

		$HTML .= '  </item>'                                                                                      . "\n";
	}

	$HTML .= '</channel>' . "\n";
	$HTML .= '</rss>' . "\n";

	echo '<?xml version="1.0"?>', "\n";
	echo $HTML;
?>
