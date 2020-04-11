<?php
	#
	# $Id: ports_updating.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for packages related to a port
class Packages {

	var $id;       # the port id for this set of packages
#	var $packages; # list of packages (ABI text, package_name text, package_version text)
#	var $packages; # list of packages (ABI text, package_name text, package_version text)

	var $branches;
	var $packages;
	var $dbh;

	function __construct($dbh) {  
		$this->dbh	= $dbh;

		$this->branches =  array(NormalizeBranch(BRANCH_HEAD), NormalizeBranch(BRANCH_QUARTERLY));
	}

	function Fetch($PortID) {
		# fetch all rows in ports_updating with id = $PortID

		$Debug     = 0;
		$TotalRows = 0;

		if ($Debug) "<pre>" . ' the branches are ' . var_dump($this->branches) . '</pre>';
/*
		foreach($this->branches as $branch) {
			$branch_escaped = pg_escape_literal($branch);
			$PortID_escaped = pg_escape_literal($PortID);
			$sql = "SELECT * FROM PortPackages({$PortID_escaped}, {$branch_escaped})";

			if ($Debug) echo "<pre>$sql</pre>";

			$result = pg_exec($this->dbh, $sql);
			if ($result) {
				$numrows = pg_numrows($result);
				if ($Debug) echo "<pre>$numrows</pre>";
				if ($numrows > 0) {
					$this->{'packages_'. $branch} = pg_fetch_all($result);
					if ($Debug) {
						echo "<pre>";
						var_dump($this->{'packages_' . $branch});
						echo "</pre>";
					}
					$TotalRows += $numrows;
				}
			} else {
				echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
			}
*/
		$branch_head      = NormalizeBranch(BRANCH_HEAD);
		$branch_quarterly = NormalizeBranch(BRANCH_QUARTERLY);

		$branch_head_escaped       = pg_escape_literal($branch_head);
		$branch_quarterly_escaped = pg_escape_literal($branch_quarterly);
		
		$sql = "SELECT * FROM PortPackages($PortID, {$branch_head_escaped}, {$branch_quarterly_escaped})";

		if ($Debug) echo "<pre>$sql</pre>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($Debug) echo "<pre>$numrows</pre>";
			if ($numrows > 0) {
				$this->{'packages'} = pg_fetch_all($result);
				if ($Debug) {
					echo "<pre>";
					var_dump($this->{'packages'});
					echo "</pre>";
				}
				$TotalRows += $numrows;
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}
#		} # foreach

		return $TotalRows;
	}

}
