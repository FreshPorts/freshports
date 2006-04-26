<?php
	#
	# $Id: commits.php,v 1.1.2.21 2006-04-26 21:29:26 dan Exp $
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

	function Commits($dbh) {
		$this->dbh	= $dbh;
	}
	
	function SetLimit($Limit) {
		$this->Limit = $Limit;
	}
	
	function SetOffset($Offset) {
		$this->Offset = $Offset;
	}
	
	function GetCountCommitsByCommitter($Committer) {
		$count = 0;
		
		$sql = "select count(*) as count from commit_log where committer = '" . AddSlashes($Committer) . "'";
#		echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$myrow = pg_fetch_array($result);
			$count = $myrow['count'];
		}

		return $count;
	}

	function GetCountPortCommitsByCommitter($Committer) {
		$count = 0;
		
		$sql = "
		SELECT count(distinct CL.id) as count 
		  FROM commit_log CL, commit_log_ports CLP 
		 WHERE CL.id = CLP.commit_log_id
		   AND committer = '" . AddSlashes($Committer) . "'";
		;
#		echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$myrow = pg_fetch_array($result);
			$count = $myrow['count'];
		}

		return $count;
	}

	function GetCountPortsTouchedByCommitter($Committer) {
		$count = 0;
		
		$sql = "
		SELECT count(*) as count 
		  FROM commit_log CL, commit_log_ports CLP 
		 WHERE CL.id = CLP.commit_log_id
		   AND committer = '" . AddSlashes($Committer) . "'";
		;
#		echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$myrow = pg_fetch_array($result);
			$count = $myrow['count'];
		}

		return $count;
	}

	function FetchByCommitter($Committer, $UserID) {
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
			security_notice.id                                                                                          AS security_notice_id";
		if ($UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports LEFT OUTER JOIN security_notice
       ON commit_log_ports.commit_log_id = security_notice.commit_log_id
          AND security_notice.security_notice_status_id = 'A', commit_log, categories, ports, element ";

		if ($UserID) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = $UserID
	       AND watch_list.in_service		
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = element.id";
		}

		$sql .= "
	  WHERE commit_log.committer = '" . AddSlashes($Committer) . "'
	    AND commit_log_ports.commit_log_id = commit_log.id
	    AND commit_log_ports.port_id       = ports.id
	    AND categories.id                  = ports.category_id
	    AND element.id                     = ports.element_id
   ORDER BY 1 desc,
			commit_log_id,
			category,
			port";
			
		if ($this->Limit) {
			$sql .= "\nLIMIT " . $this->Limit;
		}
		
		if ($this->Offset) {
			$sql .= "\nOFFSET " . $this->Offset;
		}



#		echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
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
			commit_log_ports.port_id                                                                                    AS port_id,
			categories.name                                                                                             AS category,
			categories.id                                                                                               AS category_id,
			element.name                                                                                                AS port,
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
			security_notice.id                                                                                          AS security_notice_id";
		if ($UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports LEFT OUTER JOIN security_notice
       ON commit_log_ports.commit_log_id = security_notice.commit_log_id
          AND security_notice.security_notice_status_id = 'A', commit_log, categories, ports, element ";

		if ($UserID) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = $UserID
	       AND watch_list.in_service		
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = element.id";
		}

		$sql .= "
	  WHERE commit_log.commit_date         BETWEEN '$Date'::timestamptz  + SystemTimeAdjust()
	                                           AND '$Date'::timestamptz  + SystemTimeAdjust() + '1 Day'
		 AND commit_log_ports.commit_log_id = commit_log.id
	    AND commit_log_ports.port_id       = ports.id
	    AND categories.id                  = ports.category_id
	    AND element.id                     = ports.element_id
   ORDER BY 1 desc,
			commit_log_id,
			category,
			port";



#		echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch
		#

#		echo "fetching row $N<br>";

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
  FROM commit_log CL, commit_log_ports CLP
 WHERE CL.id = CLP.commit_log_id
   AND CL.commit_date BETWEEN '$Date'::timestamptz  + SystemTimeAdjust()
                          AND '$Date'::timestamptz  + SystemTimeAdjust() + '1 Day'";
		
#		echo '<pre>' . $sql . '</pre>';
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$myrow = pg_fetch_array($result);
			$last_modified = $myrow['last_modified'];
		}

		return $last_modified;
	}
}
