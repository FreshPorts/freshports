<?php
	#
	# $Id: vuxml.php,v 1.1.2.1 2004-10-03 14:01:20 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

// base class for VuXML
class VuXML {
	var $id;
	var $vid;
	var $topic;
	var $description;
	var $date_discovery;
	var $date_entry;
	var $date_modified;
	var $status;

	var $dbh;

	function VuXML($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByVID($VID) {
		$this->vid = $VID;
		$sql = "select * from vuxml where vid = '" . AddSlashes($VID) . "'";
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_query($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			# there should only be one row.
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->PopulateValues($myrow);
			} else {
				die('I found ' . $numrows . ' entries for ' . $VID . '. There should be only one.');
			}
		} else {
			echo 'VuXML SQL failed: ' . $result . pg_last_error();
		}

        return $this->id;
	}

	function PopulateValues($myrow) {
		$this->id             = $myrow['id'];
		$this->vid            = $myrow['vid'];
		$this->topic          = $myrow['topic'];
		$this->description    = $myrow['description'];
		$this->date_discovery = $myrow['date_discovery'];
		$this->date_entry     = $myrow['date_entry'];
		$this->date_modified  = $myrow['date_modified'];
		$this->status         = $myrow['status'];
	}

}
