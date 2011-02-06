<?php
	#
	# $Id: port_dependencies.php,v 1.3 2011-02-06 14:45:46 dan Exp $
	#
	# Copyright (c) 1998-2011 DVL Software Limited
	#


// base class for port dependencies
class PortDependencies {

	var $port_id;
	var $port_id_dependent_upon;
	var $dependency_type;
	
	var $category;  // of the dependent port
	var $port;      // of the dependent port

	var $dbh;
	var $LocalResult;

	function PortDependencies($dbh) {
		$this->dbh	= $dbh;
	}

	function _PopulateValues($myrow) {
		$this->port_id                = $myrow['port_id'];
		$this->port_id_dependent_upon = $myrow['port_id_dependent_upon'];
		$this->dependency_type        = $myrow['dependency_type'];
		$this->category               = $myrow['category'];
		$this->port                   = $myrow['port'];
	}

	function FetchInitialise( $PortID, $depends_type ) {
		# fetch all rows in port_dependencies with port_id = $PortID

		$Debug = 0;

		$sql = "
  SELECT port_id,
         port_id_dependent_upon,
         dependency_type,
         categories.name    as category,
         element.name       as port
    FROM port_dependencies
         LEFT OUTER JOIN ports      ON ports.id         = port_dependencies.port_id
         LEFT OUTER JOIN categories ON categories.id    = ports.category_id
         LEFT OUTER JOIN element    ON ports.element_id = element.id
   WHERE port_id_dependent_upon = $PortID
     AND dependency_type = '" . pg_escape_string($depends_type) . "'";

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

?>