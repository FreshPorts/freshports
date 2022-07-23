<?php
	#
	# $Id: vuxml.php,v 1.3 2013-05-12 20:21:26 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/vuxml_names.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/vuxml_references.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/vuxml_ranges.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/vuxml_packages.php');

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

	var $packages;
	var $references;

	var $dbh;

	function __construct($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByVID($VID) {
		$this->vid = $VID;
		# let's try to avoid:  PHP Warning:  pg_query(): Query failed: ERROR:  invalid byte sequence for encoding &quot;UTF8&quot;: 0xfc
		$sql = "set client_encoding = 'ISO-8859-15'; select * from vuxml where vid = '" . pg_escape_string($this->dbh, $VID) . "'";
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_query($this->dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
			# there should only be one row.
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->PopulateValues($myrow);

				$this->FetchPackages();
				$this->FetchReferences();
			} else {
				die('I found ' . $numrows . ' entries for ' . htmlentities($VID) . '. There should be only one.');
			}
		} else {
			echo 'VuXML SQL failed: ' . $result . pg_last_error($this->dbh);
		}

        return $this->id;
	}

	function FetchPackages() {
		unset($this->packages);
		$this->packages = new VuXML_Packages($this->dbh);
		$this->packages->FetchByVID($this->id);
	}

	function FetchReferences() {
		unset($this->references);
		$this->references = new VuXML_References($this->dbh);
		$this->references->FetchByVID($this->id);
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

	function display() {
		echo $this->topic        . '<br>';
		echo $this->description . '<br>';
		if (IsSet($this->date_discovery)) echo "Discovery " . $this->date_discovery . '<br>';
		if (IsSet($this->date_entry))     echo "Entry     " . $this->date_entry     . '<br>';
		if (IsSet($this->date_modified))  echo "Modified  " . $this->date_modified  . '<br>';

		if (IsSet($this->packages)) {
			$this->packages->display();
		} else {
			echo 'no package data found - did an error occur?';
		}
		$this->references->display();
	}

}
