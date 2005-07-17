<?php
	#
	# $Id: ports.php,v 1.1.2.54 2005-07-17 14:19:36 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
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
	var $epoch;
	var $maintainer;
	var $homepage;
	var $master_sites;
	var $extract_suffix;
	var $package_exists;
	var $depends_build;
	var $depends_run;
	var $depends_lib;
	var $last_commit_id;
	var $found_in_index;
	var $forbidden;
	var $broken;
	var $deprecated;
	var $ignore;
	var $date_added;
	var $categories;
	var $master_port;
	var $latest_link;
	var $no_latest_link;
	var $no_package;
	var $package_name;
	var $restricted;
	var $no_cdrom;
	var $expiration_date;

	// derived or from other tables
	var $category;
	var $port;
	var $needs_refresh;
	var $status;
	var $updated;		// timestamp of last update

	var $onwatchlist;	// count of how many watch lists is this port on for this user. 
						// not actually fetched directly by this class.
						// normally used only if you've specified it in your own SQL.

	var $vulnerable_current;
	var $vulnerable_past;

	// not always present/set
	var $update_description;

	// so far used by ports-deleted.php and include/list-of-ports.php
	var $message_id;
	var $encoding_losses;

	// taken from commit_log based upon ports.last_commit_id
	var $last_modified;

	// for any vulnerabilities
	var $VuXML_List;

	// needed for fetch by category
	var $LocalResult;

	var $committer; 

	function Port($dbh) {
		$this->dbh = $dbh;
		unset($this->VuXML_List);
	}

	function _PopulateValues($myrow) {
		$this->id                 = $myrow["id"];
		$this->element_id         = $myrow["element_id"];
		$this->category_id        = $myrow["category_id"];
		$this->short_description  = $myrow["short_description"];
		$this->long_description   = $myrow["long_description"];
		$this->version            = $myrow["version"];
		$this->revision           = $myrow["revision"];
		$this->epoch              = $myrow["epoch"];
		$this->maintainer         = $myrow["maintainer"];
		$this->homepage           = $myrow["homepage"];
		$this->master_sites       = $myrow["master_sites"];
		$this->extract_suffix     = $myrow["extract_suffix"];
		$this->package_exists     = $myrow["package_exists"];
		$this->depends_build      = $myrow["depends_build"];
		$this->depends_run        = $myrow["depends_run"];
		$this->depends_lib        = $myrow["depends_lib"];
		$this->last_commit_id     = $myrow["last_commit_id"];
		$this->found_in_index     = $myrow["found_in_index"];
		$this->forbidden          = $myrow["forbidden"];
		$this->broken             = $myrow["broken"];
		$this->deprecated         = $myrow["deprecated"];
		$this->ignore             = $myrow["ignore"];
		$this->date_added         = $myrow["date_added"];
		$this->categories         = $myrow["categories"];
		$this->master_port        = $myrow["master_port"];
		$this->latest_link        = $myrow["latest_link"];
		$this->no_latest_link     = $myrow["no_latest_link"];
		$this->no_package         = $myrow["no_package"];
		$this->package_name       = $myrow["package_name"];
		$this->restricted         = $myrow["restricted"];
		$this->no_cdrom           = $myrow["no_cdrom"];
		$this->expiration_date    = $myrow["expiration_date"];

		$this->port               = $myrow["port"];
		$this->category           = $myrow["category"];
		$this->needs_refresh      = $myrow["needs_refresh"];
		$this->status             = $myrow["status"];
		$this->updated            = $myrow["updated"];

		$this->onwatchlist        = $myrow["onwatchlist"];
		$this->last_modified      = $myrow["last_modified"];

		$this->update_description = $myrow["update_description"];
		$this->message_id         = $myrow["message_id"];
		$this->encoding_losses    = $myrow["encoding_losses"];
		$this->committer          = $myrow["committer"];

		$this->vulnerable_current = $myrow["vulnerable_current"];
		$this->vulnerable_past    = $myrow["vulnerable_past"];

		// We might be looking at category lang.  japanese/gawk is listed in both japanese and lang.
		// So when looking at lang, we don't want to say, Also listed in lang...  
		//
		$this->category_looking_at= $myrow["category_looking_at"];
	}

	function FetchByElementID($element_id, $UserID = 0) {

		$Debug = 0;

		# fetch a single port based on element_id.
		# e.g. net/samba
		#
		# It will not bring back any commit information.

		$this->element_id = $element_id;

		$sql = "
select ports.id,
       ports.element_id,
       ports.category_id       as category_id, 
       ports.short_description as short_description, 
       ports.long_description, 
       ports.version           as version,
       ports.revision          as revision,
       ports.portepoch         as epoch,
       ports.maintainer, 
       ports.homepage, 
       ports.master_sites, 
       ports.extract_suffix, 
       ports.package_exists, 
       ports.depends_build, 
       ports.depends_run, 
       ports.depends_lib, 
       ports.last_commit_id, 
       ports.found_in_index, 
       ports.forbidden, 
       ports.broken, 
       ports.deprecated, 
       ports.ignore, 
       ports.master_port,
       ports.latest_link,
       ports.no_latest_link,
       ports.no_package,
       ports.package_name,
       ports.restricted,
       ports.no_cdrom,
       ports.expiration_date,

       to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added, 
       ports.categories as categories,
	    element.name     as port, 
	    categories.name  as category,
	    element.status,
       ports_vulnerable.current as vulnerable_current,
       ports_vulnerable.past    as vulnerable_past,
       GMT_Format(commit_log.date_added) as last_modified ";

		if ($UserID) {
			$sql .= ",
	        TEMP.onwatchlist";
		}

		$sql .= "
       from categories, element, ports_vulnerable right outer join ports 
                       on (ports_vulnerable.port_id = ports.id)
               left outer join commit_log on ports.last_commit_id = commit_log.id ";

		if ($UserID) {
			$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = $UserID
           AND watch_list.in_service
	  GROUP BY element_id) AS TEMP
	       ON TEMP.wle_element_id = ports.element_id";
		}
	

		$sql .= " WHERE element.id        = $this->element_id 
			        and ports.category_id = categories.id 
			        and ports.element_id  = element.id ";


		if ($Debug) {
			echo "<pre>$sql</pre>";
		}

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result);
				$this->_PopulateValues($myrow);
			} else {
				echo "Ports::FetchByPartialName I'm concerned I got $numrows from that.<BR>$sql<BR>";
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}
	}

	function FetchByID($id, $UserID = 0) {
		# fetch a single port based on id
		# used by missing-port.php

		$Debug = 0;

		$sql = "select ports.id, 
		               ports.element_id, 
		               ports.category_id       as category_id,
		               ports.short_description as short_description, 
		               ports.long_description, 
		               ports.version           as version,
		               ports.revision          as revision, 
		               ports.portepoch         as epoch, 
		               ports.maintainer,
		               ports.homepage, 
		               ports.master_sites, 
		               ports.extract_suffix, 
		               ports.package_exists,
		               ports.depends_build, 
		               ports.depends_run, 
		               ports.depends_lib, 
		               ports.last_commit_id, 
		               ports.found_in_index,
		               ports.forbidden, 
		               ports.broken, 
		               ports.deprecated, 
		               ports.ignore, 
		               to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added,
		               ports.master_port,
		               ports.latest_link,
		               ports.no_latest_link,
		               ports.no_package,
		               ports.package_name,
	                   ports.restricted,
	                   ports.no_cdrom,
	                   ports.expiration_date,
		               ports.categories as categories,
			           element.name     as port, 
			           categories.name  as category,
			           element.status,
                       ports_vulnerable.current as vulnerable_current,
                       ports_vulnerable.past    as vulnerable_past,
                       GMT_Format(commit_log.date_added) as last_modified ";

		if ($UserID) {
			$sql .= ', 
CASE WHEN TEMP.onwatchlist IS NULL
THEN 0 ELSE 1
END as onwatchlist';
		}


		$sql .= " from categories, element, ports_vulnerable right outer join ports
                       on (ports_vulnerable.port_id = ports.id)
               left outer join commit_log on ports.last_commit_id = commit_log.id ";

		#
		# if the watch list id is provided (i.e. they are logged in and have a watch list id...)
		#
		if ($UserID) {
			$sql .="
LEFT OUTER JOIN (
SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $UserID
       AND watch_list.in_service
  GROUP BY element_id
) AS TEMP
ON TEMP.wle_element_id = ports.element_id";

		}

		$sql .= "\nWHERE ports.id        = $id 
		          and ports.category_id = categories.id 
		          and ports.element_id  = element.id ";

		if ($Debug) {
			echo "<pre>$sql</pre>";
			exit;
		}

      $result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result);
				$this->_PopulateValues($myrow);

				#
				# I had considered including an OUTER JOIN in the above SQL
				# but didn't.  I figured the above was
				if (IsSet($WatchListID)) {
					$this->onwatchlist = IsOnWatchList($WatchListID);
				}

			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}
	}

	function FetchByCategoryInitialise($CategoryName, $UserID = 0, $PageSize = 0, $PageNo = 0) {
		# fetch all ports based on category
		# e.g. id for net
		
		$Debug = 0;

		$sql = "";
		if ($UserID) {
			$sql .= "SELECT PE.*,

CASE WHEN watchlistcount IS NULL
THEN 0 ELSE 1
END as onwatchlist

FROM
 (";
     	}

		$sql .= "
SELECT P.*, element.name    as port,
        element.status  as status
   FROM element JOIN
 (SELECT ports.id,
        ports.element_id        as element_id,
        ports.category_id       as category_id,
        ports.short_description as short_description,
        ports.long_description,
        ports.version           as version,
        ports.revision          as revision,
        ports.portepoch         as epoch,
        ports.maintainer,
        ports.homepage,
        ports.master_sites,
        ports.extract_suffix,
        ports.package_exists,
        ports.depends_build,
        ports.depends_run,
        ports.depends_lib,
        ports.last_commit_id,
        ports.found_in_index,
        ports.forbidden,
        ports.broken,
        ports.deprecated,
        ports.ignore,
        to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added,
        ports.master_port,
        ports.latest_link,
        ports.no_latest_link,
        ports.no_package,
        ports.package_name,
        ports.restricted,
        ports.no_cdrom,
        ports.expiration_date,
        ports.categories      as categories,
        categories.name       as category_looking_at,
        PRIMARY_CATEGORY.name as category,
        ports_vulnerable.current as vulnerable_current,
        ports_vulnerable.past    as vulnerable_past 

   FROM ports_vulnerable right outer join ports on (ports_vulnerable.port_id = ports.id),
        categories, ports_categories, categories PRIMARY_CATEGORY
  WHERE ports_categories.port_id     = ports.id
    AND ports_categories.category_id = categories.id
    AND categories.name              = '$CategoryName'
    AND PRIMARY_CATEGORY.id          = ports.category_id ) AS P
   ON (P.element_id     = element.id
   AND element.status   = 'A')";

		if ($UserID) {
			$sql .= ") AS PE
LEFT OUTER JOIN
 (SELECT element_id           as wle_element_id,
         COUNT(watch_list_id) as watchlistcount
    FROM watch_list JOIN watch_list_element
      ON watch_list.id      = watch_list_element.watch_list_id
     AND watch_list.user_id = $UserID
     AND watch_list.in_service
 GROUP BY wle_element_id) AS TEMP
  ON TEMP.wle_element_id = PE.element_id";
    	}

		$sql .= " ORDER by port ";
		
#echo "\$PageSize='$PageSize'\n";
		if ($PageSize) {
			$sql .= " LIMIT $PageSize";
			if ($PageNo) {
				$sql .= ' OFFSET ' . ($PageNo - 1 ) * $PageSize;
			}
		}

		if ($Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
#				echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($this->LocalResult);
				$this->_PopulateValues($myrow);

			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
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

	function IsOnWatchList($WatchListID) {
		#
		# return non-zero if this port is on the supplied watch list ID.
		# zero otherwise.
		#

		$result = 0;

		$sql = "	select element_id
					  from watch_list_element
					 where watch_list_id = $WatchListID
					   and element_id    = $this->element_id";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				if ($Debug) echo "IsOnWatchList succeeded<BR>";
				$result = 1;
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

		return $result;
	}

	function Fetch($Category, $Port, $UserID = 0) {
		#
		# introduced for virtual categories.
		# given a category port combination, let's get the port id
		# and then fetch
		#

		$sql = "select GetPortID('" . AddSlashes($Category) . "', '"  . AddSlashes($Port) . "') as port_id";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
				$myrow = pg_fetch_row($result);
				$PortID = $myrow[0];
				if (IsSet($PortID)) {
					$result = $this->FetchByID($PortID, $UserID);
				} else {
					return $PortID;
				}
			} else {
				echo 'that port was not found:' . $Category . '/' . $Port;
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

		return $result;
	}

	function WatchListCount() {
		#
		# return the number of watch lists upon which this port appears
		#

		$result = 0;

		$sql = 'select watch_list_count(' . $this->element_id . ')';

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows >= 1) {
				$myrow = pg_fetch_row($result);
				$result = $myrow[0];
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

		return $result;
	}

	function PackageExists() {
		return $this->package_exists == 't';
	}

	function EncodingLosses() {
		return $this->encoding_losses == 't';
	}

	function IsSlavePort() {
		return $this->master_port != '';
	}

	function LoadVulnerabilities() {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_log_ports_vuxml.php');

		$this->VuXML_List = array();

		$VuXML = new Commit_Log_Ports_VuXML($this->dbh);
		$this->VuXML_List = $VuXML->VuXML_List_Get($this->id);

		return count($this->VuXML_List);
	}

	function IsCurrentPortVulnerable() {
		return 1;
	}

	function IsVulnerable() {
		return $this->vulnerable_current > 0;
	}

	function WasVulnerable() {
		return $this->vulnerable_past > 0;
	}

}

?>