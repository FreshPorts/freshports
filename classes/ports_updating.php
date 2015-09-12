<?php
	#
	# $Id: ports_updating.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for user tasks
class PortsUpdating {

	var $id;
	var $date;
	var $affects;
	var $author;
	var $reason;

	var $dbh;
	var $LocalResult;

	function PortsUpdating($dbh) {
		$this->dbh	= $dbh;
	}

	function _PopulateValues($myrow) {
		$this->id      = $myrow['id'];
		$this->date    = $myrow['date'];
		$this->affects = $myrow['affects'];
		$this->author  = $myrow['author'];
		$this->reason  = $myrow['reason'];
	}

	function FetchInitialise($PortID) {
		# fetch all rows in ports_updating with id = $PortID

		$Debug = 0;

		$sql = "
  SELECT PU.id,
         PU.date,
         PU.affects,
         PU.author,
         PU.reason,
         E.name       as port,
         C.name       as category
    FROM ports_updating PU, ports_updating_ports_xref PUPX, ports, categories C, element E
   WHERE PUPX.port_id           = " . pg_escape_string($PortID) . "
     AND PUPX.ports_updating_id = PU.id
     AND PUPX.port_id           = ports.id
     AND ports.category_id      = C.id
     AND ports.element_id       = E.id
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
