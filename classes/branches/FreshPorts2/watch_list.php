<?
	# $Id: watch_list.php,v 1.1.2.1 2002-12-04 21:27:22 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#

$Debug = 0;

// base class for a single watchlist
class WatchList {

	var $dbh;

	var $id;
	var $user_id;
	var $name;
	
	
	var $LocalResult;


	function WatchList($dbh) {
		$this->dbh	= $dbh;
	}
	
	function Create($UserID, $Name) {
		#
		# create a new and empty watch list
		#
		GLOBAL $Sequence_Watch_List_ID;

		$return = 0;

		AddSlashes($Name);

		$NextValue = freshports_GetNextValue($Sequence_Watch_List_ID, $this->dbh);

		$query  = "insert into watch_list (id, user_id, name) values ($NextValue, $UserID, '$Name')";
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result && pg_affected_rows($result) == 1) {
			$return = $NextValue;
		}

		return $return;
		
	}

	function Delete($WatchListID) {
		#
		# Delete a watch list
		#
		unset($return);

		$query  = 'DELETE FROM watch_list WHERE id = ' . AddSlashes($WatchListID);
		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result && pg_affected_rows($result) == 1) {
			$return = $WatchListID;
		}

		return $return;
	}

	function EmptyTheList($WatchListID) {
		#
		# Empty a watch list (couldn't use empty, as that's reserved)
		#
		unset($return);

		$query  = 'DELETE FROM watch_list_element WHERE watch_list_id = ' . AddSlashes($WatchListID);
		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result) {
			$return = $WatchListID;
		}

		return $return;
	}

	function Rename($WatchListID, $NewName) {
		#
		# Delete a watch list
		#
		unset($return);

		$query  = 'UPDATE watch_list SET name = \'' . AddSlashes($NewName) . '\' WHERE id = ' . AddSlashes($WatchListID);
		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result && pg_affected_rows($result) == 1) {
			$return = $NewName;
		}

		return $return;
	}

	
	function Fetch($ID) {
		$sql = "
		SELECT id,
		       user_id,
		       name
		  FROM watch_list
		 WHERE id = $ID";

#		echo '<pre>' . $sql . '</pre>';

		if ($Debug)	echo "WatchLists::Fetch sql = '$sql'<BR>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}


	function PopulateValues($myrow) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch.
		#

		$this->id		= $myrow["id"];
		$this->user_id	= $myrow["user_id"];
		$this->name		= $myrow["name"];
	}
}
