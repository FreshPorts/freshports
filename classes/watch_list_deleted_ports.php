<?php
	#
	# $Id: watch_list_deleted_ports.php,v 1.2 2006-12-17 11:37:22 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for getting deleted ports on a watch list
class WatchListDeletedPorts {

	var $name_old;
	var $category_old;
	var $name_new;
	var $category_new;

	var $LocalResult;

	function WatchListDeletedPorts($dbh) {
		$this->dbh	= $dbh;
	}

    function _PopulateValues($myrow) {
		$this->name_old     = $myrow['name_old'];
		$this->category_old = $myrow['category_old'];
		$this->name_new     = $myrow['name_new'];
		$this->category_new = $myrow['category_new'];
	}

	function FetchInitialise($WatchListID) {
		# fetch all rows in ports_moved with To_port_id = $PortID

		$Debug = 0;

		$sql = "SELECT * from WatchListDeletedPorts(" . pg_escape_string($WatchListID) . ")";

		if ($Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
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
