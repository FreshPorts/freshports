<?php
	#
	# $Id: sanity_test_failures.php,v 1.3 2013-04-07 01:19:59 dan Exp $
	#
	# Copyright (c) 2003-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_record.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

// base class for keeping statistics on page rendering issues
class SanityTestFailures {

	var $Debug = 0;
	var $dbh;
	var $MaxNumberOfPorts;

	var $Filter;
	var $WatchListAsk    = '';	// either default or ask.  the watch list to which add/remove works.
	var $UserID          = 0;
	var $DaysMarkedAsNew = 10;
	var $MessageID       = '';
	var $LocalResult;
	var $HTML;

	function __construct($dbh) {
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
	
	function SetMessageID($MessageID) {
		$this->MessageID = $MessageID;
	}

	function CreateHTML() {
		GLOBAL	$freshports_CommitMsgMaxNumOfLinesToShow;

		if (IsSet($this->Filter)) {
			$sql = 'select * from SanityTestFailures($1, $2)';
			$params = array($this->UserID, $this->Filter);
		} else {
			# we don't need/use the value for stf_message here but a non-empty value is required
			# to get port-display to provide a link to the sanity test failure message.
			# to reduce the data set set, let's just pull back 1.
			$sql = "
SELECT S.*, 1 as stf_message
  FROM SanityTestFailures($1) S LEFT OUTER JOIN sanity_test_failures STF
    ON S.commit_log_id = STF.commit_log_id";
    			$params = array($this->UserID);
		}
		
		if ($this->MessageID != '') {
			$sql .= ' WHERE message_id = $' . count($params) + 1;
			$params[] = $this->MessageID;
		}

		$sql .= " ORDER BY S.commit_date_raw DESC, S.category, S.port";
		
		if ($this->Debug) echo "\n<p>sql=$sql</p>\n";

		$result = pg_query_params($this->dbh, "set client_encoding = 'ISO-8859-15'", array()) or die('query failed ' . pg_last_error($this->dbh));

		$result = pg_query_params($this->dbh, $sql, $params);
		if (!$result) {
            die("read from database failed");
			exit;
		}
		
		$DisplayCommit = new DisplayCommit($this->dbh, $result);
		$DisplayCommit->Debug = $this->Debug;
		
		$DisplayCommit->SetDaysMarkedAsNew($this->DaysMarkedAsNew);
		$DisplayCommit->SetUserID($this->UserID);
		$DisplayCommit->SetWatchListAsk($this->WatchListAsk);
		if ($this->MessageID == '') {
			$DisplayCommit->ShowLinkToSanityTestFailure = true;
		}

		$RetVal = $DisplayCommit->CreateHTML();
		
		$this->HTML = $DisplayCommit->HTML;

		return $RetVal;

	}
}
