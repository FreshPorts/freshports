<?php
	#
	# $Id: commit_log_elements.php,v 1.2 2006-12-17 11:37:18 dan Exp $
	#
	# Copyright (c) 2003-2006 DVL Software Limited
	#


// base class for commit_log_elements
# it seems this class is not used. dvl - 2023-04-01
class Commit_Log_Elements {

	var $dbh;

	var $element_id;
	var $message_id;
	var $commit_hash_short;
	var $commit_date;
	var $description;
	var $committer;
	var $committer_name;
	var $committer_email;
	var $author_name;
	var $author_email;
	var $encoding_losses;
	var $revision_name;

	var $result;
	var $Debug;

	function __construct($dbh) {
		$this->dbh	= $dbh;
	}
	
	function FetchInitialise($element_id) {

		# get ready to fetch all the commit_log_elements for this element
		# return the number of commits found

		$sql = "-- " . __FILE__ . '::' . __FUNCTION__ . "
select commit_log_elements.element_id, 
       message_id,
       commit_hash_short,
       to_char(commit_date - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS')  as commit_date,
       commit_log.description,
       committer,
       committer_name,
       committer_email,
       author_name,
       author_email,
       encoding_losses,
       revision_name
  from commit_log, commit_log_elements
 where commit_log.id                  = commit_log_elements.commit_log_id
   and commit_log_elements.element_id = $1
 order by commit_log.commit_date desc ";

		if ($Debug) echo "\$sql='<pre>$sql</pre><br>\n";

		$this->result = pg_query_params($this->dbh, $sql, array($element_id));
		if (!$this->result) {
			echo pg_last_error($this->dbh) . " $sql";
		}
		$numrows = pg_num_rows($this->result);

		return $numrows;
	}

	function FetchNthCommit($N) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$myrow = pg_fetch_array($this->result, $N);

		$this->element_id         = $myrow["element_id"];
		$this->message_id         = $myrow["message_id"];
		$this->commit_hash_short  = $myrow["commit_hash_short"];
		$this->commit_date        = $myrow["commit_date"];
		$this->description        = $myrow["description"];
		$this->committer          = $myrow["committer"];
		$this->committer_name     = $myrow["committer_name"];
		$this->committer_email    = $myrow["committer_email"];
		$this->author_name        = $myrow["author_name"];
		$this->author_email       = $myrow["author_email"];
		$this->encoding_losses    = $myrow["encoding_losses"];
		$this->revision_name      = $myrow["revision_name"];
	}

	function EncodingLosses() {
		return $this->encoding_losses == 't';
	}

}
