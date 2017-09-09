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

	if (IsSet($_GET["interval"])) {
		$interval = pg_escape_string($_GET["interval"]);
	} else {
		$interval = '';
	}

	switch ($interval) {
		case 'today':
			$IntervalAdjust = '1 day';
			$Interval       = 'past 24 hours';
			break;

		case 'yesterday':
			$IntervalAdjust = '2 days';
			$Interval       = 'past 48 hours';
			break;

		default:
		case 'week':
			$interval       = 'week';
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
			$IntervalAdjust = '3 months';
			$Interval       = 'past 3 months';
	}


	if (IsSet($_REQUEST['branch'])) {
		$BranchName = htmlspecialchars($_REQUEST['branch']);
	} else {
		$BranchName = BRANCH_HEAD;
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
	if (IsSet($_GET["sort"])) {
		$sort = pg_escape_string($_GET["sort"]);
	} else {
		$sort = '';
	}

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
select NP.id,
       E.name as port,
       C.name as category,
       NP.category_id,
       NP.element_id,
       element_pathname(NP.element_id) as element_pathname,
       NP.version as version,
       NP.revision as revision,
       NP.element_id,
       NP.maintainer,
       NP.short_description,
       NP.date_added,
       NP.date_added_raw,
       NP.last_change_log_id,
       NP.package_exists,
       NP.extract_suffix,
       NP.homepage,
       E.status,
       NP.broken,
       NP.forbidden,
       NP.latest_link,
       NP.license,
       NP.last_commit_id,
       R.svn_hostname,
       R.path_to_repo ";

	if ($User->id) {
		$sql .= ",
         onwatchlist";
	} else {
		$sql .= ",
         NULL AS onwatchlist ";
	}

	$sql .= "
	 FROM (
   SELECT P.id,
          P.category_id,
          version as version,
          revision as revision,
          P.element_id,
          maintainer,
          short_description,
          to_char(P.date_added - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added,
          P.date_added as date_added_raw,
          last_commit_id as last_change_log_id,
          package_exists,
          extract_suffix,
          homepage,
          broken,
          forbidden,
          latest_link,
          license,
          last_commit_id
          
";
	if ($User->id) {
		$sql .= ",
         onwatchlist";
	} else {
		$sql .= ",
         NULL AS onwatchlist ";
        }

	$sql .= "   FROM ports P  WHERE P.date_added  > (SELECT now() - interval '" . pg_escape_string($IntervalAdjust) . "')) AS NP";

	if ($User->id) {
			$sql .= "
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $User->id
       AND watch_list.in_service
  GROUP BY wle_element_id) AS TEMP2
       ON TEMP2.wle_element_id = NP.element_id";
	}
	
	$sql .= "
               LEFT OUTER JOIN commit_log          CL  ON NP.last_commit_id = CL.id
                          JOIN commit_log_ports    CLP ON CLP.commit_log_id = CL.id 
                          JOIN commit_log_branches CLB ON CLP.commit_log_id = CLB.commit_log_id
                          JOIN system_branch       SB  ON SB.branch_name    = '" . pg_escape_string($BranchName) . "' AND SB.id = CLB.branch_id
               LEFT OUTER JOIN repo                R   ON CL.repo_id        = R.id
                          JOIN element             E   ON NP.element_id     = E.id
                                                      AND E.status          = 'A'
                          JOIN categories          C   ON C.id              = NP.category_id


  ";

	$sql .= "\n  order by $sort ";

	if ($Debug) {
		echo "<pre>$sql</pre>";
	}

	$numrows = 0;
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
