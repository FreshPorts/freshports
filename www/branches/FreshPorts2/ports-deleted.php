<?php
	#
	# $Id: ports-deleted.php,v 1.1.2.21 2003-04-27 14:48:15 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	$Debug = 0;

	$Interval = '3 months';
	$Title    = 'Deleted ports - past ' . $Interval;

	freshports_Start($Title,
					'freshports - deleted ports, applications',
					'FreeBSD, index, applications, ports');

?>

<TABLE width="<? echo $TableWidth ?>" border="0" ALIGN="center">

<TR><TD valign="top" width="100%">
<TABLE width="100%" border="0">
<TR>
	<? echo freshports_PageBannerText($Title); ?>
</TR>
<TR><TD>
These are the latest deleted ports.
</TD></TR>
<?

	$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

	$visitor = $_COOKIE["visitor"];
	$sort    = $_REQUEST["sort"];

	// make sure the value for $sort is valid

	echo "<TR><TD>\nThis page is ";

	switch ($sort) {
		case 'updated':
			$sort = 'updated desc, category, port';
			echo 'sorted by last update date.  but you can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=category">category</a>';
			$ShowCategoryHeaders = 0;
			break;

		default:
			$sort ='category, port';
			echo 'sorted by category.  but you can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=updated">last update</a>';
			$ShowCategoryHeaders = 1;
		}

	echo "</TD></TR>\n";

	$sql = "
select ports.id,
       element.name as port, 
       to_char(max(commit_log.commit_date) - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as updated,
       categories.name as category,
       ports.category_id,
       version as version,
       revision as revision,
       commit_log.committer,
       commit_log.description as update_description,
       ports.element_id,
       maintainer,
       short_description,
       to_char(max(ports.date_added) - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added, 
       commit_log.message_id,
       last_commit_id as last_change_log_id,
       package_exists,
       extract_suffix,
       homepage,
       status,
       broken,
       forbidden ";

	if ($User->id) {
		$sql .= ",
       onwatchlist";
   }

	$sql .= " from commit_log, element, categories, ports   ";

	if ($User->id) {
			$sql .= "
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $User->id
  GROUP BY wle_element_id) AS TEMP
       ON TEMP.wle_element_id = ports.element_id";
	}

	$sql .= "
WHERE ports.element_id      = element.id
  and ports.category_id     = categories.id 
  and status                = 'D' 
  and commit_log.commit_date > (now() - interval '$Interval') 
  and ports.last_commit_id   = commit_log.id ";

	$sql .= "
GROUP BY ports.id,
         element.name,
         commit_log.commit_date,
         categories.name,
         ports.category_id,
         version,
         revision,
         commit_log.committer,
         commit_log.description,
         ports.element_id,
         maintainer,
         short_description,
         ports.date_added,
         commit_log.message_id,
         last_commit_id,
         package_exists,
         extract_suffix,
         homepage,
         status,
         broken,
         forbidden";

	if ($User->id) {
		$sql .= ",
         onwatchlist ";
	}

	$sql .= "\norder by $sort ";
#	$sql .= "\nlimit 20";

	if ($Debug) {
		echo "<pre>$sql</pre>";
	}

	$result = pg_exec($db, $sql);
	if (!$result) {
		echo pg_errormessage();
	} else {
		$numrows = pg_numrows($result);
#		echo "There are $numrows to fetch<BR>\n";
	}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/list-of-ports.php');

	echo freshports_ListOfPorts($result, $db, 'N', $ShowCategoryHeaders);
?>

</TABLE>

</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
freshports_ShowFooter();
?>

</body>
</html>

