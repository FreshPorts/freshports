<?php
#
# $Id: ports-new.php,v 1.2 2006-12-17 12:06:15 dan Exp $
#
# Copyright (c) 1998-2026 Dan Langille
#

require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/system_branch.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-news.php');

$Debug = 0;

# we allow the following intervals: today, yesterday, this past week, past 3 months

if (isset($_REQUEST["interval"])) {
    $interval = $_REQUEST["interval"];
} else {
    $interval = '';
}

# note $interval being used to set $Interval and $interval
switch ($interval) {
    case 'today':
        $IntervalAdjust = '1 day';
        $Interval = _('past 24 hours');
        break;

    case 'yesterday':
        $IntervalAdjust = '2 days';
        $Interval = _('past 48 hours');
        break;

    default:
    case 'week':
        $interval = 'week';
        $IntervalAdjust = '1 week';
        $Interval = _('past 7 days');
        break;

    case 'fortnight':
        $IntervalAdjust = '2 weeks';
        $Interval = _('past 2 weeks');
        break;

    case 'month':
        $IntervalAdjust = '1 month';
        $Interval = _('past month');
        break;

    case '3months':
        $IntervalAdjust = '3 months';
        $Interval = _('past 3 months');
}


if (isset($_REQUEST['branch'])) {
    $BranchName = NormalizeBranch(htmlspecialchars($_REQUEST['branch']));
} else {
    $BranchName = BRANCH_HEAD;
}

$Title = sprintf(_('New ports - %s'), $Interval);

$visitor = pg_escape_string($db, $_COOKIE[USER_COOKIE_NAME] ?? '');
if (isset($_REQUEST["sort"])) {
    $sort = pg_escape_string($db, $_REQUEST["sort"]);
} else {
    $sort = '';
}

// make sure the value for $sort is valid

switch ($sort) {
    case "dateadded":
        $SortKey = 'dateadded';
        $sort = "date_added_raw desc, category, port";
        $SortText = sprintf(_('This page is sorted by date added.  %s'), '<a href="' . $_SERVER["PHP_SELF"] . '?interval=' . $interval . '&amp;sort=category">' . _('Sort by category') . '</a>');
        $ShowCategoryHeaders = 0;
        break;

    default:
        $SortKey = 'category';
        $sort = "category, port";
        $SortText = sprintf(_('This page is sorted by category.  %s'), '<a href="' . $_SERVER["PHP_SELF"] . '?interval=' . $interval . '&amp;sort=dateadded">' . _('Sort by date added') . '</a>');
        $ShowCategoryHeaders = 1;
}

###
### Check the cache
###
# The list of ports is cached in the news cache, which fp-listen empties on every
# commit and port update.  Each user has their own entry, because the watch list
# icons differ; anonymous users share user 0.  The window (e.g. past 24 hours) moves
# even when nothing is committed, so an entry older than an hour is not used.
#
define('PORTS_NEW_CACHE_MAX_AGE', 3600);

$CacheFileName = 'ports-new.' . $interval . '.' . $SortKey . '.' . $BranchName . '.' . intval($User->id ?? 0);
$Cache = new CacheNews(NEWS_DIRECTORY);
$CacheResult = $Cache->Retrieve($CacheFileName);
if (!$CacheResult && time() - $Cache->LastModifiedGet() < PORTS_NEW_CACHE_MAX_AGE) {
    $ListOfPorts = $Cache->CacheDataGet();
} else {
    $Cache->LastModified = null;  # an expired entry's time must not be sent if Add() fails
    $ListOfPorts = ports_new_ListOfPorts($db, $User, $IntervalAdjust, $BranchName, $sort, $ShowCategoryHeaders, $Debug);
    if ($ListOfPorts !== false) {
        $Cache->CacheDataSet($ListOfPorts);
        $Cache->Add($CacheFileName);
    }
}

### page building starts here

# when the cache Add fails, LastModifiedGet() is empty, and this gives the current time
freshports_ConditionalGetUnix($Cache->LastModifiedGet());

freshports_Start($Title,
    $Title,
    "FreeBSD, index, applications, ports");

?>

<?php echo freshports_MainTable(); ?>

<tr>
    <td class="content">

        <?php echo freshports_MainContentTable(); ?>

<tr>
    <?php echo freshports_PageBannerText($Title); ?>
</tr>
<tr>
    <td>
        <?php echo _('These are the recently added ports.'); ?>
    </td>
</tr>
<?php

echo "<tr><td>\n";
echo $SortText;
echo "</td></tr>\n";

if ($ListOfPorts === false) {
    echo pg_last_error($db);
} else {
    echo $ListOfPorts;
}
?>

</table>

<td class="sidebar">
    <?php
    echo freshports_SideBar();
    ?>
</td>

</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
<?php

# returns the HTML for the list of ports, or false if the query failed
function ports_new_ListOfPorts($db, $User, $IntervalAdjust, $BranchName, $sort, $ShowCategoryHeaders, $Debug) {

# The branch id goes in as a literal, not via a join on system_branch:
# with a literal, the planner knows head has most of the rows in
# commit_log_branches.  Joined by name, it guesses about 10,000 rows
# instead of 568,000, and walks them all (about 1 second).  An unknown
# branch gives id 0, which matches nothing, as the join did.
$system_branch = new SystemBranch($db);
$BranchID = intval($system_branch->getBranchID($BranchName));

# we might add in other parameters below.
$params = array($IntervalAdjust);
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
       NP.license,
       NP.last_commit_id,
       R.name                                                                                              AS repo_name,
       R.repository,
       R.repo_hostname,
       R.path_to_repo,
       NP.uses,
       NP.package_name ";

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
          license,
          last_commit_id,
          uses,
          package_name
          
";

# we need the single quotes for the interval operation, not for quoting.
# No sub-select around now(): the planner can then estimate the rows and use ports_date_added_idx.
$sql .= "   FROM ports P  WHERE P.date_added  > now() - $1::interval) AS NP";

if ($User->id) {
    $sql .= "
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $2
       AND watch_list.in_service
  GROUP BY wle_element_id) AS TEMP2
       ON TEMP2.wle_element_id = NP.element_id";

       $params[] = intval($User->id);
}

$sql .= "
               LEFT OUTER JOIN commit_log          CL  ON NP.last_commit_id = CL.id
                          JOIN commit_log_ports    CLP ON CLP.commit_log_id = CL.id 
                          JOIN commit_log_branches CLB ON CLP.commit_log_id = CLB.commit_log_id AND CLP.port_id = NP.id
                                                      AND CLB.branch_id     = $BranchID
               LEFT OUTER JOIN repo                R   ON CL.repo_id        = R.id
                          JOIN element             E   ON NP.element_id     = E.id
                                                      AND E.status          = 'A'
                          JOIN categories          C   ON C.id              = NP.category_id


  ";

$sql .= "\n  order by $sort ";

if ($Debug) {
    syslog(LOG_NOTICE, 'ports-new.php: ' . $sql);
}

$result = pg_query_params($db, $sql, $params);
if (!$result) {
    return false;
}
$numrows = pg_num_rows($result);

require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/list-of-ports.php');

return freshports_ListOfPorts($result, $db, 'Y', $ShowCategoryHeaders, $User, $numrows);
}
