<?php
	#
	# $Id: commit_log_elements.php,v 1.1.2.2 2003-09-24 17:53:03 dan Exp $
	#
	# Copyright (c) 2003 DVL Software Limited
	#


// base class for commit_log_elements
class Commit_Log_Elements {

	var $dbh;

	var $element_id;
	var $message_id;
	var $commit_date;
	var $description;
	var $committer;
	var $encoding_losses;
	var $revision_name;
	var $security_notice_id;

	var $result;

	function Commit_Log_Elements($dbh) {
		$this->dbh	= $dbh;
	}
	
	function FetchInitialise($element_id) {

		# get ready to fetch all the commit_log_elements for this element
		# return the number of commits found

		$sql = "
select $element_id, 
       message_id,
       to_char(commit_date - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS')  as commit_date,
       commit_log.description,
       committer,
       encoding_losses,
       revision_name,
       security_notice.id as security_notice_id
  from commit_log, commit_log_elements LEFT OUTER JOIN security_notice
       ON commit_log_elements.commit_log_id = security_notice.commit_log_id
 where commit_log.id                  = commit_log_elements.commit_log_id
   and commit_log_elements.element_id = $element_id
 order by commit_log.commit_date desc ";

#		echo "\$sql='<pre>$sql</pre><br>\n";

		$this->result = pg_exec($this->dbh, $sql);
		if (!$this->result) {
			echo pg_errormessage() . " $sql";
		}
		$numrows = pg_numrows($this->result);

		return $numrows;
	}

	function FetchNthCommit($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$myrow = pg_fetch_array($this->result, $N);

		$this->element_id			= $myrow["element_id"];
		$this->message_id			= $myrow["message_id"];
		$this->commit_date			= $myrow["commit_date"];
		$this->description			= $myrow["description"];
		$this->committer			= $myrow["committer"];
		$this->encoding_losses		= $myrow["encoding_losses"];
		$this->revision_name		= $myrow["revision_name"];
		$this->security_notice_id	= $myrow["security_notice_id"];
	}
}

?>