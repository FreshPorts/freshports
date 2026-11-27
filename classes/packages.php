<?php
	#
	# $Id: ports_updating.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#


// base class for packages related to a port
class Packages {

	var $id;       # the port id for this set of packages

	var $package_names = array();
	var $package;
	var $CategoryPort;
	var $packages;
	var $dbh;

	function __construct($dbh) {  
		$this->dbh = $dbh;
	}

	function GetCategoryPortFromPackageName($package) {
		$this->package = $package;
		$sql = 'select GetCategoryPortFromPackageName($1) as categoryport';
#		echo "<pre>sql = '$sql'</pre><br>";

		$result = pg_query_params($this->dbh, $sql, array($package));
		if ($result) {
			$numrows = pg_num_rows($result);
			# there should only be one row.
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result, 0);
				$this->CategoryPort = $myrow['categoryport'];
			} else {
				$this->CategoryPort = '';
			}
		} else {
			echo freshports_QueryFailed($this->dbh);
		}

	        return $this->CategoryPort;
	}

	function Fetch($PortID) {
		# fetch all rows from PortPackages with id = $PortID

		$this->id = $PortID;

		$Debug     = 0;
		$TotalRows = 0;

		$sql = 'SELECT * FROM PortPackages($1)';

		if ($Debug) echo "<pre>$sql</pre>";

		$result = pg_query_params($this->dbh, $sql, array($PortID));
		if ($result) {
			$numrows = pg_num_rows($result);
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
			echo freshports_QueryFailed($this->dbh, $sql);
		}

#		exit("Stopping because we are testing PackagesGetPackageNamesForPort()");

		return $TotalRows;
	}

	function GetListOfArchs($package) {

		$archs = array();
		# we need to know the archs so we can use them as the column headings
		foreach($package as $package_line) {

			$abi = $package_line['abi'];
			$arch = substr($abi, strrpos($abi, ':') + 1);
			$archs[$arch] = $arch;
		}

		return $archs;
	}

	#
	# Which FreeBSD major versions are still supported, from the
	# supported_releases table (refreshed nightly by ingress-scripts
	# supported_releases_update.pl).  The port page uses this to label the
	# ABI rows of the Packages table: CURRENT, or EOL.
	#
	# Sets $this->SupportedMajors to array(major => major) and
	# $this->CurrentMajor to the major of branch 'main' (null if none).
	# If the query fails or the table is empty, both stay empty and
	# ReleaseStatus() labels nothing, rather than calling every ABI EOL.
	#
	var $SupportedMajors = array();
	var $CurrentMajor    = null;

	function FetchSupportedReleases() {
		$this->SupportedMajors = array();
		$this->CurrentMajor    = null;

		$sql = 'SELECT branch, major FROM supported_releases';
		$result = pg_query_params($this->dbh, $sql, array());
		if (!$result) {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			return 0;
		}

		while ($myrow = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			$major = intval($myrow['major']);
			$this->SupportedMajors[$major] = $major;
			if ($myrow['branch'] == 'main') {
				$this->CurrentMajor = $major;
			}
		}

		return count($this->SupportedMajors);
	}

	#
	# $ABI is FreeBSD:14:amd64, FreeBSD:14:latest, or similar.
	# Returns 'current' if its major is the one on main, 'eol' if that major
	# is no longer supported, otherwise ''.
	#
	# A major above every supported one is not EOL: pkg.freebsd.org can list
	# a new ABI (e.g. the next CURRENT) a day or so before supported_releases
	# does, because newvers.sh moves main to N+1 the day stable/N is cut.
	#
	function ReleaseStatus($ABI) {
		$parts = explode(':', $ABI);
		if (count($parts) < 2 || !ctype_digit($parts[1]) || empty($this->SupportedMajors)) {
			return '';
		}

		$major = intval($parts[1]);
		if ($major === $this->CurrentMajor) {
			return 'current';
		}

		if (!isset($this->SupportedMajors[$major]) && $major < max($this->SupportedMajors)) {
			return 'eol';
		}

		return '';
	}

}
