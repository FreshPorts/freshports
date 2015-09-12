<?php
	# $Id: commit_log_ports_vuxml.php,v 1.2 2006-12-17 11:37:19 dan Exp $
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

	var	$vid;

	var $result;

	function Commit_Log_Ports_VuXML($dbh) {
		$this->dbh	= $dbh;
	}
	
	function FetchInitialise($port_id) {

		# get ready to fetch all the commit_log_ports_vuxml for this port
		# return the number of commits found

		$sql = "
select CLPV.id,
       CLPV.commit_log_id, 
       CLPV.port_id,
       CLPV.vuxml_id,
       vuxml.vid
  from commit_log_ports_vuxml CLPV, vuxml
 where CLPV.port_id  = " . pg_escape_string($port_id) . "
   and CLPV.vuxml_id = vuxml.id
 order by CLPV.commit_log_id ";

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

		$this->vid				= $myrow['vid'];
	}

	function VuXML_List_Get($port_id) {
		$VID = array();

		$numrows = $this->FetchInitialise($port_id);
		for ($i = 0; $i < $numrows; $i++) {
			$this->FetchNth($i);
			if (IsSet($VID[$this->commit_log_id])) {
				$VID[$this->commit_log_id] = $VID[$this->commit_log_id] . '|' . $this->vid;
			} else {
				$VID[$this->commit_log_id] = $this->vid;
			}
		}

		return $VID;
	}

}
?>