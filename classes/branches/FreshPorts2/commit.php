<?
	# $Id: commit.php,v 1.1.2.2 2002-12-09 20:24:03 dan Exp $
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
}
