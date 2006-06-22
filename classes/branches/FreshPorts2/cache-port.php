<?php
	#
	# $Id: cache-port.php,v 1.1.2.3 2006-06-22 15:09:46 dan Exp $
	#
	# Copyright (c) 2006 DVL Software Limited
	#

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//
class CachePort extends Cache {

	function CachePort() {
		return Parent::Cache();
	}
	
	function Retrieve($Category, $Port, &$data) {
		$this->_Log("CachePort: Retrieving for $Category/$Port");
		$Key = $this->_PortKey($Category, $Port);
		$result = Parent::Retrieve($Key, $data);

		return $result;
	}

	function Add($Category, $Port, $data) {
		$this->_Log("CachePort: Adding for $Category/$Port");

		$CategoryCacheDir = $this->CacheDir . '/ports/' . $Category;
		$Key = $this->_PortKey($Category, $Port);
		 
		if (!file_exists($CategoryCacheDir)) {
			$this->_Log("CachePort: creating directory $CategoryCacheDir");
			mkdir($CategoryCacheDir, 0774);
		}
		$result = 0;
		$result = Parent::Add($Key, $data);

		return $result;
	}

	function Remove($Category, $Port) {
		$this->_Log("CachePort: Removing for $Category/$Port");
		$Key = $this->_PortKey($Category, $Port);
		$result = Parent::Remove($Key, $data);

		return $result;
	}
	
	function _PortKey($Category, $Port) {
		// might want some parameter checking here
		$Key = 'ports/' . $Category . '/' . $Port . '.html';

		return $Key;
	}

	function _CacheFileName($key) {
		return $this->CacheDir . '/'. $key;
	}
	

}
