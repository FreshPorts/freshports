<?php
	#
	# $Id: security_notice.php,v 1.1.2.6 2004-02-13 15:09:27 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

DEFINE('SECURITY_NOTICE_STATUS_CANDIDATE', 'C');
DEFINE('SECURITY_NOTICE_STATUS_ACTIVE',    'A');
DEFINE('SECURITY_NOTICE_STATUS_IGNORE',    'I');

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

	var $commit_date;
	var $commit_time;
	var $committer;
	var $commit_description;
	var $encoding_losses;

	var $user_name;
	var $user_email;

	function SecurityNotice($dbh) {
		$this->dbh	= $dbh;
	}
	
	function PopulateValues($myrow) {
		$this->id				= $myrow['id'];
		$this->user_id			= $myrow['user_id'];
		$this->date_added		= $myrow['date_added'];
		$this->ip_address		= $myrow['ip_address'];
		$this->description		= $myrow['description'];
		$this->commit_log_id	= $myrow['commit_log_id'];
		$this->status			= $myrow['status'];

		$this->commit_date        = $myrow['commit_date'];
		$this->commit_time        = $myrow['commit_time'];
		$this->committer          = $myrow['committer'];
		$this->commit_description = $myrow['commit_description'];
		$this->encoding_losses    = $myrow['encoding_losses'];
		$this->message_id         = $myrow['message_id'];

		$this->user_name          = $myrow['user_name'];
		$this->user_email         = $myrow['user_email'];
	}

	function Create($message_id) {
		#
		# create a security_notice item
		#

		$return = 0;

		$query = "select SecurityNoticeCreate("  . $this->user_id      . ",
		                                      '" . $this->ip_address  . "',
		                                      '" . $this->description . "',
		                                      '" . $message_id        . "',
                                              '" . $this->status      . "')";
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

	function FetchByStatus($status) {

		$num_rows = 0;

		$query = "
SELECT SN.*,
       to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  as commit_date,
       to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI:SS')   as commit_time,
       CL.committer,
       CL.description as commit_description,
       CL.encoding_losses,
       CL.message_id,
       U.name as user_name,
       U.email as user_email
  FROM commit_log CL, security_notice SN LEFT OUTER JOIN users U on SN.user_id = U.id
 WHERE CL.id = SN.commit_log_id";

		if (IsSet($status) && $status != '') {
			$query .= "\n   AND SN.status = '" . AddSlashes($status) . "'";
		}

		$query .= "\n  ORDER BY SN.date_added desc";

#echo "<pre>$query</pre>";

		$this->LocalResult = pg_query($this->dbh, $query);
		if ($this->LocalResult) {
			$num_rows = pg_numrows($this->LocalResult);
		}

		return $num_rows;
	}

	function FetchNth($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$myrow = pg_fetch_array ($this->LocalResult, $N);
		$this->PopulateValues($myrow);

		return $this->id;
	}

	function StatusText() {
		$OutStatus = '';
		switch ($this->status) {
			case SECURITY_NOTICE_STATUS_ACTIVE:
				$OutStatus = 'Active';
				break;

			case SECURITY_NOTICE_STATUS_CANDIDATE:
				$OutStatus = 'Candidate';
				break;

			case SECURITY_NOTICE_STATUS_IGNORE:
				$OutStatus = 'Ignored';
				break;

			default:
				die("unexpected value for Status: '$this->status'");
		}

		return $OutStatus;	
	}
}
