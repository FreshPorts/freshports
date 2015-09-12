<?php
	#
	# $Id: ports_moved.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for user tasks
class PortsMoved {

	var $from_port_id;
	var $to_port_id;
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
		$this->from_port_id   = $myrow['from_port_id'];
		$this->to_port_id     = $myrow['to_port_id'];
		$this->ports_moved_id = $myrow['ports_moved_id'];
		$this->port           = $myrow['port'];
		$this->category       = $myrow['category'];
		$this->date           = $myrow['date'];
		$this->reason         = $myrow['reason'];
	}

	function FetchInitialiseFrom($PortID) {
		# fetch all rows in ports_moved with from_port_id = $PortID

		$Debug = 0;

		$sql = "
  SELECT from_port_id,
         to_port_id,
         ports_moved.id     as ports_moved_id,
         element.name       as port,
         categories.name    as category,
         ports_moved.date   as date,
         ports_moved.reason as reason
    FROM ports_moved 
         LEFT OUTER JOIN ports      ON ports.id         = ports_moved.to_port_id
         LEFT OUTER JOIN categories ON categories.id    = ports.category_id
         LEFT OUTER JOIN element    ON ports.element_id = element.id
   WHERE from_port_id = " . pg_escape_string($PortID) . "
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

	function FetchInitialiseTo($PortID) {
		# fetch all rows in ports_moved with To_port_id = $PortID

		$Debug = 0;

		$sql = "
  SELECT from_port_id,
         to_port_id,
         ports_moved.id     as ports_moved_id,
         element.name       as port,
         categories.name    as category,
         ports_moved.date   as date,
         ports_moved.reason as reason
    FROM ports_moved 
         LEFT OUTER JOIN ports      ON ports.id         = ports_moved.from_port_id
         LEFT OUTER JOIN categories ON categories.id    = ports.category_id
         LEFT OUTER JOIN element    ON ports.element_id = element.id
   WHERE to_port_id    = " . pg_escape_string($PortID) . "
     AND from_port_id <> " . pg_escape_string($PortID) . "
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

