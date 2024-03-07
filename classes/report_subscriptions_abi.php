<?php
	#
	# Copyright (c) 2024 Dan Langille
	#

// base class for subscriptions to package notifications
class report_subscriptions_abi {

	var $dbh;
	var $LocalResult;

	var $Debug;

	var $user_id;
	var $watch_list_id;
	var $abi_id;
	var $package_set;

	var $abi_name;
	var $watch_list_name;

	function __construct($dbh) {
		$this->dbh   = $dbh;
		$this->Debug = 1;
	}

	function DeleteAllSubscriptions($UserID) {
		#
		# Delete a the subscriptions for a user
		#
		unset($return);

		$query  = '
DELETE FROM report_subscriptions_abi 
 WHERE user_id = $1';

		if ($this->Debug) echo $query;
		$result = pg_query_params($this->dbh, $query, array($UserID));

		# that worked and we updated exactly one row
		if ($result) {
			$return = 1;
		}

		return $return;
	}

	function Fetch($UserID) {

		$this->Debug = 0;

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . '
		SELECT RSA.user_id,
			   RSA.abi_id,
			   abi.name AS abi_name,
			   RSA.package_set,
			   WL.id    AS watch_list_id,
			   WL.name  AS watch_list_name
		  FROM report_subscriptions_abi RSA JOIN abi on RSA.abi_id = abi.id 
		                                    JOIN watch_list WL ON RSA.watch_list_id = WL.id
		 WHERE RSA.user_id = $1
	  ORDER BY watch_list_name, abi_name';

		if ($this->Debug) {
			echo 'WatchLists::Fetch sql = <pre>' . $sql . '</pre>';
		}

		$this->LocalResult = pg_query_params($this->dbh, $sql, array($UserID));
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_query_params failed: ' . $sql;
		}

		return $numrows;
	}

	function Save($UserID, $watch_list_id, $abi_id, $package_set) {
		#
		# Save the list of ABI/watch list combinations.
		# abi_ids is an array of "$abi_id:$watch_list"
		#
		GLOBAL $Sequence_Watch_List_ID;

		$return = 0;

		# insert only that user owns that watch list.
		$query = '
insert into report_subscriptions_abi(user_id, watch_list_id, abi_id, package_set)
select $1, $2, $3, $4::package_sets
from watch_list
where id = $2 and user_id = $1
on conflict on constraint report_subscriptions_abi_user_abi_watch_pk do nothing';
		if ($this->Debug) echo "<pre>$query</pre>";

		$this->LocalResult = pg_query_params($this->dbh, $query, array($UserID, $watch_list_id, $abi_id, $package_set));
		if ($this->LocalResult) {
			$return = 1;
		} else {
			$return = 1;
		}
	}
	function Delete($UserID, $watch_list_id, $abi_id, $package_set) {
		#
		# Save the list of ABI/watch list combinations.
		# abi_ids is an array of "$abi_id:$watch_list"
		#
		GLOBAL $Sequence_Watch_List_ID;

		$return = 0;

		#
		# The "subselect" ensures the user can only delete things from their
		# own watch list
		#
		$query = '
DELETE from report_subscriptions_abi RSA
using watch_list WL
WHERE WL.user_id = $1
  AND WL.id = $2
  AND RSA.watch_list_id = WL.id
  AND RSA.abi_id = $3
  AND RSA.package_set = $4';


		if ($this->Debug) echo "<pre>$query</pre>";

		$this->LocalResult = pg_query_params($this->dbh, $query, array($UserID, $watch_list_id, $abi_id, $package_set));
		if ($this->LocalResult) {
			$return = 1;
		} else {
			$return = 1;
		}
	}

	function FetchNth($N) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch
		#

#		echo "fetching row $N<br>";

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->PopulateValues($myrow);

		return $myrow;
	}

	function PopulateValues($myrow) {
		#
		# call Fetch first.
		# then call this function N times, where N is the number
		# returned by Fetch.
		#

		$this->user_id          = $myrow['user_id'];
		$this->watch_list_id    = $myrow['watch_list_id'];
		$this->abi_id           = $myrow['abi_id'];
		$this->package_set      = $myrow['package_set'];
		$this->abi_name         = $myrow['abi_name'];
		$this->watch_list_name  = $myrow['watch_list_name'];
	}
}
