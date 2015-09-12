<?php
	#
	# $Id: port_commits_by_committer.php,v 1.3 2013-02-16 01:58:47 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/commits.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/commits_by_committer.php");

// base class for fetching commits by a particular committer
class PortCommitsByCommitter extends CommitsByCommitter {

	var $Committer;

	function PortCommitsByCommitter($dbh) {
		parent::Commits($dbh);
	}
	
	function GetCountCommits() {
		$count = 0;
		
		$sql = "
		SELECT count(distinct CL.id) as count 
		  FROM commit_log CL, commit_log_ports CLP 
		 WHERE CL.id = CLP.commit_log_id
		   AND committer = '" . pg_escape_string($this->Committer) . "'";

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
		  FROM commit_log CL, commit_log_ports CLP 
		 WHERE CL.id = CLP.commit_log_id
		   AND committer = '" . pg_escape_string($this->Committer) . "'";
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
		$sql = "set client_encoding = 'ISO-8859-15';
		SELECT DISTINCT
			CL.commit_date - SystemTimeAdjust()                                            AS commit_date_raw,
			CL.id                                                                          AS commit_log_id,
			CL.encoding_losses                                                             AS encoding_losses,
			CL.message_id                                                                  AS message_id,
			CL.committer                                                                   AS committer,
			CL.description                                                                 AS commit_description,
			to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')                    AS commit_date,
			to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI')                        AS commit_time,
			CLP.port_id                                                                    AS port_id,
			C.name                                                                         AS category,
			C.id                                                                           AS category_id,
			E.name                                                                         AS port,
			element_pathname(E.id)                                                         AS pathname,
			CASE when CLP.port_version IS NULL then P.version   else CLP.port_version  END AS version,
			CASE when CLP.port_version is NULL then P.revision  else CLP.port_revision END AS revision,
			CASE when CLP.port_epoch   is NULL then P.portepoch else CLP.port_epoch    END AS epoch,
			E.status                                                                       AS status,
			CLP.needs_refresh                                                              AS needs_refresh,
			P.forbidden                                                                    AS forbidden,
			P.broken                                                                       AS broken,
			P.deprecated                                                                   AS deprecated,
			P.ignore                                                                       AS ignore,
			P.expiration_date                                                              AS expiration_date,
			date_part('epoch', P.date_added)                                               AS date_added,
			P.element_id                                                                   AS element_id,
			P.short_description                                                            AS short_description,
			STF.message                                                                    AS stf_message";
		if ($this->UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports CLP JOIN (SELECT * FROM commit_log WHERE commit_log.committer = '" . pg_escape_string($this->Committer) . "' ORDER BY commit_date DESC ";
    
		if ($this->Limit) {
			$sql .= " LIMIT " . $this->Limit;
		}
		
		if ($this->Offset) {
			$sql .= " OFFSET " . $this->Offset;
		}

    
        $sql .= ") CL on (CLP.commit_log_id = CL.id) 
          LEFT OUTER JOIN sanity_test_failures STF ON STF.commit_log_id = CLP.commit_log_id, categories C, ports P, element E ";

		if ($this->UserID) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = " . pg_escape_string($this->UserID) . "
	       AND watch_list.in_service		
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = E.id";
		}
		
		$sql .= "
	  WHERE CLP.port_id = P.id
	    AND C.id        = P.category_id
	    AND E.id        = P.element_id
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
}

