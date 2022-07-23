<?php
	#
	# $Id: cache-port.php,v 1.6 2007-06-04 02:16:33 dan Exp $
	#
	# Copyright (c) 2006-2007 DVL Software Limited
	#

	require_once('cache.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');

// base class for caching
// Supplies methods for adding, removing, and retrieving.
//

class CacheCategory extends Cache {

	const CacheCategory = 'categories';

	var $PageSize = 100;

	function __construct() {
		return parent::__construct();
	}
	
	function PageSizeSet($PageSize) {
		$this->PageSize = $PageSize;
	}

	function RetrieveCategory($Category, $User = 0, $PageNum = 1, $Branch = BRANCH_HEAD) {
		$this->_Log("CacheCategory: Retrieving for $Category page $PageNum");
		$Key = $this->_CategoryKey($Category, $User, $PageNum, $Branch);
		$result = parent::Retrieve($Key);

		return $result;
	}

	function AddCategory($Category, $User, $PageNum = 1, $Branch = BRANCH_HEAD) {
		$this->_Log("CacheCategory: Adding for $Category page $PageNum");

		$CacheDir = $this->CacheDir . '/' . self::CacheCategory . '/' . $Category;
		$Key = $this->_CategoryKey($Category, $User, $PageNum, $Branch);
		 
		if (!is_dir($CacheDir)) {
			$this->_Log("CacheCategory: creating directory $CacheDir");
			$old_mask = umask(0000);
			#
			# we use @mkdir because we still this even if we check:
			# [23-Jul-2022 02:59:21 UTC] PHP Warning:  mkdir(): File exists in /usr/local/www/freshports/classes/cache-port.php on line 53
			# concurrency.
			#
			if (!@mkdir($CacheDir, 0774, true)) {
				$this->_Log("CacheCategory: unable to create directory $CacheDir");
			}
			umask($old_mask);
		}

		$result = 0;
		$result = parent::Add($Key);

		return $result;
	}

	function RemoveCategory($Category) {
		$this->_Log("CacheCategory: Removing for $Category");

		#
		# the wild card allows us to remove all cache entries for this category
		# regardless of the CacheType or page number
		#
		$Key = $this->_CategoryKey($Category,  '*', '*', '*');
		$result = parent::Remove($Key, $data);

		return $result;
	}

	function _CategoryKey($Category, $User = 0, $PageNum = 1, $Branch = BRANCH_HEAD) {
		// might want some parameter checking here
		$Key = self::CacheCategory . "/$Category/$Category.$User.$Branch.PageSize$this->PageSize.PageNum$PageNum.html";

		$this->_Log("CacheCategory: Key created is $Key");

		return $Key;
	}

	function _CacheFileName($key) {
		return $this->CacheDir . '/'. $key;
	}

}
