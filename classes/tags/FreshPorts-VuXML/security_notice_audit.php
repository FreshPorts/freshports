<?php
	#
	# $Id: security_notice_audit.php,v 1.1.2.2 2004-02-13 16:43:39 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

$Debug = 0;

// base class for a single security notice entry
class SecurityNoticeAudit {

	var $dbh;

	var $id;
	var $security_notice_id;
	var $user_id;
	var $date_added;
	var $ip_address;
	var $description;
	var $commit_log_id;
	var $status;

	var $user_name;
	var $user_email;

	var $LocalResult;

	function SecurityNoticeAudit($dbh) {
		$this->dbh	= $dbh;
	}
	
	function PopulateValues($myrow) {
		$this->id						= $myrow["id"];
		$this->security_notice_id	= $myrow["security_notice_id"];
		$this->user_id					= $myrow["user_id"];
		$this->date_added				= $myrow["date_added"];
		$this->ip_address				= $myrow["ip_address"];
		$this->description			= $myrow["description"];
		$this->commit_log_id			= $myrow["commit_log_id"];
		$this->status					= $myrow["security_notice_status_id"];

		$this->user_name				= $myrow["user_name"];
		$this->user_email				= $myrow["user_email"];
	}

	function FetchByMessageID($message_id) {

		$numrows = 0;

		$query = "
SELECT security_notice_audit.*,
       users.name  AS user_name,
       users.email AS user_email
  FROM security_notice_audit, commit_log, users
 WHERE commit_log.message_id = '" . AddSlashes($message_id) . "'
   AND commit_log.id         = security_notice_audit.commit_log_id
   AND users.id              = security_notice_audit.user_id
 ORDER BY date_added desc";

#echo "<pre>$query</pre>";

		$this->LocalResult = pg_query($this->dbh, $query);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		Unset($this->id);

		$myrow = pg_fetch_array ($this->LocalResult);
		$this->PopulateValues($myrow);

		return $this->id;
	}

}
