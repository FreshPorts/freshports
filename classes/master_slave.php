<?php
	# $Id: master_slave.php,v 1.3 2007-09-30 16:46:23 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#


require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/system_branch.php');

// base class for Master-Slave relationships
class MasterSlave {

	var $slave_port_id;
	var $slave_port_name;
	var $slave_category_id;
	var $slave_category_name;

	var $dbh;
	var $LocalResult;

	function __construct($dbh) {
		$this->dbh = $dbh;
	}

	function _PopulateValues($myrow) {
		$this->slave_port_id       = $myrow['slave_port_id'];
		$this->slave_port_name     = $myrow['slave_port_name'];
		$this->slave_category_id   = $myrow['slave_category_id'];
		$this->slave_category_name = $myrow['slave_category_name'];
	}

	function FetchByMaster($MasterName) {
          #
          # The branch id goes in as a literal, not via a join on system_branch:
          # joined by name, the planner guesses about 10,000 head rows in
          # commit_log_branches instead of 568,000, and walks them all
          # (900 ms for a master with many slaves; 3 ms with the literal).
          # An unknown branch gives id 0, which matches nothing, as the join did.
          #
          $system_branch = new SystemBranch($this->dbh);
          $BranchID = intval($system_branch->getBranchID(BRANCH_HEAD));

          $sql = "
SELECT P.id          AS slave_port_id,
       P.name        AS slave_port_name,
       P.category_id AS slave_category_id,
       P.category    AS slave_category_name
  FROM ports_active P JOIN commit_log_branches CLB ON CLB.commit_log_id = P.last_commit_id
                                                  AND CLB.branch_id     = $BranchID
 WHERE P.master_port = $1
ORDER BY slave_category_name, slave_port_name";

		#echo "sql = <pre>$sql</pre>";

          $this->LocalResult = pg_query_params($this->dbh, $sql, array($MasterName));
          if (!$this->LocalResult) {
            echo pg_last_error($this->dbh) . " $sql";
          }

          $numrows = pg_num_rows($this->LocalResult);

          return $numrows;
	}
	
	function FetchNth($N) {
		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->_PopulateValues($myrow);
	}

}
