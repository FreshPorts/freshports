<?
	# $Id: user.php,v 1.1.2.1 2002-12-08 17:34:21 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#

$Debug = 0;

// base class for a single User
class User {

	var $dbh;

	var $id;
	var $name;
	var $password;
	var $cookie;
	var $firstlogin;
	var $lastlogin;
	var $email;
	var $watch_notice_id;
	var $emailsitenotices_yn;
	var $emailbouncecount;
	var $type;
	var $status;
	var $ip_address;
	var $number_of_commits;
	var $number_of_days;
	var $watch_list_add_remove;

	var $LocalResult;


	function User($dbh) {
		$this->dbh	= $dbh;
	}
	

	function Rename($UserID, $NewName) {
		#
		# Delete a watch list
		#
		unset($return);

		$query  = 'UPDATE watch_list SET name = \'' . AddSlashes($NewName) . '\' WHERE id = ' . AddSlashes($UserID);
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
		SELECT *
		  FROM users
		 WHERE id = $ID";

#		echo '<pre>' . $sql . '</pre>';

		if ($Debug)	echo "Users::Fetch sql = '$sql'<BR>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$myrow = pg_fetch_array($this->LocalResult, 0);
			$this->PopulateValues($myrow);
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

		$this->id							= $myrow["id"];
		$this->name							= $myrow["name"];
		$this->password					= $myrow["password"];
		$this->cookie						= $myrow["cookie"];
		$this->firstlogin					= $myrow["firslogin"];
		$this->lastlogin					= $myrow["lastlogin"];
		$this->email						= $myrow["email"];
		$this->watch_notice_id			= $myrow["watch_notice_id"];
		$this->emailsitenotices_yn		= $myrow["emailsitenotices_yn"];
		$this->emailbouncecount			= $myrow["emailbouncecount"];
		$this->type							= $myrow["type"];
		$this->status						= $myrow["status"];
		$this->ip_address					= $myrow["ip_address"];
		$this->number_of_commits		= $myrow["number_of_commits"];
		$this->number_of_days			= $myrow["number_of_days"];
		$this->watch_list_add_remove	= $myrow["watch_list_add_remove"];
	}
	
	function SetWatchListAddRemove($UserID, $WatchListAddRemove) {
		
		$sql = 'UPDATE users 
		          set watch_list_add_remove = \'' . AddSlashes($WatchListAddRemove) . '\'
		        WHERE id                    =   ' . AddSlashes($UserID);
		
		if ($Debug)	echo "Users::Fetch sql = '$sql'<BR>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_affected_rows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}
	
}
