<?php
	#
	# $Id: security_notice.php,v 1.1.2.4 2003-03-08 13:31:41 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

$Debug = 0;

// base class for a single security notice entry
class SecurityNotice {

	var $dbh;

	var $id;
	var $user_id;
	var $date_added;
	var $ip_address;
	var $description;
	var $commit_log_id;
	var $status;

	var $LocalResult;

	function SecurityNotice($dbh) {
		$this->dbh	= $dbh;
	}
	
	function PopulateValues($myrow) {
		$this->id				= $myrow["id"];
		$this->user_id			= $myrow["user_id"];
		$this->date_added		= $myrow["date_added"];
		$this->ip_address		= $myrow["ip_address"];
		$this->description	= $myrow["description"];
		$this->commit_log_id	= $myrow["commit_log_id"];
		$this->status			= $myrow["status"];
	}

	function Create($message_id) {
		#
		# create a security_notice item
		#

		$return = 0;

		$query = "select SecurityNoticeCreate("  . $this->user_id      . ",
		                                      '" . $this->ip_address  . "',
		                                      '" . $this->description . "',
		                                      '" . $message_id        . "')";
		echo "<pre>$query</pre>";

		$this->LocalResult = pg_query($this->dbh, $query);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($this->LocalResult);
				$this->id = $myrow['securitynoticecreate'];
				$return = $this->id;
			} else {
				syslog(LOG_ERR, "Could not add item to security notice: $query " . pg_lasterror());
				die('Could not add item to security notice table');
			}
		} else {
			syslog(LOG_ERR, "Error adding item to security notice: $query " . pg_last_error());
			die('Could not add item to security notice table');
		}

		return $return;
	}

	function FetchByMessageID($message_id) {

		$query = "
SELECT security_notice.* 
  FROM security_notice, commit_log
 WHERE commit_log.message_id = '" . AddSlashes($message_id) . "'
   AND commit_log.id         = security_notice.commit_log_id";

		$this->LocalResult = pg_query($this->dbh, $query);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($this->LocalResult);
				$this->PopulateValues($myrow);
			}
		}

		return $this->id;
	}
}
