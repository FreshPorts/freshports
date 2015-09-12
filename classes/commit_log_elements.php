<?php
	#
	# $Id: commit_log_elements.php,v 1.2 2006-12-17 11:37:18 dan Exp $
	#
	# Copyright (c) 2003-2006 DVL Software Limited
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

	var $result;
	var $Debug;

	function Commit_Log_Elements($dbh) {
		$this->dbh	= $dbh;
	}
	
	function FetchInitialise($element_id) {

		# get ready to fetch all the commit_log_elements for this element
		# return the number of commits found

		$sql = "
select commit_log_elements.element_id, 
       message_id,
       to_char(commit_date - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS')  as commit_date,
       commit_log.description,
       committer,
       encoding_losses,
       revision_name
  from commit_log, commit_log_elements
 where commit_log.id                  = commit_log_elements.commit_log_id
   and commit_log_elements.element_id = " . pg_escape_string($element_id) . "
 order by commit_log.commit_date desc ";

		if ($Debug) echo "\$sql='<pre>$sql</pre><br>\n";

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
	}

	function EncodingLosses() {
		return $this->encoding_losses == 't';
	}

}

?>