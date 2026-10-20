<?php
	#
	# $Id: sanity_test_failures.php,v 1.3 2013-04-07 01:19:59 dan Exp $
	#
	# Copyright (c) 2003-2026 Dan Langille
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
	var $Limit           = 0;	// number of commits per page; 0 means all
	var $Offset          = 0;
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

	function SetLimit($Limit) {
		$this->Limit = $Limit;
	}

	function SetOffset($Offset) {
		$this->Offset = $Offset;
	}

	function Count() {
		# the number of commits CreateHTML() can show, i.e. those which touched at least one port
		$sql = '
SELECT count(DISTINCT STF.commit_log_id) AS count
  FROM sanity_test_failures STF
 WHERE EXISTS (SELECT 1 FROM commit_log_ports CLP WHERE CLP.commit_log_id = STF.commit_log_id)';

		$result = pg_query_params($this->dbh, $sql, array());
		if (!$result) {
			die("read from database failed");
		}
		$myrow = pg_fetch_array($result);

		return intval($myrow['count']);
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
			# SanityTestFailures() already joins sanity_test_failures, so we don't join it again.
			$sql = "
SELECT S.*, 1 as stf_message
  FROM SanityTestFailures($1) S";
    			$params = array($this->UserID);
		}
		
		if ($this->MessageID != '') {
			$sql .= ' WHERE message_id = $' . count($params) + 1;
			$params[] = $this->MessageID;
		} elseif ($this->Limit) {
			# page by commit, not by row, so a commit is never split across two pages
			$params[] = $this->Limit;
			$params[] = $this->Offset;
			$sql .= '
 WHERE S.commit_log_id IN (
	SELECT STF.commit_log_id
	  FROM sanity_test_failures STF JOIN commit_log CL ON CL.id = STF.commit_log_id
	 WHERE EXISTS (SELECT 1 FROM commit_log_ports CLP WHERE CLP.commit_log_id = STF.commit_log_id)
	GROUP BY STF.commit_log_id, CL.commit_date
	ORDER BY CL.commit_date DESC, STF.commit_log_id DESC
	 LIMIT $' . (count($params) - 1) . ' OFFSET $' . count($params) . ')';
		}

		# commit_log_id keeps each commit together when two commits share a commit_date
		$sql .= " ORDER BY S.commit_date_raw DESC, S.commit_log_id DESC, S.category, S.port";
		
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
