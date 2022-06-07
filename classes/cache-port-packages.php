<?php
	#
	# $Id: cache-port.php,v 1.6 2007-06-04 02:16:33 dan Exp $
	#
	# Copyright (c) 2006-2007 DVL Software Limited
	#

	require_once('cache-port.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//

if (!defined('CACHE_PORT_COMMITS')) {
	define('CACHE_PORT_COMMITS', 'Commits');
}
if (!defined('CACHE_PORT_DETAIL')) {
	define('CACHE_PORT_DETAIL',  'Detail');
}

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
		 
		if (!file_exists($CacheDir)) {
			$this->_Log("CachePortPackages: creating directory $CacheDir");
			$old_mask = umask(0000);
			if (!mkdir($CacheDir, 0774, true)) {
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
