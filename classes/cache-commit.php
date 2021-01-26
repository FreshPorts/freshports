<?php
	#
	# $Id: cache-port.php,v 1.6 2007-06-04 02:16:33 dan Exp $
	#
	# Copyright (c) 2006-2007 DVL Software Limited
	#

	require_once('cache.php');

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//

define('CACHE_PORT_COMMITS', 'Commits');
define('CACHE_PORT_DETAIL',  'Detail');

class CacheCommit extends Cache {

	const CacheCategory = 'commits';

	var $PageSize = 100;

	function __construct() {
		return parent::__construct();
	}
	
	function PageSizeSet($PageSize) {
		$this->PageSize = $PageSize;
	}

	function RetrieveCommit($MessageId, $category = '', $port = '', $files = 'n') {
		$this->_Log("CacheCommit: Retrieving for message_id='$MessageId' category='$category' port='$port' files='$files'");
		$Key = $this->_CommitKey($MessageId, $category, $port, $files);
		$result = parent::Retrieve($Key);

		return $result;
	}

	function AddCommit($MessageId, $category = '', $port = '', $files = 'n') {
		$this->_Log("CacheCommit: Adding for message_id='$MessageId' category='$category' port='$port' files='$files'");

		# We create a directory based on the message-id, and store all cache items related to that message-id under that directory.
		$CacheDir = $this->CacheDir . '/' . self::CacheCategory . '/' . $this->_cleanKey($MessageId);
		$Key = $this->_CommitKey($MessageId, $category, $port, $files);
		 
		if (!file_exists($CacheDir)) {
			$this->_Log("CacheCommit: creating directory $CacheDir");
			$old_mask = umask(0000);
			if (!mkdir($CacheDir, 0774, true)) {
				$this->_Log("CacheCommit: unable to create directory $CacheDir");
			}
			umask($old_mask);
		}

		$result = 0;
		$result = parent::Add($Key);

		return $result;
	}

	function RemoveCommit($MessageId, $PageNum = 1) {
		$this->_Log("CacheCommit: Removing for $MessageId");

		#
		# the wild card allows us to remove all cache entries for this port
		# regardless of the CacheType or page number
		#
		$Key = $this->_CommitKey($MessageId, $category, $port, $files);
		$result = parent::Remove($Key, $data);

		return $result;
	}

	function _CommitKey($MessageId, $category, $port, $files) {
		// might want some parameter checking here
		$CleanMessageId = $this->_CleanKey($MessageId);
		$Key = self::CacheCategory . "/$CleanMessageId/$CleanMessageId." . $this->_CleanKey($category) . '.' . $this->_CleanKey($port) . '.' . $this->_CleanKey($files) . '.html';

		return $Key;
	}

	function _CacheFileName($key) {
		return $this->CacheDir . '/'. $key;
	}


}
