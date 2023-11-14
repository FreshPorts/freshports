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

	function __construct($dbh) {
		parent::__construct($dbh);
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

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
			SELECT count(DISTINCT CL.id) AS    count
			  FROM element_pathname EP, commit_log_ports_elements CLPE, commit_log CL
			 WHERE $this->TreePathCondition
			   AND EP.element_id = CLPE.element_ID
			   AND CL.id         = CLPE.commit_log_id";
   
		if ($this->Debug) echo "<pre>$sql</pre>";
		$result = pg_query($this->dbh, $sql);
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

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ ."
			SELECT count(DISTINCT CL.id) AS                 count
			  FROM element_pathname EP, commit_log_elements CLE, commit_log CL
			 WHERE $this->TreePathCondition
			   AND EP.element_id = CLE.element_ID
			   AND CL.id         = CLE.commit_log_id";
   
		if ($this->Debug) echo "<pre>$sql</pre>";
		$result = pg_query($this->dbh, $sql);
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
		$params = array();
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ ."
	 SELECT commit_log.commit_date - SystemTimeAdjust()                                                                         AS commit_date_raw,
			commit_log.id                                                                                               AS commit_log_id,
			commit_log.encoding_losses                                                                                  AS encoding_losses,
			commit_log.message_id                                                                                       AS message_id,
			commit_log.commit_hash_short                                                                                AS commit_hash_short,
			commit_log.committer                                                                                        AS committer,
                        commit_log.committer_name                                                                                   AS committer_name,
                        commit_log.committer_email                                                                                  AS committer_email,
                        commit_log.author_name                                                                                      AS author_name,
                        commit_log.author_email                                                                                     AS author_email,
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
			null                                                                                                        AS stf_message";
		if ($this->UserID) {
				$sql .= ",
		        onwatchlist ";
	        } else {
				$sql .= ",
		        null AS onwatchlist ";
		}

		$sql .= "
    FROM commit_log_ports, commit_log, categories, ports, element ";

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
	  WHERE commit_log.id IN (SELECT tmp.id FROM (SELECT DISTINCT CL.id, CL.commit_date
  FROM element_pathname EP, commit_log_elements CLE, commit_log CL
 WHERE $this->TreePathCondition
   AND EP.element_id = CLE.element_ID
   AND CL.id         = CLE.commit_log_id
ORDER BY CL.commit_date DESC ";

   		if ($this->Limit) {
			$sql .= " LIMIT $" . count($params) + 1;
			$params[] = $this->Limit;
		}
		
		if ($this->Offset) {
			$sql .= " OFFSET $" . count($params) + 1;
			$params[] = $this->Offset;
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

	# neither of these arguments are used in this function
	# they are present to be compatible with the parent class
	function Fetch($date = null, $UserID = null) {
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
with mycommits as (
SELECT tmp.ID as commit_log_id FROM (SELECT DISTINCT CL.id, CL.commit_date
   FROM element_pathname EP, commit_log_elements CLE, commit_log CL
  WHERE " . $this->TreePathCondition . "
    AND EP.element_id = CLE.element_ID
    AND CL.id         = CLE.commit_log_id
 ORDER BY CL.commit_date DESC\n";

		if ($this->Limit) {
			$params[] = $this->Limit;
			$sql .= "\nLIMIT $" . count($params);
		}

		if ($this->Offset) {
			$params[] = $this->Offset;
			$sql .= "\nOFFSET $" . count($params) ;
		}

		$sql .= "
) as tmp)
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
                         null                                                                                                AS port,
                         null                                                                                                AS pathname,
                         null                                                                                                AS status,
                         null                                                                                                AS element_pathname,
                         CL.message_subject,
                         CL.svn_revision                                                                                     AS svn_revision,
                         NULL AS port_id,
                         0    AS needs_refresh,
                         NULL AS forbidden,
                         NULL AS broken,
                         NULL AS deprecated,
                         NULL AS ignore,
                         null as element_id,
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
                         null as revision,
                         R.name          AS repo_name,
                         R.repository    AS repository,
                         R.repo_hostname AS repo_hostname,
                         R.path_to_repo  AS path_to_repo,
                         null AS onwatchlist ";

		# I tried to make '$this->TreePathCondition' a parameter. I was blocked by this error:
		# Warning: pg_query_params(): Query failed: ERROR: invalid input syntax for type boolean: ""EP.pathname = '/ports/head/MOVED'"" in /usr/local/www/freshports/classes/commits_by_tree_location.php on line 291
		# pg_query_params failed:

		$sql .= "
	       FROM mycommits MC join commit_log CL on MC.commit_log_id = CL.id
                       LEFT OUTER JOIN repo R on  CL.repo_id = R.id ";


   		$sql .= "
   ORDER BY 1 desc, commit_log_id, element_pathname";

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
}
