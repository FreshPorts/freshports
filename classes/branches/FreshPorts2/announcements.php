<?php
	#
	# $Id: announcements.php,v 1.1.2.2 2003-05-09 21:32:32 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for announcements
class Announcement {

	var $dbh;
	var $LocalResult;

	var $id;
	var $text;
	var $start_date;
	var $end_date;

	function Announcement($dbh) {
		$this->dbh = $dbh;
	}

	function IDGet() {
		return $this->id;
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

	function PopulateValues($myrow) {
		$this->id			= $myrow["id"];
		$this->text			= $myrow["text"];
		$this->start_date	= $myrow["start_date"];
		$this->end_date	= $myrow["end_date"];
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
	
	function Update() {
		# delete the ignore entry for this commit/port combination

		$sql = "UPDATE announcements set text = '" . $this->text . "', start_date = ";

		if ($this->start_date != '') {
			$sql .= ', start_date';
		} else {
			$sql .= 'NULL';
		}

		$sql .= ", end_date = '";		
		if ($this->end_date != '') {
			$sql .= ', end_date';
		} else {
			$sql .= 'NULL';
		}

		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	function Fetch($id) {

		$sql = "
SELECT *
  FROM announcements
 WHERE id = $id";

#		echo "sql = '<pre>$sql</pre>'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result, 0);
				$this->PopulateValues($myrow);
			}
		}

		return $this->id;
	}

	function FetchAllActive() {
		$sql = "
		SELECT id,
             text,
             start_date,
             end_date
        FROM announcements
       WHERE (start_date <= current_date OR start_date IS NULL)
         AND (end_date   >= current_date OR end_date   IS NULL)";

#		echo '<pre>' . $sql . '</pre>';

		if ($Debug)	echo "commits::Fetch sql = '$sql'<BR>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch
		#

#		echo "fetching row $N<br>";

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->PopulateValues($myrow);

		return $this->id;
	}
	

}
