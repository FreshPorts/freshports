<?php
	#
	# Copyright (c) 2016 Dan Langille
	#


// base class for system_branch table
class SystemBranch {

  var $branch_names = array();

  var $dbh;

  function __construct($dbh) {
    $this->dbh	= $dbh;
  }

  function getBranchNames() {
    # empty the result
    $this->system_branches = array();

    $sql = "select branch_name from system_branch where branch_name ilike '20%Q%' ORDER BY branch_name";

#   echo "sql = '$sql'<br>";

    $result = pg_exec($this->dbh, $sql);
    if ($result) {
      $numrows = pg_num_rows($result);
        if ($numrows > 0) {
        for ($i = 1; $i <= $numrows; $i++) {
          $myrow = pg_fetch_array($result, $i - 1);
          $this->branch_names[$myrow['branch_name']] = $myrow['branch_name'];
        }
      }
    }

    return $this->branch_names;
  }
}
