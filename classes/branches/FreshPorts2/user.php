<?php
	#
	# $Id: user.php,v 1.1.2.15 2006-07-26 20:11:25 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

$Debug = 0;

DEFINE('SUPER_USER', 'S');
DEFINE('USER',       'U');

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
	var $last_watch_list_chosen;
	var $page_size;
	var $filter;

	var $UserTasks;

	var $LocalResult;


	function User($dbh) {
		GLOBAL $DefaultPageSize;	# from include/getvalues.php

		$this->dbh	     = $dbh;
		$this->id        = 0;
		$this->page_size = 100;
#echo 'page size = .' . $this->page_size . '.';
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


	function FetchByCookie($Cookie) {
		$sql = "SELECT users.*
		          FROM users
				 WHERE cookie = '$Cookie'";

#		echo "Users::Fetch sql = '$sql'<BR>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
#			echo "That would give us $numrows rows";
			if ($numrows == 1) {
				$myrow = pg_fetch_array($this->LocalResult, 0);
				$this->PopulateValues($myrow);
			} else {
				freshports_CookieClear();
				syslog(LOG_ERR, "Could not find user details for '$Cookie' from '" . 
				        $_SERVER['REMOTE_ADDR'] . "' for '". $SERVER['REQUEST_URI'] . "'.");
				die('Your user details were not found.  You have been logged out.  Please reload this page.');
			}
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

		$this->id						= $myrow['id'];
		$this->name						= $myrow['name'];
		$this->password					= $myrow['password'];
		$this->cookie					= $myrow['cookie'];
		$this->firstlogin				= $myrow['firstlogin'];
		$this->lastlogin				= $myrow['lastlogin'];
		$this->email					= $myrow['email'];
		$this->watch_notice_id			= $myrow['watch_notice_id'];
		$this->emailsitenotices_yn		= $myrow['emailsitenotices_yn'];
		$this->emailbouncecount			= $myrow['emailbouncecount'];
		$this->type						= $myrow['type'];
		$this->status					= $myrow['status'];
		$this->ip_address				= $myrow['ip_address'];
		$this->number_of_commits		= $myrow['number_of_commits'];
		$this->number_of_days			= $myrow['number_of_days'];
		$this->watch_list_add_remove	= $myrow['watch_list_add_remove'];
		$this->last_watch_list_chosen	= $myrow['last_watch_list_chosen'];
		$this->filter					= $myrow['filter'];

		$this->page_size				= $myrow['page_size'];
		if (!IsSet($this->page_size) || $this->page_size == '') {
			GLOBAL $DefaultPageSize;	# from configuration/freshports.conf.php
										# and also set in include/getvalues.php

			$this->page_size = $DefaultPageSize;
		}
	}
	
	function SetWatchListAddRemove($WatchListAddRemove) {
		
		$sql = 'UPDATE users 
		          set watch_list_add_remove = \'' . AddSlashes($WatchListAddRemove) . '\'
		        WHERE id                    =   ' . $this->id;

#		if ($Debug)	echo "Users::Fetch sql = '$sql'<BR>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_affected_rows($this->LocalResult);
#			echo "That would give us $numrows rows";
			$this->watch_list_add_remove = AddSlashes($WatchListAddRemove);
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}

	function SetLastWatchListChosen($WatchListID) {

		$Debug = 0;
		
		$sql = 'UPDATE users 
		          set last_watch_list_chosen = \'' . AddSlashes($WatchListID) . '\'
		        WHERE id                     =   ' . $this->id;
		
		if ($Debug)	echo "Users::Fetch sql = '$sql'<BR>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_affected_rows($this->LocalResult);
#			echo "That would give us $numrows rows";
			$this->last_watch_list_chosen = AddSlashes($WatchListID);
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}

	function GetTasks() {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');

		$UserTasks = new UserTasks($this->dbh);
		$UserTasks->FetchByID($this->id);

		$this->UserTasks = $UserTasks->tasks;
	}

	function IsTaskAllowed($task) {
#		echo "class::user \$this->id='$this->id' and task '$task'<br>\n";
		if (IsSet($this->id) && $this->id != '' && !IsSet($this->UserTasks)) {
			$this->GetTasks();
#			die('getting the tasks now');
		}

#		echo 'looking for ' . $task . ' which gives ' . $this->UserTasks{$task};

		if (IsSet($this->UserTasks{$task})) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}
