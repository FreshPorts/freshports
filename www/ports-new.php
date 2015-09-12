<?php
	#
	# $Id: ports-new.php,v 1.2 2006-12-17 12:06:15 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$Debug = 0;

	# we allow the following intervals: today, yesterday, this past week, past 3 months

	$interval = pg_escape_string($_GET["interval"]);

	switch ($interval) {
		case 'today':
			$IntervalAdjust = '1 day';
			$Interval       = 'past 24 hours';
			break;

		case 'yesterday':
			$IntervalAdjust = '2 days';
			$Interval       = 'past 48 hours';
			break;

		case 'week':
			$IntervalAdjust = '1 week';
			$Interval       = 'past 7 days';
			break;

		case 'fortnight':
			$IntervalAdjust = '2 weeks';
			$Interval       = 'past 2 weeks';
			break;

		case 'month':
			$IntervalAdjust = '1 month';
			$Interval       = 'past month';
			break;

		case '3months':
		default:
			$interval       = '3months';
			$IntervalAdjust = '3 months';
			$Interval       = 'past 3 months';
	}


	$Title    = "New ports - " . $Interval;

	freshports_Start($Title,
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>

<TR>
	<? echo freshports_PageBannerText($Title); ?>
</TR>
<TR><TD>
These are the recently added ports.
</TD></TR>
<?

	$visitor = pg_escape_string($_COOKIE["visitor"]);
	$sort    = pg_escape_string($_GET["sort"]);

	// make sure the value for $sort is valid
	
	echo "<TR><TD>\nThis page is ";
	
	switch ($sort) {
		case "dateadded":
			$sort = "date_added_raw desc, category, port";
			echo 'sorted by date added.  <A HREF="' . $_SERVER["PHP_SELF"] . '?interval=' . $interval . '&amp;sort=category">Sort by category</A>';
			$ShowCategoryHeaders = 0;
			break;
	
		default:
			$sort ="category, port";
			echo 'sorted by category.  <A HREF="' . $_SERVER["PHP_SELF"] . '?interval=' . $interval . '&amp;sort=dateadded">Sort by date added</A>';
			$ShowCategoryHeaders = 1;
	}
	
	echo "</TD></TR>\n";

	$sql = "
select TEMP.id,
       element.name as port,
       categories.name as category,
       TEMP.category_id,
       TEMP.version as version,
       TEMP.revision as revision,
       TEMP.element_id,
       TEMP.maintainer,
       TEMP.short_description,
       TEMP.date_added,
       TEMP.date_added_raw,
       TEMP.last_change_log_id,
       TEMP.package_exists,
       TEMP.extract_suffix,
       TEMP.homepage,
       element.status,
       TEMP.broken,
       TEMP.forbidden,
       TEMP.latest_link ";

	if ($User->id) {
		$sql .= ",
         onwatchlist";
   }

	$sql .= "
	 FROM (
   SELECT ports.id,
          ports.category_id,
          version as version,
          revision as revision,
          ports.element_id,
          maintainer,
          short_description,
          to_char(ports.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added,
          ports.date_added as date_added_raw,
          last_commit_id as last_change_log_id,
          package_exists,
          extract_suffix,
          homepage,
          broken,
          forbidden,
          latest_link
";
	if ($User->id) {
		$sql .= ",
         onwatchlist";
   }

	$sql .= "   from ports ";

	if ($User->id) {
			$sql .= "
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $User->id
       AND watch_list.in_service
  GROUP BY wle_element_id) AS TEMP2
       ON TEMP2.wle_element_id = ports.element_id";
	}
	
	$sql .= "
 WHERE ports.date_added  > (SELECT now() - interval '" . pg_escape_string($IntervalAdjust) . "')) AS
TEMP, element, categories
 WHERE TEMP.category_id = categories.id
   and element.status   = 'A'
   and TEMP.element_id  = element.id
  ";

	$sql .= "\n  order by $sort ";

	if ($Debug) {
		echo "<pre>$sql</pre>";
	}

	$result = pg_exec($db, $sql);
	if (!$result) {
		echo pg_errormessage();
	} else {
		$numrows = pg_numrows($result);
	}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/list-of-ports.php');

	echo freshports_ListOfPorts($result, $db, 'Y', $ShowCategoryHeaders, $User, $numrows);
?>

</TABLE>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
