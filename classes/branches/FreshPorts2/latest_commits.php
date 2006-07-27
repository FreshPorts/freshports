<?php
	#
	# $Id: latest_commits.php,v 1.1.2.19 2006-07-27 19:04:51 dan Exp $
	#
	# Copyright (c) 2003-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_record.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

// base class for keeping statistics on page rendering issues
class LatestCommits {

	var $Debug = 0;
	var $dbh;
	var $MaxNumberOfPorts;

	var $Filter;
	var $WatchListAsk    = '';	// either default or ask.  the watch list to which add/remove works.
	var $UserID          = 0;
	var $DaysMarkedAsNew = 10;
	var $LocalResult;
	var $HTML;

	function LatestCommits($dbh) {
		$this->dbh = $dbh;
	}

	function SetMaxNumberOfPorts($MaxNumberOfPorts) {
		$this->MaxNumberOfPorts = $MaxNumberOfPorts;
	}

	function SetDaysMarkedAsNew($DaysMarkedAsNew) {
		$this->DaysMarkedAsNew = $DaysMarkedAsNew;
	}

	function SetUserID($UserID) {
		$this->UserID = $UserID;
	}

	function SetWatchListAsk($WatchListAsk) {
		$this->WatchListAsk = $WatchListAsk;
	}

	function SetFilter($Filter) {
		$this->Filter = $Filter;
	}

	function CreateHTML() {
		GLOBAL	$freshports_CommitMsgMaxNumOfLinesToShow;

		if (IsSet($this->Filter)) {
			$sql = "select * from LatestCommitsFiltered($this->MaxNumberOfPorts, $this->UserID, '" . AddSlashes($this->Filter) . "')";
		} else {
			$sql = "select * from LatestCommits($this->MaxNumberOfPorts, $this->UserID)";
		}

		if ($this->Debug) echo "\n<pre>sql=$sql</pre>\n";

		$result = pg_exec($this->dbh, $sql);
		if (!$result) {
            die("read from database failed");
			exit;
		}
		
		$DisplayCommit->Debug = $this->Debug;
		
		$DisplayCommit = new DisplayCommit($result);
		$DisplayCommit->SetDaysMarkedAsNew($this->DaysMarkedAsNew);
		$DisplayCommit->SetUserID($this->UserID);
		$DisplayCommit->SetWatchListAsk($this->WatchListAsk);
		$RetVal = $DisplayCommit->CreateHTML();
		
		$this->HTML = $DisplayCommit->HTML;

		return $RetVal;

	}
}
?>
