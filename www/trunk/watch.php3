<?
   # $Id: watch.php3,v 1.23 2001-10-02 17:14:12 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");


   freshports_Start("title",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>
<?
// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header("Location: login.php3?origin=" . $PHP_SELF);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports</title>
</head>

 <? include("./include/header.php") ?>
<table width="100%" border="0">

<tr><td colspan="2">
This page lists the ports which are on your watch list. To modify the contents of this list, click on 
<a href="watch-categories.php3">watch list - Categories</a> at the right.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td colspan="5" bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">freshports - your watch list</font></td>
  </tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

if ($UserID == '') {
   echo '<tr><td>';
   echo 'You must be logged in order to view your watch lists.';
   echo '</td></tr>';
} else {


$WatchID = freshports_MainWatchID($UserID, $db);

// make sure the value for $sort is valid

echo "<tr><td>\n";

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
      $sort ="category, port";
      echo 'sorted by category.  but you can sort by <a href="' . $PHP_SELF . '?sort=updated">last update</a>';
      $ShowCategoryHeaders = 1;
      $cache_file .= ".updated";
}

echo "</td></tr>\n";

srand((double)microtime()*1000000);
$cache_time_rnd =       300 - rand(0, 600);


if ($Debug) {
echo '<br>';
echo '$cache_file=', $cache_file, '<br>';
echo '$LastUpdateFile=', $LastUpdateFile , '<br>';
echo '!(file_exists($cache_file))=',     !(file_exists($cache_file)), '<br>';
echo '!(file_exists($LastUpdateFile))=', !(file_exists($LastUpdateFile)), "<br>";
echo 'filectime($cache_file)=',          filectime($cache_file), "<br>";
echo 'filectime($LastUpdateFile)=',      filectime($LastUpdateFile), "<br>";
echo '$cache_time_rnd=',                 $cache_time_rnd, '<br>';
echo 'filectime($cache_file) - filectime($LastUpdateFile) + $cache_time_rnd =', filectime($cache_file) - filectime($LastUpdateFile) + $cache_time_rnd, '<br>';
}

$UpdateCache = 0;
if (!file_exists($cache_file)) {
//   echo 'cache does not exist<br>';
   // cache does not exist, we create it
   $UpdateCache = 1;
} else {
//   echo 'cache exists<br>';
   if (!file_exists($LastUpdateFile)) {
      // no updates, so cache is fine.
//      echo 'but no update file<br>';
   } else {
//      echo 'cache file was ';
      // is the cache older than the db?
      if ((filectime($cache_file) + $cache_time_rnd) < filectime($LastUpdateFile)) {
//         echo 'created before the last database update<br>';
         $UpdateCache = 1;
      } else {
//         echo 'created after the last database update<br>';
      }
   }
}

$UpdateCache = 1;

if ($WatchID == '') {
   echo "<tr><td>Your watch list is empty.</td></tr>";
} else {

if ($UpdateCache == 1) {
//   echo 'time to update the cache';

$sql = "";
$sql = "select ports.id, ports.name as port, ports.id as ports_id, change_log.commit_date as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "change_log.committer, change_log.update_description as update_description, " .
       "ports.maintainer, ports.short_description, UNIX_TIMESTAMP(ports.date_created) as date_created, ".
       "date_format(date_created, '$FormatDate $FormatTime') as date_created_formatted, ".
       "ports.last_change_log_id as last_change_log_id, " .
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, ports.status, " .
       "ports.broken, ports.forbidden ".
       "from ports, categories, watch_port, change_log " .
       "WHERE ports.system				= 'FreeBSD' ".
       "  and ports.primary_category_id			= categories.id " .
       "  and ports.id					= watch_port.port_id " .
       "  and watch_port.watch_id			= $WatchID " .
       "  and ports.last_change_log_id 			= change_log.id ";

$sql .= " order by $sort ";
//$sql .= " limit 20";

//$Debug=1;
if ($Debug) {
   echo $sql;
}

$result = mysql_query($sql, $db);
if (!$result) {
   echo mysql_error();
}
//$HTML = "</tr></td><tr>";

$HTML .= '<tr><td>';

// get the list of topics, which we need to modify the order
$NumPorts=0;

$LastCategory='';
$GlobalHideLastChange = "N";
while ($myrow = mysql_fetch_array($result)) {
   $NumPorts++;
   if ($ShowCategoryHeaders) {
      $Category = $myrow["category"];

      if ($LastCategory != $Category) {
         $LastCategory = $Category;
         $URL_Category = "category.php3?category=" . $myrow["category_id"];
         $HTML .= '<h3><a href="' . $URL_Category . '">Category ' . $myrow["category"] . '</a></h3>';
      }
   }

   $HTML .= freshports_PortDetails($myrow, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription);
//   include("./include/port-basics.php");
}

}
  $HTML .= "</td></tr>\n";

   $HTML .= "<tr><td>$NumPorts ports found</td></tr>\n";
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
<? include("./include/footer.php") ?>
</body>
</html>
