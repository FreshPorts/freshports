<?
	# $Id: commit_log_ports.php,v 1.1.2.7 2002-04-12 05:11:47 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#


// base class for commit_log_ports
class Commit_Log_Ports {

	var $dbh;

	var $id;
	var $message_id;
	var $commit_date;
    var $description;
	var $committer;
	var $encoding_losses;

	var $result;

	function Commit_Log_Ports($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchInitialise($port_id) {

		# get ready to fetch all the commit_log_ports for this port
		# return the number of commits found

		$sql = "select	commit_log.id, 
						port_id,
						message_id,
					    to_char(commit_date - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS')  as commit_date,
						description,
						committer,
						encoding_losses
				   from commit_log, commit_log_ports
				  where commit_log.id             = commit_log_ports.commit_log_id
					and commit_log_ports.port_id  =  $port_id
				  order by commit_log.commit_date desc ";

		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
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

		$this->id				= $myrow["id"];
		$this->port_id			= $myrow["port_id"];
		$this->message_id		= $myrow["message_id"];
		$this->commit_date		= $myrow["commit_date"];
		$this->description		= $myrow["description"];
		$this->committer		= $myrow["committer"];
		$this->encoding_losses	= $myrow["encoding_losses"];
	}

}
