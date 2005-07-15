<?php
	#
	# $Id: freshports_page_list_ports.php,v 1.1.2.12 2005-07-15 03:08:34 dan Exp $
	#
	# Copyright (c) 2005 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports_page.php');

class freshports_page_list_ports extends freshports_page {

	var $_sql;
	var $_description;
	var $_port_status = 'A';  # show only active ports

	var $_condition   = '';   # any conditions on the SQL

    function freshports_page_list_ports($attributes = array()) {
		$this->freshports_page($attributes);
	}

	function Display() {
		freshports_ConditionalGet($this->getLastModified());

		# disable the HTML_Page2 headers.  we do our own.
		#
		$this->setCache(true);

		parent::Display();
	}

	function setStatus($Status) {
		switch ($Status) {
			case 'A':
			case 'D':
				$this->_port_status = $Status;
			break;

			default:
				$this->_port_status = 'A';
		}
	}

	function getStatus() {
		return $this->_port_status;
	}

	function setDescription($Description) {
		$this->_description = $Description;
	}

	function getDescription() {
		return $this->_description;
	}

	function getSQL() {
		return $this->_sql;
	}

	function setSQL($Condition, $UserID=0) {
		$this->_sql = "
SELECT ports.id, 
       element.name    as port,  
       categories.name as category, 
       ports.category_id, 
       version         as version, 
       revision        as revision,
       ports.element_id,
       maintainer, 
       short_description, 
       to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added,
       last_commit_id  as last_change_log_id,
       package_exists,
       extract_suffix,
       homepage,
       status,
       broken,
       forbidden,
       ignore,
       PV.current as vulnerable_current,
       PV.past    as vulnerable_past,
       restricted,
       deprecated,
       no_cdrom,
       expiration_date,
       latest_link ";

		if ($UserID) {
			$this->_sql .= ",
       onwatchlist";
		}

		$this->_sql .= "
from element, categories, ports_vulnerable PV right outer join ports on PV.port_id = ports.id ";

		if ($UserID) {
			$this->_sql .= '
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = ' . AddSlashes($UserID) . '
       AND watch_list.in_service
  GROUP BY wle_element_id) AS TEMP
       ON TEMP.wle_element_id = ports.element_id';
		}

		$this->_sql .= "
WHERE ports.element_id  = element.id
  and ports.category_id = categories.id 
  and status            = '" . $this->getStatus() . "'";

		if ($Condition) {
			$this->_sql .= '
  and ' . $Condition;
			$this->_condition = $Condition;
		}

		$this->_sql .= " order by " . $this->getSort();
#		$this->_sql .= " limit 20";

#		echo '<pre>' . $this->_sql . '</pre>';
	}

	function getLastModified() {
		$sql = "
SELECT gmt_format(max(commit_log.date_added)) as last_modified
  FROM ports, element, commit_log
WHERE ports.last_commit_id = commit_log.id
  AND ports.element_id     = element.id
  AND element.status       = '" . $this->getStatus() . "'";

		if ($this->_condition) {
			$sql .= '
  and ' . $this->_condition;
		}

#		echo '<pre>' . $sql . '</pre>';

		$result = pg_exec($this->_db, $sql);
		if ($result) {
			$myrow = pg_fetch_array ($result);
			$last_modified = $myrow['last_modified'];
		}

echo 'Last Modified is ' . $last_modified . '<br>';
		return $last_modified;
	}

	function getPorts() {
		$HTML = '';

		if ($this->getDebug()) {
			$HTML .= '<pre>' . $this->getSQL() . '</pre>';
		}

		$result = pg_exec($this->_db, $this->getSQL());
		if (!$result) {
			echo pg_errormessage();
		} else {
			$numrows = pg_numrows($result);
#			echo "There are $numrows to fetch<BR>\n";
		}

		require_once($_SERVER['DOCUMENT_ROOT'] . '/include/list-of-ports.php');

		$HTML .= freshports_ListOfPorts($result, $this->_db, 'Y', $this->getShowCategoryHeaders());

		return $HTML;
	}

	function getSort() {
		$HTML = '';

		if (IsSet( $_REQUEST["sort"])) {
			$sort = $_REQUEST["sort"];
		} else {
			$sort = '';
		}

		switch ($sort) {
			case 'dateadded':
				$sort = 'ports.date_added desc, category, port';
				break;

			case 'port':
				$sort = 'port';
				break;

			default:
				$sort ='category, port';
		}

		return $sort;
	}


	function getShowCategoryHeaders() {
		$sort = $this->getSort();

		switch ($sort) {
			case 'port':
			case 'dateadded':
				$ShowCategoryHeaders = 0;
				break;

			default:
				$ShowCategoryHeaders = 1;
		}

		return $ShowCategoryHeaders;
	}


	function getSortedbyHTML() {
		$HTML = '';

		$sort = $this->getSort();

		switch ($sort) {
			case 'dateadded':
				$HTML .= 'sorted by date added.  You can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=category">category</a>' .
							', or by <a href="' . $_SERVER["PHP_SELF"] . '?sort=port">port</a>.';
				break;

			case 'port':
				$HTML .= 'sorted by port.  You can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=category">category</a>' .
							', or by <a href="' . $_SERVER["PHP_SELF"] . '?sort=dateadded">date added</a>.';
				break;

			default:
				$HTML .= 'sorted by category.  You can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=dateadded">date added</a>' . 
							', or by <a href="' . $_SERVER["PHP_SELF"] . '?sort=port">port</a>.';
		}

		return $HTML;
	}



	function toHTML() {

		$this->addBodyContent('<TR><TD>' . $this->getDescription() . '</TD></TR>');

		// make sure the value for $sort is valid

		$SortStatement = "<TR><TD>\nThis page is " . $this->getSortedbyHTML() . "</TD></TR>\n";

		$this->addBodyContent($SortStatement); 

		$this->addBodyContent($HTML);

		$this->addBodyContent($this->getPorts());

		$this->addBodyContent($SortStatement); 

		return parent::toHTML();
	}
}
