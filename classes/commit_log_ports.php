<?php
	#
	# $Id: commit_log_ports.php,v 1.2 2006-12-17 11:37:19 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


// base class for commit_log_ports
class Commit_Log_Ports {

	var $dbh;

	var $id;
	var $svn_revision;
	var $port_id;
	var $message_id;
	var $commit_hash_short;
	var $commit_date;
	var $description;
	var $committer;
	var $committer_name;
	var $committer_email;
	var $author_name;
	var $author_email;
	var $encoding_losses;
	var $port_version;
	var $port_revision;
	var $port_epoch;
	var $needs_refresh;
	var $stf_message;
	var $repo_hostname;
	var $path_to_repo;

	var $result;
	var $Debug = 0;

	var $Limit;
	var $Offset;

	function __construct($dbh) {
		$this->dbh    = $dbh;
		$this->Limit  = '';
		$this->Offset = '';
	}
	
	function CommitLogIDSet($commit_log_id) {
		$this->commit_log_id = $commit_log_id;
	}

	function PortIDSet($port_id) {
		$this->port_id = $port_id;
	}

	function Count($port_id) {

		# how many commits do we have for this port?

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
   SELECT count(*)
     FROM commit_log           CL,
          commit_log_ports     CLP
    WHERE CL.id       = CLP.commit_log_id
      AND CLP.port_id = $1";

		if ($this->Debug) echo "\$sql='<pre>$sql</pre><br>\n";
		$this->result = pg_query_params($this->dbh, $sql, array($port_id));
		if (!$this->result) {
			syslog(LOG_ERR, pg_last_error($this->dbh) . " $sql");
			die('that query failed.  details have been logged');
		}

		$myrow = pg_fetch_array ($this->result);
		$numrows = $myrow[0];
		
		return $numrows;
		return $numrows;
	}

	function LimitSet($Limit) {
		$this->Limit = $Limit;
	}
 
	function OffsetSet($Offset) {
		$this->Offset = $Offset;
	}
 
	function FetchInitialise($port_id) {

		# get ready to fetch all the commit_log_ports for this port
		# return the number of commits found

		$params = array($port_id);
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
   SELECT CL.id,
          CL.svn_revision,
          R.name          AS repo_name,
          R.repo_hostname,
          R.path_to_repo,
          port_id,
          message_id,
          commit_hash_short,
          to_char(commit_date - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') AS commit_date,
          CL.description,
          committer,
          committer_name,
          committer_email,
          author_name,
          author_email,
          encoding_losses,
          port_version,
          port_revision,
          port_epoch,
          needs_refresh,
          STF.message AS stf_message
     FROM commit_log           CL  LEFT OUTER JOIN repo R ON CL.repo_id = R.id,
          commit_log_ports     CLP LEFT OUTER JOIN
          sanity_test_failures STF on 
            CLP.commit_log_id = STF.commit_log_id
    WHERE CL.id       = CLP.commit_log_id
      AND CLP.port_id = $1
 ORDER BY CL.commit_date desc, CL.id desc ";
 
 		if ($this->Limit) {
			$sql .= " LIMIT $" . count($params) + 1;
			$params[] = $this->Limit;
		}

		if ($this->Offset) {
			$sql .= " OFFSET $" . count($params) + 1;
			$params[] = $this->Offset;
		}

		if ($this->Debug) echo "\$sql='<pre>$sql</pre><br>\n";
		$this->result = pg_query_params($this->dbh, $sql, $params);
		if (!$this->result) {
			syslog(LOG_ERR, pg_last_error($this->dbh) . " $sql");
			die('that query failed.  details have been logged');
		}
		$numrows = pg_num_rows($this->result);

		return $numrows;
	}

	function FetchNthCommit($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$myrow = pg_fetch_array($this->result, $N);

		$this->id                 = $myrow["id"];
		$this->svn_revision       = $myrow["svn_revision"];
		$this->port_id	          = $myrow["port_id"];
		$this->message_id         = $myrow["message_id"];
		$this->commit_hash_short  = $myrow["commit_hash_short"];
		$this->commit_date        = $myrow["commit_date"];
		$this->description        = $myrow["description"];
		$this->committer          = $myrow["committer"];
		$this->committer_name     = $myrow["committer_name"];
		$this->committer_email    = $myrow["committer_email"];
		$this->author_name        = $myrow["author_name"];
		$this->author_email       = $myrow["author_email"];
		$this->encoding_losses    = $myrow["encoding_losses"];
		$this->port_version       = $myrow["port_version"];
		$this->port_revision      = $myrow["port_revision"];
		$this->port_epoch         = $myrow["port_epoch"];
		$this->needs_refresh      = $myrow["needs_refresh"];
		$this->stf_message        = $myrow["stf_message"];
		$this->repo_hostname      = $myrow["repo_hostname"];
		$this->path_to_repo       = $myrow["path_to_repo"];
	}

	function NeedsRefreshClear() {
		# Clear the needs_refresh flag for this commit/port combination
		# yeah, not used: dvl 2023-04-01

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
UPDATE commit_log_ports
   SET needs_refresh = 0
 WHERE commit_log_id = $1
   AND port_id       = $2";

		if ($this->Debug) echo "\$sql='<pre>$sql</pre><br>\n";
		
		$this->result = pg_query_params($this->dbh, $sql, array($this->commit_log_id, $this->port_id));
		if (!$this->result) {
			syslog(LOG_ERR, pg_last_error($this->dbh) . " $sql");
			die('that query failed.  details have been logged');
		} else {
			$this->needs_refresh = 0;
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	function EncodingLosses() {
		return $this->encoding_losses == 't';
	}

}
