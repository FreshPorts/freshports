<?
	# $Id: commit.php,v 1.1.2.3 2003-01-10 15:50:53 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
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
	var $status;
	var $needs_refresh;
	var $forbidden;
	var $broken;
	var $date_added;
	var $element_id;
	var $short_description;
	var $onwatchlist;

	function Commit($dbh) {
		$this->dbh	= $dbh;
	}

	function PopulateValues($myrow) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$this->commit_log_id			= $myrow["commit_log_id"];
		$this->commit_date_raw		= $myrow["commit_date_raw"];
		$this->encoding_losses		= $myrow["encoding_losses"];
		$this->message_id				= $myrow["message_id"];
		$this->committer				= $myrow["committer"];
		$this->commit_description	= $myrow["commit_description"];
		$this->commit_date			= $myrow["commit_date"];
		$this->commit_time			= $myrow["commit_time"];
		$this->port_id					= $myrow["port_id"];
		$this->category				= $myrow["category"];
		$this->category_id			= $myrow["category_id"];
		$this->port						= $myrow["port"];
		$this->version					= $myrow["version"];
		$this->revision				= $myrow["revision"];
		$this->status					= $myrow["status"];
		$this->needs_refresh			= $myrow["needs_refresh"];
		$this->forbidden				= $myrow["forbidden"];
		$this->broken					= $myrow["broken"];
		$this->date_added				= $myrow["date_added"];
		$this->element_id				= $myrow["element_id"];
		$this->short_description	= $myrow["short_description"];
		$this->onwatchlist			= $myrow["onwatchlist"];
	}

	function FetchByMessageId($message_id) {
		if (IsSet($message_id)) {
			$this->message_id = $message_id;
		}
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
       encoding_losses       
  FROM commit_log 
 WHERE message_id = '$this->message_id'";

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
}
