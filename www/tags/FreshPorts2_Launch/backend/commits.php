<?
	# $Id: commits.php,v 1.1.2.2 2002-05-22 01:38:26 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require($_SERVER["DOCUMENT_ROOT"] . "/include/common.php");
	require($_SERVER["DOCUMENT_ROOT"] . "/include/freshports.php");
	require($_SERVER["DOCUMENT_ROOT"] . "/include/databaselogin.php");
	require($_SERVER["DOCUMENT_ROOT"] . "/include/getvalues.php");

	$Debug = 0;

	$Title    = "Broken ports";

	$sql = "SELECT message_id, 
				   to_char(message_date - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS'), 
				   to_char(commit_date  - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS'), 
				   committer,
				   system_id
			  FROM commit_log
			 WHERE date_added < now() - INTERVAL '5 minutes'
		  ORDER BY commit_date desc, message_date desc, message_id, committer
			 LIMIT 100";


	if ($Debug) {
		echo $sql;
	}

	$result = pg_exec($db, $sql);
	if (!$result) {
		echo pg_errormessage();
	} else {
		$numrows = pg_numrows($result);
#		echo "There are $numrows to fetch<BR>\n";
	}

	$numrows = pg_numrows($result);
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array($result, $i);
		print $myrow["message_id"] . "\t" . $myrow["message_date"] . "\t" . $myrow["commit_date"] . "\t" . 
			  $myrow["committer"]  . "\t" . $myrow["system_id"] . "\n";
	}
?>
