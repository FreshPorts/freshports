<?php
	# $Id: ports-unrefreshed.php,v 1.2 2006-12-17 11:37:20 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for unrefreshed ports
class PortsUnrefreshed {

	var $dbh;
	var $LocalResult;

	var $port_id;
	var $port_name;
	var $category_id;
	var $category_name;
	var $commit_log_id;
	var $date_ignored;
	var $reason;
	var $message_id;

	function PortsUnrefreshed($dbh) {
		$this->dbh	= $dbh;
	}

	function _PopulateValues($myrow) {
		$this->port_id       = $myrow["port_id"];
		$this->port_name     = $myrow["port_name"];
		$this->category_id   = $myrow["category_id"];
		$this->category_name = $myrow["category_name"];
		$this->commit_log_id = $myrow["commit_log_id"];
		$this->date_ignored  = $myrow["date_ignored"];
		$this->reason        = $myrow["reason"];
		$this->message_id    = $myrow["message_id"];
	}

	function FetchAll() {
		# fetch all unrefreshed ports
		
		$Debug = 1;

		$sql = "
select ports.id         as port_id,
       element.name     as port_name,
       categories.id    as category_id,
       categories.name  as category_name,
       commit_log_ports.commit_log_id,
       commit_log_ports_ignore.date_ignored,
       commit_log_ports_ignore.reason,
       commit_log.message_id
  from ports, categories, element, commit_log, commit_log_ports LEFT OUTER JOIN commit_log_ports_ignore
         ON (commit_log_ports.commit_log_id = commit_log_ports_ignore.commit_log_id AND
             commit_log_ports.port_id       = commit_log_ports_ignore.port_id)
 where ports.category_id               = categories.id
   and ports.element_id                = element.id
   and commit_log_ports.port_id        = ports.id
   and commit_log_ports.needs_refresh <> 0
   and element.status                  = 'A'
   and commit_log_ports.commit_log_id  = commit_log.id
order by category_name, port_name";

#		if ($Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call FetchAll first.
		# then call this function N times, where N is the number
		# returned by FetchAll
		#
		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->_PopulateValues($myrow);
	}

}

