<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<table width="100%" border="0">
<tr>
 <td colspan="2">
 <font size="+4">freshports</font>
 </td>
</tr>


<tr><td colspan="2">Welcome to the freshports.org test page. This site is not yet in production. We are still
testing. Information found here may be widely out of date and/or inaccurate.  Use at your own risk.
See also <a href="categories.php3">freshports by category</a>.
</td></tr>
<tr><td colspan="2">
Note: <font size="-1">[refresh]</font> indicates a port for which the Makefile, 
pkg/DESC, or pkg/COMMENT has changed and has not yet been updated within 
FreshPorts.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td colspan="5" bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+1">freshports - last 20 ports</font></td>
  </tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
require( "/www/freshports.org/_private/freshports.php3");

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

//$UpdateCache = 1;

if ($UpdateCache == 1) {
//   echo 'time to update the cache';

$sql = "select ports.id, ports.name as port, ports.id as ports_id, ports.last_update as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage " .
       "from ports, categories  ".
       "WHERE ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id ";

$sql .= "order by $sort limit 20";

//echo $sql;

$result = mysql_query($sql, $db);

$HTML = "</tr></td><tr>";

$HTML .= '<tr><td>';

// get the list of topics, which we need to modify the order
$NumTopics=0;

while ($myrow = mysql_fetch_array($result)) {
   $HTML .= "<dl>";

   $HTML .= "<b>" . $myrow["port"];
   if (strlen($myrow["version"]) > 0) {
      $HTML .= '-' . $myrow["version"];
   }

   $HTML .= "</b>";

   // indicate if this port needs refreshing from CVS
   if ($myrow["needs_refresh"] == "Y") {
      $HTML .= ' <font size="-1">[refresh]</font>';
   }

   $URL_Category = "category.php3?category=" . $myrow["category_id"];
   $HTML .= ' <font size="-1"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></font>';


   $HTML .= "<dd>";
                
   // description   
   $HTML .= $myrow["short_description"] . "<br>\n";

   // maintainer
   $HTML .= 'Maintained by: <a href="mailto:' . $myrow["maintainer"];
   $HTML .= '?cc=ports@FreeBSD.org&amp;subject=FreeBSD%20Port:%20' . $myrow["port"] . "-" . $myrow["version"] . '">';
   $HTML .= $myrow["maintainer"] . '</a></br>' . "\n";

   $HTML .= 'last change committed by ' . $myrow["committer"];  // separate lines in case committer is null                   
                   
   $HTML .= ' on <font size="-1">' . $myrow["updated"] . '</font><br>' . "\n";

   $HTML .= $myrow["update_description"] . "<br>" . "\n";

   // Long descripion
   $HTML .= '<a HREF="port-description.php3?port=' . $myrow["id"] .'">Description</a>';

   $HTML .= ' <b>:</b> ';

   // changes
   $HTML .= '<a HREF="http://www.FreeBSD.org/cgi/cvsweb.cgi/ports/' . 
            $myrow["category"] . '/' .  $myrow["port"] . '">Changes</a>';
   $HTML .= ' <b>:</b> ';

   // download
   $HTML .= '<a HREF="ftp://ftp.FreeBSD.org/pub/FreeBSD/branches/-current/ports/' . 
            $myrow["category"] . '/' .  $myrow["port"] . '.tar">Download Port</a>';

   if ($myrow["package_exists"] == "Y") {
      // package
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="ftp://ftp.FreeBSD.org/pub/FreeBSD/FreeBSD-stable/packages/' .
               $myrow["category"] . '/' .  $myrow["port"] . "-" . $myrow["version"] . '.tgz">Package</a><p></p>';
      $HTML .= "</dd>";
      $HTML .= "</dl>";
   }

     $HTML .= "</dd>";
     $HTML .= "</dl>" . "\n";
}

  $HTML .= "</td></tr>\n";
//$HTML .= '</tr>';

mysql_free_result($result);


//$HTML .= '</table>';
//$HTML .= '</td></tr>';

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
</body>
</html>
