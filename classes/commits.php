<?php
	#
	# $Id: commits.php,v 1.4 2012-09-25 18:10:12 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/commit.php");

// base class for fetching commits
class Commits {

	var $dbh;
	var $LocalResult;
	var $Limit  = 0;
	var $Offset = 0;

	#
	# a condition for the tree path comparison
	# e.g. LIKE '/src/sys/i386/conf/%'
	#
	var $TreePathCondition = '';
	var $UserID            = 0;
	var $BranchName;

	var $Debug = 0;

	function Commits($dbh, $BranchName = BRANCH_HEAD) {
		$this->dbh	      = $dbh;
		$this->BranchName = $BranchName;
	}
	
	function SetLimit($Limit) {
		$this->Limit = $Limit;
	}
	
	function SetOffset($Offset) {
		$this->Offset = $Offset;
	}
	
	function UserIDSet($UserID) {
		$this->UserID = $UserID;
	}

	function SetBranch($BranchName) {
		$this->BranchName = $BranchName;
		echo 'setting branch to ' . $BranchName;
	}
	
	function Fetch($Date, $UserID) {
		$sql = "
        SELECT DISTINCT
            commit_log.commit_date - SystemTimeAdjust()                                                                 AS commit_date_raw,
            commit_log.id                                                                                               AS commit_log_id,
            commit_log.encoding_losses                                                                                  AS encoding_losses,
            commit_log.message_id                                                                                       AS message_id,
            commit_log.committer                                                                                        AS committer,
            commit_log.description                                                                                      AS commit_description,
            to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')                                         AS commit_date,
            to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI')                                             AS commit_time,
            CLP.port_id                                                                                    AS port_id,
            categories.name                                                                                             AS category,
            categories.id                                                                                               AS category_id,
            element.name                                                                  AS port,
            CASE when CLP.port_version IS NULL then ports.version  else CLP.port_version  END AS version,
            CASE when CLP.port_version is NULL then ports.revision else CLP.port_revision END AS revision,
            CASE when CLP.port_epoch   is NULL then ports.portepoch else CLP.port_epoch   END AS epoch,
            element.status                                                                                              AS status,
            CLP.needs_refresh                                                                              AS needs_refresh,
            ports.forbidden                                                                                             AS forbidden,
            ports.broken                                                                                                AS broken,
            ports.deprecated                                                                                            AS deprecated,
            ports.ignore                                                                                                AS ignore,
            ports.expiration_date                                                                                       AS expiration_date,
            date_part('epoch', ports.date_added)                                                                        AS date_added,
            ports.element_id                                                                                            AS element_id,
            ports.short_description                                                                                     AS short_description,
            commit_log.svn_revision                                                                                     AS svn_revision,
            R.svn_hostname                                                                                              AS svn_hostname,
            R.path_to_repo                                                                                              AS path_to_repo,
            STF.message                                                                                                 AS stf_message";

        if ($UserID) {
                $sql .= ",
            onwatchlist ";
        }

        $sql .= "
    FROM commit_log_ports CLP JOIN commit_log_branches CLB ON CLP.commit_log_id = CLB.commit_log_id
                              JOIN system_branch SB ON SB.branch_name = '" . pg_escape_string($this->BranchName) . "' AND SB.id = CLB.branch_id
      LEFT OUTER JOIN sanity_test_failures STF ON STF.commit_log_id = CLP.commit_log_id
    , commit_log LEFT OUTER JOIN repo R on commit_log.repo_id = R.id, categories, ports, element ";

        if ($UserID) {
                $sql .= "
          LEFT OUTER JOIN
     (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
        FROM watch_list JOIN watch_list_element 
            ON watch_list.id      = watch_list_element.watch_list_id
           AND watch_list.user_id = " . pg_escape_string($UserID) . "
           AND watch_list.in_service        
      GROUP BY wle_element_id) AS TEMP
           ON TEMP.wle_element_id = element.id";
        }

        $sql .= "
      WHERE commit_log.commit_date         BETWEEN '" . pg_escape_string($Date) . "'::timestamptz  + SystemTimeAdjust()
                                               AND '" . pg_escape_string($Date) . "'::timestamptz  + SystemTimeAdjust() + '1 Day'
        AND CLP.commit_log_id = commit_log.id
        AND CLP.port_id       = ports.id
        AND categories.id     = ports.category_id
        AND element.id        = ports.element_id
   ORDER BY 1 desc,
            commit_log_id,
            category,
            port";



		if ($this->Debug) echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($this->Debug) echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . "<pre>$sql</pre>";
		}

		return $numrows;
	}

	function Count($Date) {
		$sql = "
SELECT count(DISTINCT CL.id) AS count
  FROM commit_log CL JOIN commit_log_ports    CLP ON CLP.commit_log_id = CL.id
                                                 AND CL.commit_date BETWEEN '" . pg_escape_string($Date) . "'::timestamptz  + SystemTimeAdjust()
                                                                        AND '" . pg_escape_string($Date) . "'::timestamptz  + SystemTimeAdjust() + '1 Day'
                     JOIN commit_log_branches CLB ON CL.id             = CLB.commit_log_id
                     JOIN system_branch       SB  ON SB.branch_name    = '" . pg_escape_string($this->BranchName) . "'
                                                 AND SB.id             = CLB.branch_id";

		if ($this->Debug) echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$myrow = pg_fetch_array($this->LocalResult);
			$count = $myrow['count'];
		} else {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			die('SQL ERROR');
		}

		return $count;
	}

	function FetchNth($N) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch
		#

		if ($this->Debug) echo "fetching row $N<br>";

		$commit = new Commit($this->dbh);

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$commit->PopulateValues($myrow);

		return $commit;
	}

	function LastModified($Date) {

		# default to the current GMT time
		$last_modified = gmdate(LAST_MODIFIED_FORMAT);

		$sql = "
SELECT gmt_format(max(CL.date_added)) AS last_modified
  FROM commit_log CL, commit_log_ports CLP JOIN commit_log_branches CLB ON CLP.commit_log_id = CLB.commit_log_id
                                           JOIN system_branch SB ON SB.branch_name = '" . pg_escape_string($this->BranchName) . "' AND SB.id = CLB.branch_id
 WHERE CL.id = CLP.commit_log_id
   AND CL.commit_date BETWEEN '" . pg_escape_string($Date) . "'::timestamptz  + SystemTimeAdjust()
                          AND '" . pg_escape_string($Date) . "'::timestamptz  + SystemTimeAdjust() + '1 Day'";
		
		if ($this->Debug) echo '<pre>' . $sql . '</pre>';
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$myrow = pg_fetch_array($result);
			$last_modified = $myrow['last_modified'];
		} else {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			die('SQL ERROR');
		}

		return $last_modified;
	}
}
