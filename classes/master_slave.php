<?php
	# $Id: master_slave.php,v 1.3 2007-09-30 16:46:23 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


// base class for Master-Slave relationships
class MasterSlave {

	var $slave_port_id;
	var $slave_port_name;
	var $slave_category_id;
	var $slave_category;

	var $dbh;
	var $LocalResult;

	function MasterSlave($dbh) {
		$this->dbh	= $dbh;
	}

    function _PopulateValues($myrow) {
		$this->slave_port_id			= $myrow['slave_port_id'];
		$this->slave_port_name			= $myrow['slave_port_name'];
		$this->slave_category_id		= $myrow['slave_category_id'];
		$this->slave_category_name		= $myrow['slave_category_name'];
	}

	function FetchByMaster($MasterName) {
		$sql = "
SELECT id          AS slave_port_id,
       name        AS slave_port_name,
       category_id AS slave_category_id,
       category    AS slave_category_name
  FROM ports_active
 WHERE master_port = '". pg_escape_string($MasterName). "'
ORDER BY slave_category_name, slave_port_name";

		#echo "sql = <pre>$sql</pre>";

        $this->LocalResult = pg_exec($this->dbh, $sql);
		if (!$this->LocalResult) {
			echo pg_errormessage() . " $sql";
		}

		$numrows = pg_numrows($this->LocalResult);

		return $numrows;
	}
	
	function FetchNth($N) {
		$myrow = pg_fetch_array($this->LocalResult, $N);
		$this->_PopulateValues($myrow);
	}

}

?>