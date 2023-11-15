<?php
	#
	# $Id: commits_by_description.php,v 1.3 2011-09-24 19:55:18 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/commits.php");

// base class for fetching commits under a given path
class CommitsByDescription extends commits {

	var $Condition = '';

	function __construct($dbh) {
		parent::__construct($dbh);
	}
	
	function ConditionSet($Condition) {
		$this->Condition = $Condition;
	}

	function Fetch() {
		$params = array();
		$sql = "
		SELECT DISTINCT
			CL.commit_date - SystemTimeAdjust()                                                                          AS commit_date_raw,
			CL.id                                                                                                        AS commit_log_id,
			CL.encoding_losses                                                                                           AS encoding_losses,
			CL.message_id                                                                                                AS message_id,
			CL.commit_hash_short                                                                                         AS commit_hash_short,
			CL.committer	                                                                                             AS committer,
			CL.committer_name                                                                                            AS committer_name,
			CL.committer_email                                                                                           AS committer_email,
			CL.author_name                                                                                               AS author_name,
			CL.author_email                                                                                              AS author_email,
			CL.description                                                                                               AS commit_description,
			to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')                                                  AS commit_date,
			to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI')                                                      AS commit_time,
			commit_log_ports.port_id                                                                                     AS port_id,
			categories.name                                                                                              AS category,
			categories.id                                                                                                AS category_id,
			element.name                                                                                                 AS port,
			CASE when commit_log_ports.port_version IS NULL then ports.version   else commit_log_ports.port_version  END AS version,
			CASE when commit_log_ports.port_version is NULL then ports.revision  else commit_log_ports.port_revision END AS revision,
			CASE when commit_log_ports.port_epoch   is NULL then ports.portepoch else commit_log_ports.port_epoch    END AS epoch,
			element.status                                                                                               AS status,
			commit_log_ports.needs_refresh                                                                               AS needs_refresh,
			ports.forbidden                                                                                              AS forbidden,
			ports.broken                                                                                                 AS broken,
			ports.deprecated                                                                                             AS deprecated,
			ports.ignore                                                                                                 AS ignore,
			ports.expiration_date                                                                                        AS expiration_date,
			date_part('epoch', ports.date_added)                                                                         AS date_added,
			ports.element_id                                                                                             AS element_id,
			element_pathname(ports.element_id)                                                                           AS element_pathname,
                        R.name                                                                                                       AS repo_name,
			R.repository,
			R.repo_hostname,
			R.path_to_repo,
			ports.short_description                                                                                      AS short_description,
			null                                                                                                         AS stf_message";
		if ($this->UserID) {
				$sql .= ",
		        onwatchlist ";
		} else {
				$sql .= ",
		        NULL AS onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports, (SELECT * FROM commit_log CL JOIN commit_log_ports CLP on CL.id = CLP.commit_log_id WHERE " . $this->Condition;

		if ($this->Limit) {
			$params[] = $this->Limit;
			$sql .= ' LIMIT $' . count($params);
		}
		
		if ($this->Offset) {
			$params[] = $this->Offset;
			$sql .= ' OFFSET $' . count($params);
		}


    $sql .= ") AS CL JOIN commit_log_branches  CLB ON CL.id = CLB.commit_log_id
                     JOIN system_branch        SB  ON CLB.branch_id = SB.id
          LEFT OUTER JOIN repo R ON CL.repo_id = R.id, categories, ports, element ";

		if ($this->UserID) {
			$params[] = $this->UserID;
			$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = $" . count($params) . "
	       AND watch_list.in_service		
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = element.id";
		}

		$sql .= "
	  WHERE commit_log_ports.commit_log_id = CL.id
	    AND commit_log_ports.port_id       = ports.id
	    AND categories.id                  = ports.category_id
	    AND element.id                     = ports.element_id
   ORDER BY 1 desc,
			commit_log_id,
			category,
			port";

		if ($this->Debug) echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_query_params($this->dbh, $sql, $params);
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
			if ($this->Debug) echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			syslog(LOG_ERR, 'pg_query_params failed: ' . pg_last_error($this->dbh) . $sql);
		}

		return $numrows;
	}

	function GetCountCommits() {
		$count = 0;
		
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
		SELECT count(*) as count 
		  FROM commit_log CL JOIN commit_log_ports CLP on CL.id = CLP.commit_log_id
		 WHERE " . $this->Condition;

		if ($this->Debug) echo "<pre>$sql</pre>";
		$result = pg_query_params($this->dbh, $sql, array());
		if ($result) {
			$myrow = pg_fetch_array($result);
			$count = $myrow['count'];
		} else {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			die('SQL ERROR');
		}

		return $count;
	}
}
