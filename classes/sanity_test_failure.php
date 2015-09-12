<?php
	#
	# $Id: sanity_test_failure.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


// base class for a single sanity test failure
class SanityTestFailure {

	var $id;
	var $commit_log_id;
	var $message;
	
	var $dbh;

	function SanityTestFailure($dbh) {
		$this->dbh = $dbh;
	}

	function _PopulateValues($myrow) {
		$this->id					= $myrow['id'];
		$this->commit_log_id		= $myrow['commit_log_id'];
		$this->message				= $myrow['message'];
	}
	
	function FetchByMessageID($message_id) {
		$Debug = 0;
		$id    = -1;

		$sql = "
SELECT STF.id,
       STF.commit_log_id,
       STF.message
  FROM sanity_test_failures STF, commit_log CL
 WHERE CL.message_id     = '" . pg_escape_string($message_id) . "'
   AND STF.commit_log_id = CL.id";
   
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result);
				$this->_PopulateValues($myrow);
				$id = $this->id;
			} else {
				die(__CLASS__ . ':' . __FUNCTION__ . " got $numrows rows at line " . __LINE__);
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

		return $id;
	}

}
