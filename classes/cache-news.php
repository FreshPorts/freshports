<?php
	#
	# $Id: cache-file.php,v 1.1 2007-06-03 16:00:17 dan Exp $
	#
	# Copyright (c) 2006-2007 DVL Software Limited
	#

	require_once('cache.php');

// base class for caching news stuff, data which expires with each commit.

class CacheNews extends Cache {

	var $PageSize = 100;

	function __construct($CacheDir = CACHE_DIRECTORY, $SpoolDir = SPOOLING_DIRECTORY) {
		return parent::__construct($CacheDir, $SpoolDir);
	}
	
	function PageSizeSet($PageSize) {
		$this->PageSize = $PageSize;
	}

	function Retrieve($FileName, $PageNum = 1) {
		$this->_Log("CacheNews: Retrieving for $FileName and looking in $this->CacheDir PageNum=$PageNum");
		$Key = $this->_FileKey($FileName, $PageNum);
		$this->_Log("CacheNews: using key $Key");
		$result = parent::Retrieve($Key);

		return $result;
	}

	function Add($FileName, $PageNum = 1) {
		$this->_Log("CacheNews: Adding for $FileName");

		$CacheDir = $this->CacheDir . '/' . dirname($FileName);
		$Key = $this->_FileKey($FileName, $PageNum);
		 
		$result = parent::Add($Key);

		return $result;
	}

	function Remove($FileName) {
		$this->_Log("CacheNews: Removing for $FileName");

		#
		# the wild card allows us to remove all cache entries for this port
		# regardless of the CacheType or page number
		#
		$Key = $this->_FileKey($FileName);
		$result = parent::Remove($Key, $data);

		return $result;
	}
	
	function _FileKey($FileName, $PageNum = 1) {
		// might want some parameter checking here
		$Key = $FileName . '.PageSize' . $this->PageSize . '.PageNum' . $PageNum . '.html';

		return $Key;
	}
}
