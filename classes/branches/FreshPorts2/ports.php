<?
	# $Id: ports.php,v 1.1.2.7 2002-01-05 20:14:08 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#


// base class for Port
class Port {
	// set on new
	var $dbh;

	// from the ports table
	var $id;
	var $element_id;
	var $category_id;
	var $short_description;
	var $long_description;
	var $version;
	var $revision;
	var $maintainer;
	var $homepage;
	var $master_sites;
	var $extract_suffix;
	var $package_exists;
	var $depends_build;
	var $depends_run;
	var $last_commit_id;
	var $found_in_index;
	var $forbidden;
	var $broken;
	var $date_created;
	var $categories;

	// derived or from other tables
	var $category;
    var $port;
	var $needs_refresh;
	var $status;
	var $updated;	// timestamp of last update

	// not always present/set
	var $update_description;


	// needed for fetch by category
	var $LocalResult;

	function Port($dbh) {
		$this->dbh	= $dbh;
	}

	function _PopulateValues($myrow) {
		$this->id                 = $myrow["id"];
		$this->element_id         = $myrow["element_id"];
		$this->category_id        = $myrow["category_id"];
		$this->short_description  = $myrow["short_description"];
		$this->long_description   = $myrow["long_description"];
		$this->version            = $myrow["version"];
		$this->revision           = $myrow["revision"];
		$this->maintainer         = $myrow["maintainer"];
		$this->homepage           = $myrow["homepage"];
		$this->master_sites       = $myrow["master_sites"];
		$this->extract_suffix     = $myrow["extract_suffix"];
		$this->package_exists     = $myrow["package_exists"];
		$this->depends_build      = $myrow["depends_build"];
		$this->depends_run        = $myrow["depends_run"];
		$this->last_commit_id     = $myrow["last_commit_id"];
		$this->found_in_index     = $myrow["found_in_index"];
		$this->forbidden          = $myrow["forbidden"];
		$this->broken             = $myrow["broken"];
		$this->date_created       = $myrow["date_created"];
		$this->categories         = $myrow["categories"];

		$this->port               = $myrow["port"];
		$this->category           = $myrow["category"];
		$this->needs_refresh      = $myrow["needs_refresh"];
		$this->status             = $myrow["status"];
		$this->updated            = $myrow["updated"];

		$this->update_description = $myrow["update_description"];
	}

	function FetchByPartialName($pathname) {
		# fetch a single port based on pathname.
		# e.g. net/samba
		#
		# It will not bring back any commit information.

		#
		# first, we get the element relating to this port
		#
		$element = new Element($this->dbh);
        $element->FetchByName($pathname);

		if ($Debug) echo "into FetchByPartialName with $pathname<BR>";

		if (IsSet($element->id)) {
			$this->element_id = $element->id;

			$sql = "select ports.id, ports.element_id, ports.id as id, ports.category_id as category_id, " .
			       "ports.short_description as short_description, ports.long_description, ports.version as version, ".
			       "ports.revision as revision, ports.maintainer, ".
			       "ports.homepage, ports.master_sites, ports.extract_suffix, ports.package_exists, " .
			       "ports.depends_build, ports.depends_run, ports.last_commit_id, ports.found_in_index, " .
			       "ports.forbidden, ports.broken, ports.date_created, " .
			       "ports.categories as categories, ".
				   "element.name as port, categories.name as category, " .
				   "element.status " .
			       "from ports, categories, element ".
			       "WHERE ports.element_id     = $this->element_id ".
			       "  and ports.category_id    = categories.id " .
			       "  and ports.element_id     = element.id ";

	        $result = pg_exec($this->dbh, $sql);
			if ($result) {
				$numrows = pg_numrows($result);
				if ($numrows == 1) {
					if ($Debug) echo "fetched by ID succeeded<BR>";
					$myrow = pg_fetch_array ($result, 0);
					$this->_PopulateValues($myrow);
				} else {
					echo "Ports::FetchByPartialName I'm concerned I got $numrows from that.<BR>$sql<BR>";
				}
			} else {
				echo 'pg_exec failed: ' . $sql;
			}
		} else {
			echo 'ports FetchByPartialName for $path failed';
		}
	}

	function FetchByID($id) {
		# fetch a single port based on id

		$sql = "select ports.id, ports.element_id, ports.id as id, ports.category_id as category_id, " .
		       "ports.short_description as short_description, ports.long_description, ports.version as version, ".
		       "ports.revision as revision, ports.maintainer, ".
		       "ports.homepage, ports.master_sites, ports.extract_suffix, ports.package_exists, " .
		       "ports.depends_build, ports.depends_run, ports.last_commit_id, ports.found_in_index, " .
		       "ports.forbidden, ports.broken, ports.date_created, " .
		       "ports.categories as categories, ".
			   "element.name as port, categories.name as category, commit_log_ports.needs_refresh, " .
			   "element.status, commit_log.commit_date as updated " .
		       "from ports, categories, element, commit_log_ports, commit_log ".
		       "WHERE ports.id             = $id ".
		       "  and ports.category_id    = categories.id " .
		       "  and ports.element_id     = element.id " .
			   "  and ports.last_commit_id = commit_log_ports.commit_log_id " .
			   "  and ports.id             = commit_log_ports.port_id " .
			   "  and commit_log.id        = commit_log_ports.commit_log_id ";

        $result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result, 0);
				$this->_PopulateValues($myrow);

			}
		} else {
			echo 'pg_exec failed: ' . $sql;
		}
	}

	function FetchByCategoryInitialise($CategoryID) {
		# fetch all ports based on category
		# e.g. id for net

		$sql = "select ports.id, ports.element_id, ports.id as id, ports.category_id as category_id, " .
		       "ports.short_description as short_description, ports.long_description, ports.version as version, ".
		       "ports.revision as revision, ports.maintainer, ".
		       "ports.homepage, ports.master_sites, ports.extract_suffix, ports.package_exists, " .
		       "ports.depends_build, ports.depends_run, ports.last_commit_id, ports.found_in_index, " .
		       "ports.forbidden, ports.broken, ports.date_created, " .
		       "ports.categories as categories, ".
			   "element.name as port, categories.name as category, commit_log_ports.needs_refresh, " .
			   "element.status, commit_log.commit_date as updated " .
		       "from ports, categories, element, commit_log_ports, commit_log ".
		       "WHERE ports.category_id    = categories.id " .
		       "  and ports.element_id     = element.id " .
			   "  and ports.last_commit_id = commit_log_ports.commit_log_id " .
			   "  and ports.id             = commit_log_ports.port_id " .
			   "  and commit_log.id        = commit_log_ports.commit_log_id " .
			   "  and categories.id        = $CategoryID";

        $this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
#				echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($this->LocalResult, 0);
				$this->_PopulateValues($myrow);

			}
		} else {
			echo 'pg_exec failed: ' . $sql . ' : ' . pg_errormessage();
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
}
