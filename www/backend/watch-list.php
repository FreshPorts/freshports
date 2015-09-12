<?php
	#
	# $Id: watch-list.php,v 1.3 2007-01-18 13:35:55 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#
	
	DEFINE('MAX_PORTS', 20);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_lists.php');

	$Debug = 0;

function DisplayNewsFeed($db, $token) {
	$Debug = 0;


	GLOBAL $FreshPortsSlogan;
	GLOBAL $FreshPortsName;

	$wlid  = freshports_WatchListVerifyToken($db, $token);
	if ($wlid == '') {
		syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . 
			' watch list token requested by ' . $_SERVER['REMOTE_ADDR'] . 
			' not found ' . $token);
		header('HTTP/1.1 404 NOT FOUND');
		exit; 
	}

	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['SERVER_NAME']);

	header('Content-type: application/rss+xml');

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
	$HTML .= '    <url>http://'  . $ServerName . '/images/freshports_mini.jpg</url>' . "\n";
	$HTML .= '    <link>http://' . $ServerName . '/</link>'                          . "\n";
	$HTML .= '    <width>128</width>'                                                          . "\n";
	$HTML .= '    <height>28</height>'                                                         . "\n";
	$HTML .= '    <description>FreshPorts - The place for ports</description>'                 . "\n";
	$HTML .= '  </image>'                                                                      . "\n";

	$sort ="commit_date_sort DESC, CL.id ASC, E.name, category, version";

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
	select E.name 			as port, 
		   P.id 				as id, 
	       C.name 		as category, 
	       C.id 		as category_id, 
	       P.version 		as version, 
	       P.revision 		as revision, 
	       E.id 			as element_id,
           to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon')  AS commit_date,
           to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI') AS commit_time,
           CL.description       AS commit_description,
           CLP.port_epoch as epoch,
           CL.committer,
           CL.commit_date as commit_date_sort
	  FROM watch_list_element WLE, element E, categories C, ports P,
           commit_log CL, commit_log_ports CLP
	 WHERE CLP.commit_log_id = CL.id
       AND CLP.port_id       = P.id
       AND P.element_id      = WLE.element_id
	   AND P.element_id      = E.id
	   AND P.category_id     = C.id 
	   AND WLE.watch_list_id = $wlid";
	
	$sql .= " order by $sort ";
	$sql .= " LIMIT 100 ";

#	syslog (LOG_NOTICE, $wlid . ' ' . $sort);
	
	if ($Debug) {
	   echo "<pre>$sql</pre>";
	}
	
	$result = pg_exec($db, $sql);
	if (!$result) {
		echo pg_errormessage();
	}


	# oh really?  Why two pg_exec?
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
			$HTML .= '[' . $myrow['commit_date'] . '] ';
		}

		if ($time == 1) {
			$HTML .= '[' . $myrow['commit_time'];
		}

		if ($committer == 1) {
			if ($time != 1) {
				$HTML .= '[';
			} else {
				$HTML .= ' ';
			}

			$HTML .= $myrow['committer'];
		}

		if ($time == 1 || $committer == 1) {
			$HTML .= '] ';
		}

		$HTML .= $myrow["category"] . '/' . $myrow["port"] . ' - ' . freshports_PackageVersion($myrow["version"], $myrow["revision"], $myrow["epoch"]);

		$HTML .= '</title>'                                                                                       . "\n";

		$HTML .= '    <link>http://' . $ServerName . '/' . $myrow["category"] . '/' . $myrow["port"] . '/</link>' . "\n";
		$HTML .= '    <description>' . htmlspecialchars(trim($myrow["commit_description"])) . '</description>'    . "\n";

		$HTML .= '  </item>'                                                                                      . "\n";
	}

	$HTML .= '</channel>' . "\n";
	$HTML .= '</rss>' . "\n";

	echo '<?xml version="1.0"?>', "\n";
	echo $HTML;
}

function DisplayWatchListNewsFeeds($db, $UserID) {
	$Debug = 0;

	echo '<h1>These are your newsfeeds</h1>';
	$WatchLists = new WatchLists($db);
	$NumRows = $WatchLists->Fetch($UserID);

	if ($Debug) {
		echo "$NumRows rows found!<br>";
		echo "selected = '$selected'<br>";
	}

	$HTML = '';

	if ($NumRows) {
		for ($i = 0; $i < $NumRows; $i++) {
			$WatchList = $WatchLists->FetchNth($i);
			$URL = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?id=' . $WatchList->token;
			$HTML .= '<a href="' . $URL . '">' . $WatchList->name . '</a><br>';
		}
	}

	$HTML .= '</select>';

	echo $HTML;	
}

	if (IsSet($_REQUEST['id'])) {
		$token = $_REQUEST['id'];
	}

	if (IsSet($token)) {
		DisplayNewsFeed($db, $token);
	} else {
		// if we don't know who they are, we'll make sure they login first
		if (!$visitor) {
			header("Location: /login.php");
			exit;  /* Make sure that code below does not get executed when we redirect. */
		}

		DisplayWatchListNewsFeeds($db, $User->id);
	}

?>
