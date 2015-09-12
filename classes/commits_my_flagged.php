<?php
	#
	# $Id: commits_my_flagged.php,v 1.2 2006-12-17 11:37:19 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/commits.php");

// base class for fetching commits by a particular committer
class CommitsMyFlagged extends commits {

	var $UserID;

	function CommitsMyFlagged($dbh) {
		parent::Commits($dbh);
	}
	
	function UserIDSet($UserID) {
		$this->UserID = $UserID;
	}

	function GetCountCommits() {
		$count = 0;
		
		$sql = "select count(*) as count from commits_flagged where user_id = '" . pg_escape_string($this->UserID) . "'";
		if ($this->Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$myrow = pg_fetch_array($result);
			$count = $myrow['count'];
		} else {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			die('SQL ERROR');
		}

		return $count;
	}

	function GetCountPortCommits() {
		$count = 0;
		
		$sql = "
		SELECT count(distinct CL.id) as count 
		  FROM commit_log CL, commit_log_ports CLP, commits_flagged CF
		 WHERE CL.id      = CF.commit_log_id
		   AND CL.id      = CLP.commit_log_id
		   AND CF.user_id = '" . pg_escape_string($this->UserID) . "'";

		if ($this->Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$myrow = pg_fetch_array($result);
			$count = $myrow['count'];
		} else {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			die('SQL ERROR');
		}

		return $count;
	}

	function GetCountPortsTouched() {
		$count = 0;
		
		$sql = "
		SELECT count(*) as count 
		  FROM commit_log CL, commit_log_ports CLP, commits_flagged CF
		 WHERE CL.id      = CF.commit_log_id
		   AND CL.id      = CLP.commit_log_id
		   AND CF.user_id = '" . pg_escape_string($this->UserID) . "'";
		;
		if ($this->Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$myrow = pg_fetch_array($result);
			$count = $myrow['count'];
		} else {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			die('SQL ERROR');
		}

		return $count;
	}

	function Fetch() {
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
			commit_log_ports.port_id                                                                                    AS port_id,
			categories.name                                                                                             AS category,
			categories.id                                                                                               AS category_id,
			element.name                                                                                                AS port,
			element_pathname(element.id)                                                                                AS pathname,
			CASE when commit_log_ports.port_version IS NULL then ports.version  else commit_log_ports.port_version  END AS version,
			CASE when commit_log_ports.port_version is NULL then ports.revision else commit_log_ports.port_revision END AS revision,
			CASE when commit_log_ports.port_epoch   is NULL then ports.portepoch else commit_log_ports.port_epoch   END AS epoch,
			element.status                                                                                              AS status,
			commit_log_ports.needs_refresh                                                                              AS needs_refresh,
			ports.forbidden                                                                                             AS forbidden,
			ports.broken                                                                                                AS broken,
			ports.deprecated                                                                                            AS deprecated,
			ports.ignore                                                                                                AS ignore,
			ports.expiration_date                                                                                       AS expiration_date,
			date_part('epoch', ports.date_added)                                                                        AS date_added,
			ports.element_id                                                                                            AS element_id,
			ports.short_description                                                                                     AS short_description,
			STF.message                                                                                                 AS stf_message";
		if ($this->UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports LEFT OUTER JOIN sanity_test_failures STF ON STF.commit_log_id = commit_log_ports.commit_log_id, commit_log, categories, ports, commits_flagged CF, element ";

		if ($this->UserID) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = " . $this->UserID . "
	       AND watch_list.in_service		
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = element.id";
		}
		
		$sql .= "
	  WHERE commit_log_ports.commit_log_id = commit_log.id
	    AND commit_log_ports.port_id       = ports.id
	    AND categories.id                  = ports.category_id
	    AND element.id                     = ports.element_id
	    AND CF.user_id                     = " . pg_escape_string($this->UserID) . "
	    AND CF.commit_log_id               = commit_log.id
   ORDER BY 1 desc,
			commit_log_id,
			category,
			port";
			
		if ($this->Limit) {
			$sql .= " LIMIT " . pg_escape_string($this->Limit);
		}
		
		if ($this->Offset) {
			$sql .= " OFFSET " . pg_escape_string($this->Offset);
		}



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
}

