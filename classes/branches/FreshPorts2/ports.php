<?
	# $Id: ports.php,v 1.1.2.2 2001-12-29 04:09:02 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#


// base class for Port
class Port {

	var $dbh;

	var $id;
	var $element_id;
    var $port;
	var $category;
	var $category_id;
	var $version;
	var $maintainer;
	var $short_description;
	var $long_description;
	var $package_exists;
	var $extract_suffix;
	var $needs_refresh;
	var $homepage;
	var $depends_run;
	var $depends_build;
	var $status;
	var $broken;
	var $forbidden;
	var $categories;

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

			$sql = "select ports.id, element.name as port, ports.id as id, " .
			       "categories.name as category, categories.id as category_id, ports.version as version, ".
			       "ports.maintainer, ports.short_description, ports.long_description, ".
			       "ports.package_exists, ports.extract_suffix, commit_log_ports.needs_refresh, ports.homepage, " .
			       "ports.depends_run, ports.depends_build, element.status, " .
			       "ports.broken, ports.forbidden, " .
			       "ports.categories as categories ".
			       "from ports, categories, element, commit_log_ports  ".
			       "WHERE ports.element_id  = $this->element_id ".
			       "  and ports.category_id = categories.id " .
			       "  and ports.element_id  = element.id " .
				   "  and ports.last_commit_id = commit_log_ports.id " .
				   "  and ports.id             = commit_log_ports.port_id";

	        $result = pg_exec($this->dbh, $sql);
			if ($result) {
				$numrows = pg_numrows($result);
				if ($numrows == 1) {
#					echo "fetched by ID succeeded<BR>";
					$myrow = pg_fetch_array ($result, 0);
					$this->id = $myrow["id"];

					$this->port              = $myrow["port"];
					$this->category          = $myrow["category"];
					$this->category_id       = $myrow["category_id"];
					$this->version           = $myrow["version"];
					$this->maintainer        = $myrow["maintainer"];
					$this->short_description = $myrow["short_description"];
					$this->long_description  = $myrow["long_description"];
					$this->package_exists    = $myrow["package_exists"];
					$this->extract_suffix    = $myrow["extract_suffix"];
					$this->needs_refresh     = $myrow["needs_refresh"];
					$this->homepage          = $myrow["homepage"];
					$this->depends_run       = $myrow["depends_run"];
					$this->depends_build     = $myrow["depends_build"];
					$this->status            = $myrow["status"];
					$this->broken            = $myrow["broken"];
					$this->forbidden         = $myrow["forbidden"];
					$this->categories        = $myrow["categories"];
				}
			} else {
#				echo 'pg_exec failed: ' . $sql;
			}
		}
	}
}
