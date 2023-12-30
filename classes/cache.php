<?php
	#
	# $Id: cache.php,v 1.4 2008-01-26 23:41:05 dan Exp $
	#
	# Copyright (c) 2006-2022 DVL Software Limited
	#

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//
class Cache {

	var $LastModified;
	var $CacheData;

	var $CacheDir;
	var $SpoolDir;

	function __construct($CacheDir = CACHE_DIRECTORY, $SpoolDir = SPOOLING_DIRECTORY) {
		$this->CacheDir = $CacheDir;
		$this->SpoolDir = $SpoolDir;
	}

	function LastModifiedGet() {
		return $this->LastModified;
	}

	function CacheDataGet() {
		return $this->CacheData;
	}

	function CacheDataSet($Data) {
		$this->CacheData = $Data;
	}

	function Retrieve($key) {
		$result = 0;

		$this->_Log('Cache: retrieving for ' . $key);
		$CacheFileName = $this->_CacheFileName($key);
		$this->_Log('Cache: CacheFileName is ' . $CacheFileName);
		if (file_exists($CacheFileName) && is_readable($CacheFileName)) {
			// open, read, and return
			$this->LastModified = filemtime($CacheFileName);
			$CacheFileHandle = fopen($CacheFileName, 'r');
			if ($CacheFileHandle) {
				$filesize = filesize($CacheFileName);
				if ($filesize) {
					$this->CacheData = fread($CacheFileHandle, $filesize);
					fclose($CacheFileHandle);
					$this->_Log('Cache: Retrieve ' . $CacheFileName);
				}  else {
					$this->_Log('Cache: CRITICAL filesize is zero/false: ' . $CacheFileName, true);
					$result = -3;
				}
			} else {
				$this->_Log('Cache: FAILED Retrieve file open ' . $CacheFileName);
				$result = -1;
			}
		} else {
			$this->_Log('Cache: FAILED Retrieve NOT FOUND ' . $CacheFileName);
			$result = -2;
		}
		
		return $result;
	}

	function Add($key) {
		$result = 0;
		// need to use key name with care.  Remove all non a-z, A-Z, and 0-9.
		$SpoolFileName = $this->_SpoolFileName($key);
		$SpoolFileHandle = fopen($SpoolFileName, 'w');
		if ($SpoolFileHandle) {
			// write $data to file
			// returns the number of bytes written, or false on error
			// nothing write is still an error for us
			if (fwrite($SpoolFileHandle, $this->CacheData)) {
				// close $SpoolFileHandle
				fclose($SpoolFileHandle);

				// chmod to group writable so that the perl scripts, running
				// as freshports, can remove them when a new commit comes in.
				// the leading zero is important.
				$old = umask(0);
				chmod($SpoolFileName, 0774);
				umask($old);

				// mv spool file to cache dir
				$CacheFileName = $this->_CacheFileName($key);

				// mv $SpoolFileName $CacheFileName
				if (rename($SpoolFileName, $CacheFileName)) {
					// success
					$this->LastModified = filemtime($CacheFileName);

					$this->_Log('Cache: Add ' . $CacheFileName);
				} else {
					// rm $SpoolFileName
					unlink($SpoolFileName);
					$this->_Log('Cache: FAILED Add on move" ' . $SpoolFileName . ' to ' . $CacheFileName);
				}
			} else {
				fclose($SpoolFileHandle);
				$this->_Log('Cache: FAILED Add on write: ' . $SpoolFileName);
			}
		} else {
			$this->_Log('Cache: FAILED Add on opening file: ' . $SpoolFileName);
			$result = -1;
		}

		return $result;
	}

	function Remove($key) {
		$result = 0;

		$FileName = $this->_CacheFilename($key);
		// rm $Filename
		if (unlink($Filename)) {
			// success
			$this->_Log('Cache: Remove ' . $CacheFileName);
		} else {
			$this->_Log('Cache: FAILED Remove ' . $CacheFileName);
			$result = -1;
		}
		
		return $result;
	}

	function _CleanKey($key) {
		// convert /../ to .
		$this->_Log('Cache: cleaning  ' . ($key ?? '<NULL>') );

		$new_key = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $key ?? '' ) );

		return $new_key;
	}

	function _SpoolFileName($key) {
		$FileName = tempnam($this->SpoolDir, $this->_CleanKey($key) . '.tmp');

		$this->_Log('Cache: creating spool file ' . $FileName . ' hopefully in ' . $this->SpoolDir);

		return $FileName;
	}

	function _CacheFileName($key) {
		# remember: this function is often overridden in the descendant class
		$FileName = $this->CacheDir . '/' . $this->_CleanKey($key);

		return $FileName;
	}

	function _Log($activity, $always_log = false) {
		// log the above message
		# Logging cache takes up a lot of room. It is off by default
		# But we always log errors, for example.
		if (defined('FRESHPORTS_LOG_CACHE_ACTIVITY') || $always_log) {
			syslog(LOG_NOTICE, $activity);
		}
	}
}
