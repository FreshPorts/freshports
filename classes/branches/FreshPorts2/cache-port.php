<?php
	#
	# $Id: cache-port.php,v 1.1.2.5 2006-06-29 13:19:08 dan Exp $
	#
	# Copyright (c) 2006 DVL Software Limited
	#

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//

define('CACHE_PORT_COMMITS', 'Commits');
define('CACHE_PORT_DETAIL',  'Detail');

class CachePort extends Cache {

	function CachePort() {
		return Parent::Cache();
	}
	
	function Retrieve($Category, $Port, &$data, $CacheType = CACHE_PORT_COMMITS) {
		$this->_Log("CachePort: Retrieving for $Category/$Port");
		$Key = $this->_PortKey($Category, $Port, $CacheType);
		$result = Parent::Retrieve($Key, $data);

		return $result;
	}

	function Add($Category, $Port, $data, $CacheType = CACHE_PORT_COMMITS) {
		$this->_Log("CachePort: Adding for $Category/$Port");

		$CategoryCacheDir = $this->CacheDir . '/ports/' . $Category;
		$Key = $this->_PortKey($Category, $Port, $CacheType);
		 
		if (!file_exists($CategoryCacheDir)) {
			$this->_Log("CachePort: creating directory $CategoryCacheDir");
			$old_mask = umask(0000);
			mkdir($CategoryCacheDir, 0774);
			umask($old_mask);
		}
		$result = 0;
		$result = Parent::Add($Key, $data);

		return $result;
	}

	function Remove($Category, $Port) {
		$this->_Log("CachePort: Removing for $Category/$Port");

		#
		# the wild card allows us to remove all cache entries for this port
		#
		$Key = $this->_PortKey($Category, $Port, '*');
		$result = Parent::Remove($Key, $data);

		return $result;
	}
	
	function _PortKey($Category, $Port, $CacheType) {
		// might want some parameter checking here
		$Key = "ports/$Category/$Port.$CacheType.html";

		return $Key;
	}

	function _CacheFileName($key) {
		return $this->CacheDir . '/'. $key;
	}
	

}
