<?
	# $Id: watch.php,v 1.1.2.34 2002-12-12 02:45:25 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/watch-lists.php');

	// if we don't know who they are, we'll make sure they login first
	if (!$visitor) {
		header("Location: login.php?origin=" . $_SERVER["PHP_SELF"]);  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

$Debug = 0;

if ($Debug) phpinfo();

$visitor = $_COOKIE['visitor'];

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header("Location: login.php?origin=" . $_SERVER["PHP_SELF"]);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

	freshports_Start('your watched ports',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');
					
	if ($_POST["watch_list_select_x"] && $_POST["watch_list_select_y"]) {
		# they clicked on the GO button and we have to apply the 
		# watch staging area against the watch list.
		$WatchListID = AddSlashes($_POST["watch_list_id"]);
		if ($Debug) echo "\$WatchListID='$WatchListID'";
	} else {
		$WatchListID = '';
	}

?>

<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">

<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
	<? echo freshports_PageBannerText("your watch list - PERHAPS the watch list name[s] should go here?"); ?>
</tr>
<tr><td>
<table border=0 width="100%">
<tr><td>
These are the ports which are on your <a href="watch-categories.php">watch list</A>. 
That link also occurs on the right hand side of this page, under Login.
</td><td valign="top" nowrap align="right">

<?php

echo freshports_WatchListDDLBForm($db, $User->id, $WatchListID);

?>

</td></tr>
</table>
</td></tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";


// make sure the value for $sort is valid

echo "<tr><td>\nThis page is ";

$sort = $_GET["sort"];

switch ($sort) {
/* sorting by port is disabled. Doesn't make sense to do this
   case "port":
      $sort = "version, updated desc";
      $cache_file .= ".port";
      break;
*/
   case "category":
      $sort ="category, port";
      echo 'sorted by category.  but you can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=updated">last update</a>';
      $ShowCategoryHeaders = 1;
      $cache_file .= ".updated";
      break;

   default:
      $sort = "commit_date_sort_field desc, port";
      echo 'sorted by last update date.  but you can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=category">category</a>';
      $ShowCategoryHeaders = 0;
      break;

}

echo "</td></tr>\n";


if ($WatchListID == '') {
	echo '<tr><td align="right"><BIG>Please select a watch list.</BIG><p>eventually, if you have just one watch list, this will default.</td></tr>';
} else {
	
	$sql = "
	SELECT temp.*,
		to_char(max(commit_log.commit_date) - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') 	as updated,
		commit_log.committer, commit_log.description 														as update_description, 
		to_char(max(commit_log.date_added) - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') 		as date_added, 
		commit_log.message_id, max(commit_log.commit_date) 												as commit_date_sort_field,
		commit_log.committer
	from commit_log
		RIGHT OUTER JOIN
	(
	
	select element.name 			as port, 
			 ports.id 				as ports_id, 
	       categories.name 		as category, 
	       categories.id 		as category_id, 
	       ports.version 		as version, 
	       ports.revision 		as revision, 
	       element.id 			as element_id, 
	       ports.maintainer, 
	       ports.short_description, 
	       ports.last_commit_id, 
	       ports.package_exists, 
	       ports.extract_suffix, 
	       ports.homepage, 
	       element.status, 
	       ports.broken, 
	       ports.forbidden, 
	       1 as onwatchlist 
	       from watch_list_element, element, categories, ports
	 WHERE ports.category_id                = categories.id 
	   and watch_list_element.element_id    = ports.element_id 
		and ports.element_id                 = element.id
	   and watch_list_element.watch_list_id = $WatchListID
	
	
	) as TEMP
	on (TEMP.last_commit_id = commit_log.id) 
	
	GROUP BY port,
				ports_id, 
	         category, 
	         category_id, 
	         version, 
	         revision, 
	         commit_log.committer, 
	         update_description, 
	         element_id, 
	         maintainer, 
	         short_description, 
	         date_added, 
	         last_commit_id, 
	         commit_log.message_id, 
	         package_exists, 
	         extract_suffix, 
	         homepage, 
	         status, 
	         broken, 
	         forbidden, 
	         onwatchlist  
	";
	
	$sql .= " order by $sort ";
	
	if ($Debug) {
	   echo "<pre>$sql</pre>";
	}
	
	$result = pg_exec($db, $sql);
	if (!$result) {
		echo pg_errormessage();
	}

	$HTML .= '<tr><td>';

	// get the list of topics, which we need to modify the order
	$NumPorts=0;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	$port = new Port($db);
	$port->LocalResult = $result;

	$LastCategory='';
	$GlobalHideLastChange = 'N';
	$numrows = pg_numrows($result);

	$ShowDescriptionLink = 0;
	$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink = $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
	$ShowPortCreationDate = 0;
	$HideCategory = 1;
	$ShowCategories		= 1;
	GLOBAL	$ShowDepends;
	$ShowDepends		= 1;
	$HideDescription = 1;
	$ShowEverything  = 1;
	$ShowShortDescription = 'Y';
	$ShowMaintainedBy     = 'Y';
	#$GlobalHideLastChange = 'Y';
	$ShowDescriptionLink  = 'N';

	if ($ShowCategoryHeaders) {
		$HTML .= '<DL>';
	}

	for ($i = 0; $i < $numrows; $i++) {
		$port->FetchNth($i);
		if ($ShowCategoryHeaders) {
			$Category = $port->category;
	
			if ($LastCategory != $Category) {
				if ($i > 0) {
					$HTML .= "\n</DD>\n";
				}

				$LastCategory = $Category;
				if ($ShowCategoryHeaders) {
					$HTML .= '<DT>';
				}

				$HTML .= '<BIG><BIG><B><a href="/' . $Category . '/">' . $Category . '</a></B></BIG></BIG>';
				if ($ShowCategoryHeaders) {
					$HTML .= "</DT>\n<DD>";
				}
			}
		}

		$HTML .= freshports_PortDetails($port, $db, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, 1, '', 0);
		$HTML .= '<BR>';
	}

	if ($ShowCategoryHeaders) {
		$HTML .= "\n</DD>\n</DL>\n";
	}

	$HTML .= "</td></tr>\n";

	$HTML .= "<tr><td>$numrows ports found</td></tr>\n";

	echo $HTML;

} // end if no WatchListID

</script>
</table>
</td>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
<? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/side-bars.php') ?>
</td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/footer.php') ?>
</TD></TR>
</TABLE>

</body>
</html>
