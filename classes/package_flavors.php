<?php
	#
	# Copyright (c) 1998-2018 DVL Software Limited
	#


// base class used for resolving URI
class PackageFlavors {

	var $id;
	var $port_id;
	var $flavor;
	var $flavor_id;
	var $flavor_name;
	var $name;
	var $flavor_number;

	var $dbh;
	var $LocalResult;

	function __construct($dbh) {
		$this->dbh = $dbh;
	}

	function _PopulateValues($myrow) {
		$this->id            = $myrow['id'];
		$this->port_id       = $myrow['port_id'];
		$this->flavor        = $myrow['flavor'] ?? null;
		$this->flavor_id     = $myrow['flavor_id'];
		$this->flavor_name   = $myrow['flavor_name'];
		$this->name          = $myrow['name'];
		$this->flavor_number = $myrow['flavor_number'];
	}

	function FetchInitialise($PortID) {
		# this returns package flavors with the default package first
		$sql = 'SELECT * FROM PackageFlavors($1)';
		
#		echo "<pre>$sql</pre>";

		$this->LocalResult = pg_query_params($this->dbh, $sql, array($PortID));
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
			if ($numrows == 1) {
				$myrow = pg_fetch_array($this->LocalResult, 0);
			}
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise
		#

		$myrow = pg_fetch_array($this->LocalResult, $N);

		$this->_PopulateValues($myrow);
	}
}
