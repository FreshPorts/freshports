<?php
	#
	# $Id: freshports_page_list_ports.php,v 1.2 2006-12-17 11:55:53 dan Exp $
	#
	# Copyright (c) 2005-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports_page.php');
	require_once('Pager/Pager.php');

class freshports_page_list_ports extends freshports_page {

	var $User;

	var $_sql;
	var $_description;
	var $_port_status = 'A';  # show only active ports

	var $_result;
	var $_condition   = '';   # any conditions on the SQL

	var $_pager;				# if set, a Pager::Pager() object
								# set 'pager' in the attributes

	# if either of these two are set, it implies a pager is required
	var $_pageSize    = 100;	# max number of items per page.
	var $_pageNumber  = 1;		# the page number to display now

	function freshports_page_list_ports($attributes = array()) {
		$this->freshports_page($attributes);
		
		GLOBAL $User;
		$this->User = $User;

		$page_number = 1;
		if (IsSet($_REQUEST['page'])) {
			$page_number = intval($_REQUEST['page']);
			if ($page_number != $_REQUEST['page']) {
				$page_number = 1;
			}
		}

		$this->setPageNumber($page_number);
	}
	
	function SetUser($User) {
		$this->User = $User;
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
		$sql = $this->_sql;
		
		$NumPorts = $this->getRowCount();

		if (IsSet($this->_pager)) {
			unset($this->_pager);
		}

		$params = array(
				'mode'        => 'Sliding',
				'perPage'     => $this->_pageSize,
				'delta'       => 5,
				'totalItems'  => $NumPorts,
				'urlVar'      => 'page',
				'currentPage' => $this->_pageNumber,
				'spacesBeforeSeparator' => 1,
				'spacesAfterSeparator'  => 1,
			);
		$this->_pager = & Pager::factory($params);
		unset($params);

		if ($this->_pageNumber > 1) {
			$offset = $this->_pager->getOffsetByPageId();
			$sql .= "\nOFFSET " . ($offset[0] - 1);
			unset($offset);
		}

		if ($this->_pageSize) {
			$sql .= "\nLIMIT " . $this->_pageSize;
		}
		
#		echo '<pre>From line ' . __LINE__ . ' of ' . __FILE__ . ': ' . $sql . '</pre>';exit;
#echo 'in GetSQL now';
		return $sql;
	}
	
	function getSQLCount() {
		$sql  = 'SELECT count(*) FROM ports LEFT OUTER JOIN ports_vulnerable PV on PV.port_id = ports.id';
		$sql .= " WHERE ports.status = '" . $this->getStatus() . "'";
		if ($this->_condition) {
			$sql .= "\n   AND " . $this->_condition;
		}

#		echo '<pre>From line ' . __LINE__ . ' of ' . __FILE__ . ': ' . $sql . '</pre>';exit;

		return $sql;
	}

	function getRowCount() {
		$numrows = -1;
		$result = pg_exec($this->_db, $this->getSQLCount());
		if ($result) {
			$myrow = pg_fetch_array ($result);
			$numrows = $myrow[0];
#			echo "There are $numrows to fetch<BR>\n";
		} else {
			echo pg_errormessage();
		}
		
		return $numrows;
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
       ports.status,
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
       AND watch_list.user_id = ' . pg_escape_string($UserID) . '
       AND watch_list.in_service
  GROUP BY wle_element_id) AS TEMP
       ON TEMP.wle_element_id = ports.element_id';
		}

		$this->_sql .= "
WHERE ports.element_id  = element.id
  AND ports.category_id = categories.id 
  AND ports.status      = '" . pg_escape_string($this->getStatus()) . "'";

		if ($Condition) {
			$this->_sql .= '
   AND ' . $Condition;
			$this->_condition = $Condition;
		}

		$this->_sql .= "\n order by " . $this->getSort();
	}

	function getLastModified() {
		$sql = "
SELECT gmt_format(max(commit_log.date_added)) as last_modified
  FROM element, commit_log, ports_vulnerable PV right outer join ports on PV.port_id = ports.id
 WHERE ports.element_id     = element.id
   AND ports.last_commit_id = commit_log.id
   AND ports.element_id     = element.id
   AND element.status       = '" . pg_escape_string($this->getStatus()) . "'";

		if ($this->_condition) {
			$sql .= '
   AND ' . $this->_condition;
		}

#		echo '<pre>' . $sql . '</pre>';

		$result = pg_exec($this->_db, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			#
			# here we are doing a max. Even if we have nothing in the result set,
			# we will still get a null value.
			#
			if ($numrows == 1) {
				$myrow = pg_fetch_array ($result);
				$last_modified = $myrow['last_modified'];
			}

			if ($numrows != 1 || $last_modified == '') {
				$sql = 'select gmt_format(LatestCommitDatePorts()) as last_modified';
				$result = pg_exec($this->_db, $sql);
				if (!$result) {
					# if the above failed, give them the current date time
					$sql = 'select GMT_Format(CURRENT_TIMESTAMP) as last_modified';
					$result = pg_exec($this->_db, $sql);
					if (!$result) {
						die('could not get last_modified value: ' . __FILE__);
					}
				}

				$myrow = pg_fetch_array ($result);
				$last_modified = $myrow['last_modified'];
			}
			
		}

#echo 'Last Modified is ' . $last_modified . '<br>';
		return $last_modified;
	}

	function executeQuery() {
		$numrows = -1;
		if ($this->getDebug()) {
			$HTML .= '<pre>' . $this->getSQL() . '</pre>';
		}

		$this->_result = pg_exec($this->_db, $this->getSQL());
		if (!$this->_result) {
			echo pg_errormessage();
		} else {
			$numrows = pg_numrows($this->_result);
#			echo "There are $numrows to fetch<BR>\n";
		}
		
		return $numrows;
	}

	function getPorts($NumPorts) {
		$HTML = '';

		require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/list-of-ports.php');

		$HTML .= freshports_ListOfPorts($this->_result, $this->_db, 'Y', $this->getShowCategoryHeaders(), $this->User, $NumPorts);

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

	function setPageSize($PageSize) {
		$this->_pageSize = $PageSize;
	}

	function setPageNumber($PageNumber) {
		$this->_pageNumber = $PageNumber;
	}

	function pageLinks() {
		$HTML = '';
		$links = $this->_pager->GetLinks();

		$HTML .= $links['all'];

		return $HTML;
	}

	function toHTML() {
		$this->addBodyContent('<TR><TD>' . $this->getDescription() . '</TD></TR>');

		// make sure the value for $sort is valid

		$SortStatement = "<TR><TD>\nThis page is " . $this->getSortedbyHTML() . "</TD></TR>\n";

		$this->addBodyContent($SortStatement); 

		$NumPorts = $this->getRowCount();
		$numrows  = $this->ExecuteQuery();


		$PageLinks = $this->pageLinks();
		if ($PageLinks != '') {
			$this->AddBodyContent('<tr><td align="center">');
			$this->AddBodyContent($PageLinks);
			$this->AddBodyContent('</td></tr>');
		}

		$this->addBodyContent($this->getPorts($NumPorts));

		$this->addBodyContent($SortStatement); 

		if ($PageLinks != '') {
			$this->AddBodyContent('<tr><td align="center">');
			$this->AddBodyContent($PageLinks);
			$this->AddBodyContent('</td></tr>');
		}
		
		return parent::toHTML();
	}
}
