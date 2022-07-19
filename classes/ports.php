<?php
	#
	# $Id: ports.php,v 1.5 2012-12-21 18:20:53 dan Exp $
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
	var $is_interactive;
	var $only_for_archs;
	var $not_for_archs;
	var $status;
	var $showconfig;
	var $options_name;
	var $license;
	var $fetch_depends;
	var $extract_depends;
	var $patch_depends;
	var $uses;
	var $pkgmessage;
	var $distinfo;
	var $license_restricted;
	var $manual_package_build;
	var $license_perms;
	var $conflicts;
	var $conflicts_build;
	var $conflicts_install;
	var $conflicts_matches;

	// derived or from other tables
	var $category;
	var $port;
	var $needs_refresh;
	var $updated;		// timestamp of last update

	var $onwatchlist;	// count of how many watch lists is this port on for this user. 
				// not actually fetched directly by this class.
				// normally used only if you've specified it in your own SQL.

	var $vulnerable_current;
	var $vulnerable_past;

	var $pkg_plist_library_matches;	// items from generate_plist.installed_file which match: 'lib/[^/]*?\.so''
	                                // used for listing LIB_DEPENDS. This information was previously in ports.pkg_plist

	// not always present/set
	var $update_description;

	// so far used by ports-deleted.php and include/list-of-ports.php
	var $message_id;
	var $commit_hash_short;
	var $encoding_losses;

	// taken from commit_log based upon ports.last_commit_id
	var $last_commit_date;
	var $svn_revision;

	// for any vulnerabilities
	var $VuXML_List;

	// needed for fetch by category
	var $LocalResult;

	var $committer; 

	// needed for watch.php
	var $committer_name;
	var $committer_email;
	var $author_name;
	var $author_email;

	var $repository;
	var $repo_hostname;
	var $git_hostname; # not yet populated
	var $path_to_repo;
	var $element_pathname;

	// version on current quarterly branch. see https://github.com/FreshPorts/freshports/issues/115
	var $quarterly_revision;

	private $Debug = 0;
	
	function __construct($dbh) {
		$this->dbh = $dbh;
		unset($this->VuXML_List);
	}

	function _PopulateValues($myrow) {
		$this->id                   = $myrow["id"];
		$this->element_id           = $myrow["element_id"];
		$this->category_id          = $myrow["category_id"];
		$this->short_description    = $myrow["short_description"];
		$this->long_description     = isset($myrow["long_description"]) ? $myrow["long_description"] : null;
		$this->version              = $myrow["version"];
		$this->revision             = $myrow["revision"];
		$this->epoch                = $myrow["epoch"] ?? null;
		$this->maintainer           = $myrow["maintainer"];
		$this->homepage             = $myrow["homepage"];
		$this->master_sites         = isset($myrow["master_sites"]) ? $myrow["master_sites"] : null;
		$this->extract_suffix       = $myrow["extract_suffix"];
		$this->package_exists       = $myrow["package_exists"];
		
		$this->depends_build        = $myrow["depends_build"]        ?? null;
		$this->depends_run          = $myrow["depends_run"]          ?? null;
		$this->depends_lib          = $myrow["depends_lib"]          ?? null;
		$this->last_commit_id       = $myrow["last_commit_id"]       ?? null;
		$this->found_in_index       = $myrow["found_in_index"]       ?? null;
		$this->forbidden            = $myrow["forbidden"];
		$this->broken               = $myrow["broken"]               ?? null;
		$this->deprecated           = $myrow["deprecated"]           ?? null;
		$this->ignore               = $myrow["ignore"]               ?? null;
		$this->date_added           = $myrow["date_added"]           ?? null;
		$this->categories           = $myrow["categories"]           ?? null;
		$this->master_port          = $myrow["master_port"]          ?? null;
		$this->latest_link          = $myrow["latest_link"]          ?? null;
		$this->no_latest_link       = $myrow["no_latest_link"]       ?? null;
		$this->no_package           = $myrow["no_package"]           ?? null;
		$this->package_name         = $myrow["package_name"];
		$this->restricted           = $myrow["restricted"]           ?? null;
		$this->no_cdrom             = $myrow["no_cdrom"]             ?? null;
		$this->expiration_date      = $myrow["expiration_date"]      ?? null;
		$this->is_interactive       = $myrow["is_interactive"]       ?? null;
		$this->only_for_archs       = $myrow["only_for_archs"]       ?? null;
		$this->not_for_archs        = $myrow["not_for_archs"]        ?? null;
		$this->status               = $myrow["status"];
		$this->showconfig           = $myrow["showconfig"]           ?? null;
		$this->options_name         = $myrow["options_name"]         ?? null;
		$this->license              = $myrow["license"]              ?? null;
		$this->fetch_depends        = $myrow["fetch_depends"]        ?? null;
		$this->extract_depends      = $myrow["extract_depends"]      ?? null;
		$this->patch_depends        = $myrow["patch_depends"]        ?? null;
		$this->uses                 = $myrow["uses"]                 ?? null;
		$this->pkgmessage           = $myrow["pkgmessage"]           ?? null;
		$this->distinfo             = $myrow["distinfo"]             ?? null;
		$this->license_restricted   = $myrow["license_restricted"]   ?? null;
		$this->manual_package_build = $myrow["manual_package_build"] ?? null;
		$this->license_perms        = $myrow["license_perms"]        ?? null;
		$this->conflicts            = $myrow["conflicts"]            ?? null;
		$this->conflicts_build      = $myrow["conflicts_build"]      ?? null;
		$this->conflicts_install    = $myrow["conflicts_install"]    ?? null;
		$this->generate_plist       = $myrow["generate_plist"]       ?? null;

		$this->port                 = $myrow["port"];
		$this->category             = $myrow["category"];
		$this->needs_refresh        = $myrow["needs_refresh"]        ?? null;
		$this->updated              = $myrow["updated"]              ?? null;

		$this->onwatchlist          = $myrow["onwatchlist"]          ?? null;
		$this->svn_revision         = $myrow["svn_revision"]         ?? null;

		$this->update_description   = $myrow["update_description"]   ?? null;
		$this->message_id           = $myrow["message_id"]           ?? null;
		$this->commit_hash_short    = $myrow["commit_hash_short"]    ?? null;
		$this->encoding_losses      = $myrow["encoding_losses"]      ?? null;
		$this->committer            = $myrow["committer"]            ?? null;
		$this->committer_name       = $myrow["committer_name"]       ?? null;
		$this->committer_email      = $myrow["committer_email"]      ?? null;
		$this->author_name          = $myrow["author_name"]          ?? null;
		$this->author_email         = $myrow["author_email"]         ?? null;

		$this->vulnerable_current   = $myrow["vulnerable_current"]   ?? null;
		$this->vulnerable_past      = $myrow["vulnerable_past"]      ?? null;

		if (IsSet($this->pkg_plist_library_matches)) {
			$this->pkg_plist_library_matches = $myrow["pkg_plist_library_matches"];
		}

		// We might be looking at category lang.  japanese/gawk is listed in both japanese and lang.
		// So when looking at lang, we don't want to say, Also listed in lang...  
		//
		$this->category_looking_at  = isset($myrow["category_looking_at"]) ? $myrow["category_looking_at"] : null;

		$this->repository           = $myrow['repository']       ?? null;
		$this->repo_hostname        = $myrow['repo_hostname']    ?? null;
#		$this->git_hostname         = '';
		$this->path_to_repo         = $myrow['path_to_repo']     ?? null;
		$this->element_pathname     = $myrow['element_pathname'] ?? null;
		if (isset($this->quarterly_revision)) {
			$this->quarterly_revision = $myrow['quarterly_revision'];
		}

		$this->last_commit_date     = isset($myrow['last_commit_date']) ? $myrow['last_commit_date'] : null;

		$this->ConflictMatches();
	}

	function FetchByElementID($element_id, $UserID = 0) {

		# fetch a single port based on element_id.
		# e.g. net/samba
		#
		# It will not bring back any commit information.

		$this->element_id = $element_id;

		$sql = "set client_encoding = 'ISO-8859-15';
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
       ports.is_interactive,
       ports.only_for_archs,
       ports.not_for_archs,
       ports.status,
       ports.showconfig,
       ports.options_name,
       ports.license,
       ports.fetch_depends,
       ports.extract_depends,
       ports.patch_depends,
       ports.uses,
       ports.pkgmessage,
       ports.distinfo,
       ports.license_restricted,
       ports.manual_package_build,
       ports.license_perms,
       ports.conflicts,
       ports.conflicts_build,
       ports.conflicts_install,
       to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added, 
       ports.categories as categories,
       element.name     as port, 
       categories.name  as category,
       ports_vulnerable.current as vulnerable_current,
       ports_vulnerable.past    as vulnerable_past,
       pkg_plist(ports.id) AS pkg_plist_library_matches,
       commit_log.commit_date - SystemTimeAdjust() AS last_commit_date,
       commit_log.svn_revision,
       commit_log.commit_hash_short,
       commit_log.message_id,
       R.repository,
       R.repo_hostname,
       R.path_to_repo,
       element_pathname(ports.element_id) as element_pathname,
       PortVersionOnQuarterlyBranch(ports.id, categories.name || '/' || element.name) AS quarterly_revision  ";

		if ($UserID) {
			$sql .= ",
	        TEMP.onwatchlist";
		}
		else
		{
			$sql .= ",
			0 as onwatchlist";
		}

		$sql .= "
       from categories, element, ports_vulnerable right outer join ports 
                       on (ports_vulnerable.port_id = ports.id)
               left outer join commit_log on ports.last_commit_id = commit_log.id 
               LEFT OUTER JOIN repo R ON commit_log.repo_id = R.id ";

		if ($UserID) {
			$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = " . pg_escape_string($this->dbh, $UserID) . "
           AND watch_list.in_service
	  GROUP BY element_id) AS TEMP
	       ON TEMP.wle_element_id = ports.element_id";
		}
	

		$sql .= " WHERE element.id        = " . pg_escape_string($this->dbh, $this->element_id) . " 
			        and ports.category_id = categories.id 
			        and ports.element_id  = element.id ";


		if ($this->Debug) {
			echo "<pre>$sql</pre>";
		}

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				if ($this->Debug) echo "FetchByElementID succeeded<BR>";
				$myrow = pg_fetch_array ($result);
				$this->_PopulateValues($myrow);
			} else {
				die(__CLASS__ . ':' . __FUNCTION__ . " got $numrows rows at line " . __LINE__);
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}
	}

	function FetchByID($id, $UserID = 0) {
		# fetch a single port based on id
		# used by missing-port.php

		$sql = "set client_encoding = 'ISO-8859-15'; select ports.id, 
		               ports.element_id,
		               element_pathname(ports.element_id)        as element_pathname,
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
		               ports.is_interactive,
		               ports.only_for_archs,
		               ports.not_for_archs,
		               ports.status,
		               ports.showconfig,
		               ports.options_name,
		               ports.license,
		               ports.fetch_depends,
		               ports.extract_depends,
		               ports.patch_depends,
		               ports.uses,
		               ports.pkgmessage,
		               ports.distinfo,
		               ports.license_restricted,
		               ports.manual_package_build,
		               ports.license_perms,
		               ports.conflicts,
		               ports.conflicts_build,
		               ports.conflicts_install,
		               ports.categories as categories,
		               element.name     as port, 
		               categories.name  as category,
		               ports_vulnerable.current as vulnerable_current,
		               ports_vulnerable.past    as vulnerable_past,
		               pkg_plist(ports.id) AS pkg_plist_library_matches,
		               commit_log.commit_date - SystemTimeAdjust() AS last_commit_date,
		               commit_log.svn_revision,
		               commit_log.commit_hash_short,
		               commit_log.message_id,
		               R.repository,
		               R.repo_hostname,
		               R.path_to_repo,
		               element_pathname(ports.element_id) as element_pathname,
		               PortVersionOnQuarterlyBranch(ports.id, categories.name || '/' || element.name) AS quarterly_revision ";

		if ($UserID) {
			$sql .= ', 
CASE WHEN TEMP.onwatchlist IS NULL
THEN 0 ELSE 1
END as onwatchlist';
		}
		else
		{
		   $sql .= ',
		   0 as onwatchlist';
		}


		$sql .= " from categories, element, ports_vulnerable right outer join ports
                       on (ports_vulnerable.port_id = ports.id)
               left outer join commit_log on ports.last_commit_id = commit_log.id 
               LEFT OUTER JOIN repo R ON commit_log.repo_id = R.id ";

		#
		# if the watch list id is provided (i.e. they are logged in and have a watch list id...)
		#
		if ($UserID) {
			$sql .="
LEFT OUTER JOIN (
SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = " . pg_escape_string($this->dbh, $UserID) . "
       AND watch_list.in_service
  GROUP BY element_id
) AS TEMP
ON TEMP.wle_element_id = ports.element_id";

		}

		$sql .= "\nWHERE ports.id        = " . pg_escape_string($this->dbh, $id) . " 
		          and ports.category_id = categories.id 
		          and ports.element_id  = element.id ";

		if ($this->Debug) {
			echo "<pre>$sql</pre>";
		}

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				if ($this->Debug) echo "FetchByID succeeded<BR>";
				$myrow = pg_fetch_array ($result);
				$this->_PopulateValues($myrow);
				$result = $this->{'id'};
			}
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

		return $result;
	}

	function FetchByCategoryInitialise($CategoryName, $UserID = 0, $PageSize = 0, $PageNo = 0, $Branch = BRANCH_HEAD) {
		# fetch all ports based on category
		# e.g. id for net
		
		$sql = "set client_encoding = 'ISO-8859-15';";
		if ($UserID) {
			$sql .= "SELECT PE.*,

CASE WHEN watchlistcount IS NULL
THEN 0 ELSE 1
END as onwatchlist

FROM
 (";
     	}

		$sql .= "
SELECT P.*, element.name    as port
   FROM element JOIN
 (SELECT ports.id,
        ports.element_id        as element_id,
        element_pathname(ports.element_id)        as element_pathname,
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
        ports.is_interactive,
        ports.only_for_archs,
        ports.not_for_archs,
        ports.status,
        ports.showconfig,
        ports.options_name,
        ports.license,
        ports.fetch_depends,
        ports.extract_depends,
        ports.patch_depends,
        ports.uses,
        ports.pkgmessage,
        ports.distinfo,
        ports.license_restricted,
        ports.manual_package_build,
        ports.license_perms,
        ports.conflicts,
        ports.conflicts_build,
        ports.conflicts_install,
        ports.categories      as categories,
        categories.name       as category_looking_at,
        PRIMARY_CATEGORY.name as category,
        ports_vulnerable.current as vulnerable_current,
        ports_vulnerable.past    as vulnerable_past,
        pkg_plist(ports.id) AS pkg_plist_library_matches,
        NULL AS needs_refresh,
        NULL AS updated,
        NULL AS last_commit_date,
        NULL AS svn_revision,
        NULL AS update_description,
        NULL AS message_id,
        NULL AS commit_hash_short,
        NULL AS message_id,
        NULL AS encoding_losses,
        NULL AS committer,
        NULL AS committer_name,
        NULL AS committer_email,
        NULL AS author_name,
        NULL AS author_email,
        NULL AS path_to_repo,
        NULL AS repository,
        NULL AS repo_hostname,
        NULL AS onwatchlist,
        PortVersionOnQuarterlyBranch(ports.id, categories.name || '/' || element.name) AS quarterly_revision

   FROM ports_vulnerable right outer join ports on (ports_vulnerable.port_id = ports.id),
        categories, ports_categories, categories PRIMARY_CATEGORY, element
  WHERE ports_categories.port_id     = ports.id
    AND ports_categories.category_id = categories.id
    AND categories.name              = '" . pg_escape_string($this->dbh, $CategoryName) . "'
    AND PRIMARY_CATEGORY.id          = ports.category_id
    AND ports.element_id             = element.id) AS P
   ON (P.element_id     = element.id
   AND element.status   = 'A') JOIN element_pathname EP ON P.element_id = EP.element_id AND EP.pathname like '/ports/";

	if ($Branch != BRANCH_HEAD) {
		$sql .= 'branches/';
	}

	$sql .= pg_escape_string($this->dbh, $Branch) . "/%'";

		if ($UserID) {
			$sql .= ") AS PE
LEFT OUTER JOIN
 (SELECT element_id           as wle_element_id,
         COUNT(watch_list_id) as watchlistcount
    FROM watch_list JOIN watch_list_element
      ON watch_list.id      = watch_list_element.watch_list_id
     AND watch_list.user_id = " . pg_escape_string($this->dbh, $UserID) . "
     AND watch_list.in_service
 GROUP BY wle_element_id) AS TEMP
  ON TEMP.wle_element_id = PE.element_id";
    	}

		$sql .= " ORDER by port ";
		
#echo "\$PageSize='$PageSize'\n";
		if ($PageSize) {
			$sql .= " LIMIT $PageSize";
			if ($PageNo) {
				$sql .= ' OFFSET ' . pg_escape_string($this->dbh, ($PageNo - 1 ) * $PageSize);
			}
		}

		if ($this->Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_num_rows($this->LocalResult);
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
					 where watch_list_id = " . pg_escape_string($this->dbh, $WatchListID) . "
					   and element_id    = " . pg_escape_string($this->dbh, $this->element_id);

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($numrows == 1) {
				if ($this->Debug) echo "IsOnWatchList succeeded<BR>";
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

		$sql = "select GetPortID('" . pg_escape_string($this->dbh, $Category) . "', '"  . pg_escape_string($this->dbh, $Port) . "') as port_id";
		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
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

		$sql = 'select watch_list_count(' . pg_escape_string($this->dbh, $this->element_id) . ')';

		if ($this->Debug) echo $sql;

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
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

	function IsVulnerable() {
		return $this->vulnerable_current > 0;
	}

	function WasVulnerable() {
		return $this->vulnerable_past > 0;
	}
	
	function IsDeleted() {
		return $this->{'status'} == "D";
	}

	function PackageIsAvailable() {
		$available = true;
		if (IsSet($this->{'license_restricted'}) && strpos($this->{'license_restricted'}, DELETE_PACKAGE) !== false) {
			# false === not found
			# non false = found
			$available = false;
		}

		# if either of these are non-black, there is no package
		if (!empty($this->{'no_package'}) || !empty($this->{'manual_package_build'})) {
			$available = false;
		}

		return $available;
	}

	function PackageNotAvailableReason() {
		$available = '';
		if (IsSet($this->{'license_restricted'}) && strpos($this->{'license_restricted'}, DELETE_PACKAGE) !== false) {
			# false === not found
			# non false = found
			$available = '_LICENSE_RESTRICTED = ' . $this->{'license_restricted'};
		}

		# if either of these are non-black, there is no package
		if (!empty($this->{'no_package'}) || !empty($this->{'manual_package_build'})) {
			$available = 'NO_PACKAGE = ' . $this->{'no_package'};
		}

		if (!empty($this->{'manual_package_build'})) {
			$available = 'MANUAL_PACKAGE_BUILD = ' . $this->{'manual_package_build'};
		}

		return $available;
	}

	function ConflictMatches() {
		$sql = 'SELECT DISTINCT PackageName(PCM.port_id) as package_name, f.*
  FROM ports_conflicts_matches PCM
  JOIN ports_conflicts PC ON PCM.ports_conflicts_id = PC.id
  JOIN GetPortFromPackageName(PackageName(PCM.port_id)) AS f ON true
 WHERE PC.port_id = ' . $this ->{'id'};

		if ($this->Debug) {
			echo "<pre>$sql</pre>";
		}

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
			if ($this->Debug) echo "FetchByElementID succeeded<BR>";
			$this->{'conflicts_matches'} = pg_fetch_all($result);
		} else {
			echo 'pg_exec failed: <pre>' . $sql . '</pre> : ' . pg_errormessage();
		}

	}

}
