<?
	# $Id: categories.php,v 1.1.2.6 2003-03-06 21:26:43 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#


// base class for Category
class Category {

	var $dbh;

	var $id;
	var $is_primary;
	var $element_id;
	var $name;
	var $description;

	function Category($dbh) {
		$this->dbh	= $dbh;
	}
	
	function Populate($myrow) {
		$this->id					= $myrow["id"];
		$this->is_primary			= $myrow["is_primary"];
		$this->element_id			= $myrow["element_id"];
		$this->name					= $myrow["name"];
		$this->description		= $myrow["description"];
	}

	function FetchByID($id) {
		if (IsSet($id)) {
			$this->id = $id;
		}
		$sql = "select * from categories where id = $this->id";
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
		if (IsSet($Name)) {
			$this->name = $Name;
			UnSet($this->id);
		}
		$sql = "select * from categories where name = '$this->name'";
		if ($Debug) echo "sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "FetchByName succeeded<BR>";
				$myrow = pg_fetch_array ($result, 0);
				$this->Populate($myrow);
			}
		}

		return $this->id;
	}

	function PortCount($Name) {
		$Count = 0;
		$Debug = 0;

		if (IsSet($Name)) {
			$this->name = $Name;
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
		$Debug = 1;
		$sql = "UPDATE categories SET description = '" . $this->description . "' WHERE id = " . $this->id . ' AND is_primary = FALSE';
		if ($Debug) echo "sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);

		return  pg_affected_rows($result);
	}

}
