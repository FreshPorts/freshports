<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">
 <? include("./_private/header.inc") ?>
<table width="100%" border="0">
</tr><tr><td colspan="2">This page lists the categories sorted by various categories.
</td></tr>
<tr><td colspan="2">
You can sort each column by clicking on the header.  e.g. click on <b>Category</b> to sort by category.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
  <tr>
    <td colspan="4" bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - list of categories</font></td>
  </tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");

// make sure the value for $sort is valid

//echo "sort is $sort\n";

switch ($sort) {
   case "category":
   case "count":
   case "description":
      $sort = $sort;
      $cache_file .= ".$sort";
      break;

   case "lastupdate":
      $sort ="updated desc";
      $cache_file .= ".updated";
      break;

   default:
      $sort = "category";
      $cache_file .= ".category";
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

//$UpdateCache = 1;
if ($UpdateCache == 1) {
//   echo 'time to update the cache';

$sql = "select max(ports.last_update) as updated, count(ports.id) as count, " .
       "categories.id as category_id, categories.name as category, categories.description as description ".
       "from ports, categories ".
       "WHERE ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id " .
       "group by categories.id ";

$sql .=  " order by $sort";

//echo $sql, "\n";
//echo $sort, "\n";

$result = mysql_query($sql, $db);

$HTML .= freshports_echo_HTML('<tr><td>');

//$HTML .= freshports_echo_HTML('<table width="100%" border=6><tr>');
$HTML .= freshports_echo_HTML('<tr>');

if ($sort == "category") {
   $HTML .= freshports_echo_HTML('<td><b>Category</b></td>');
} else {
   $HTML .= freshports_echo_HTML('<td><a href="categories.php3?sort=category"><b>Category<b></a></td>');
}


if ($sort == "count") {
   $HTML .= freshports_echo_HTML('<td><b>Count</b></td>');
} else {
   $HTML .= freshports_echo_HTML('<td><a href="categories.php3?sort=count"><b>Count</b></a></td>');
}

if ($sort == "description") {
   $HTML .= freshports_echo_HTML('<td><b>Description</b></td>');
} else {
   $HTML .= freshports_echo_HTML('<td><a href="categories.php3?sort=description"><b>Description</b></a></td>');
}

if ($sort == "updated desc") {
   $HTML .= freshports_echo_HTML('<td><b>Last Update</b></td>');
} else {
   $HTML .= freshports_echo_HTML('<td><a href="categories.php3?sort=lastupdate"><b>Last Update</b></a></td>');
}

$HTML .= freshports_echo_HTML('</tr>');

$HTML .= freshports_echo_HTML('<tr>');
// get the list of topics, which we need to modify the order



$NumTopics=0;
while ($myrow = mysql_fetch_array($result)) {
//        $URL_Category = "http://www.freebsd.org/ports/" . $myrow["category"];
        $URL_Category = "category.php3?category=" . $myrow["category_id"];

	$HTML .= freshports_echo_HTML('<td valign="top"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></td>');
	$HTML .= freshports_echo_HTML('<td valign="top">' . $myrow["count"] . '</td>');
	$HTML .= freshports_echo_HTML('<td valign="top">' . $myrow["description"] . '</td>');
        $HTML .= freshports_echo_HTML('<td valign="top"><font size="-1">' . $myrow["updated"] . '</font></td>');
	$HTML .= freshports_echo_HTML("</tr>\n");
}

$HTML .= freshports_echo_HTML('</tr>');

mysql_free_result($result);


$HTML .= freshports_echo_HTML('</table>');
//$HTML .= freshports_echo_HTML('</td></tr>');


freshports_echo_HTML_flush();

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
</td>
  <td valign="top" width="*">
    <? include("./_private/side-bars.php3") ?>
 </td>
</tr>
</table>

<? include("./_private/footer.inc") ?>

</body>
</html>
