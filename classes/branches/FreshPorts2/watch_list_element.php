<?
	# $Id: watch_list_element.php,v 1.1.2.3 2002-12-10 03:55:45 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#

$Debug = 0;

// base class for a single item on a watch list
class WatchListElement {

	var $dbh;

	var $watch_list_id;
	var $element_id;

	var $watch_list_count;
	var $user_id;

	var $LocalResult;


	function WatchListElement($dbh) {
		$this->dbh	= $dbh;
	}
	
	function Delete($UserID, $WatchListID, $ElementID) {
		#
		# Delete an item from a watch list
		#

		#
		# The "subselect" ensures the user can only delete things from their
		# own watch list
		#
		$sql = "DELETE FROM watch_list_element
		         WHERE watch_list_element.element_id    = $ElementID
		           AND watch_list.id                    = $WatchListID
		           AND watch_list.user_id               = $UserID
		           AND watch_list_element.watch_list_id = watch_list.id";
		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);

		# that worked and we updated exactly one row
		if ($result) {
			$return = pg_affected_rows($result);
		} else {
			$return = -1;
		}

		return $return;
	}


	function DeleteElementFromWatchLists($UserID, $ElementID) {
		#
		# Delete this element from all watch lists
		#

		#
		# The "subselect" ensures the user can only delete things from their
		# own watch list
		#
		$sql = "DELETE FROM watch_list_element
		         WHERE watch_list_element.element_id    = $ElementID
		           AND watch_list.user_id               = $UserID
		           AND watch_list_element.watch_list_id = watch_list.id";
		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);

		# that worked and we updated exactly one row
		if ($result) {
			$return = pg_affected_rows($result);
		} else {
			$return = -1;
		}

		return $return;
	}

	function DeleteFromDefault($UserID, $ElementID) {
		#
		# Delete an item from all default watch lists
		#

		#
		# The "subselect" ensures the user can only delete things from their
		# own watch list
		#
		$sql = "DELETE FROM watch_list_element
		         WHERE watch_list_element.element_id    = $ElementID
		           AND watch_list.in_service            = TRUE
		           AND watch_list.user_id               = $UserID
		           AND watch_list_element.watch_list_id = watch_list.id";
		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);

		# that worked and we updated exactly one row
		if ($result) {
			$return = pg_affected_rows($result);
		} else {
			$return = -1;
		}

		return $return;
	}

	function Add($UserID, $WatchListID, $ElementID) {
		#
		# Add an item to a watch list
		#

		$Debug = 0;

		#
		# make sure we don't report the duplicate entry error when adding...
		#
		$PreviousReportingLevel = error_reporting(E_ALL ^ E_WARNING);

		#
		# The subselect ensures the user can only add things to their
		# own watch list
		#
		$sql = "
INSERT INTO watch_list_element 
select $WatchListID, $ElementID
  from watch_list 
 where user_id = $UserID
   and id      = $WatchListID
   and not exists (
    SELECT watch_list_element.watch_list_id, watch_list_element.element_id
      FROM watch_list_element
     WHERE watch_list_element.watch_list_id = $WatchListID
       AND watch_list_element.element_id    = $ElementID)";
		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$return = 1;
		} else {
			# If this isn't s aduplicate key error, then break
			if (stristr(pg_last_error(), "Cannot insert a duplicate key") == '') {
				$return = -1;
			} else {
				$return = 1;
			}
		}

		error_reporting($PreviousReportingLevel);

		return $return;
	}

	function AddToDefault($UserID, $ElementID) {
		#
		# Add an item to all default watch lists
		#

		$Debug = 0;

		#
		# The subselect ensures the user can only add things to their
		# own watch list and avoid duplicate key problems.
		#
		$sql = "
INSERT INTO watch_list_element 
select id, $ElementID 
  from watch_list 
 where in_service = TRUE 
   and user_id = $UserID
   and not exists (
    SELECT *
      FROM watch_list_element
     WHERE watch_list_element.watch_list_id = watch_list.id
       AND watch_list_element.element_id    = $ElementID)";

		if ($Debug) echo "<pre>$sql</pre>";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$return = 1;
		} else {
			$return = -1;
		}

		error_reporting($PreviousReportingLevel);

		return $return;
	}

	function PopulateValues($myrow) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch.
		#

		$this->watch_list_id		= $myrow["watch_list_id"];
		$this->element_id			= $myrow["element_id"];

		$this->watch_list_count	= $myrow["watch_list_count"];
		$this->user_id				= $myrow["user_id"];
	}
}
