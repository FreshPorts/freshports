<?php
	#
	# $Id: category-listing.php,v 1.2 2006-12-17 11:37:18 dan Exp $
	#
	# Copyright (c) 2006 DVL Software Limited
	#

// base class for listing the categories
class Categories {

	var $category_id;
	
	var $result;

	function __construct($dbh) {
		$this->dbh	= $dbh;
	}

	function GetAllCategoriesOnWatchLists($UserID) {
		$sql = '
   SELECT distinct C.id AS category_id
     FROM watch_list WL, watch_list_element WLE, categories C
    WHERE WL.user_id     = $1
      AND WL.id          = WLE.watch_list_id
      AND WLE.element_id = C.element_id';

#		echo "<pre>sql = '$sql'</pre><br>";

		$this->result = pg_query_params($this->dbh, $sql, array($UserID));
		if ($this->result) {
			$numrows = pg_num_rows($this->result);
		} else {
			$numrows = 0;
		}

		return $numrows;
	}

	function GetAllCategories($UserID) {
		$sql = '
   SELECT name
     FROM categories
 ORDER BY name';

		 # why don't we cache this, and recreate it once per day?

#		echo "<pre>sql = '$sql'</pre><br>";

		$this->result = pg_query_params($this->dbh, $sql, array());
		if ($this->result) {
			$numrows = pg_num_rows($this->result);
		} else {
			$numrows = 0;
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call GetAllCategoriesOnWatchLists first.
		# then call this function N times, where N is the number
		# returned by GetAllCategoriesOnWatchLists.
		#

		$myrow = pg_fetch_array($this->result, $N);

		$this->category_id = $myrow['category_id'];
	}
}
