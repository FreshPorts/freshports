<?php
	#
	# $Id: vuln_latest.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


// base class for user tasks
class vuln_latest {

	var $id;
	var $category;
	var $port;
	var $dater;
	var $vid;

	var $dbh;
	var $LocalResult;

	function Vuln_Latest($dbh) {
		$this->dbh	= $dbh;
	}

	function _PopulateValues($myrow) {
		$this->category = $myrow['category'];
		$this->port     = $myrow['port'];
		$this->date     = $myrow['date'];
		$this->vid      = $myrow['vid'];
	}

	function FetchInitialise($PortID) {
		# fetch all rows in ports_updating with id = $PortID

		$Debug = 0;

		$sql = "
SELECT distinct PA.category, PA.name as port, coalesce(V.date_modified, V.date_entry, V.date_discovery), V.vid
  FROM commit_log_ports_vuxml CLPV, vuxml V, ports_all PA
 WHERE CLPV.vuxml_id = V.id
   AND CLPV.port_id  = PA.id
 ORDER BY coalesce(V.date_modified, V.date_entry, V.date_discovery) desc, category, name
 LIMIT 15;";
		if ($Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($this->LocalResult);
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call FetchInitialiseTo first.
		# then call this function N times, where N is the number
		# returned by FetchInitialiseTo
		#

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->_PopulateValues($myrow);
	}

}

