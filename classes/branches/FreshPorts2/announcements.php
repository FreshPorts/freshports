<?php
	#
	# $Id: announcements.php,v 1.1.2.1 2003-05-09 19:39:55 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for announcements
class Announcement {

	var $dbh;

	var $id;
	var $text;
	var $start_date;
	var $end_date;

	function Announcement($dbh) {
		$this->dbh = $dbh;
	}
	
	function TextSet($text) {
		$this->text = AddSlashes($text);
	}

	function TextGet() {
		return $this->text;
	}

	function StartDateSet($start_date) {
		$this->start_date = AddSlashes($start_date);
	}

	function StartDateGet() {
		return $this->start_date;
	}

	function EndDateSet($end_date) {
		$this->end_date = AddSlashes($end_date);
	}

	function EndDateGet() {
		return $this->end_date;
	}

	function Delete() {
		# delete the ignore entry for this commit/port combination

		$sql = "
DELETE from announcements
 WHERE id = $this->id";

		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	function Insert() {
		# delete the ignore entry for this commit/port combination

		$sql = 'INSERT INTO announcements (text';

		if ($this->start_date != '') {
			$sql .= ', start_date';
		}

		if ($this->end_date != '') {
			$sql .= ', end_date';
		}

		$sql .= ") values ('" . $this->text . "'";

		if ($this->start_date != '') {
			$sql .= ", '" . $this->start_date . "'";
		}

		if ($this->end_date != '') {
			$sql .= ", '" . $this->end_date . "'";
		}

		$sql .= ")";

		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	

}
