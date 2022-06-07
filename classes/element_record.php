<?php
	#
	# $Id: element_record.php,v 1.3 2013-04-08 12:15:34 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class used for resolving URI
class ElementRecord {

	var $dbh;

	var $id;
	var $name;
	var $type;
	var $status;
	var $iscategory;
	var $isport;

	var $element_pathname;

	function __construct($dbh) {
		$this->dbh = $dbh;
	}

	function PopulateValues($myrow) {
		$this->id         = $myrow['id'];
		$this->name       = $myrow['name'];
		$this->type       = $myrow['type'];
		$this->status     = $myrow['status'];
		$this->iscategory = $myrow['iscategory'];
		$this->isport     = $myrow['isport'];

		$this->element_pathname	= $myrow['element_pathname'];
	}

	function FetchByName($Name, $caseSensitive = true) {
		$Debug = 0;
		
		# unset this so we can see we don't get anything.
		$this->id = null;

		if ($Debug) echo "looking for '$Name' and caseSensitive is '$caseSensitive'<br>";
		if (IsSet($Name)) {
			$this->element_pathname = $Name;
		}

		if ($caseSensitive) {
			$sql = "select * from elementGet('" . pg_escape_string($this->dbh, $Name) . "')";
			if ($Debug) echo "invoking $sql<br>";
			$result = pg_exec($this->dbh, $sql);
		} else {
			$result = pg_query_params($this->dbh, 'select * from elementGetCaseInsensitive($1)', array($Name));
		}

		if ($result) {
			if ($Debug) echo "we got a result<br>";

			$numrows = pg_num_rows($result);
			if ($Debug) echo "we have '$numrows' rows<br>";
			if ($numrows == 0 ) {
				# nothing to do here
			}
			elseif ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->PopulateValues($myrow);
			} else {
				# multiple values
				return -1;
			}
		}

		return $this->id;
	}

	function IsPort() {
		return $this->isport == 't';
	}

	function IsCategory() {
		return $this->iscategory == 't';
	}

}
