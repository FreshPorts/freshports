<?php
	#
	# $Id: vuxml_packages.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

// base class for VuXML_Packages
class VuXML_Packages {
	var $id;
	var $vuxml_id;
	var $type;

	var $dbh;

	function VuXML_Packages($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByVID($vuxml_id) {
		$sql = "SELECT vuxml_affected.*
	              FROM vuxml_affected
	             WHERE vuxml_affected.vuxml_id = " . pg_escape_string($vuxml_id);
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_query($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				$this->PopulateValues($myrow);
			}

			$this->FetchNames();
			$this->FetchRanges();

		} else {
			echo 'VuXML_Packages SQL failed: ' . $result . pg_last_error();
		}

        return $this->id;
	}

	function PopulateValues($myrow) {
		$this->id[]       = $myrow['id'];
		$this->vuxml_id[] = $myrow['vuxml_id'];
		$this->type[]     = $myrow['type'];
	}

	function display() {
#		echo "<br>\n   vuxml_packages.pm:10<br>\n";

		for ($i = 0; $i < count($this->id); $i++) {
#			echo 'For this package<br>';
#			echo "   id       = '" . $this->id[$i]       . "' ";
#			echo "   vuxml_id = '" . $this->vuxml_id[$i] . "' ";
#			echo "   type     = '" . $this->type[$i]     . "'<br>\n";

			$this->names [$i]->display();
			$this->ranges[$i]->display();
		}
	}

	function FetchNames() {
		unset($this->names);
		for ($i = 0; $i < count($this->id); $i++) {
#			echo 'names for this package ' . $this->id[$i] . '<br>';
			$this->names[$i] = new VuXML_Names($this->dbh);
			$this->names[$i]->FetchByVuXMLAffectedID($this->id[$i]);
		}
	}

	function FetchRanges() {
		unset($this->ranges);
		for ($i = 0; $i < count($this->id); $i++) {
#			echo 'ranges for this package ' . $this->id[$i] . '<br>';
			$this->ranges[$i] = new VuXML_Ranges($this->dbh);
			$this->ranges[$i]->FetchByVuXMLAffectedID($this->id[$i]);
		}
	}

}

?>