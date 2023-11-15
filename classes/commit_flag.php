<?php
	#
	# $Id: commit_flag.php,v 1.2 2006-12-17 11:37:18 dan Exp $
	#
	# Copyright (c) 2006 DVL Software Limited
	#

	$Debug = 0;

// base class for a flagged commit
class CommitFlag {

	var $dbh;

	var $user_id;
	var $commit_log_id;

	var $LocalResult;
	var $_TableName = 'commits_flagged';
	
	var $_Debug;


	function __construct($dbh) {
		$this->dbh	= $dbh;
		$this->_Debug = false;
	}
	
	function Delete($UserID, $CommitLogID) {
		#
		# Delete an item from the table
		#

		#
		# The "subselect" ensures the user can only delete things from their
		# own watch list
		#
		$sql = 'DELETE FROM ' . $this->_TableName . '
		         WHERE user_id       = $1
		           AND commit_log_id = (SELECT id from commit_log where message_id = $2)';
		if ($this->_Debug) echo "<pre>$sql</pre>";
		$result = pg_query_params($this->dbh, $sql, array($UserID, $CommitLogID));

		# that worked and we updated exactly one row
		if ($result) {
			$return = pg_affected_rows($result);
		} else {
			$return = -1;
		}

		return $return;
	}


	function Add($UserID, $CommitLogID) {
		#
		# Add an item to the list
		#

		#
		# make sure we don't report the duplicate entry error when adding...
		#
		$PreviousReportingLevel = error_reporting(E_ALL ^ E_WARNING);

		#
		# The subselect ensures the user can only add things to their
		# own watch list
		#
		$sql = '
INSERT INTO '. $this->_TableName . '
SELECT $1 as user_id, 
	   (SELECT id from commit_log where message_id = $2) as commit_log_id
 WHERE not exists (
    SELECT T.user_id, T.commit_log_id
      FROM ' . $this->_TableName . ' T
     WHERE T.user_id       = $1
       AND T.commit_log_id = (SELECT id from commit_log where message_id = $2))';
		if ($this->_Debug) echo "<pre>$sql</pre>";
		$result = pg_query_params($this->dbh, $sql, array($UserID, $CommitLogID));
		if ($result) {
			$return = 1;
		} else {
			# If this isn't a duplicate key error, then break
			if (stristr(pg_last_error($this->dbh), "Cannot insert a duplicate key") == '') {
				$return = -1;
			} else {
				$return = 1;
			}
		}

		error_reporting($PreviousReportingLevel);

		return $return;
	}
	
	function Fetch($UserID) {
		$sql = '
		SELECT *
		  FROM '. $this->_TableName . ' T
		 WHERE T.user_id = $1';

		$this->LocalResult = pg_query_params($this->dbh, $sql, array($UserID));
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
		} else {
			$numrows = -1;
			syslog(LOG_ERR, __FILE__  . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			die('something terrible has happened');
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by FetchByCategoryInitialise
		#

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->_PopulateValues($myrow);
	}

	function _PopulateValues($myrow) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch.
		#

		$this->user_id	     = $myrow["user_id"];
		$this->commit_log_id = $myrow["commit_log_id"];
	}
}
