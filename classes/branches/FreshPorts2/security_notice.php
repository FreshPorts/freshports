<?php
	#
	# $Id: security_notice.php,v 1.1.2.1 2003-01-10 15:51:30 dan Exp $
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

	function Create($UserID, $message_id, $description, $ip_address) {
		#
		# create a security_notice item
		#
		GLOBAL $Sequence_Security_Notice_ID;

		$return = 0;

		$NextValue = freshports_GetNextValue($Sequence_Security_Notice_ID, $this->dbh);

		$query = "
INSERT INTO security_notice (id,
                             user_id, 
                             ip_address,
                             description,
                             commit_log_id)
                     VALUES ($NextValue,
                             $UserID,
                             '$ip_address',
                             '$description',
                             (SELECT id
                                FROM commit_log
                               WHERE message_id = '$message_id')
                             )";

#		echo "<pre>$query</pre>";

		$this->LocalResult = pg_query($this->dbh, $query);
		if ($this->LocalResult) {
			$numrows = pg_affected_rows($this->LocalResult);
			if ($numrows == 1) {
				$this->id = $NextValue;			
				$return   = $NextValue;
				syslog(LOG_NOTICE, "added a new security notice: $NextValue : $description : $message_id");
			} else {
				syslog(LOG_ERR, "Could add item to security notice: $query " . pg_lasterror());
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
				$myrow = pg_fetch_array ($this->LocalResult, 0);
				$this->PopulateValues($myrow);
			}
		}

		return $this->id;
	}
}
