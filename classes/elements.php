<?php
	# $Id: elements.php,v 1.2 2006-12-17 11:37:20 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for element
class Element {

	var $Active		= 'A';
	var $Deleted	= 'D';
	var $dbh;

	var $id;
	var $name;
	var $parent_id;
	var $directory_file_flag;
	var $status;
	var $pathname;

	function Element($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByID($id) {
		if (IsSet($id)) {
			$this->id = $id;
		}
		$sql = "select *, element_pathname(id) as pathname from element where id = " . pg_escape_string($this->id);
		if ($Debug) echo "sql = '$sql'<BR>";

        $result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result, 0);
				$this->id					= $myrow["id"];
				$this->name					= $myrow["name"];
				$this->parent_id			= $myrow["parent_id"];
				$this->directory_file_flag	= $myrow["directory_file_flag"];
				$this->status				= $myrow["status"];
				$this->pathname				= $myrow["pathname"];
			}
		}

		return $this->id;
	}

	
	function FetchByName($pathname) {
		# obtain the element based on the pathname supplied
		$sql = "select Pathname_ID('" . pg_escape_string($pathname) . "') as id";

		if ($Debug)	echo "Element::FetchByName sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->id = $myrow["id"];
				if ($Debug) echo "id for '$pathname' is $this->id<BR>";

				if (IsSet($this->id)) {
					return $this->FetchByID($this->id);
				}
			}
		} else {
			echo 'pg_exec failed: ' . $sql;
		}
	}
}
