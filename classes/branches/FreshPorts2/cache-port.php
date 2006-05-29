<?php
	#
	# $Id: cache-port.php,v 1.1.2.2 2006-05-29 06:27:19 dan Exp $
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
		$this->_Log("CachPort: Retrieving for $Category/$Port");
		$Key = $this->_PortKey($Category, $Port);
		$result = Parent::Retrieve($Key, $data);

		return $result;
	}

	function Add($Category, $Port, $data) {
		$this->_Log("CachPort: Adding for $Category/$Port");
		$Key = $this->_PortKey($Category, $Port);
		$result = Parent::Add($Key, $data);

		return $result;
	}

	function Remove($Category, $Port) {
		$this->_Log("CachPort: Removing for $Category/$Port");
		$Key = $this->_PortKey($Category, $Port);
		$result = Parent::Remove($Key, $data);

		return $result;
	}
	
	function _PortKey($Category, $Port) {
		// might want some parameter checking here
		$Key = $Category . '.' . $Port;

		return $Key;
	}
}
