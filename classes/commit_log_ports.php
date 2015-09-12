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
	var $message_id;
	var $commit_date;
	var $description;
	var $committer;
	var $encoding_losses;
	var $port_version;
	var $port_revision;
	var $port_epoch;
	var $needs_refresh;
	var $stf_message;
	var $svn_hostname;
	var $path_to_repo;

	var $result;
	var $Debug = 0;

	var $Limit;
	var $Offset;

	function Commit_Log_Ports($dbh) {
		$this->dbh	  = $dbh;
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

		$sql = "
   SELECT count(*)
     FROM commit_log           CL,
          commit_log_ports     CLP
    WHERE CL.id       = CLP.commit_log_id
      AND CLP.port_id = " . pg_escape_string($port_id);

		if ($this->Debug) echo "\$sql='<pre>$sql</pre><br>\n";
		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			syslog(LOG_ERR, pg_errormessage() . " $sql");
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

		$sql = "
   SELECT CL.id,
          CL.svn_revision,
          R.svn_hostname,
          R.path_to_repo,
          port_id,
          message_id,
          to_char(commit_date - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') AS commit_date,
          CL.description,
          committer,
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
      AND CLP.port_id = " . pg_escape_string($port_id) . "
 ORDER BY CL.commit_date desc ";
 
 		if ($this->Limit) {
 			$sql .= ' LIMIT ' . pg_escape_string($this->Limit);
		}

		if ($this->Offset) {
			$sql .= ' OFFSET ' . pg_escape_string($this->Offset);
		}

		if ($this->Debug) echo "\$sql='<pre>$sql</pre><br>\n";
		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			syslog(LOG_ERR, pg_errormessage() . " $sql");
			die('that query failed.  details have been logged');
		}
		$numrows = pg_numrows($this->result);

		return $numrows;
	}

	function FetchNthCommit($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$myrow = pg_fetch_array($this->result, $N);

		$this->id			= $myrow["id"];
		$this->svn_revision		= $myrow["svn_revision"];
		$this->port_id			= $myrow["port_id"];
		$this->message_id		= $myrow["message_id"];
		$this->commit_date		= $myrow["commit_date"];
		$this->description		= $myrow["description"];
		$this->committer		= $myrow["committer"];
		$this->encoding_losses		= $myrow["encoding_losses"];
		$this->port_version		= $myrow["port_version"];
		$this->port_revision		= $myrow["port_revision"];
		$this->port_epoch		= $myrow["port_epoch"];
		$this->needs_refresh		= $myrow["needs_refresh"];
		$this->stf_message		= $myrow["stf_message"];
		$this->svn_hostname             = $myrow["svn_hostname"];
		$this->path_to_repo             = $myrow["path_to_repo"];
	}

	function NeedsRefreshClear() {
		# Clear the needs_refresh flag for this commit/port combination

		$sql = "
UPDATE commit_log_ports
   SET needs_refresh = 0
 WHERE commit_log_id = " . pg_escape_string($this->commit_log_id) . "
   AND port_id       = " . pg_escape_string($this->port_id);

		if ($this->Debug) echo "\$sql='<pre>$sql</pre><br>\n";
		
		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			syslog(LOG_ERR, pg_errormessage() . " $sql");
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
