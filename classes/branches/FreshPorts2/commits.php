<?php
	#
	# $Id: commits.php,v 1.1.2.27 2006-10-22 16:20:23 dan Exp $
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

	var $Debug;

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

	function GetCountPortCommitsByCommitter($Committer) {
		$count = 0;
		
		$sql = "
		SELECT count(distinct CL.id) as count 
		  FROM commit_log CL, commit_log_ports CLP 
		 WHERE CL.id = CLP.commit_log_id
		   AND committer = '" . AddSlashes($Committer) . "'";

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

	function GetCountPortsTouchedByCommitter($Committer) {
		$count = 0;
		
		$sql = "
		SELECT count(*) as count 
		  FROM commit_log CL, commit_log_ports CLP 
		 WHERE CL.id = CLP.commit_log_id
		   AND committer = '" . AddSlashes($Committer) . "'";
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

	function GetCountPortCommitsByTreeLocation($TreePath) {
		$count = 0;

		$sql = "
			SELECT count(DISTINCT CL.id) AS count
			  FROM element_pathname EP, commit_log_ports_elements CLPE, commit_log CL
			 WHERE EP.pathname   LIKE '$TreePath%'
			   AND EP.element_id = CLPE.element_ID
			   AND CL.id         = CLPE.commit_log_id";
   
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

	function GetCountCommitsByTreeLocation($TreePath) {
		$count = 0;

		$sql = "
			SELECT count(DISTINCT CL.id) AS count
			  FROM element_pathname EP, commit_log_elements CLE, commit_log CL
			 WHERE EP.pathname   LIKE '$TreePath%'
			   AND EP.element_id = CLE.element_ID
			   AND CL.id         = CLE.commit_log_id";
   
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
		if ($UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports LEFT OUTER JOIN sanity_test_failures STF ON STF.commit_log_id = commit_log_ports.commit_log_id, commit_log, categories, ports, element ";

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

	function TreePathConditionSet($TreePathCondition) {
		$this->TreePathCondition = $TreePathCondition;
	}

	function UserIDSet($UserID) {
		$this->UserID = $UserID;
	}

	function FetchByTreePath() {
		$sql = "
	 SELECT commit_log.commit_date - SystemTimeAdjust()                                                                 AS commit_date_raw,
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
			ports.short_description                                                                                     AS short_description";
		if ($UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports, commit_log, categories, ports, element ";

		if ($UserID) {
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
  FROM element_pathname EP, commit_log_elements CLE, commit_log CL
 WHERE EP.pathname   " . $this->TreePathCondition . "
   AND EP.element_id = CLE.element_ID
   AND CL.id         = CLE.commit_log_id
ORDER BY CL.commit_date DESC ";

   		if ($this->Limit) {
			$sql .= "\nLIMIT " . $this->Limit;
		}
		
		if ($this->Offset) {
			$sql .= "\nOFFSET " . $this->Offset;
		}



		$sql .= ")as tmp)
	    AND commit_log_ports.commit_log_id = commit_log.id
	    AND commit_log_ports.port_id       = ports.id
	    AND categories.id                  = ports.category_id
	    AND element.id                     = ports.element_id
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

	function FetchByTreePathSrc($TreePath, $UserID) {
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
			element.name                                                                                                AS port,
			element_pathname(element.id)                                                                                AS pathname,
			element.status                                                                                              AS status,
			element_pathname.pathname                            as element_pathname,
			commit_log_elements.revision_name as revision_name ";
		if ($UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_elements, commit_log, element_pathname, element ";

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
	  WHERE commit_log.id IN (SELECT tmp.ID FROM (SELECT DISTINCT CL.id, CL.commit_date
  FROM element_pathname EP, commit_log_elements CLE, commit_log CL
 WHERE EP.pathname   LIKE '$TreePath%'
   AND EP.element_id = CLE.element_ID
   AND CL.id         = CLE.commit_log_id
ORDER BY CL.commit_date DESC ";

		if ($this->Limit) {
			$sql .= "\nLIMIT " . $this->Limit;
		}
		
		if ($this->Offset) {
			$sql .= "\nOFFSET " . $this->Offset;
		}

   		$sql .= ") AS tmp)
	    AND commit_log_elements.commit_log_id = commit_log.id
	    AND commit_log_elements.element_id    = element.id
        AND element_pathname.element_id       = element.id
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

	function FetchByCommitMessageContents($sqlUserSpecifiedCondition, $UserID) {
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
			ports.short_description                                                                                     AS short_description";
		if ($UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports, commit_log, categories, ports, element ";

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
	  WHERE " . $sqlUserSpecifiedCondition . "
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



		if ($this->Debug) echo '<pre>' . $sql . '</pre>';

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($this->Debug) echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			syslog(LOG_ERR, 'pg_exec failed: ' . pg_last_error() . $sql);
		}

		return $numrows;
	}

	function GetCountCommitsByCommitMessage($sqlUserSpecifiedCondition) {
		$count = 0;
		
		$sql = "
		SELECT count(*) as count 
		  FROM commit_log
		 WHERE $sqlUserSpecifiedCondition";

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
			STF.message                                                                                                 AS stf_message";

		if ($UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports LEFT OUTER JOIN sanity_test_failures STF ON STF.commit_log_id = commit_log_ports.commit_log_id
	, commit_log, categories, ports, element ";

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
  FROM commit_log CL, commit_log_ports CLP
 WHERE CL.id = CLP.commit_log_id
   AND CL.commit_date BETWEEN '$Date'::timestamptz  + SystemTimeAdjust()
                          AND '$Date'::timestamptz  + SystemTimeAdjust() + '1 Day'";
		
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
