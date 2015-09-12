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

	function Categories($dbh) {
		$this->dbh	= $dbh;
	}

	function GetAllCategoriesOnWatchLists($UserID) {
		$sql = "
   SELECT distinct C.id AS category_id
     FROM watch_list WL, watch_list_element WLE, categories C
    WHERE WL.user_id     = $UserID
      AND WL.id          = WLE.watch_list_id
      AND WLE.element_id = C.element_id";

#		echo "<pre>sql = '$sql'</pre><BR>";

		$this->result = pg_query($this->dbh, $sql);
		if ($this->result) {
			$numrows = pg_numrows($this->result);
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
?>