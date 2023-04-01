<?php
	# $Id: commit_log_ports_ignore.php,v 1.2 2006-12-17 11:37:19 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for commit_log_ports_ignore
# not sure this class is used: dvl 2023-04-01
class Commit_Log_Ports_Ignore {

	var $dbh;

	var $id;
	var $commit_log_id;
	var $port_id;
	var $date_ignored;
	var $reason;

	var $result;

	function __construct($dbh) {
		$this->dbh = $dbh;
	}
	
	function CommitLogIDSet($commit_log_id) {
		$this->commit_log_id = $commit_log_id;
	}

	function PortIDSet($port_id) {
		$this->port_id = $port_id;
	}

	function ReasonSet($reason) {
		$this->reason = $reason;
	}

	function Delete() {
		# delete the ignore entry for this commit/port combination

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
DELETE from commit_log_ports_ignore
 WHERE commit_log_id = $1
   AND port_id       = $2";

		echo ("\$sql='<pre>$sql</pre><br>\n");
		
		$this->result = pg_query_params($this->dbh, $sql, array($this->commit_log_id, $this->commit_log_id));
		if (!$this->result) {
			echo pg_last_error($this->dbh) . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	function Insert() {
		# delete the ignore entry for this commit/port combination

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
INSERT INTO commit_log_ports_ignore (commit_log_id, port_id, reason)
   values ($1, $2, $3)";

		echo "\$sql='<pre>$sql</pre><br>\n";
		
		$this->result = pg_query_params($this->dbh, $sql, array($this->commit_log_id, $this->port_id, $this->reason));
		if (!$this->result) {
			echo pg_last_error($this->dbh) . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	

}
