<?php
	#
	# $Id: ports_updating.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for packages related to a port
class Packages {

	var $id;       # the port id for this set of packages

	var $package_names = array();
	var $dbh;

	function __construct($dbh) {  
		$this->dbh	= $dbh;
	}

	function Fetch($PortID) {
		# fetch all rows in ports_updating with id = $PortID

		$this->id = $PortID;

		$Debug     = 0;
		$TotalRows = 0;

		$sql = "SELECT * FROM PortPackages($PortID)";

		if ($Debug) echo "<pre>$sql</pre>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($Debug) echo "<pre>$numrows returned from PortPackages()</pre>";
			if ($numrows > 0) {
				$packages = pg_fetch_all($result);
				if ($Debug) {
					echo "<pre>This was fetched from PortPackages:<br>";
					var_export($packages);
					echo "</pre>";
				}

				foreach($packages as $package) {
					$this->{'packages'}[$package['package_name']][] = $package;
				}

				if ($Debug) {
					echo "<pre>PortPackages was converted into this:<br>";
					var_export($this->{'packages'});
					echo "</pre>";
				}

				$TotalRows += $numrows;
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

#		exit("Stopping because we are testing PackagesGetPackageNamesForPort()");

		return $TotalRows;
	}

}
