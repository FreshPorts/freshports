<?
	# $Id: watch.php,v 1.1.2.17 2002-05-21 02:10:49 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

$Debug = 0;

$visitor = $_COOKIE["visitor"];

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header("Location: login.php?origin=" . $_SERVER["PHP_SELF"]);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

	freshports_Start("your watched ports",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");
?>

<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">

<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
	<? freshports_PageBannerText("your watch list"); ?>
</tr>
<tr><td>
These are the which are on your <a href="watch-categories.php">watch list</A>. 
That link also occurs on the right hand side of this page, under Login.
</td></tr>
<script language="php">

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
      echo 'sorted by last update date.  but you can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=category">category</a>';
      $ShowCategoryHeaders = 0;
      break;

   default:
      $sort ="category, port";
      echo 'sorted by category.  but you can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=updated">last update</a>';
      $ShowCategoryHeaders = 1;
      $cache_file .= ".updated";
}

echo "</td></tr>\n";

$UpdateCache = 1;

if ($WatchID == '') {
   echo "<tr><td>Your watch list is empty.</td></tr>";
} else {

if ($UpdateCache == 1) {
//   echo 'time to update the cache';

$sql = "";
$sql = "select ports.id, element.name as port, ports.id as ports_id, to_char(max(commit_log.commit_date) - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ports.revision as revision, ".
       "commit_log.committer, commit_log.description as update_description, element.id as element_id, " .
       "ports.maintainer, ports.short_description, to_char(max(commit_log.date_added) - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') as date_added, ".
       "ports.last_commit_id as last_change_log_id, " .
       "ports.package_exists, ports.extract_suffix, ports.homepage, element.status, " .
       "ports.broken, ports.forbidden, 1 as onwatchlist ".
       "from watch_list_element, element, categories, ports LEFT OUTER JOIN commit_log on (ports.last_commit_id = commit_log.id) " .
       "WHERE ports.category_id             = categories.id " .
	   "  and watch_list_element.element_id = ports.element_id " .
	   "  and ports.element_id              = element.id
          and watch_list_element.watch_list_id = $WatchListID ";

$sql .= "GROUP BY ports.id, port, ports_id, " .
        "         category, categories.id, version, revision, ".
        "         commit_log.committer, update_description, element.id, " .
        "         ports.maintainer, ports.short_description, ports.date_added, ".
        "         last_change_log_id, " .
        "         ports.package_exists, ports.extract_suffix, ports.homepage, element.status, " .
        "         ports.broken, ports.forbidden, onwatchlist ";

$sql .= " order by $sort ";
//$sql .= " limit 20";

//$Debug=1;
if ($Debug) {
   echo $sql;
}

$result = pg_exec($db, $sql);
if (!$result) {
	echo pg_errormessage();
}
//$HTML = "</tr></td><tr>";

$HTML .= '<tr><td>';

// get the list of topics, which we need to modify the order
$NumPorts=0;

require("../classes/ports.php");
$port = new Port($db);
$port->LocalResult = $result;

$LastCategory='';
$GlobalHideLastChange = "N";
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
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
#$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";

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

}

$HTML .= "</td></tr>\n";

$HTML .= "<tr><td>$numrows ports found</td></tr>\n";

echo $HTML;

} // end if no WatchID
}

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
