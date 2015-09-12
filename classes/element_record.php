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

	var	$element_pathname;

	function ElementRecord($dbh) {
		$this->dbh = $dbh;
	}

	function PopulateValues($myrow) {
		$this->id				= $myrow['id'];
		$this->name				= $myrow['name'];
		$this->type				= $myrow['type'];
		$this->status			= $myrow['status'];
		$this->iscategory		= $myrow['iscategory'];
		$this->isport			= $myrow['isport'];

		$this->element_pathname	= $myrow['element_pathname'];
	}

	function FetchByName($Name) {
		if (IsSet($Name)) {
			$this->element_pathname = $Name;
			$this->id = '';
		}
		$sql = "select * from elementGet('" . pg_escape_string($Name) . "')";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->PopulateValues($myrow);
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

?>