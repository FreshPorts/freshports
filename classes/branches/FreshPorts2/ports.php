<?
	# $Id: ports.php,v 1.1.2.21 2002-12-09 20:22:49 dan Exp $
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
	var $date_added;
	var $categories;

	// derived or from other tables
	var $category;
	var $port;
	var $needs_refresh;
	var $status;
	var $updated;	// timestamp of last update

	var $onwatchlist;	// count of how many watch lists is this port on for this user. 
							// not actually fetched directly by this class.
							// normally used only if you've specified it in your own SQL.

	// not always present/set
	var $update_description;

	// so far used by ports-deleted.php and include/list-of-ports.php
	var $message_id;
	var $encoding_losses;

	// needed for fetch by category
	var $LocalResult;

	var $committer; 

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
		$this->date_added         = $myrow["date_added"];
		$this->categories         = $myrow["categories"];

		$this->port               = $myrow["port"];
		$this->category           = $myrow["category"];
		$this->needs_refresh      = $myrow["needs_refresh"];
		$this->status             = $myrow["status"];
		$this->updated            = $myrow["updated"];

		$this->onwatchlist        = $myrow["onwatchlist"];

		$this->update_description = $myrow["update_description"];
		$this->message_id         = $myrow["message_id"];
		$this->encoding_losses    = $myrow["encoding_losses"];
		$this->committer          = $myrow["committer"];
	}

	function FetchByPartialName($pathname, $UserID = 0) {

		$Debug = 0;

		# fetch a single port based on pathname.
		# e.g. net/samba
		#
		# It will not bring back any commit information.

		#
		# first, we get the element relating to this port
		#
		$element = new Element($this->dbh);
      $element->FetchByName($pathname);
      
      # * * * * * * * * * * * * * * * * * * * * * * * * *
      # Now that we have the ID, we should call FetchByID!
      # * * * * * * * * * * * * * * * * * * * * * * * * *

		if ($Debug) echo "into FetchByPartialName with $pathname<BR>";

		if (IsSet($element->id)) {
			$this->element_id = $element->id;

			$sql = "
select ports.id,
       ports.element_id,
       ports.category_id       as category_id, 
       ports.short_description as short_description, 
       ports.long_description, 
       ports.version           as version, 
       ports.revision          as revision, 
       ports.maintainer, 
       ports.homepage, 
       ports.master_sites, 
       ports.extract_suffix, 
       ports.package_exists, 
       ports.depends_build, 
       ports.depends_run, 
       ports.last_commit_id, 
       ports.found_in_index, 
       ports.forbidden, 
       ports.broken, 
       to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added, 
       ports.categories as categories,
	    element.name     as port, 
	    categories.name  as category,
	    element.status ";

			if ($UserID) {
				$sql .= ",
	        onwatchlist";
	      }

	      $sql .= "
       from categories, element, ports ";

			if ($UserID) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = $UserID
	  GROUP BY watch_list_element.element_id) AS TEMP
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

	function FetchByName($PortName, $WatchListID=0) {
		
		die("classes/ports.php::FetchByName has been invoked. Who called it?");

		$Debug   = 0;

		$numrows = 0; 	# nothing found

		# fetch zero or more ports based on the name.
		# e.g. samba, logcheck
		# it returns the number of ports found.  You must call FetchNth
		#
		$sql = "select ports.id, 
		               ports.element_id, 
		               ports.category_id       as category_id,
		               ports.short_description as short_description, 
		               ports.long_description, 
		               ports.version           as version,
		               ports.revision          as revision, 
		               ports.maintainer,
		               ports.homepage, 
		               ports.master_sites, 
		               ports.extract_suffix, 
		               ports.package_exists,
		               ports.depends_build, 
		               ports.depends_run, 
		               ports.last_commit_id, 
		               ports.found_in_index,
		               ports.forbidden, 
		               ports.broken, 
		               to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added,
		               ports.categories as categories,
			            element.name     as port, 
			            categories.name  as category,
			            element.status ";

		if ($WatchListID) {
			$sql .= ",
		       CASE when watch_list_element.element_id is null
	    	      then 0
	        	  else 1
		       END as onwatchlist ";
		}


		$sql .= "from categories, element, ports ";

		#
		# if the watch list id is provided (i.e. they are logged in and have a watch list id...)
		#
		if ($WatchListID) {
			$sql .="
		            left outer join watch_list_element
					on (ports.element_id                 = watch_list_element.element_id 
				   and watch_list_element.watch_list_id = $WatchListID) ";
		}

		$sql .="WHERE element.id        = $this->element_id 
		          and ports.category_id = categories.id 
		          and ports.element_id  = element.id ";


		if ($Debug) {
			echo "<pre>$sql</pre>";
			exit;
		}

        $this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
		} else {
			echo 'pg_exec failed: ' . $sql;
		}

		return $numrows;
	}

	function FetchByID($id, $UserID = 0) {
		# fetch a single port based on id
		# I don't think this is actually used.

#		die("classes/ports.php::FetchByID has been invoked. Who called it?");

		$sql = "select ports.id, 
		               ports.element_id, 
		               ports.category_id       as category_id,
		               ports.short_description as short_description, 
		               ports.long_description, 
		               ports.version           as version,
		               ports.revision          as revision, 
		               ports.maintainer,
		               ports.homepage, 
		               ports.master_sites, 
		               ports.extract_suffix, 
		               ports.package_exists,
		               ports.depends_build, 
		               ports.depends_run, 
		               ports.last_commit_id, 
		               ports.found_in_index,
		               ports.forbidden, 
		               ports.broken, 
		               to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added,
		               ports.categories as categories,
			            element.name     as port, 
			            categories.name  as category,
			            element.status ";

		if ($UserID) {
			$sql .= ",
		       CASE when watch_list_element.element_id is null
	    	      then 0
	        	  else 1
		       END as onwatchlist ";
		}


		$sql .= "from categories, element, ports ";

		#
		# if the watch list id is provided (i.e. they are logged in and have a watch list id...)
		#
		if ($WatchListID) {
			$sql .="
		            left outer join watch_list_element
					on (ports.element_id                 = watch_list_element.element_id 
				   and watch_list_element.watch_list_id = $WatchListID) ";
		}

		$sql .= "\nWHERE element.id        = $id 
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
				$myrow = pg_fetch_array ($result, 0);
				$this->_PopulateValues($myrow);

				#
				# I had considered including an OUTER JOIN in the above SQL
				# but didn't.  I figured the above was
				if ($WatchListID) {
					$this->onwatchlist = IsOnWatchList($WatchListID);
				}

			}
		} else {
			echo 'pg_exec failed: ' . $sql;
		}
	}

	function FetchByCategoryInitialise($CategoryID, $UserID = 0) {
		# fetch all ports based on category
		# e.g. id for net
		
		$Debug = 0;

		$sql = "
 SELECT ports.id, 
        ports.element_id, 
        ports.category_id as category_id, 
        ports.short_description as short_description, 
        ports.long_description, 
        ports.version as version,
        ports.revision as revision, 
        ports.maintainer,
        ports.homepage, 
        ports.master_sites, 
        ports.extract_suffix, 
        ports.package_exists, 
        ports.depends_build, 
        ports.depends_run, 
        ports.last_commit_id, 
        ports.found_in_index, 
        ports.forbidden, 
        ports.broken, to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added, 
        ports.categories as categories, 
        element.name as port, 
        categories.name as category, 
        element.status";

		if ($UserID) {
			$sql .= ",
        onwatchlist";
      }
      $sql .= "
   FROM categories, element, ports ";

		if ($UserID) {
			$sql .= "
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element 
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $UserID
  GROUP BY watch_list_element.element_id) AS TEMP
       ON TEMP.wle_element_id = ports.element_id ";
      }

		$sql .= " WHERE ports.category_id    = categories.id 
		            and ports.element_id     = element.id 
				      and categories.id        = $CategoryID 
				      and element.status       = 'A' 
";

		if ($User->id) {
			$sql .= ", wle_element_id, watch";
		}

		$sql .= " ORDER by port ";
		
		if ($Debug) echo "<pre>$sql</pre>";

		$this->LocalResult = pg_exec($this->dbh, $sql);
		if ($this->LocalResult) {
			$numrows = pg_numrows($this->LocalResult);
			if ($numrows == 1) {
#				echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($this->LocalResult, 0);
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
			echo 'pg_exec failed: ' . $sql;
		}

		return $result;
	}
}
