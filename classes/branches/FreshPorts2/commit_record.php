<?php
	#
	# $Id: commit_record.php,v 1.1.2.4 2003-09-25 15:46:41 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for a single commit
class CommitRecord {

	var $dbh;

	var $commit_log_id;
	var $commit_date_raw;
	var $message_subject;
	var $message_id;
	var $committer;
	var $commit_description;
	var $commit_date;
	var $commit_time;
	var $encoding_losses;
	var $port_id;
	var $needs_refresh;
	var $forbidden;
	var $broken;
	var $element_id;
	var $version;
	var $revision;
	var $date_added;
	var $short_description;
	var $category_id;
	var $port;
	var $status;
	var $category;
	var $security_notice_id;
	var $watch;
	var $element_pathname;

	function CommitRecord($dbh) {
		$this->dbh = $dbh;
	}

	function PopulateValues($myrow) {
		$this->commit_log_id		= $myrow['commit_log_id'];
		$this->commit_date_raw		= $myrow['commit_date_raw'];
		$this->message_subject		= $myrow['message_subject'];
		$this->message_id			= $myrow['message_id'];
		$this->committer			= $myrow['committer'];
		$this->commit_description	= $myrow['commit_description'];
		$this->commit_date			= $myrow['commit_date'];
		$this->commit_time			= $myrow['commit_time'];
		$this->encoding_losses		= $myrow['encoding_losses'];
		$this->port_id				= $myrow['port_id'];
		$this->needs_refresh		= $myrow['needs_refresh'];
		$this->forbidden			= $myrow['forbidden'];
		$this->broken				= $myrow['broken'];
		$this->element_id			= $myrow['element_id'];
		$this->version				= $myrow['version'];
		$this->revision				= $myrow['revision'];
		$this->date_added			= $myrow['date_added'];
		$this->short_description	= $myrow['short_description'];
		$this->category_id			= $myrow['category_id'];
		$this->port					= $myrow['port'];
		$this->status				= $myrow['status'];
		$this->category				= $myrow['category'];
		$this->security_notice_id	= $myrow['security_notice_id'];
		$this->watch				= $myrow['watch'];
		$this->element_pathname		= $myrow['element_pathname'];
	}

	function EncodingLosses() {
		return $this->encoding_losses == 't';
	}

}

?>
