<?php
	#
	# $Id: cache-port.php,v 1.1.2.1 2006-05-29 05:26:54 dan Exp $
	#
	# Copyright (c) 2006 DVL Software Limited
	#

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//
class CachePort extends Cache {

	function CachePort()
		return Parent::Cache();
	}
	
	function Retrieve($Category, $Port, &$data) {
		$Key = $this->_PortKey($Category, $Port);
		$result = Parent::Retrieve($Key, $data);

		return $result;
	}

	function Add($Category, $Port, $data) {
		$Key = $this->_PortKey($Category, $Port);
		$result = Parent::Add($Key, $data);

		return $result;
	}

	function Remove($Category, $Port) {
		$Key = $this->_PortKey($Category, $Port);
		$result = Parent::Remove($Key, $data);

		return $result;
	}
	
	function _PortKey($Category, $Port);
		// might want some parameter checking here
		$key = $Category . '.' . $Port;
	}
}
