<?php
	#
	# Copyright (c) 1998-2022 Dan Langille
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . "/../classes/ports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/../include/constants.php");

// searching ports by what's in pkgmessage
class PortsByUses extends Port {

	var $Query;
	var $PortStatus = PORT_STATUS_ACTIVE;
	var $Limit  = 0;
	var $Offset = 0;

	var $Debug = 0;
	
	var $LocalResult = null;
	
	const WITH_CLAUSE = 'WITH short_list AS MATERIALIZED (
    SELECT
        DISTINCT id as port_id
    FROM
        ports
    WHERE
        uses @@ websearch_to_tsquery(\'simple\',  $1)
)';

	function __construct($dbh) {
		parent::__construct($dbh);
	}
	
	function UsesSet($Query) {
		$this->Query = $Query;
	}

	function IncludeDeletedPorts($IncludeDeletedPorts = false) {
		if ($IncludeDeletedPorts) {
			$this->PortStatus = PORT_STATUS_DELETED;
			if ($this->Debug) echo 'deleted';
		} else {
			$this->PortStatus = PORT_STATUS_ACTIVE;
			if ($this->Debug) echo 'active';
		}
	}

	function GetQueryCount() {
		$count = 0;
		
		$sql = $this::WITH_CLAUSE . 'select count(*) as count from short_list, ports P, element_pathname EP WHERE P.id = short_list.port_id
   AND P.element_id = EP.element_id and EP.pathname like \'/ports/head/%\'';
		if ($this->Debug) echo "<pre>$sql</pre> with <pre>" . htmlentities($this->Query) . "</pre>";
		$result = pg_query_params($this->dbh, $sql, array(pg_escape_string($this->dbh, $this->Query)));
		if ($result) {
			$myrow = pg_fetch_array($result);
			$count = $myrow['count'];
		} else {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error($this->dbh));
			die('SQL ERROR');
		}

		return $count;
	}

	function FetchPorts($UserID = null, $sqlOrderBy = null) {
		# but yeah, it's not really ports we are fetching.
	

		$sqlFrom = "
       FROM short_list, ports P
       LEFT OUTER JOIN ports_vulnerable    PV  ON PV.port_id       = P.id
       LEFT OUTER JOIN commit_log          CL  ON P.last_commit_id = CL.id,
       element_pathname EP,
       categories C, element E ";


		$sqlWatchListFrom = '';
		if (IsSet($User->id)) {
			$sqlWatchListFrom .= "
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $User->id
       AND watch_list.in_service
  GROUP BY wle_element_id) AS TEMP
       ON TEMP.wle_element_id = E.id";
		}
			
		$sqlWhere =  "
 WHERE P.id = short_list.port_id
   AND P.element_id = EP.element_id and EP.pathname like '/ports/head/%'
   AND P.category_id  = C.id
   AND P.element_id   = E.id ";

		if ($this->PortStatus == PORT_STATUS_ACTIVE) {
			# restrict this to active ports only
			$sqlWhere .= " AND E.status = 'A'" ;
		}

   		# the CTE (Common Table Expression) exists because the LIMIT would hit performance
		$sql = "WITH t as (" . $this::WITH_CLAUSE . SEARCH_SELECT_FIELD . $sqlFrom . $sqlWatchListFrom . 
			$sqlWhere . ") SELECT  * from t " . $sqlOrderBy;

		$sql = $this::WITH_CLAUSE . SEARCH_SELECT_FIELD . $sqlFrom . $sqlWatchListFrom . 
			$sqlWhere . $sqlOrderBy;

	
		if ($this->Limit) {
			$sql .= " LIMIT " . $this->Limit;
		}
		
		if ($this->Offset) {
			$sql .= " OFFSET " . $this->Offset;
		}

		if ($this->Debug) echo '<pre>' . $sql . '</pre>';
		$this->LocalResult = pg_query_params($this->dbh, $sql, array(pg_escape_string($this->dbh, $this->Query)));
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
			if ($this->Debug) echo "That would give us $numrows rows";
		} else {
			$numrows = -1;
			echo 'pg_query_params failed: ' . "<pre>$sql</pre>";
		}

		return $numrows;
	}

	function SetLimit($Limit) {
		$this->Limit = $Limit;
	}
	
	function SetOffset($Offset) {
		$this->Offset = $Offset;
	}

}
