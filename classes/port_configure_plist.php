<?php
	#
	# $Id: port_dependencies.php,v 1.4 2011-02-07 00:38:59 dan Exp $
	#
	# Copyright (c) 1998-2011 DVL Software Limited
	#


// base class for port configure plist
class PortConfigurePlist {

	var $port_id;
	var $installed_file;
	
	var $dbh;
	var $LocalResult;

	function PortConfigurePlist($dbh) {
		$this->dbh	= $dbh;
	}

	function _PopulateValues($myrow) {
		$this->port_id        = $myrow['port_id'];
		$this->installed_file = $myrow['installed_file'];
	}

	function FetchInitialise( $PortID ) {
		# fetch all rows in configure_plist with port_id = $PortID

		$Debug = 0;

		$sql = "
  SELECT port_id,
         installed_file
    FROM generate_plist
   WHERE port_id = " . pg_escape_string($PortID) . "
   ORDER BY installed_file";

		if ($Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($this->LocalResult);
				$this->_PopulateValues($myrow);

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
