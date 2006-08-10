<?php
	#
	# $Id: categories.php,v 1.1.2.21 2006-08-10 18:32:16 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for Category
class Category {

	var $dbh;

	var $id;
	var $is_primary;  # will contain either 't' or 'f'
	var $element_id;
	var $name;
	var $description;

	function Category($dbh) {
		$this->dbh	= $dbh;
	}
	
	function Populate($myrow) {
		$this->id				= $myrow["id"];
		$this->is_primary		= $myrow["is_primary"];
		$this->element_id		= $myrow["element_id"];
		$this->name				= $myrow["name"];
		$this->description		= $myrow["description"];
		$this->last_modified	= $myrow["last_modified"];
	}

	function FetchByID($id) {
		$Debug = 0;

		if (IsSet($id)) {
			$this->id = $id;
		}

		# Get the category details, and the date of the
		# last modified port therein
		#
		$sql = '
SELECT C.*, (SELECT MAX(CL.date_added)
               FROM ports            P,
                    commit_log       CL,
                    ports_categories PC
              WHERE PC.port_id       = P.id
                AND P.last_commit_id = CL.id
                AND PC.category_id   = C.id) AS last_modified
  FROM categories C
 WHERE id = ' . $this->id;

		if ($Debug) echo "sql = '$sql'<BR>";

        $result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result, 0);
				$this->Populate($myrow);
			}
		}

        return $this->id;
	}

	function FetchByElementID($element_id) {

		$Debug = 0;

		if (IsSet($element_id)) {
			$this->element_id = $element_id;
		}
		$sql = '
SELECT C.*, (SELECT MAX(CL.date_added)
               FROM ports            P,
                    commit_log       CL,
                    ports_categories PC
              WHERE PC.port_id       = P.id
                AND P.last_commit_id = CL.id
                AND PC.category_id   = C.id) AS last_modified
  FROM categories C
 WHERE C.element_id = ' . $this->element_id;
		if ($Debug) echo "sql = '$sql'<BR>";

        $result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result, 0);
				$this->Populate($myrow);
			}
		}

        return $this->id;
	}

	function FetchByName($Name) {

		$Debug = 0;

		Unset($CategoryID);

		if (IsSet($Name)) {
			$this->name = AddSlashes($Name);
			unset($this->id);
		}
		$sql = "
SELECT C.*, (SELECT MAX(CL.date_added)
               FROM ports            P,
                    commit_log       CL,
                    ports_categories PC
              WHERE PC.port_id       = P.id
                AND P.last_commit_id = CL.id
                AND PC.category_id   = C.id) AS last_modified
  FROM categories C
 WHERE C.name = '" . $this->name . "'";

		if ($Debug) echo "sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->Populate($myrow);
				$CategoryID = $this->id;
			}
		}

		return $CategoryID;
	}

	function IsCategoryByName($Name) {

		$Debug = 0;

		Unset($CategoryID);

		$sql = "SELECT id FROM categories where name = '$Name'";

		if ($Debug) echo "sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$CategoryID = $myrow['id'];
			}
		}

		return $CategoryID;
	}

	function PortCount($Name) {
		$Count = 0;
		$Debug = 0;

		if (IsSet($Name)) {
			$this->name = AddSlashes($Name);
		}
		$sql = "select CategoryPortCount('$this->name')";
		if ($Debug) echo "sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "PortCount succeeded<BR>";
				$myrow = pg_fetch_array ($result, 0);
				$Count = $myrow[0];
			}
		}

		return $Count;
	}
		

	function UpdateDescription() {
		GLOBAL $User;

		$Debug = 0;
		$sql = "UPDATE categories SET description = '" . $this->description . "' WHERE id = " . $this->id . ' AND is_primary = FALSE';
		syslog(LOG_NOTICE, 'User \'' . $User->name . '\' at '
			. AddSlashes($_SERVER[REMOTE_ADDR]) . ' is changing category \'' 
			. $this->name . '\' to \'' . $this->description . '\'.');
		if ($Debug) echo "sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);

		return  pg_affected_rows($result);
	}

	function IsPrimary() {
		return $this->is_primary == 't';
	}

}
