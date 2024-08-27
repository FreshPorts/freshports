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

	var $Math_values = array(
		'lt' => '<',
		'gt' => '>',
		'le' => '<=',
		'ge' => '>=',
		'eq' => '=',
	);

	function __construct($dbh) {
		$this->dbh = $dbh;
	}

	function FetchByVuXMLAffectedID($vuxml_affected_id) {
		$sql = 'SELECT vuxml_ranges.*
	              FROM vuxml_ranges
	             WHERE vuxml_ranges.vuxml_affected_id = $1';
#		echo "<pre>sql = '$sql'</pre><br>";

		$result = pg_query_params($this->dbh, $sql, array($vuxml_affected_id));
		if ($result) {
			$numrows = pg_num_rows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				$this->PopulateValues($myrow);
			}
		} else {
			syslog(LOG_ERR, 'VuXML_Ranges SQL failed: ' . $result . pg_last_error($this->dbh));
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

	function TextToMath($text) {
		# this function converts lt to <, etc
		if (array_key_exists($text, $this->Math_values)) {
			$math = $this->Math_values[$text];
		} else {
			$math = $text . '..';
		}
		
		return $math;
	}

	function display() {
#		echo "<br>\n   vuxml_ranges.pm:10<br>\n";

		for ($i = 0; $i < count($this->id); $i++) {
	
#			echo "   id                 = '" . $this->id[$i]                . " ";
#			echo "   vuxml_affected_id  = '" . $this->vuxml_affected_id[$i] . " ";
			echo "<blockquote>" . $this->TextToMath($this->operator1[$i])   . " ";
			echo htmlentities($this->version1[$i])          . " ";
			echo htmlentities($this->operator2[$i] ?? '')   . " ";
			echo htmlentities($this->version2[$i]  ?? '')   . "</blockquote><br>\n";
		}
	}

}
