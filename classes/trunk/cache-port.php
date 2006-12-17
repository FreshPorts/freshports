<?php
	#
	# $Id: cache-port.php,v 1.2 2006-12-17 11:37:18 dan Exp $
	#
	# Copyright (c) 2006 DVL Software Limited
	#

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//

define('CACHE_PORT_COMMITS', 'Commits');
define('CACHE_PORT_DETAIL',  'Detail');

class CachePort extends Cache {

	var $PageSize = 100;

	function CachePort() {
		return Parent::Cache();
	}
	
	function PageSizeSet($PageSize) {
		$this->PageSize = $PageSize;
	}

	function Retrieve($Category, $Port, $CacheType = CACHE_PORT_COMMITS, $PageNum = 1) {
		$this->_Log("CachePort: Retrieving for $Category/$Port");
		$Key = $this->_PortKey($Category, $Port, $CacheType, $PageNum);
		$result = Parent::Retrieve($Key);

		return $result;
	}

	function Add($Category, $Port, $CacheType = CACHE_PORT_COMMITS, $PageNum = 1) {
		$this->_Log("CachePort: Adding for $Category/$Port");

		$CategoryCacheDir = $this->CacheDir . '/ports/' . $Category;
		$Key = $this->_PortKey($Category, $Port, $CacheType, $PageNum);
		 
		if (!file_exists($CategoryCacheDir)) {
			$this->_Log("CachePort: creating directory $CategoryCacheDir");
			$old_mask = umask(0000);
			mkdir($CategoryCacheDir, 0774);
			umask($old_mask);
		}
		$result = 0;
		$result = Parent::Add($Key);

		return $result;
	}

	function Remove($Category, $Port) {
		$this->_Log("CachePort: Removing for $Category/$Port");

		#
		# the wild card allows us to remove all cache entries for this port
		# regardless of the CacheType or page number
		#
		$Key = $this->_PortKey($Category, $Port, '*', '*');
		$result = Parent::Remove($Key, $data);

		return $result;
	}
	
	function _PortKey($Category, $Port, $CacheType, $PageNum = 1) {
		// might want some parameter checking here
		$Key = "ports/$Category/$Port.$CacheType.PageSize$this->PageSize.PageNum$PageNum.html";

		return $Key;
	}

	function _CacheFileName($key) {
		return $this->CacheDir . '/'. $key;
	}
	

}
?>