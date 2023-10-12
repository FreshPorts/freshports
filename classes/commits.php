<?php
	#
	# $Id: commits.php,v 1.4 2012-09-25 18:10:12 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/commit_ports.php");

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

	var $Debug = 1;

	function __construct($dbh, $BranchName = BRANCH_HEAD) {
		$this->dbh        = $dbh;
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
	}

        
	function FetchCommitsOnADay($Date, $UserID) {
		$params = array();
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
        SELECT DISTINCT
            CL.commit_date - SystemTimeAdjust()                                                                 AS commit_date_raw,
            CL.id                                                                                               AS commit_log_id,
            CL.encoding_losses                                                                                  AS encoding_losses,
            CL.message_id                                                                                       AS message_id,
            CL.commit_hash_short                                                                                AS commit_hash_short,
            CL.committer                                                                                        AS committer,
            CL.committer_name                                                                                   AS committer_name,
            CL.committer_email                                                                                  AS committer_email,
            CL.author_name                                                                                      AS author_name,
            CL.author_email                                                                                     AS author_email,
            CL.description                                                                                      AS commit_description,
            to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')                                         AS commit_date,
            to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI')                                             AS commit_time,
            CLP.port_id                                                                                    	AS port_id,
            C.name                                                                                              AS category,
            C.id                                                                                                AS category_id,
            E.name                                                                                              AS port,
            CASE when CLP.port_version IS NULL then P.version  else CLP.port_version  END                       AS version,
            CASE when CLP.port_version is NULL then P.revision else CLP.port_revision END                       AS revision,
            CASE when CLP.port_epoch   is NULL then P.portepoch else CLP.port_epoch   END                       AS epoch,
            E.status                                                                                            AS status,
            CLP.needs_refresh                                                                              	AS needs_refresh,
            P.forbidden                                                                                         AS forbidden,
            P.broken                                                                                            AS broken,
            P.deprecated                                                                                        AS deprecated,
            P.ignore                                                                                            AS ignore,
            P.expiration_date                                                                                   AS expiration_date,
            date_part('epoch', P.date_added)                                                                    AS date_added,
            P.element_id                                                                                        AS element_id,
            P.short_description                                                                                 AS short_description,
            CL.svn_revision                                                                                     AS svn_revision,
            R.repository                                                                                        AS repository,
            R.repo_hostname                                                                                     AS repo_hostname,
            R.path_to_repo                                                                                      AS path_to_repo,
            R.name                                                                                              AS repo_name,
            PV.current                                                                                          AS vulnerable_current,
            PV.past                                                                                             AS vulnerable_past,
            STF.message                                                                                         AS stf_message,
            P.is_interactive                                                                                    AS is_interactive,
            P.no_cdrom                                                                                          AS no_cdrom,
            P.restricted                                                                                        AS restricted,
            SB.branch_name                                                                                      AS branch";

        if ($UserID) {
                $sql .= ",
            onwatchlist ";
        } else {
                $sql .= ",
            NULL AS onwatchlist ";
        }

        $params[] = $Date;
        $sql .= "
    FROM commit_log CL JOIN commit_log_ports CLP ON CL.id = CLP.commit_log_id 
                        AND CL.commit_date BETWEEN $" . count($params) . "::timestamptz  + SystemTimeAdjust()
                                               AND $" . count($params) . "::timestamptz  + SystemTimeAdjust() + '1 Day'
            LEFT OUTER JOIN sanity_test_failures STF ON STF.commit_log_id = CLP.commit_log_id 
            JOIN repo R on CL.repo_id = R.id
            LEFT OUTER JOIN ports_vulnerable     PV ON CLP.port_id = PV.port_id
            JOIN commit_log_branches CLB ON CLP.commit_log_id = CLB.commit_log_id
            JOIN system_branch        SB ON SB.id = CLB.branch_id ";

        # allow for quarterly branches
        # see also the Count() function below
        if ($this->BranchName != BRANCH_HEAD) {
            $params[] = $this->BranchName;
            $sql .= " AND SB.branch_name = $" . count($params) . "\n";
        }

        $sql .= "
            JOIN ports                P  ON P.id           = CLP.port_id
            JOIN categories           C  ON C.id           = P.category_id
            JOIN element              E  ON E.id           = P.element_id
            ";

        if ($UserID) {
                $params[] = $UserID;
                $sql .= "
          LEFT OUTER JOIN
     (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
        FROM watch_list JOIN watch_list_element 
            ON watch_list.id      = watch_list_element.watch_list_id
           AND watch_list.user_id = $" . count($params) . "
           AND watch_list.in_service
      GROUP BY wle_element_id) AS TEMP
           ON TEMP.wle_element_id = E.id";
        }

        # we once ordered by 1 desc, commit_log.id - but that would give different results between dev and test if a commit had to be rerun on dev
        # and therefore had a higher commit id than commits which preceeded it.
        # The goal of sorting by message_id is to keep together all ports for a given commit keep.
        $sql .= "
   ORDER BY 1 desc,
            CL.id desc,
            category,
            port";



		if ($this->Debug) echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_query_params($this->dbh, $sql, $params);
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
			if ($this->Debug) echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_query_params failed: ' . "<pre>$sql</pre>";
		}

		return $numrows;
	}

	function FetchLimit($UserID, $Limit) {

		$params = array();
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__;

		if ($this->BranchName == BRANCH_HEAD ) {
			$params[] = $Limit;
			$sql .= "
with recent_commits AS
(WITH RC AS
  (select * from commit_log order by commit_date DESC limit 1000)
  SELECT distinct RC.*, CLPE.element_id AS clpe_element_id
    FROM commit_log_ports_elements CLPE, RC
   WHERE CLPE.commit_log_id = RC.id
   ORDER by RC.commit_date DESC
   LIMIT $" . count($params) . ')';
		} else {
			$params[] = $this->BranchName;
			$params[] = $Limit;
			$sql .= "
with recent_commits AS
(WITH CL AS
  (select * from commit_log order by commit_date DESC limit 5000)
  select distinct CL.*, CLPE.element_id AS clpe_element_id from CL join commit_log_branches clb on clb.branch_id =
        (select id from system_branch where branch_name = $" . (count($params) - 1). ") and CL.id = CLB.commit_log_id
        JOIN commit_log_ports_elements CLPE on CLPE.commit_log_id = CL.id
   LIMIT $" . count($params) . ')';
		}

		$sql .= "
        SELECT DISTINCT
            RC.commit_date - SystemTimeAdjust()                                                                 AS commit_date_raw,
            RC.id                                                                                               AS commit_log_id,
            RC.encoding_losses                                                                                  AS encoding_losses,
            RC.message_id                                                                                       AS message_id,
            RC.commit_hash_short                                                                                AS commit_hash_short,
            RC.committer                                                                                        AS committer,
            RC.committer_name                                                                                   AS committer_name,
            RC.committer_email                                                                                  AS committer_email,
            RC.author_name                                                                                      AS author_name,
            RC.author_email                                                                                     AS author_email,
            RC.description                                                                                      AS commit_description,
            to_char(RC.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')                                         AS commit_date,
            to_char(RC.commit_date - SystemTimeAdjust(), 'HH24:MI')                                             AS commit_time,
            CLP.port_id                                                                                         AS port_id,
            C.name                                                                                              AS category,
            C.id                                                                                                AS category_id,
            E.name                                                                                              AS port,
            element_pathname(clpe_element_id)                                                                   AS element_pathname,
            clpe_element_id                                                                                     AS element_id,
            CASE when CLP.port_version IS NULL then P.version  else CLP.port_version  END                       AS version,
            CASE when CLP.port_version is NULL then P.revision else CLP.port_revision END                       AS revision,
            CASE when CLP.port_epoch   is NULL then P.portepoch else CLP.port_epoch   END                       AS epoch,
            E.status                                                                                            AS status,
            CLP.needs_refresh                                                                                   AS needs_refresh,
            P.forbidden                                                                                         AS forbidden,
            P.broken                                                                                            AS broken,
            P.deprecated                                                                                        AS deprecated,
            P.ignore                                                                                            AS ignore,
            P.expiration_date                                                                                   AS expiration_date,
            date_part('epoch', P.date_added)                                                                    AS date_added,
            P.short_description                                                                                 AS short_description,
            RC.svn_revision                                                                                     AS svn_revision,
            R.repo_hostname                                                                                     AS repo_hostname,
            R.repository                                                                                        AS repository,
            R.path_to_repo                                                                                      AS path_to_repo,
            R.name                                                                                              AS repo_name,
            PV.current                                                                                          AS vulnerable_current,
            PV.past                                                                                             AS vulnerable_past,
            STF.message                                                                                         AS stf_message,
            P.is_interactive                                                                                    AS is_interactive,
            P.no_cdrom                                                                                          AS no_cdrom,
            P.restricted                                                                                        AS restricted,
            SB.branch_name                                                                                      AS branch";

		if ($UserID) {
			$sql .= ",
	    onwatchlist ";
                } else {
                	$sql .= ",
            NULL AS onwatchlist ";
                }

                $sql .= "
    FROM recent_commits RC
    LEFT OUTER JOIN commit_log_branches CLB  ON RC.id             = CLB.commit_log_id
    LEFT OUTER JOIN system_branch SB         ON SB.id             = CLB.branch_id
    LEFT OUTER JOIN ports P                  ON P.element_id      = clpe_element_id
    LEFT OUTER JOIN commit_log_ports CLP     ON P.id              = CLP.port_id and CLP.commit_log_id = RC.id
    LEFT OUTER JOIN element E                ON E.id              = P.element_id
    LEFT OUTER JOIN categories C             ON C.id              = P.category_id
    LEFT OUTER JOIN repo R                   on RC.repo_id        = R.id
    LEFT OUTER JOIN sanity_test_failures STF ON STF.commit_log_id = RC.id
    LEFT OUTER JOIN ports_vulnerable PV      ON P.id              = PV.port_id";

	        if ($UserID) {
			$params[] = $UserID;
			$sql .= "
          LEFT OUTER JOIN
     (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
        FROM watch_list JOIN watch_list_element 
            ON watch_list.id      = watch_list_element.watch_list_id
           AND watch_list.user_id = $" . count($params) . "
           AND watch_list.in_service
      GROUP BY wle_element_id) AS TEMP
           ON TEMP.wle_element_id = E.id";
		}

		$sql .= "
   ORDER BY 1 desc,
            RC.id DESC,
            category,
            port";

		if ($this->Debug) echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_query_params($this->dbh, $sql, $params);
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
			if ($this->Debug) echo "That would give us $numrows rows, which might be more than the number of commits, if that commit touched more items than just one port.";
			if ($numrows < $Limit) syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': we should have ' . $Limit . 
			    ' commits. We got ' . $numrows);

		} else {
			$numrows = -1;
			echo 'pg_query_params failed: ' . "<pre>$sql</pre>";
		}

		return $numrows;
	}

	function Count($Date) {
	        $params = array($Date);
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
        SELECT count(DISTINCT CL.id) AS count
          FROM commit_log CL JOIN commit_log_ports CLP ON CL.id = CLP.commit_log_id 
                        AND CL.commit_date BETWEEN $1::timestamptz  + SystemTimeAdjust()
                                               AND $1::timestamptz  + SystemTimeAdjust() + '1 Day'
            JOIN commit_log_branches CLB ON CLP.commit_log_id = CLB.commit_log_id
            JOIN system_branch        SB ON SB.id = CLB.branch_id";

                # allow for quarterly branches
                # see also the FetchCommitsOnADay() function above
                if ($this->BranchName != BRANCH_HEAD) {
                  $params[] = $this->BranchName;
                  $sql .= " AND SB.branch_name = $2\n";
                }

		if ($this->Debug) echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_query_params($this->dbh, $sql, $params);
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

		$commit = new Commit_Ports($this->dbh);

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$commit->PopulateValues($myrow);

		return $commit;
	}

	function LastModified($Date) {

		# default to the current GMT time
		$last_modified = gmdate(LAST_MODIFIED_FORMAT);

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
SELECT gmt_format(max(CL.date_added)) AS last_modified
  FROM commit_log CL, commit_log_ports CLP JOIN commit_log_branches CLB ON CLP.commit_log_id = CLB.commit_log_id
                                           JOIN system_branch        SB ON SB.id             = CLB.branch_id
 WHERE CL.id = CLP.commit_log_id
   AND CL.commit_date BETWEEN $1::timestamptz  + SystemTimeAdjust()
                          AND $1::timestamptz  + SystemTimeAdjust() + '1 Day'";
		
		if ($this->Debug) echo '<pre>' . $sql . '</pre>';
		$result = pg_query_params($this->dbh, $sql, array($Date));
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
