<?php
	#
	# $Id: cache-file.php,v 1.1 2007-06-03 16:00:17 dan Exp $
	#
	# Copyright (c) 2006-2007 DVL Software Limited
	#

	require_once('cache.php');

// base class for caching files.

class CacheFile extends Cache {

	var $PageSize = 100;

	function CacheFile() {
		return parent::Cache();
	}
	
	function PageSizeSet($PageSize) {
		$this->PageSize = $PageSize;
	}

	function Retrieve($FileName, $PageNum = 1) {
		$this->_Log("CacheFile: Retrieving for $FileName");
		$Key = $this->_FileKey($FileName, $PageNum);
		$result = parent::Retrieve($Key);

		return $result;
	}

	function Add($FileName, $PageNum = 1) {
		$this->_Log("CacheFile: Adding for $FileName");

		$CacheDir = $this->CacheDir . '/ports/' . dirname($FileName);
		$Key = $this->_FileKey($FileName, $PageNum);
		 
		if (!file_exists($CacheDir)) {
			$this->_Log("CacheFile: creating directory $CacheDir");
			$old_mask = umask(0000);
			if (!mkdir($CacheDir, 0774, true)) {
				$this->_Log("CacheFile: unable to create directory $CacheDir");
			}
			umask($old_mask);
		}

		$result = 0;
		$result = parent::Add($Key);

		return $result;
	}

	function Remove($FileName) {
		$this->_Log("CacheFile: Removing for $FileName");

		#
		# the wild card allows us to remove all cache entries for this port
		# regardless of the CacheType or page number
		#
		$Key = $this->_FileKey($FileName, '*');
		$result = parent::Remove($Key, $data);

		return $result;
	}
	
	function _FileKey($FileName, $PageNum = 1) {
		// might want some parameter checking here
		$Key = "ports/$FileName.PageSize$this->PageSize.PageNum$PageNum.html";

		return $Key;
	}

	function _CacheFileName($key) {
		return $this->CacheDir . '/'. $key;
	}
	

}
?>