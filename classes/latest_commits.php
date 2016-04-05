<?php
	#
	# $Id: latest_commits.php,v 1.4 2012-09-25 18:10:12 dan Exp $
	#
	# Copyright (c) 2003-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_record.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

// base class for keeping statistics on page rendering issues
class LatestCommits {

	var $Debug = 0;
	var $dbh;
	var $MaxNumberOfPorts;

	var $BranchName;
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

	function SetBranch($BranchName) {
		$this->BranchName = $BranchName;
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
			$sql = "select * from LatestCommitsFiltered($this->MaxNumberOfPorts, $this->UserID, '" . pg_escape_string($this->Filter) . "')";
		} else {
#			$sql = "select * from LatestCommits($this->MaxNumberOfPorts, $this->UserID)";
			$sql = "
  SELECT LC.*, STF.message AS stf_message
    FROM LatestCommits(" . pg_escape_string($this->MaxNumberOfPorts) . ", 0, '" . pg_escape_string($this->BranchName) . "') LC LEFT OUTER JOIN sanity_test_failures STF
      ON LC.commit_log_id = STF.commit_log_id
ORDER BY LC.commit_date_raw DESC, LC.category, LC.port, element_pathname";
		}
		
		if ($this->Debug) echo "\n<p>sql=$sql</p>\n";

		$result = pg_exec($this->dbh, $sql);
		if (!$result) {
            die("read from database failed");
			exit;
		}
		
		$DisplayCommit = new DisplayCommit($this->dbh, $result);
		$DisplayCommit->Debug = $this->Debug;
		$DisplayCommit->SetDaysMarkedAsNew($this->DaysMarkedAsNew);
		$DisplayCommit->SetUserID($this->UserID);
		$DisplayCommit->SetWatchListAsk($this->WatchListAsk);
		$RetVal = $DisplayCommit->CreateHTML();
		
		$this->HTML = $DisplayCommit->HTML;

		return $RetVal;
	}
}
