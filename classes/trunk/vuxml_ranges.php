<?php
	#
	# $Id: vuxml_ranges.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

// base class for VuXML_Ranges
class VuXML_Ranges {
	var $id;
	var $vuxml_affected_id;
	var $operator1;
	var $version1;
	var $operator2;
	var $version2;

	var $dbh;

	function VuXML_Ranges($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByVuXMLAffectedID($vuxml_affected_id) {
		$sql = "SELECT vuxml_ranges.*
	              FROM vuxml_ranges
	             WHERE vuxml_ranges.vuxml_affected_id = " . pg_escape_string($vuxml_affected_id);
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_query($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				$this->PopulateValues($myrow);
			}
		} else {
			echo 'VuXML_Ranges SQL failed: ' . $result . pg_last_error();
		}

        return $this->id;
	}

	function PopulateValues($myrow) {
		$this->id[]                = $myrow['id'];
		$this->vuxml_affected_id[] = $myrow['vuxml_affected_id'];
		$this->operator1[]         = $myrow['operator1'];
		$this->version1[]          = $myrow['version1'];
		$this->operator2[]         = $myrow['operator2'];
		$this->version2[]          = $myrow['version2'];
	}

	function display() {
#		echo "<br>\n   vuxml_ranges.pm:10<br>\n";

		for ($i = 0; $i < count($this->id); $i++) {
	
#			echo "   id                 = '" . $this->id[$i]                . " ";
#			echo "   vuxml_affected_id  = '" . $this->vuxml_affected_id[$i] . " ";
			echo "<blockquote>" . $this->operator1[$i]         . " ";
			echo $this->version1[$i]          . " ";
			echo $this->operator2[$i]         . " ";
			echo $this->version2[$i]          . "</blockquote><br>\n";
		}
	}

}

?>