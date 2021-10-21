<?php
	#
	# $Id: commits.php,v 1.2 2006-12-17 12:06:21 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require($_SERVER["DOCUMENT_ROOT"] . "/../include/common.php");
	require($_SERVER["DOCUMENT_ROOT"] . "/../include/freshports.php");
	require($_SERVER["DOCUMENT_ROOT"] . "/../include/databaselogin.php");
	require($_SERVER["DOCUMENT_ROOT"] . "/../include/getvalues.php");

	DEFINE('MAX_COMMITS', 250);

	$Debug = 0;

	if (IsSet($_REQUEST['n'])) {
		$MaxCommits = pg_escape_string($_REQUEST['n']);
	}
	if (IsSet($MaxCommits)) {
		if ($MaxCommits < 1 or $MaxCommits > MAX_COMMITS) {
			$MaxCommits = MAX_COMMITS;
		}
	} else {
		$MaxCommits = MAX_COMMITS;
	}

	$sql = "
WITH recent_commits AS (
  SELECT CL.id,
         to_char(CL.commit_date, 'YYYY-MM-DD-HH24:MI:SS') as commit_date,
         CL.committer,
         CL.system_id,
         CL.message_id,
         CL.commit_date as commit_date_raw
    FROM commit_log CL
   WHERE CL.date_added < now() - INTERVAL '1 minutes'
ORDER BY CL.commit_date DESC
   LIMIT $MaxCommits)
  SELECT RC.commit_date,
         RC.committer,
         RC.system_id,
         EP.pathname as element_pathname,
         RC.commit_date_raw
    FROM recent_commits RC JOIN commit_log_elements CLE ON RC.id = CLE.commit_log_id
                           JOIN element             E   ON CLE.element_id = E.id AND E.directory_file_flag != 'D'
                           JOIN element_pathname    EP  ON E.id = EP.element_id
                                                       AND (EP.pathname LIKE '/base/head/%' OR EP.pathname LIKE '/ports/%' or EP.pathname LIKE '/doc/head/%')
ORDER BY RC.commit_date DESC,
         RC.committer,
         RC.commit_date_raw,
         element_pathname;
";

	if ($Debug) {
		echo "<pre>$sql</pre>\n";
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
		print $myrow["commit_date"] . "\t" . $myrow["committer"]  . "\t" . $myrow["system_id"] . "\t" . $myrow["element_pathname"] . "\n";
	}

	$Statistics->Save();
