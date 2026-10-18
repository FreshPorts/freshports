<?php
	#
	# $Id: categories.php,v 1.3 2013-04-07 01:19:59 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#


// base class for Category
class Category {

	var $dbh;

	var $BranchName;

	var $id;
	var $is_primary;  # will contain either 't' or 'f'
	var $element_id;
	var $name;
	var $description;

	var $Debug = 0;

	protected const FETCH_SQL = '
SELECT C.*
  FROM categories C
';

	function __construct($dbh, $BranchName = BRANCH_HEAD) {
		$this->dbh        = $dbh;
		$this->BranchName = $BranchName;
	}
	
	function Populate($myrow) {
		$this->id               = $myrow["id"];
		$this->is_primary       = $myrow["is_primary"];
		$this->element_id       = $myrow["element_id"];
		$this->name             = $myrow["name"];
		$this->description      = $myrow["description"];
	}

	function FetchByID($id) {
		if (IsSet($id)) {
			$this->id = $id;
		}

		# Get the category details
		#
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "\n" . self::FETCH_SQL . ' WHERE id = $1';

		if ($this->Debug) echo "<pre>1. sql = '$sql'</pre><br>";

		$result = pg_query_params($this->dbh, $sql, array($this->id));
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				if ($this->Debug) echo "fetched by ID succeeded<br>";
				$myrow = pg_fetch_array ($result, 0);
				$this->Populate($myrow);
			}
		}

        return $this->id;
	}

	function FetchByElementID($element_id) {

		if (IsSet($element_id)) {
			$this->element_id = $element_id;
		}
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "\n" . self::FETCH_SQL . '  WHERE C.element_id = $1';
		if ($this->Debug) echo "<pre>sql = '$sql'</pre><br>";

		$result = pg_query_params($this->dbh, $sql, array($this->element_id));
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				if ($this->Debug) echo "fetched by ID succeeded<br>";
				$myrow = pg_fetch_array ($result, 0);
				$this->Populate($myrow);
			}
		}

        return $this->id;
	}

	function FetchByName($Name) {

		$CategoryID = 0;

		if (IsSet($Name)) {
			$this->name = pg_escape_string($this->dbh, $Name);
			unset($this->id);
		}
		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "\n" . self::FETCH_SQL . " WHERE C.name = $1";

		if ($this->Debug) echo "<pre>sql = '$sql'</pre><br>";

		$result = pg_query_params($this->dbh, $sql, array($this->name));
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->Populate($myrow);
				$CategoryID = $this->id;
			}
		}

		return $CategoryID;
	}

	function IsCategoryByName($Name) {
		# I suspect this is unused - dvl 2023-04-01

		Unset($CategoryID);

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "\n" . "SELECT id FROM categories where name = $1";

		if ($this->Debug) echo "sql = '$sql'<br>";

		$result = pg_query_params($this->dbh, $sql, array($Name));
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$CategoryID = $myrow['id'];
			}
		}

		return $CategoryID;
	}

	function PortCount($Name, $Branch = BRANCH_HEAD) {
		$Count = 0;

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "\n";
		if (IsSet($Name)) {
			$this->name = pg_escape_string($this->dbh, $Name);
		}
		if ($Branch == BRANCH_HEAD) {
			$params = array($this->name);
			$sql .= "select CategoryPortCount($1)";
		} else {
			$params = array($this->name, $Branch);
			$sql .= "select CategoryPortCount($1, $2)";
		}
		if ($this->Debug) {
			echo "sql = '$sql'<br>";
			echo "Name = '$Name'<br>";
			echo "Branch = '$Branch'<br>";
		}

		$result = pg_query_params($this->dbh, $sql, $params);
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				if ($this->Debug) echo "PortCount succeeded<br>";
				$myrow = pg_fetch_array ($result, 0);
				$Count = $myrow[0];
			}
		}

		return $Count;
	}
		

	function UpdateDescription() {
		GLOBAL $User;

		$sql = "UPDATE categories SET description = $1 WHERE id = $2 AND is_primary = FALSE";
		syslog(LOG_NOTICE, 'User \'' . $User->name . '\' at '
			. pg_escape_string($this->dbh, $_SERVER[REMOTE_ADDR]) . ' is changing category \'' 
			. $this->name . '\' to \'' . $this->description . '\'.');
		if ($this->Debug) echo "sql = '$sql'<br>";

		$result = pg_query_params($this->dbh, $sql, array($this->description, $this->id));

		return  pg_affected_rows($result);
	}

	function IsPrimary() {
		return $this->is_primary == 't';
	}
}
