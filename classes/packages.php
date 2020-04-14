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

		// # accumulate list of package names available for this port (e.g. py27-django-storages and py36-django-storages)
		// $sql = "SELECT package_name FROM PackagesGetPackageNamesForPort($PortID) ORDER BY package_name";
		// if ($Debug) echo "<pre>Get package names for this port: $sql</pre>";

		// $result = pg_exec($this->dbh, $sql);
		// if ($result) {
		// 	$numrows = pg_numrows($result);
		// 	if ($Debug) echo "<pre>$numrows packages found</pre>";
		// 	if ($numrows > 0) {
		// 		$packages = pg_fetch_all($result);

		// 		# convert the array of arrays to an array of package names
		// 		# We could do this with: 
		// 		#    $package_names = array_column($records, 'packages')
		// 		#    $names = array_combine($package_names, $package_names);
		// 		# but I think the following is easier to follow.
		// 		#
		// 		foreach($packages as $package) {
		// 			$this->{'packages'}[$package['package_name']] = array();
		// 		}

		// 		if ($Debug) {
		// 			echo "<pre>The package names are:<br>";
		// 			var_export($this->{'packages'});
		// 			echo "</pre>";
		// 		}

		// 	}
		// } else {
		// 	echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		// }

#		exit("Stopping because we are testing PackagesGetPackageNamesForPort()");
		
#		$sql = "SELECT * FROM PortPackages($PortID, {$branch_head_escaped}, {$branch_quarterly_escaped})";
		$sql = "SELECT * FROM PortPackages($PortID)";

		// freshports.dev=# select * from PortPackages(28303);
		//			package_name     |        abi         | package_version_latest | package_version_quarterly 
		//	----------------------+--------------------+------------------------+---------------------------
		//		py27-django-storages | FreeBSD:11:aarch64 | 1.5.1                  | 1.9.1
		//		py27-django-storages | FreeBSD:11:amd64   | 1.9.1                  | 1.9.1
	   
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
