<?php
	#
	# $Id: commits_by_tree_location.php,v 1.6 2012-12-21 18:20:53 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/commits.php");

// base class for fetching commits under a given path
class CommitsByTreeLocation extends commits {

	#
	# a condition for the tree path comparison
	# e.g. LIKE '/src/sys/i386/conf/%'
	#
	var $TreePathCondition = '';

	function CommitsByTreeLocation($dbh) {
		parent::Commits($dbh);
	}
	
	function TreePathConditionSet($TreePathCondition) {
		# this function assumes you have the operator and the value.
		$this->TreePathCondition = "EP.pathname   " . $TreePathCondition;
	}

	function TreePathConditionSetAll($TreePathCondition) {
		# this function assumes you are setting the entire condition.
		$this->TreePathCondition = $TreePathCondition;
	}

	function GetCountPortCommits() {
		$count = 0;

		$sql = "
			SELECT count(DISTINCT CL.id) AS count
			  FROM element_pathname EP, commit_log_ports_elements CLPE, commit_log CL
			 WHERE $this->TreePathCondition
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

	function GetCountCommits() {
		$count = 0;

		$sql = "
			SELECT count(DISTINCT CL.id) AS count
			  FROM element_pathname EP, commit_log_elements CLE, commit_log CL
			 WHERE $this->TreePathCondition
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

	function FetchPortCommits() {
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
		if ($this->UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports, commit_log, categories, ports, element ";

		if ($this->UserID) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = " . pg_escape_string($this->UserID) . "
	       AND watch_list.in_service		
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = element.id";
		}

		$sql .= "
	  WHERE commit_log.id IN (SELECT tmp.id FROM (SELECT DISTINCT CL.id, CL.commit_date
  FROM element_pathname EP, commit_log_elements CLE, commit_log CL
 WHERE $this->TreePathCondition
   AND EP.element_id = CLE.element_ID
   AND CL.id         = CLE.commit_log_id
ORDER BY CL.commit_date DESC ";

   		if ($this->Limit) {
			$sql .= " LIMIT " . pg_escape_string($this->Limit);
		}
		
		if ($this->Offset) {
			$sql .= " OFFSET " . pg_escape_string($this->Offset);
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
	function Fetch() {
		$sql = "
		SELECT DISTINCT
			CL.commit_date - SystemTimeAdjust()                                                                 AS commit_date_raw,
			CL.id                                                                                               AS commit_log_id,
			CL.encoding_losses                                                                                  AS encoding_losses,
			CL.message_id                                                                                       AS message_id,
			CL.committer                                                                                        AS committer,
			CL.description                                                                                      AS commit_description,
			to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')                                         AS commit_date,
			to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI')                                             AS commit_time,
			element.name                                                                                                AS port,
			element_pathname(element.id)                                                                                AS pathname,
			element.status                                                                                              AS status,
			element_pathname.pathname                            as element_pathname,
			CL.message_subject,
			NULL AS port_id,
			0    AS needs_refresh,
			NULL AS forbidden,
			NULL AS broken,
			NULL AS deprecated,
			NULL AS ignore,
			commit_log_elements.element_id,
			NULL AS version,
			NULL AS epoch,
			NULL as date_added,
			NULL AS short_description,
			NULL AS category_id,
			NULL AS category,
			NULL AS watch,
			NULL AS vulnerable_current,
			NULL AS vulnerable_past,
			NULL AS restricted,
			NULL AS no_cdrom,
			NULL AS expiration_date,
			NULL AS is_interactive,
			NULL AS only_for_archs,
			NULL AS not_for_archs,
			NULL AS stf_message,
			commit_log_elements.revision_name as revision,
			R.name         AS repo_name,
			R.svn_hostname AS hostname,
			R.path_to_repo AS path_to_repo ";
		if ($this->UserID) {
				$sql .= ",
	        onwatchlist ";
		}

		$sql .= "
    FROM commit_log_elements, commit_log CL LEFT OUTER JOIN repo R on  CL.repo_id = R.id, element_pathname, element ";

		if ($this->UserID) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = " . pg_escape_string($this->UserID) . "
	       AND watch_list.in_service		
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = element.id";
		}

		$sql .= "
	  WHERE CL.id IN (SELECT tmp.ID FROM (SELECT DISTINCT CL.id, CL.commit_date
  FROM element_pathname EP, commit_log_elements CLE, commit_log CL
 WHERE $this->TreePathCondition
   AND EP.element_id = CLE.element_ID
   AND CL.id         = CLE.commit_log_id
ORDER BY CL.commit_date DESC ";

		if ($this->Limit) {
			$sql .= "\nLIMIT " . pg_escape_string($this->Limit);
		}
		
		if ($this->Offset) {
			$sql .= "\nOFFSET " . pg_escape_string($this->Offset);
		}

   		$sql .= ") AS tmp)
	    AND commit_log_elements.commit_log_id = CL.id
	    AND commit_log_elements.element_id    = element.id
        AND element_pathname.element_id       = element.id
   ORDER BY 1 desc,
			commit_log_id, element_pathname";
			



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

