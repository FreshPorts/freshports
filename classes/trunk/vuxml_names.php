<?php
	#
	# $Id: vuxml_names.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

// base class for VuXML_Names
class VuXML_Names {
	var $id;
	var $vuxml_affected_id;
	var $name;

	var $package_link;

	var $dbh;

	function VuXML_Names($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByVuXMLAffectedID($vuxml_affected_id) {
		$sql = "SELECT vuxml_names.*, GetCategoryPortFromLatestLink(name) as package_link
	              FROM vuxml_names
	             WHERE vuxml_names.vuxml_affected_id = " . pg_escape_string($vuxml_affected_id);
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_query($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				$this->PopulateValues($myrow);
			}
		} else {
			echo 'VuXML_Names SQL failed: ' . $result . pg_last_error();
		}

        return $this->id;
	}

	function PopulateValues($myrow) {
		$this->id[]                = $myrow['id'];
		$this->vuxml_affected_id[] = $myrow['vuxml_affected_id'];
		$this->name[]              = $myrow['name'];
		$this->package_link[]      = $myrow['package_link'];
	}

	function display() {
#		echo "<br>\n   vuxml_names.pm:150<br>\n";

		for ($i = 0; $i < count($this->id); $i++) {
#			echo "   id                = '" . $this->id[$i]                . "' ";
#			echo "   vuxml_affected_id = '" . $this->vuxml_affected_id[$i] . "' ";
			if ($this->package_link[$i]) {
				echo '<a href="/' . $this->package_link[$i] . '/">';
			}
			echo $this->name[$i]              . "<br>\n";
			if ($this->package_link[$i]) {
				echo '</a>';
			}
		}
	}

}

?>