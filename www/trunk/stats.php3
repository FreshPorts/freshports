<?
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
require( "/www/freshports.org/_private/freshports.php3");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">
 <? include("/www/freshports.org/_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">This is the first attempt at any type of stats.  If anyone would like to
take this data and create a very flash set of graphics, like webalizer, I and the rest of the
FreshPorts users will be grateful.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td colspan="5" bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">most watched ports</font></td>
  </tr>
<tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

$cache_file     =       "/tmp/freshports.org.cache." . basename($PHP_SELF);
$LastUpdateFile =       "/www/freshports.org/work/msgs/lastupdate";

// make sure the value for $sort is valid

switch ($sort) {
/* sorting by port is disabled. Doesn't make sense to do this
   case "port":
      $sort = "version, updated desc";
      $cache_file .= ".port";
      break;
*/
//   case "updated":
//      $sort = "updated desc, port";
//      break;

   default:
      $sort ="updated desc, category, version";
      $cache_file .= ".updated";
}

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

if ($UpdateCache == 1) {
//   echo 'time to update the cache';

$sql = "select ports.id, ports.name as port, count(*) as count, " .
       "categories.name as category, categories.id as category_id, ports.version as version ".
       "from ports, categories, watch_port   ".
       "WHERE ports.system              = 'FreeBSD' ".
       "  and ports.primary_category_id = categories.id " .
       "  and ports.id                  = watch_port.port_id " .
       "group by ports.id, ports.name, category, category_id, version " .
       "order by count desc, ports.name " .
       "limit 20";


//echo $sql;

$result = mysql_query($sql, $db);
if (!$result) {
   echo "<br>\n" . mysql_error() . "<br>\n";
}

$HTML = "</tr></td><tr>";
//$HTML = "<td><table>";
$HTML = '<tr><td><font size="+1">Port</font></td><td><font size="+1">Number of people watching it</font></td></tr>';
while ($myrow = mysql_fetch_array($result)) {
   $HTML .= "<tr><td align='left'>";
//   include("/www/freshports.org/_private/port-basics.inc");

   $HTML .= "<dl>";

   $HTML .= '<a HREF="port-description.php3?port=' . $myrow["id"] .'">';
   $HTML .= $myrow["port"];

/*
   if (strlen($myrow["version"]) > 0) {
      $HTML .= '-' . $myrow["version"];
   }
*/

   $HTML .= '</a>';

   // indicate if this port needs refreshing from CVS
   if ($myrow["status"] == "D") {
      $HTML .= ' <font size="-1">[deleted - port removed from ports tree]</font>';
   } else {
      // no sense saying they need a refresh if they've been deleted.
      if ($myrow["needs_refresh"]) {
         $HTML .= ' <font size="-1">[refresh]</font>';
      }
   }

/*
   if (!$HideCategory) {
      $URL_Category = "category.php3?category=" . $myrow["category_id"];
      $HTML .= ' <font size="-1"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></font>';
   }
*/
   $HTML .= "<p></p></dd>";
   $HTML .= "</dl>" . "\n";

   $HTML .= "</td>";
   $HTML .= "<td align = 'left'>";
   $HTML .= $myrow["count"];
   $HTML .= "</td>";
   $HTML .= "</tr>";
}

$HTML .= "<tr><td></td><td align='right'>as of " . date("Y/m/d H:i", time()) . "</td></tr>";

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
}

</script>
</table>
</td>
  <td valign="top" width="*">
   <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
</table>
</tr>
</table>
<? include("/www/freshports.org/_private/footer.inc") ?>
</body>
</html>
