<?
	# $Id: ports-deleted.php,v 1.1.2.6 2002-04-22 03:33:13 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	$Debug = 0;

	$Interval = '3 months';
	$Title    = "Deleted ports - past " . $Interval;

	freshports_Start($Title,
					"freshports - deleted ports, applications",
					"FreeBSD, index, applications, ports");

?>

<TABLE width="<? echo $TableWidth ?>" border="0" ALIGN="center">

<TR><TD valign="top" width="100%">
<TABLE width="100%" border="0">
<TR>
	<? freshports_PageBannerText($Title); ?>
</TR>
<TR><TD>
These are the latest deleted ports.
</TD></TR>
<?

	$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

	if ($visitor) {
		$WatchID = freshports_MainWatchID($UserID, $db);
	} else {
		unset ($WatchID);
	}

		// make sure the value for $sort is valid

		echo "<TR><TD>\nThis page is ";

		switch ($sort) {
		case "updated":
			$sort = "updated desc, category, port";
			echo 'sorted by last update date.  but you can sort by <a href="' . $PHP_SELF . '?sort=category">category</a>';
			$ShowCategoryHeaders = 0;
			break;

		default:
			$sort ="category, port";
			echo 'sorted by category.  but you can sort by <a href="' . $PHP_SELF . '?sort=updated">last update</a>';
			$ShowCategoryHeaders = 1;
			$cache_file .= ".updated";
		}

		echo "</TD></TR>\n";

			$sql = "select ports.id, element.name as port, commit_log.commit_date as updated, " .
			       "categories.name as category, ports.category_id, version as version, revision as revision, ".
			       "commit_log.committer, commit_log.description as update_description, ports.element_id, " .
			       "maintainer, short_description, ports.date_added, commit_log.message_id, ".
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

			echo freshports_ListOfPorts($result, $db, "N", $ShowCategoryHeaders);
?>

</TABLE>

</TD>
  <TD valign="top" width="*">
<? include("./include/side-bars.php") ?>
</TD>
</TR>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>

