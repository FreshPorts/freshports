<?php
	#
	# $Id: vuxml_references.php,v 1.2 2006-12-17 11:37:22 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

// base class for VuXML_References
class VuXML_References {
	var $id;
	var $vuxml_id;
	var $type;
	var $reference;

	var $dbh;

	function VuXML_References($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByVID($vuxml_id) {
		$sql = "SELECT vuxml_references.*
	              FROM vuxml_references
	             WHERE vuxml_references.vuxml_id = " . pg_escape_string($vuxml_id);
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_query($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				$this->PopulateValues($myrow);
			}
		} else {
			echo 'VuXML_References SQL failed: ' . $result . pg_last_error();
		}

        return $this->id;
	}

	function PopulateValues($myrow) {
		$this->id[]        = $myrow['id'];
		$this->vuxml_id[]  = $myrow['vuxml_id'];
		$this->type[]      = $myrow['type'];
		$this->reference[] = $myrow['reference'];
	}

	function display() {
		for ($i = 0; $i < count($this->id); $i++) {
			echo $this->reference[$i] . "<br>\n";
		}
	}

}

?>