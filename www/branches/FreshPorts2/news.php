<?
	# $Id: news.php,v 1.1.2.8 2002-12-08 03:25:01 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	$Debug=0;

	GLOBAL $FreshPortsSlogan;
	GLOBAL $FreshPortsName;

	$ServerName = str_replace("freshports", "FreshPorts", $_SERVER["SERVER_NAME"]);

	$MyMaxArticles = 10;

	if (!$MaxArticles || $MaxArticles < 1 || $MaxArticles > $MyMaxArticles) {
		$MaxArticles = $MyMaxArticles;
	}

	if ($MaxArticles == $MyMaxArticles) {
		$OutputFromCach = 1;
	} else {
		$OutputFromCach = 0;
	}

	$HTML .= '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' . "\n";
	$HTML .= '        "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
	$HTML .= '<rss version="0.91">' . "\n";

	$HTML .= "\n";

	$HTML .= '<channel>' . "\n";
	$HTML .= '  <title>' . "$FreshPortsName -- $FreshPortsSlogan" . '</title>' . "\n";
	$HTML .= '  <link>http://' . $ServerName . '/</link>' . "\n";
	$HTML .= '  <description>The easiest place to find ports</description>' . "\n";
	$HTML .= '  <language>en-us</language>' . "\n";

	$sort ="commit_log.commit_date desc, commit_log.id asc, element.name, category, version";

	$sql = "select * from commits_latest_ports order by commit_date_raw desc, category, port";

	$NewsAddenda = $_SERVER['DOCUMENT_ROOT'] . "/news.addenda";
	if (file_exists($NewsAddenda)) {
		# include any highlights here
		$fp = fopen($NewsAddenda, "r");
		if ($fp) {
			$NewsAddendaContents = fread($fp, filesize ($NewsAddenda));
			fclose($fp);
		}
		$HTML .= $NewsAddendaContents;
		$sql .= " limit 19";
	} else {
		$sql .= " limit 20";
	}

	if ($Debug) {
		echo $sql;
		}

	$result = pg_exec ($db, $sql);
	if (!$result) {
		echo $sql . 'error = ' . pg_errormessage();
		exit;
	}

	$numrows = pg_numrows($result);
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array ($result, $i);
		$HTML .= '  <item>' . "\n";
		$HTML .= '    <title>' . $myrow["category"] . '/' . $myrow["port"] . '</title>' . "\n";
		$HTML .= '    <link>http://' . $ServerName . '/' . $myrow["category"] . '/' . $myrow["port"] . '/</link>' . "\n";
		$HTML .= '    <description>' . htmlspecialchars(trim($myrow["commit_description"])) . '</description>' . "\n";
		$HTML .= '  </item>' . "\n";
	}

	$HTML .= '</channel>' . "\n";
	$HTML .= '</rss>' . "\n";
   
	echo '<?xml version="1.0"?>', "\n";
	echo $HTML;
?>
