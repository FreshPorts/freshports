<?php
	#
	# $Id: packages.php,v 1.3 2013-04-07 01:19:59 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

// base class for packages
class Packages {
	var $package;

	var $CategoryPort;

	var $dbh;

	function Packages($dbh) {
		$this->dbh	= $dbh;
	}

	function GetCategoryPortFromPackageName($package) {
		$this->package = $package;
		$sql = "select GetCategoryPortFromLatestLink('" . pg_escape_string($package) . "') as categoryport";
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_query($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			# there should only be one row.
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->CategoryPort = $myrow['categoryport'];
			} else {
				$this->CategoryPort = '';
			}
		} else {
			echo 'Packages SQL failed: ' . $result . pg_last_error();
		}

        return $this->CategoryPort;
	}
}

?>