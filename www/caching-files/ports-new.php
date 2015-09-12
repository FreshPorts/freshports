<?php
	#
	# $Id: ports-new.php,v 1.2 2006-12-17 12:06:22 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
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
SELECT PA.name AS port,
       PA.category,
       PA.short_description,
       to_char(PA.date_added - SystemTimeAdjust(), 'DD Mon')  AS date_added_date,
       to_char(PA.date_added - SystemTimeAdjust(), 'HH24:MI') AS date_added_time,
       PA.maintainer,
       PA.version,
       PA.revision,
       PA.portepoch as epoch
  FROM ports_active PA
 WHERE PA.date_added is not NULL
ORDER BY PA.date_added desc
LIMIT 30";

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
			$HTML .= '[' . $myrow['date_added_date'] . '] ';
		}

		if ($time == 1) {
			$HTML .= '[' . $myrow['date_added_time'];
		}

		if ($committer == 1) {
			if ($time != 1) {
				$HTML .= '[';
			} else {
				$HTML .= ' ';
			}

			$HTML .= $myrow['maintainer'];
		}

		if ($time == 1 || $committer == 1) {
			$HTML .= '] ';
		}

		$HTML .= $myrow["category"] . '/' . $myrow["port"] . ' - ' . freshports_PackageVersion($myrow["version"], $myrow["revision"], $myrow["epoch"]);

		$HTML .= '</title>'                                                                                       . "\n";

		$HTML .= '    <link>http://' . $ServerName . '/' . $myrow["category"] . '/' . $myrow["port"] . '/</link>' . "\n";
		$HTML .= '    <description>' . htmlspecialchars(trim($myrow["short_description"])) . '</description>'    . "\n";

		$HTML .= '  </item>'                                                                                      . "\n";
	}

	$HTML .= '</channel>' . "\n";
	$HTML .= '</rss>' . "\n";

	header('Content-type: text/xml');

	echo '<?xml version="1.0"?>', "\n";
	echo $HTML;
?>
