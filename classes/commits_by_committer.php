<?php
	#
	# $Id: commits_by_committer.php,v 1.4 2010-07-11 18:23:26 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/commits.php");

// base class for fetching commits by a particular committer
class CommitsByCommitter extends commits {

	var $Committer;

	function CommitsByCommitter($dbh) {
		parent::Commits($dbh);
	}
	
	function CommitterSet($Committer) {
		$this->Committer = $Committer;
	}

	function GetCountCommits() {
		$count = 0;
		
		$sql = "select count(*) as count from commit_log where committer = '" . pg_escape_string($this->Committer) . "'";
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
			commit_log.commit_date - SystemTimeAdjust()        AS commit_date_raw,
			commit_log.id                                      AS commit_log_id,
			commit_log.encoding_losses                         AS encoding_losses,
			commit_log.message_id                              AS message_id,
			commit_log.committer                               AS committer,
			commit_log.description                             AS commit_description,
			to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  AS commit_date,
			to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI')      AS commit_time,
			NULL                                               AS port_id,
			NULL                                               AS category,
			NULL                                               AS category_id,
			NULL                                               AS port,
			element_pathname(element.id)                       AS pathname,
			NULL AS version,
			commit_log_elements.revision_name AS revision,
			NULL AS epoch,
			element.status                                     AS status,
			NULL AS needs_refresh,
			NULL                                               AS forbidden,
			NULL                                               AS broken,
			NULL                                               AS deprecated,
			NULL                                               AS ignore,
			NULL                                               AS expiration_date,
			NULL                                               AS date_added,
			NULL                                               AS element_id,
			NULL                                               AS short_description,
			NULL                                               AS stf_message";
		if ($this->UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log, commit_log_elements, element ";

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
	  WHERE commit_log.id IN (SELECT tmp.id FROM (SELECT DISTINCT CL.id, CL.commit_date
  FROM commit_log CL
 WHERE CL.committer  = '" . pg_escape_string($this->Committer) . "'
ORDER BY CL.commit_date DESC ";

   		if ($this->Limit) {
			$sql .= " LIMIT " . $this->Limit;
		}
		
		if ($this->Offset) {
			$sql .= " OFFSET " . $this->Offset;
		}




		$sql .= ")as tmp)
	    AND commit_log_elements.commit_log_id = commit_log.id
	    AND commit_log_elements.element_id    = element.id
   ORDER BY 1 desc,
			commit_log_id";
			
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

