<?php
	#
	# $Id: announcements.php,v 1.4 2007-04-20 02:07:23 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for announcements
class Announcement {

	var $dbh;
	var $LocalResult;

	var $id;
	var $text;
	var $text_plain;
	var $start_date;
	var $end_date;

	var $Debug = 0;

	function Announcement($dbh) {
		$this->dbh = $dbh;
	}

	function IDSet($id) {
		$this->id = $id;
	}

	function IDGet() {
		return $this->id;
	}

	function TextSet($text) {
		$this->text = $text;
	}

	function TextGet() {
		return $this->text;
	}

	function TextPlainSet($text_plain) {
		$this->text_plain = $text_plain;
	}

	function TextPlainGet() {
		return $this->text_plain;
	}

	function StartDateSet($start_date) {
		$this->start_date = $start_date;
	}

	function StartDateGet() {
		return $this->start_date;
	}

	function EndDateSet($end_date) {
		$this->end_date = $end_date;
	}

	function EndDateGet() {
		return $this->end_date;
	}

	function PopulateValues($myrow) {
		$this->id			= $myrow["id"];
		$this->text			= $myrow["text"];
		$this->text_plain	= $myrow["text_plain"];
		$this->start_date	= $myrow["start_date"];
		$this->end_date	    = $myrow["end_date"];
	}

	function Delete() {
		# delete the ignore entry for this commit/port combination

		$sql = '
DELETE from announcements
 WHERE id = ' . pg_escape_string($this->id);

		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	function Insert() {
		# delete the ignore entry for this commit/port combination

		$sql = 'INSERT INTO announcements (text, text_plain';

		if ($this->start_date != '') {
			$sql .= ', start_date';
		}

		if ($this->end_date != '') {
			$sql .= ', end_date';
		}

		$sql .= ") values ('" . pg_escape_string($this->text) . "', '" . pg_escape_string($this->text_plain) . "'";

		if ($this->start_date != '') {
			$sql .= ", '" . pg_escape_string($this->start_date) . "'";
		}

		if ($this->end_date != '') {
			$sql .= ", '" . pg_escape_string($this->end_date) . "'";
		}

		$sql .= ")";

#		echo "<pre>$sql</pre>";

		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	function Update() {
		# delete the ignore entry for this commit/port combination

		$sql = "UPDATE announcements set text = '" . pg_escape_string($this->text) . 
		          "', text_plain = '" . pg_escape_string($this->text_plain) . "', start_date = ";

		if ($this->start_date != '') {
			$sql .= "'" . pg_escape_string($this->start_date) . "'";
		} else {
			$sql .= 'NULL';
		}

		$sql .= ", end_date = ";		
		if ($this->end_date != '') {
			$sql .= "'" . pg_escape_string($this->end_date) . "'";
		} else {
			$sql .= 'NULL';
		}

		$sql .= ' where id = ' . pg_escape_string($this->id);

#		echo "<pre>$sql</pre>";

		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_affected_rows($this->result);

		return $numrows;
		
	}
	
	function Fetch($id) {

		$sql = '
SELECT *
  FROM announcements
 WHERE id = ' . pg_escape_string($id);

#		echo "sql = '<pre>$sql</pre>'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($this->Debug) echo "fetched by ID succeeded<BR>";
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
             text_plain,
             start_date,
             end_date
        FROM announcements
       WHERE (start_date <= CURRENT_TIMESTAMP OR start_date IS NULL)
         AND (end_date   >= CURRENT_TIMESTAMP OR end_date   IS NULL)";

#		echo '<pre>' . $sql . '</pre>';

		if ($this->Debug)	echo "commits::Fetch sql = '$sql'<BR>";

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

	function FetchAll() {
		$sql = "
		SELECT id,
             text,
             text_plain,
             start_date,
             end_date
        FROM announcements
   ORDER BY end_date, start_date";

#		echo '<pre>' . $sql . '</pre>';

		if ($this->Debug)	echo "commits::Fetch sql = '$sql'<BR>";

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

	function NumRows() {
		return pg_numrows($this->LocalResult);
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

	function GetAllActive() {
		$Announcements = '';

		$sql = "select * from AnnouncementsGet() as text";

#		echo '<pre>' . $sql . '</pre>';

		if ($this->Debug) echo "commits::Fetch sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
#			echo "That would give us $numrows rows";
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array($result, $i);
				$Announcements .= '<p>' . $myrow['text'] . '</p>';
			}
		} else {
			echo 'pg_exec failed: ' . $sql;
		}

		return $Announcements;
	}
	

}
