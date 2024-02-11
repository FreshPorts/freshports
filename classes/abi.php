<?php
	#
	# $Id: watch_lists.php,v 1.2 2006-12-17 11:37:22 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_list.php');

// base class for fetching watch lists
class ABI {

	var $dbh;
	var $LocalResult;
	var $Debug;

	var $id;
	var $name;

	function __construct($dbh) {
		$this->dbh   = $dbh;
		$this->Debug = 0;
	}

	function Fetch() {
		$this->Debug = 0;

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '
		SELECT id,
			   name
		  FROM abi
		 WHERE active
	 ORDER BY name';
		$params = array();

		if ($this->Debug) {
			echo 'ABI::Fetch sql = <pre>' . $sql . '</pre>';
		}

		$this->LocalResult = pg_query_params($this->dbh, $sql, $params);
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_query_params failed: ' . $sql;
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch
		#

#		echo "fetching row $N<br>";

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->PopulateValues($myrow);

		return 1;
	}

	function PopulateValues($myrow) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch.
		#

		$this->id               = $myrow["id"];
		$this->name             = $myrow["name"];
	}

}
