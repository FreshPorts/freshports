<?php
	#
	# $Id: ports_moved.php,v 1.1.2.1 2003-12-31 16:05:34 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for user tasks
class PortsMoved {

	var $ports_moved_id;
	var $port;
	var $category;
	var $date;
	var $reason;

	var $dbh;
	var $LocalResult;

	function PortsMoved($dbh) {
		$this->dbh	= $dbh;
	}

	function _PopulateValues($myrow) {
		$this->ports_moved_id = $myrow['ports_moved_id'];
		$this->port           = $myrow['port'];
		$this->category       = $myrow['category'];
		$this->date           = $myrow['date'];
		$this->reason         = $myrow['reason'];
	}

	function FetchInitialiseTo($PortID) {
		# fetch all rows in ports_moved with from_port_id = $PortID

		$Debug = 0;

		$sql = "
  SELECT ports_moved.id     as ports_moved_id,
         element.name       as port,
         categories.name    as category,
         ports_moved.date   as date,
         ports_moved.reason as reason
    FROM ports_moved 
         LEFT OUTER JOIN ports      ON ports.id         = ports_moved.to_port_id
         LEFT OUTER JOIN categories ON categories.id    = ports.category_id
         LEFT OUTER JOIN element    ON ports.element_id = element.id
   WHERE from_port_id = $PortID
ORDER BY date desc"
;
		if ($Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($this->LocalResult);
#				$this->_PopulateValues($myrow);

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
