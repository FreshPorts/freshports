<?
	# $Id: ports-new.php,v 1.1.2.4 2002-02-16 23:52:51 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("recently added ports",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

$Debug=1;
?>

<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
	<? freshports_PageBannerText("$FreshPortsTitle - recently added ports", 4); ?>
  </tr>
<tr><td colspan="2">
This page shows the ports which have been recently added to the ports tree.  As such, some information
may be missing (such as description, maintainer, etc.). This information will be obtained during the
next database update.
</td></tr>
<tr><td>
<?

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

// make sure the value for $sort is valid

switch ($sort) {
/* sorting by port is disabled. Doesn't make sense to do this
   case "port":
      $sort = "port, updated desc";
      $cache_file .= ".port";
      break;
*/
//   case "updated":
//      $sort = "updated desc, port";
//      break;

   default:
      $sort ="date_added desc, port";
      $cache_file .= ".updated";
}

srand((double)microtime()*1000000);
$cache_time_rnd =       300 - rand(0, 600);


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
//         echo 'crated after the last database update<br>';
      }
   }
}

$UpdateCache = 1;

if ($UpdateCache == 1) {
//   echo 'time to update the cache';

$sql = "select ports.id, ports.name as port, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, UNIX_TIMESTAMP(ports.date_added) as date_added, ".
       "date_format(date_added, '$FormatDate $FormatTime') as date_added_formatted, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, ports.status, " .
       "ports.broken, ports.forbidden " .
       "from ports, categories ".
       "WHERE ports.system = 'FreeBSD' ".
       "  and ports.primary_category_id       = categories.id " .
       "  and ports.status                    = 'A' ";

/*
$sql .= "order by $sort limit $MaxNumberOfPorts";

if ($Debug) {
   echo $sql;
}

$result = mysql_query($sql, $db);

// get the list of topics, which we need to modify the order
$NumTopics=0;
$ShowLastChange="N";
while ($myrow = mysql_fetch_array($result)) {
   include("./include/port-basics.php");
}

//$HTML .= '</tr>';

//$HTML .= "</table>\n";

mysql_free_result($result);


echo $HTML;

   $fpwrite = fopen($cache_file, 'w');
   if(!$fpwrite) {
      echo 'error on open<br>';
      echo "$errstr ($errno)<br>\n";
      exit;
   } else {
//      echo 'written<br>';
      fputs($fpwrite, $HTML);
      fclose($fpwrite);
   }
} else {
//   echo 'looks like I\'ll read from cache this time';
   if (file_exists($cache_file)) {
      include($cache_file);
   }
*/
echo '<tr><td>Sorry, but we\'ve disabled this page. Sorry about that. With luck, it will be back in FreshPorts2.</td></tr>';
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
