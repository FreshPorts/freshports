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

	function __construct($dbh) {
		$this->dbh = $dbh;
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
 where CLPV.port_id  = $1
   and CLPV.vuxml_id = vuxml.id
 order by CLPV.commit_log_id ";

#		echo "\$sql='<pre>$sql</pre><br>\n";
		$this->result = pg_query_params($this->dbh, $sql, array($port_id));
		if (!$this->result) {
			echo pg_last_error($this->dbh) . " $sql";
		}
		$numrows = pg_num_rows($this->result);

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$myrow = pg_fetch_array($this->result, $N);

		$this->id            = $myrow['id'];
		$this->commit_log_id = $myrow['commit_log_id'];
		$this->port_id       = $myrow['port_id'];
		$this->vuxml_id      = $myrow['vuxml_id'];

		$this->vid           = $myrow['vid'];
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
