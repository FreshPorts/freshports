<?php
	#
	# $Id: commit.php,v 1.1.2.16 2006-07-27 19:06:41 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


// base class for a single commit
class Commit {

	var $dbh;

	var $commit_log_id;
	var $commit_date_raw;
	var $encoding_losses;
	var $message_id;
	var $committer;
	var $commit_description;
	var $commit_date;
	var $commit_time;
	var $port_id;
	var $category;
	var $category_id;
	var $port;
	var $version;
	var $revision;
	var $epoch;
	var $status;
	var $needs_refresh;
	var $forbidden;
	var $broken;
	var $deprecated;
	var $expiration_date;
	var $ignore;
	var $date_added;
	var $element_id;
	var $short_description;
	var $onwatchlist;

	var $last_modified;

	function Commit($dbh) {
		$this->dbh	= $dbh;
	}

	function PopulateValues($myrow) {
		$this->commit_log_id		= $myrow["commit_log_id"];
		$this->commit_date_raw		= $myrow["commit_date_raw"];
		$this->encoding_losses		= $myrow["encoding_losses"];
		$this->message_id			= $myrow["message_id"];
		$this->committer			= $myrow["committer"];
		$this->commit_description	= $myrow["commit_description"];
		$this->commit_date			= $myrow["commit_date"];
		$this->commit_time			= $myrow["commit_time"];
		$this->port_id				= $myrow["port_id"];
		$this->category				= $myrow["category"];
		$this->category_id			= $myrow["category_id"];
		$this->port					= $myrow["port"];
		$this->version				= $myrow["version"];
		$this->revision				= $myrow["revision"];
		$this->epoch				= $myrow["epoch"];
		$this->status				= $myrow["status"];
		$this->needs_refresh		= $myrow["needs_refresh"];
		$this->forbidden			= $myrow["forbidden"];
		$this->broken				= $myrow["broken"];
		$this->deprecated			= $myrow["deprecated"];
		$this->expiration_date		= $myrow["expiration_date"];
		$this->ignore				= $myrow["ignore"];
		$this->date_added			= $myrow["date_added"];
		$this->element_id			= $myrow["element_id"];
		$this->short_description	= $myrow["short_description"];
		$this->onwatchlist			= $myrow["onwatchlist"];

		$this->last_modified		= $myrow["last_modified"];
	}

	function FetchByMessageId($message_id) {

		$sql = "
SELECT id as commit_log_id,
       message_id,
       message_date,
       to_char(commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  as commit_date,
       to_char(commit_date - SystemTimeAdjust(), 'HH24:MI:SS')   as commit_time,
       message_subject,
       date_added,
       committer,
       description AS commit_description,
       system_id,
       encoding_losses,
       GMT_Format(date_added) as last_modified
  FROM commit_log 
 WHERE message_id = '" . AddSlashes($message_id) . "'";

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

		return $this->message_id;
	}

	function DateNewestPort() {
		$Debug = 0;

		$sql = "
SELECT GMT_Format(date_added) as last_modified
  FROM ports
 WHERE date_added is not null
  ORDER BY date_added desc 
  LIMIT 1";

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

		return $this->message_id;
	}

	function EncodingLosses() {
		return $this->encoding_losses == 't';
	}

}
