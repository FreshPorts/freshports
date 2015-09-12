<?php
	#
	# $Id: files.php,v 1.4 2013-04-08 12:15:34 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

// base class for fetching files associated with a commit
class CommitFiles {

	var $dbh;
	var $message_id;
	var $category;
	var $port;

	var $Debug = 0;

	var $LocalResult;

	function CommitFiles($dbh) {
		$this->dbh = $dbh;

		$this->message_id = '';
		$this->category   = '';
		$this->port       = '';
	}

	function MessageIDSet($MessageID) {
		$this->MessageID = $MessageID;
	}

	function CategorySet($Category) {
		$this->Category = $Category;
	}

	function PortSet($Port) {
		$this->Port = $Port;
	}

	function UserIDSet($UserID) {
		$this->UserID = $UserID;
	}

	function Fetch() {
		if ($this->MessageID == '') {
			echo 'No message_id supplied';
			syslog(LOG_ERR, __FILE__  . '::' . __LINE__  . ' No message_id supplied: ' .$_SERVER['SCRIPT_URI']);
			exit;
		}

		// will we filter this out for just one port?		
		$ForJustOnePort = IsSet($this->Category) && $this->Category != '' &&
		                  IsSet($this->Port)     && $this->Port     != '';

		$sql = '';
		
		$sql .= "SELECT A.*, 
	       B.*,
		   PV.current AS vulnerable_current,
	       PV.past    AS vulnerable_past
	FROM (
	";
	
		$sql .= "
	SELECT element_pathname(E.id) AS pathname, 
	       CLE.commit_log_id,
	       NULL::int AS port_id, 
	       CLE.commit_log_id,
	       E.id as element_id,
	       NULL::text  AS version, 
	       NULL::text AS revision,
	       NULL::text    AS epoch,
	       to_char(CL.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  AS commit_date,
	       to_char(CL.commit_date - SystemTimeAdjust(), 'HH24:MI:SS')   AS commit_time,
	       CLE.change_type, 
	       E.name AS filename, 
	       NULL::text AS category, 
	       NULL::text AS short_description, 
	       NULL::text AS port, 
	       NULL::text AS port_status,
	       CL.committer, 
	       CL.message_id, 
	       CL.encoding_losses, 
	       CL.description, 
	       CLE.revision_name AS revision_name,
	       E.status, 
	       NULL::text AS needs_refresh, 
	       NULL::text AS date_added, 
	       NULL::text AS forbidden, 
	       NULL::text AS broken,
	       NULL::text AS deprecated,
	       NULL::text AS ignore,
	       NULL::text AS restricted,
	       NULL::text AS no_cdrom,
	       NULL::text AS expiration_date,
	       NULL::text AS is_interactive,
	       GMT_Format(CL.date_added) AS last_modified,
               R.svn_hostname,
               R.path_to_repo
	  FROM commit_log               CL
	       LEFT OUTER JOIN repo R on CL.repo_id = R.id,
	       commit_log_elements      CLE,
	       element                  E
	 WHERE CL.message_id              = '" . pg_escape_string($this->MessageID) . "'
	   AND CL.id                      = CLE.commit_log_id
	   AND CLE.element_id             = E.id";

	
		if ($ForJustOnePort) { 
			$sql .= "
	   AND element_pathname(E.id) ILIKE  element_pathname(element_id('" .  pg_escape_string($this->Category)  . "', '" . pg_escape_string($this->Port) . "')) || '%'"; 
		}
		
		$sql .= ") AS A
		      LEFT OUTER JOIN
	";
	
		#
		# if the watch list id is provided (i.e. they are logged in and have a watch list id...)
		#
		if ($this->UserID) {
			$sql .= "
		 (SELECT element_id AS wle_element_id, COUNT(watch_list_id) AS onwatchlist
		    FROM watch_list JOIN watch_list_element 
		        ON watch_list.id      = watch_list_element.watch_list_id
		       AND watch_list.user_id = " . pg_escape_string($this->UserID) . "
	          AND watch_list.in_service
		  GROUP BY wle_element_id) AS B
		       ON B.wle_element_id = A.element_id
";
		} else {
			$sql .= "(SELECT 0) AS B ON (A.element_id = A.element_id )";
		}
		
		$sql .= " LEFT OUTER JOIN ports_vulnerable PV ON A.port_id = PV.port_id ";
	
		$sql .= "\nORDER BY 1";
	
		if ($this->Debug) echo '<PRE>' . $sql . '</PRE>';

		$this->LocalResult = pg_exec($this->dbh, $sql);

		if (!$this->LocalResult) {
			syslog(LOG_ERR, __FILE__  . '::' . __LINE__  . ' ' . pg_last_error());
			exit;
		}

		$NumRows = pg_numrows($this->LocalResult);
		return $NumRows;
	}
}

