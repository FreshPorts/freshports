<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<table width="100%">
<tr><td>Welcome to the freshports.org test page. This site is not yet in production. We are still
testing. Information found here may be widely out of date and/or inaccurate.  Use at your own risk. 
See also <a href="ports.php3">freshports by ports</a>.
</td></tr>
<tr><td>
You can sort each column by clicking on the header.  e.g. click on <b>Category</b> to sort by category.
</td></td>
  <tr>
    <td bgcolor="#AD0040" height="29"><big><big><font color="#FFFFFF">freshports</font></big></big></td>
  </tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

require( "_private/commonlogin.php3");
require( "_private/getvalues.php3");

$cache_file     =       "/tmp/freshports.org.cache.categories";
$LastUpdateFile =       "/www/freshports.org/work/msgs/lastupdate";

// make sure the value for $sort is valid

//echo "sort is $sort\n";

switch ($sort) {
   case "category":
   case "count":
   case "description":
      $sort = $sort;
      $cache_file .= ".$sort";
      break;

   default:
      $sort ="updated desc";
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
//   echo 'time to update the cache';

$sql = "select max(ports.last_update) as updated, count(ports.id) as count, " .
       "categories.id as category_id, categories.name as category, categories.description as description ".
       "from ports, categories ".
       "WHERE ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id " .
       "group by categories.id ";

//$sql .=  " order by $sort";

//echo $sql, "\n";
//echo $sort, "\n";

$result = mysql_query($sql, $db);

$HTML .= '<tr><td>';

$HTML .= '<table width="100%" border=1><tr>';
$HTML .= '<td><a href="categories.php3?sort=category"><b>Category<b></a></b></td>';
$HTML .= '<td><a href="categories.php3?sort=count"><b>Count</b></a></td>';
$HTML .= '<td><a href="categories.php3?sort=description"><b>Description</b></a></td>';
$HTML .= '<td><a href="categories.php3?sort=updated desc"><b>Last Update</b></a></td>';
$HTML .= '</tr>';

$HTML .= '<tr>';
// get the list of topics, which we need to modify the order



$NumTopics=0;
while ($myrow = mysql_fetch_array($result)) {
//        $URL_Category = "http://www.freebsd.org/ports/" . $myrow["category"];
        $URL_Category = "category.php3?category=" . $myrow["category_id"];

	$HTML .= '<td valign="top"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></td>';
	$HTML .= '<td valign="top">' . $myrow["count"] . '</td>';
	$HTML .= '<td valign="top">' . $myrow["description"] . '</td>';
        $HTML .= '<td valign="top"><font size="-1">' . $myrow["updated"] . '</font></td>';
	$HTML .= "</tr>\n";
}

$HTML .= '</tr>';

mysql_free_result($result);


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
</table>
</body>
</html>
