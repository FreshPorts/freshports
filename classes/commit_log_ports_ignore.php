<?php
	# $Id: commit_log_ports_ignore.php,v 1.2 2006-12-17 11:37:19 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for commit_log_ports_ignore
class Commit_Log_Ports_Ignore {

	var $dbh;

	var $id;
	var $commit_log_id;
	var $port_id;
	var $date_ignored;
	var $reason;

	var $result;

	function Commit_Log_Ports_Ignore($dbh) {
		$this->dbh	= $dbh;
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

		$sql = "
DELETE from commit_log_ports_ignore
 WHERE commit_log_id = " . pg_escape_string($this->commit_log_id) . "
   AND port_id       = " . pg_escape_string($this->port_id);

		echo ("\$sql='<pre>$sql</pre><br>\n");
		
		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	function Insert() {
		# delete the ignore entry for this commit/port combination

		$sql = "
INSERT INTO commit_log_ports_ignore (commit_log_id, port_id, reason)
   values (" . pg_escape_string($this->commit_log_id) . ", " . pg_escape_string($this->port_id) . ", '" . pg_escape_string($this->reason) . "')";

		echo "\$sql='<pre>$sql</pre><br>\n";
		
		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	

}
