<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">

<?
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
require( "/www/freshports.org/_private/freshports.php3");

if (!$category) {                        
   $category = 1;
}

$title = freshports_Category_Name($category, $db);

?>

<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - <? echo $title ?></title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<table width="100%">
<tr><rd>Welcome to the freshports.org test page. This site is not yet in production. We are still
testing. Information found here may be widely out of date and/or inaccurate.  Use at your own risk.       
</td></tr>
  <tr>
    <td bgcolor="#AD0040" height="29"><big><big><font color="#FFFFFF">freshports - <? echo $title ?></font></big></big></td>
  </tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

// make sure the value for $sort is valid

$cache_file     =       "/tmp/freshports.org.cache.category." . $category;
$LastUpdateFile =       "/www/freshports.org/work/msgs/lastupdate";

switch ($sort) {
   case "port":
      $sort = "port, updated desc";
      $cache_file .= ".port";
      break;
/*
   case "updated":
      $sort = "updated desc, port";
      break;
*/
   default:
      $sort ="port";
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

if ($UpdateCache == 1) {
   echo 'time to update the cache';

$sql = "select ports.id, ports.name as port, ports.id as ports_id, ports.last_update as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage  " .
       "from ports, categories  ".
       "WHERE ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id " .
       "and categories.id = $category ";

$sql .= "order by $sort";

//echo $sql;

$result = mysql_query($sql, $db);

$HTML .= '<tr><td>';

$HTML .= '<table width="*" border=1>';

// get the list of topics, which we need to modify the order
$NumTopics=0;
while ($myrow = mysql_fetch_array($result)) {
   $NumTopics++;

   include("/www/freshports.org/_private/port-basics.inc");
}

$HTML .= '</tr>';

mysql_free_result($result);

$HTML .= "<p>$NumTopics ports found</p>\n";

$HTML .= '</td></tr>';

$HTML .= '</table>';
$HTML .= '</td></tr>';
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
  <tr>
    <td height="21"></td>
  </tr>
  <tr>
    <td height="35" valign="bottom" align="center">[ <a href>Home</a> | Topics | <a
    href="chronological.php3">Index</a> | <a href="help.html">Web Resources</a> | <a
    href="booksmags.html">Books/Mags</a>&nbsp;| <a href="topology.html">Topology</a> | <a
    href="search.html">Search</a> | <a href="feedback.html">Feedback</a> | <a href="faq.html">FAQ</a>
    | <a href="phorum/list.php3?num=1">Forum</a> ]</td>
  </tr>
</table>
</body>
</html>
