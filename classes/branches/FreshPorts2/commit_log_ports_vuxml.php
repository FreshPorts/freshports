<?php
	# $Id: commit_log_ports_vuxml.php,v 1.1.2.1 2004-08-27 11:08:45 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for commit_log_ports_vuxml
class Commit_Log_Ports_VuXML {

	var $dbh;

	var $id;
	var $commit_log_id;
	var $port_id;
	var $vuxml_id;

	var $result;

	function Commit_Log_Ports_VuXML($dbh) {
		$this->dbh	= $dbh;
	}
	
	function FetchInitialise($port_id) {

		# get ready to fetch all the commit_log_ports_vuxml for this port
		# return the number of commits found

		$sql = "
select id,
       commit_log_id, 
       port_id,
       vuxml_id
  from commit_log_ports_vuxml
 where commit_log_ports_vuxml.port_id = $port_id
 order by commit_log_ports_vuxml.commit_log_id ";

#		echo "\$sql='<pre>$sql</pre><br>\n";
		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_numrows($this->result);

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$myrow = pg_fetch_array($this->result, $N);

		$this->id				= $myrow['id'];
		$this->commit_log_id	= $myrow['commit_log_id'];
		$this->port_id			= $myrow['port_id'];
		$this->vuxml_id			= $myrow['vuxml_id'];
	}

	function VuXML_List_Get($port_id) {
#print 'VuXML_List_Get for ' . $port_id . "<br>\n";
		$numrows = $this->FetchInitialise($port_id);
		for ($i = 0; $i < $numrows; $i++) {
			$this->FetchNth($i);
			if (IsSet($VID[$this->commit_log_id])) {
				$VID[$this->commit_log_id] = $VID[$this->commit_log_id] . '|' . $this->vuxml_id;
			} else {
				$VID[$this->commit_log_id] = $this->vuxml_id;
			}
#print 'row ' . $i . ' has ' . $this->commit_log_id . " with " .  $VID[$this->commit_log_id] . "<br>\n";

		}

		return $VID;
	}

}
