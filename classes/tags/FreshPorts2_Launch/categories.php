<?
	# $Id: categories.php,v 1.1.2.1 2002-02-22 00:28:22 dan Exp $
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
				$this->id					= $myrow["id"];
				$this->is_primary			= $myrow["is_primary"];
				$this->element_id			= $myrow["element_id"];
				$this->name					= $myrow["name"];
				$this->description			= $myrow["description"];
			}
		}

        return $this->id;
	}

}
