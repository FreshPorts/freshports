<?
	# $Id: commits.php,v 1.1.2.1 2002-11-27 20:21:39 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#


// base class for element
class Commits {

	var $Active		= 'A';
	var $Deleted	= 'D';
	var $dbh;

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
			commit_log.commit_date																					AS commit_date_raw,
			commit_log.id																								AS commit_log_id,
			commit_log.encoding_losses																				AS encoding_losses,
			commit_log.message_id																					AS message_id,
			commit_log.committer																						AS committer,
			commit_log.description																					AS commit_description,
			to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')						AS commit_date,
			to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI')							AS commit_time,
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
	  WHERE commit_log.commit_date         BETWEEN '$Date'::timestamp - SystemTimeAdjust()
	                                           AND '$Date'::timestamp - SystemTimeAdjust() + '1 Day'
		 AND commit_log_ports.commit_log_id = commit_log.id
	    AND commit_log_ports.port_id       = ports.id
	    AND categories.id                  = ports.category_id
	    AND element.id                     = ports.element_id
   ORDER BY commit_log.commit_date desc,
			commit_log_id,
			category,
			port";

		echo '<pre>' . $sql . '</pre>';

		if ($Debug)	echo "Element::FetchByName sql = '$sql'<BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			echo "That would give us $numrows rows";
		} else {
			echo 'pg_exec failed: ' . $sql;
		}
	}
}
