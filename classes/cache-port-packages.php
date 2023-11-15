<?php
	#
	# $Id: cache-port.php,v 1.6 2007-06-04 02:16:33 dan Exp $
	#
	# Copyright (c) 2006-2022 DVL Software Limited
	#

	require_once('cache-port.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//

class CachePortPackages extends Cache {

	const CacheCategory = 'packages';

	function __construct() {
		return parent::__construct();
	}
	
	function RetrievePortPackages($Category, $Port) {
		$this->_Log("CachePortPackages: Retrieving packages for $Category/$Port");
		$Key = $this->_PortPackagesKey($Category, $Port);
		$result = parent::Retrieve($Key);

		return $result;
	}

	function AddPortPackages($Category, $Port) {
		$this->_Log("CachePortPackages: Adding packages for $Category/$Port");

		$CacheDir = $this->CacheDir . '/' . self::CacheCategory . '/' . $Category;
		$Key = $this->_PortPackagesKey($Category, $Port);
		 
		if (!is_dir($CacheDir)) {
			$this->_Log("CachePortPackages: creating directory $CacheDir");
			$old_mask = umask(0000);
			#
			# we use @mkdir because we still this even if we check:
			# [23-Jul-2022 02:59:21 UTC] PHP Warning:  mkdir(): File exists in /usr/local/www/freshports/classes/cache-port.php on line 53
			# concurrency.
			#
			if (!@mkdir($CacheDir, 0774, true)) {
				$this->_Log("CachePortPackages: unable to create directory $CacheDir");
			}
			umask($old_mask);
		}

		$result = 0;
		$result = parent::Add($Key);

		return $result;
	}

	function RemovePort($Category, $Port) {
		$this->_Log("CachePortPackages: Removing for $Category/$Port");

		#
		# the wild card allows us to remove all cache entries for this port
		# regardless of the CacheType or page number
		#
		$Key = $this->_PortPackagesKey($Category, $Port);
		$result = parent::Remove($Key, $data);

		return $result;
	}

	function _PortPackagesKey($Category, $Port) {
		// might want some parameter checking here
		$Key = self::CacheCategory . "/$Category/$Port.html";

		return $Key;
	}

	function _CacheFileName($key) {
		return $this->CacheDir . '/'. $key;
	}


}
