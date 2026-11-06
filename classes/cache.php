<?php
	#
	# $Id: cache.php,v 1.4 2008-01-26 23:41:05 dan Exp $
	#
	# Copyright (c) 2006-2026 Dan Langille
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
		# $SpoolDir is kept for compatibility but is no longer used.
		# Spool files are created next to the cache file instead (see _SpoolFileName()),
		# because rename() across filesystems (each cache dir is its own ZFS dataset)
		# is a non-atomic copy + unlink, not an atomic move.
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

		$key = $this->_LocalizedKey($key);
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
					fclose($CacheFileHandle);
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
		$key = $this->_LocalizedKey($key);
		$CacheFileName = $this->_CacheFileName($key);
		$SpoolFileName = $this->_SpoolFileName($CacheFileName);
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

				// mv $SpoolFileName $CacheFileName
				if (rename($SpoolFileName, $CacheFileName)) {
					// success
					$this->LastModified = filemtime($CacheFileName);

					$this->_Log('Cache: Add ' . $CacheFileName);
				} else {
					// rm $SpoolFileName
					unlink($SpoolFileName);
					$this->_Log('Cache: FAILED Add on move ' . $SpoolFileName . ' to ' . $CacheFileName);
				}
			} else {
				fclose($SpoolFileHandle);
				// rm $SpoolFileName
				unlink($SpoolFileName);
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

		// the key may contain wild cards, see the RemovePort() etc in the descendant classes
		$CacheFileNames = glob($this->_CacheFileName($key));
		if ($CacheFileNames === false) {
			$this->_Log('Cache: FAILED Remove glob ' . $key);
			return -1;
		}

		foreach ($CacheFileNames as $CacheFileName) {
			// rm $CacheFileName
			if (unlink($CacheFileName)) {
				// success
				$this->_Log('Cache: Remove ' . $CacheFileName);
			} else {
				$this->_Log('Cache: FAILED Remove ' . $CacheFileName);
				$result = -1;
			}
		}

		return $result;
	}

	// each language has its own cache entry - see freshports_i18n_cache_suffix()
	function _LocalizedKey($key) {
		$suffix = function_exists('freshports_i18n_cache_suffix') ? freshports_i18n_cache_suffix() : '';
		if ($suffix == '') {
			return $key;
		}

		if (substr($key, -5) == '.html') {
			return substr($key, 0, -5) . $suffix . '.html';
		}

		return $key . $suffix;
	}

	function _CleanKey($key) {
		// convert /../ to .
		$this->_Log('Cache: cleaning  ' . ($key ?? '<NULL>') );

		$new_key = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $key ?? '' ) );

		return $new_key;
	}

	function _SpoolFileName($CacheFileName) {
		# Spool in the same directory as the cache file, never in $this->SpoolDir.
		# Each cache directory is its own ZFS dataset. rename() across datasets
		# fails with EXDEV and PHP falls back to copy + unlink, which truncates
		# and rewrites the cache file in place. A concurrent Retrieve() then sees
		# an empty or partial file ("CRITICAL filesize is zero/false").
		# The leading dot keeps spool files out of * globs, e.g. Remove().
		$Dir      = dirname($CacheFileName);
		$FileName = tempnam($Dir, '.spool-');

		$this->_Log('Cache: creating spool file ' . $FileName . ' hopefully in ' . $Dir);

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
