<?
	# $Id: ports.php,v 1.1.2.1 2001-12-23 02:53:29 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#


// base class for Port
class Port {

	var $dbh;

	var $id;
	var $element_id;

	function Port($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByPartialName($pathname) {
		# obtain the port based on the partial pathname supplied
		# e.g. net/samba

		#
		# first, we get the element relating to this port
		#
		$element = new Element($this->dbh);
        $element->FetchByName($pathname);

		if (IsSet($element->id)) {
			$this->element_id = $element->id;

			$sql = "select ports.*, categories.name as category, element.name as name 
					  from ports, categories, element 
					 where ports.element_id  = $this->element_id
					   and ports.category_id = categories.id 
					   and ports.element_id  = element.id";

	        $result = pg_exec($this->dbh, $sql);
			if ($result) {
				$numrows = pg_numrows($result);
				if ($numrows == 1) {
#					echo "fetched by ID succeeded<BR>";
					$myrow = pg_fetch_array ($result, 0);
					$this->id = $myrow["id"];
				}
			} else {
#				echo 'pg_exec failed: ' . $sql;
			}
		}
	}
}
