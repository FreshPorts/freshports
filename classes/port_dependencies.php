<?php
	#
	# $Id: port_dependencies.php,v 1.4 2011-02-07 00:38:59 dan Exp $
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
	var $status;

	var $dbh;
	var $LocalResult;

	function __construct($dbh) {
		$this->dbh	= $dbh;
	}

	function _PopulateValues($myrow) {
		$this->port_id                = $myrow['port_id'];
		$this->port_id_dependent_upon = $myrow['port_id_dependent_upon'];
		$this->dependency_type        = $myrow['dependency_type'];
		$this->category               = $myrow['category'];
		$this->port                   = $myrow['port'];
		$this->status                 = $myrow['status'];
	}

	function FetchInitialise( $PortID, $depends_type ) {
		# fetch all rows in port_dependencies with port_id = $PortID

		$Debug = 0;

		$sql = '
  SELECT port_id,
         port_id_dependent_upon,
         dependency_type,
         categories.name    AS category,
         element.name       AS port,
         ports.status       AS status
    FROM port_dependencies
         LEFT OUTER JOIN ports      ON ports.id         = port_dependencies.port_id
         LEFT OUTER JOIN categories ON categories.id    = ports.category_id
         LEFT OUTER JOIN element    ON ports.element_id = element.id
   WHERE port_id_dependent_upon = $1
     AND dependency_type        = $2
ORDER BY status, category, port ';

		if ($Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_query_params($this->dbh, $sql, array($PortID, $depends_type));
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($this->LocalResult);
				$this->_PopulateValues($myrow);

			}
		} else {
			echo 'pg_query_params failed: <pre>' . $sql . '</pre> : ' . pg_last_error($this->dbh);
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
