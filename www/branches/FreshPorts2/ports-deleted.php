<?
	# $Id: ports-deleted.php,v 1.1.2.4 2002-04-19 17:05:53 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	$Debug = 1;

	// if we don't know who they are, we'll make sure they login first
	if (!$visitor) {
    	    header("Location: login.php?origin=" . $PHP_SELF);  /* Redirect browser to PHP web site */
        	exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	$Interval = '3 months';
	$Title    = "Deleted ports - past " . $Interval;

	freshports_Start($Title,
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">

<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
	<? freshports_PageBannerText($Title); ?>
</tr>
<tr><td>
These are the latest deleted ports.
</td></tr>
<?

	$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

	if ($UserID == '') {
		echo '<tr><td>';
		echo 'You must be logged in order to view your watch lists.';
		echo '</td></tr>';
	} else {

		$WatchID = freshports_MainWatchID($UserID, $db);

		// make sure the value for $sort is valid

		echo "<tr><td>\nThis page is ";

		switch ($sort) {
		/* sorting by port is disabled. Doesn't make sense to do this
		case "port":
			$sort = "version, updated desc";
			$cache_file .= ".port";
			break;
		*/

		case "updated":
			$sort = "updated desc, port";
			echo 'sorted by last update date.  but you can sort by <a href="' . $PHP_SELF . '?sort=category">category</a>';
			$ShowCategoryHeaders = 0;
			break;

		default:
			$sort ="commit_log.commit_date desc";
			echo 'sorted by category.  but you can sort by <a href="' . $PHP_SELF . '?sort=updated">last update</a>';
			$ShowCategoryHeaders = 1;
			$cache_file .= ".updated";
		}

		echo "</td></tr>\n";

		if ($WatchID == '') {
			echo "<tr><td>Your watch list is empty.</td></tr>";
		} else {

			$sql = "select ports.id, element.name as port, commit_log.commit_date as updated, " .
			       "categories.name as category, ports.category_id, version as version, revision as revision, ".
			       "commit_log.committer, commit_log.description as update_description, ports.element_id, " .
			       "maintainer, short_description, ports.date_added, ".
			       "last_commit_id as last_change_log_id, " .
			       "package_exists, extract_suffix, homepage, status, " .
			       "broken, forbidden ";

			if ($WatchListID) {
				$sql .= ", CASE when watch_list_element.element_id is null
								then 0
								else 1
							END as watch ";
			}

			$sql .= " from commit_log, element, categories, ports   ";

			if ($WatchListID) {
					$sql .= " left outer join watch_list_element
							 ON (ports.element_id        = watch_list_element.element_id
							AND watch_list_element.watch_list_id = $WatchListID) ";
			}
			$sql .= "WHERE ports.element_id = element.id
					   and ports.category_id = categories.id 
                       and status = 'D' 
					   and commit_log.commit_date > (now() - interval '$Interval') 
					   and ports.last_commit_id = commit_log.id ";

			$sql .= " order by $sort ";
#			$sql .= " limit 20";

			if ($Debug) {
				echo $sql;
			}

			$result = pg_exec($db, $sql);
			if (!$result) {
				echo pg_errormessage();
			} else {
				$numrows = pg_numrows($result);
#				echo "There are $numrows to fetch<BR>\n";
			}

			require("./include/list-of-ports.php");

			echo '<TR><TD>' . freshports_ListOfPorts($result, $db) . '</TD></TR>';
		}
	}

?>

</script>
</table>
</td>
  <td valign="top" width="*">
<? include("./include/side-bars.php") ?>
</td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>

