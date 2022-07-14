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

	DEFINE('MAXROWS', 15000);

	$Debug = 0;

	if (IsSet($_REQUEST['n'])) {
		$MaxCommits = pg_escape_string($db, $_REQUEST['n']);
	}
	if (IsSet($_REQUEST['git'])) {
		$git = true;
	} else {
		$git = false;
	}
	if (IsSet($MaxCommits)) {
		if ($MaxCommits < 1 or $MaxCommits > MAXROWS) {
			$MaxCommits = MAXROWS;
		}
	} else {
		$MaxCommits = MAXROWS;
	}

	if ($git) {
		# at present, devgit only pulls ports commits.
		# that's why we join to commit_log_ports,
		$sql .= "SELECT DISTINCT to_char(commit_date, 'YYYY MM DD HH24') as commit_date, 
                                  committer
                          FROM commit_log JOIN commit_log_ports ON commit_log.id = commit_log_ports.commit_log_id
    					                   JOIN commit_log_branches ON commit_log.id = commit_log_branches.commit_log_id
										   JOIN system_branch ON commit_log_branches.branch_id = system_branch.id AND system_branch.branch_name = 'head'
                         WHERE date_added < now() - INTERVAL '1 minutes'
		  ORDER BY commit_date  desc,
                   committer
			 LIMIT $MaxCommits";
	} else {
		$sql .= "SELECT message_id,
				   to_char(commit_date, 'DD Mon YYYY HH24') as commit_date, 
				   committer,
				   system_id
			  FROM commit_log
			 WHERE date_added < now() - INTERVAL '1 minutes'
		  ORDER BY commit_log.commit_date  desc,
                   committer
			 LIMIT $MaxCommits";
	}

	if ($Debug) {
		echo "<pre>$sql</pre>\n";
	}

	$result = pg_exec($db, $sql);
	if (!$result) {
		echo pg_errormessage();
	} else {
		$numrows = pg_num_rows($result);
#		echo "There are $numrows to fetch<BR>\n";
	}

	$numrows = pg_num_rows($result);
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array($result, $i);
		if (!$git) print $myrow["message_id"];
		print $myrow["message_date"] . "\t" . $myrow["commit_date"] . "\t" . 
			  $myrow["committer"]  . "\t" . $myrow["system_id"] . "\n";
	}

	$Statistics->Save();
