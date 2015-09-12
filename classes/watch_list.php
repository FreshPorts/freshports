<?php
	#
	# $Id: watch_list.php,v 1.2 2006-12-17 11:37:22 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#

$Debug = 0;

// base class for a single watchlist
class WatchList {

	var $dbh;

	var $id;
	var $user_id;
	var $name;
	var $in_service;
	var $token;

	var $watch_list_count;
	
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

		$Name = pg_escape_string($Name);
		
		$query = "
SELECT count(watch_list.id), users.max_number_watch_lists
    FROM users LEFT OUTER JOIN watch_list
               ON users.id = watch_list.user_id
   WHERE users.id = $UserID
GROUP BY users.max_number_watch_lists";

		$this->LocalResult = pg_query($this->dbh, $query);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
				$myrow = pg_fetch_array($this->LocalResult, 0);
				$Count = $myrow[0];
				$Max   = $myrow[1];
				if ($Count < $Max) {
					$NextValue = freshports_GetNextValue($Sequence_Watch_List_ID, $this->dbh);

					$result   = 0;
					$Attempts = 5;

					#
					# repeat the inserts until we get it in
					# we do this because the db inserts a random number into the
					# token column.  We might get a collision.  If we do, try 
					# again.  5 Collisions should be very rare.
					#
					while ($Attempts > 0 and !$result) {
						$query  = "insert into watch_list (id, user_id, name) values ($NextValue, $UserID, '$Name')";
						$result = pg_query($this->dbh, $query);
						if (!result) {
							syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ' inserting into watch_list failed on attempt ' . $Attempts . '.  collision on token column suspected.');
						}
						$Attempts--;
					}
			
					# that worked and we updated exactly one row
					if ($result && pg_affected_rows($result) == 1) {
						$return = $NextValue;
					}
					if (!result) {
						syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ' failed to insert into watch_list.  collision on token column suspected.').
						die('Sorry, I was unable to create you a watch list.  Please try again, and if failure persist, please contast the webmaster.');
					}

				} else {
					syslog(LOG_NOTICE, "You already have $Count watch lists.  If you want more than $Max watch lists, please contact the postmaster. UserID='$UserID'");
					die("You already have $Count watch lists.  If you want more than $Max watch lists, please contact the postmaster.");
				}
			} else {
				syslog(LOG_ERR, "Could not find watch list count for user $UserID - " . $_SERVER['PHP_SELF']);
				die("I couldn't find your watch list details... sorry");
			}
		} else {
			syslog(LOG_ERR, "Error finding watch list count for user $UserID - " . $_SERVER['PHP_SELF'] . ' ' . pg_last_error());
			die('Error finding watch list count for user');
		}

		return $return;
		
	}

	function Delete($UserID, $WatchListID) {
		#
		# Delete a watch list
		#
		unset($return);

		$query  = '
DELETE FROM watch_list 
 WHERE id = ' . pg_escape_string($WatchListID) .'
   AND user_id = ' . $UserID;

		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result && pg_affected_rows($result) == 1) {
			$return = $WatchListID;
		}

		return $return;
	}

	function EmptyTheList($UserID, $WatchListID) {
		#
		# Empty a watch list (couldn't use empty, as that's reserved)
		#
		unset($return);
		$Debug = 0;

		$query = "
DELETE FROM watch_list_element
 USING watch_list
 WHERE watch_list.id                    = $WatchListID
   AND watch_list.user_id               = $UserID
   AND watch_list_element.watch_list_id = watch_list.id";

		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result) {
			$return = $WatchListID;
		}

		return $return;
	}

	function EmptyTheListCategory($UserID, $WatchListID, $CategoryID) {
		#
		# Empty a watch list of all items within the supplied category
		#
		unset($return);
		$Debug = 0;

		$query = "
DELETE FROM watch_list_element
 USING ports_categories, ports, watch_list
 WHERE ports_categories.category_id     = $CategoryID
   AND ports_categories.port_id         = ports.id
   AND ports.element_id                 = watch_list_element.element_id
   AND watch_list.id                    = $WatchListID
   AND watch_list.user_id               = $UserID
   AND watch_list_element.watch_list_id = watch_list.id";

		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result) {
			$return = $WatchListID;
		}

		return $return;
	}

	function EmptyAllLists($UserID) {
		#
		# Empty all watch lists
		#
		unset($return);
		$Debug = 0;

		$query = "
DELETE FROM watch_list_element
 USING watch_list
 WHERE watch_list.user_id               = $UserID
   AND watch_list_element.watch_list_id = watch_list.id";

		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result) {
			$return = pg_affected_rows($result);
			if ($Debug) echo '<br>pg_affected_rows = ' . $return;
		}

		return $return;
	}

	function Rename($UserID, $WatchListID, $NewName) {
		#
		# Delete a watch list
		#
		unset($return);

		$query  = '
UPDATE watch_list 
   SET name = \'' . pg_escape_string($NewName) . '\' 
 WHERE id = ' . pg_escape_string($WatchListID) . '
   AND watch_list.user_id = ' . $UserID;
		if ($Debug) echo $query;
		$result = pg_query($this->dbh, $query);

		# that worked and we updated exactly one row
		if ($result && pg_affected_rows($result) == 1) {
			$return = $NewName;
		}

		return $return;
	}

	
	function Fetch($UserID, $ID) {
		$Debug = 0;

		$sql = "
		SELECT id,
		       user_id,
		       name,
		       in_service,
		       token,
               NULL as watch_list_count
		  FROM watch_list
		 WHERE id      = " . pg_escape_string($ID) . "
		   AND user_id = " . pg_escape_string($UserID);

#		echo '<pre>' . $sql . '</pre>';

		if ($Debug)	echo "WatchLists::Fetch sql = '$sql'<BR>";

		if ($ID == '') {
			syslog(LOG_NOTICE, "classes/watch_list.php::line 213 \$UserID='$UserID', \$ID='$ID'");
		}
		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows > 0) {
				$myrow = pg_fetch_array($this->LocalResult, 0);
				$this->PopulateValues($myrow);
			}
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

		$this->id				= $myrow["id"];
		$this->user_id			= $myrow["user_id"];
		$this->name				= $myrow["name"];
		$this->in_service		= $myrow["in_service"];
		$this->token			= $myrow["token"];
		$this->watch_list_count = $myrow["watch_list_count"];
	}
}

