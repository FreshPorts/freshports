<?
	# $Id: commits.php,v 1.1.2.2 2002-11-27 21:54:47 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#


// base class for element
class Commits {

	var $dbh;
	var $LocalResult;

	var $commit_log_id;
	var $commit_date_raw;
	var $encoding_losses;
	var $message_id;
	var $committer;
	var $commit_description;
	var $commit_date;
	var $commit_time;
	var $port_id;
	var $category;
	var $category_id;
	var $port;
	var $version;
	var $revision;
	var $status;
	var $needs_refresh;
	var $forbidden;
	var $broken;
	var $date_added;
	var $element_id;
	var $short_description;
	var $watch;

	function Commits($dbh) {
		$this->dbh	= $dbh;
	}

	function Fetch($Date) {
		$sql = "
		SELECT DISTINCT
			commit_log.commit_date																			AS commit_date_raw,
			commit_log.id																								AS commit_log_id,
			commit_log.encoding_losses																				AS encoding_losses,
			commit_log.message_id																					AS message_id,
			commit_log.committer																						AS committer,
			commit_log.description																					AS commit_description,
			to_char(commit_log.commit_date, 'DD Mon YYYY')						AS commit_date,
			to_char(commit_log.commit_date, 'HH24:MI')							AS commit_time,
			commit_log_ports.port_id																				AS port_id,
			categories.name																							AS category,
			categories.id																								AS category_id,
			element.name																								AS port,
			CASE when commit_log_ports.port_version IS NULL then ports.version  else commit_log_ports.port_version  END as version,
			CASE when commit_log_ports.port_version is NULL then ports.revision else commit_log_ports.port_revision END AS revision,
			element.status																								AS status,
			commit_log_ports.needs_refresh																		AS needs_refresh,
			ports.forbidden																							AS forbidden,
			ports.broken																								AS broken,
			date_part('epoch', ports.date_added)																AS date_added,
			ports.element_id																							AS element_id,
			ports.short_description 																				AS short_description
	   FROM commit_log_ports, commit_log, element, categories, ports
	  WHERE commit_log.commit_date         BETWEEN '$Date'::timestamp
	                                           AND '$Date'::timestamp + '1 Day'
		 AND commit_log_ports.commit_log_id = commit_log.id
	    AND commit_log_ports.port_id       = ports.id
	    AND categories.id                  = ports.category_id
	    AND element.id                     = ports.element_id
   ORDER BY 1 desc,
			commit_log_id,
			category,
			port";

#		echo '<pre>' . $sql . '</pre>';

		if ($Debug)	echo "Element::FetchByName sql = '$sql'<BR>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
#			echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}

	function FetchNth($N) {
		#
		# call FetchByCategoryInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchByCategoryInitialise
		#

		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->_PopulateValues($myrow);
	}


	function _PopulateValues($myrow) {
		#
		# call FetchInitialise first.
		# then call this function N times, where N is the number
		# returned by FetchInitialise.
		#

		$this->commit_log_id			= $myrow["commit_log_id"];
		$this->commit_date_raw		= $myrow["commit_date_raw"];
		$this->encoding_losses		= $myrow["encoding_losses"];
		$this->message_id				= $myrow["message_id"];
		$this->committer				= $myrow["committer"];
		$this->commit_description	= $myrow["commit_description"];
		$this->commit_date			= $myrow["commit_date"];
		$this->commit_time			= $myrow["commit_time"];
		$this->port_id					= $myrow["port_id"];
		$this->category				= $myrow["category"];
		$this->category_id			= $myrow["category_id"];
		$this->port						= $myrow["port"];
		$this->version					= $myrow["version"];
		$this->revision				= $myrow["revision"];
		$this->status					= $myrow["status"];
		$this->needs_refresh			= $myrow["needs_refresh"];
		$this->forbidden				= $myrow["forbidden"];
		$this->broken					= $myrow["broken"];
		$this->date_added				= $myrow["date_added"];
		$this->element_id				= $myrow["element_id"];
		$this->short_description	= $myrow["short_description"];
		$this->watch					= $myrow["watch"];
	}

}
