<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  

<?
//$Debug=1;
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");

#
# if no category provided or category is not numeric, try
# category zero.  inval returns zero if non-numeric
#
#echo $category         . "<br>";
#echo intval($category) . "<br>";

#
# append the category id to the cache_file
#
$cache_file .= "." . $category;

$title = "Ports not in latest /usr/ports/INDEX";
?>

<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - <? echo $title ?></title>
</head>

 <? include("./_private/header.inc") ?>

<table width="100%" border="0">
<tr><td>
From time to time, a new /usr/ports/INDEX is issued.  This file represents a snapshot at a given point in time.
This page lists the ports which are not found in that file (i.e. those ports which were added after that snapshot
was taken).  The date shown is the date the port was created.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
  <tr>
    <td bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - <? echo $title ?></font></td>
  </tr>
<script language="php">



$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

// make sure the value for $sort is valid

$LimitRows	= 50;

if (!$start) {
   $start = 1;
}

if ($start < 1) {
   $start = 1;
}

if ($start > 1) {
   $cache_file .= ".$start";

//   echo "adding $start to $cache_file";
}

if ($start > $end) {
   $end = $start + $LimitRows -1;
}

if (!$end) {
   $end = $start + $LimitRows - 1;
}

$sort ="port";

srand((double)microtime()*1000000);
$cache_time_rnd =       300 - rand(0, 600);

$UpdateCache = 0;
if (!file_exists($cache_file)) {
//   echo 'cache does not exist<br>';
   // cache does not exist, we create it
   $UpdateCache = 1;
} else {
//   echo "cache exists and is compared to $LastUpdateFile<br>";
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

$sql = "select ports.id, ports.name as port, ports.id as ports_id, ports.last_update as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, UNIX_TIMESTAMP(ports.date_created) as date_created, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, ports.status, " .
       "date_format(date_created, '$FormatDate $FormatTime') as date_created_formatted, ".
       "ports.broken, ports.forbidden " .
       "from ports, categories  ".
       "WHERE            ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id " .
       "and ports.found_in_index      = 0 " .
       "and ports.status              = 'A'";

/*
if ($next) {
   $sql .= "and ports.name > '$next' ";
}
*/

$sql .= "order by $sort";

//$sql .= " limit $LimitRows";

if ($Debug) {
   echo $sql;
   echo "GlobalHideLastChange = $GlobalHideLastChange\n";
}

$result = mysql_query($sql, $db);
$NumRows = mysql_num_rows($result);
if ($end > $NumRows) {
//   echo "end was $end and is now $NumRows";
   $end = $NumRows;
}

if ($NumRows == 0) {
   $HTML .= freshports_echo_HTML("no results found.  Is this a valid category id?<br>\n");
} else {

for ($i = 0; $i < $NumRows; $i++) {
   $myrow = mysql_fetch_array($result);
   $rows[$i]=$myrow;
}

$HTML .= freshports_echo_HTML('<tr><td>');

$HTML .= freshports_echo_HTML('<table width="*" border=0>');

// get the list of topics, which we need to modify the order
$LastPort = '';

$HTML .= freshports_echo_HTML("<tr><td>showing ");
if ($start == 1 and $end == $NumRows) {
   $HTML .= freshports_echo_HTML("all");
} else {
   $HTML .= freshports_echo_HTML($start . " to " . $end);
}

$HTML .= freshports_echo_HTML(" of $NumRows ports</td></tr>\n");

//$HTML .= freshports_echo_HTML("<tr><td>");
//$HTML .= freshports_echo_HTML("<br>start = $start, end = $end, LimitRows = $LimitRows<br>\n");

$ShowPortCreationDate = 1;
for ($i = $start; $i <= $end; $i++) {
   $myrow = $rows[$i-1];

   include("./_private/port-basics.inc");
   $LastPort = $myrow["port"];
} // end for

$HTML .= freshports_echo_HTML('</tr>');

//$HTML .= freshports_echo_HTML("<p>$NumRows ports found</p>\n");

$HTML .= freshports_echo_HTML('</td></tr>');

$HTML .= freshports_echo_HTML('</table>');

} // results found

// here $i will be $end + 1
if ($end < $NumRows) {
   $HTML .= freshports_echo_HTML('</td></tr><tr><td><a href=' . basename($PHP_SELF) . "?category=$category&start=". ($end+1));
   $HTML .= freshports_echo_HTML(">next page</a></td></tr>");
}

if ($start > 1) {
   $HTML .= freshports_echo_HTML('</td></tr><tr><td><a href=' . basename($PHP_SELF) . "?category=$category");
   $temp = $start - $LimitRows - 1;
   if ($temp > 1) {
      $HTML .= freshports_echo_HTML("&start=" . $temp);
   }
   $HTML .= freshports_echo_HTML(">previous page</a></td></tr>"); 
}

$HTML .= freshports_echo_HTML('</td></tr>');
echo $HTML;      

   if ($NumRows != 0) {
      $fpwrite = fopen($cache_file, 'w');
      if(!$fpwrite) {                      
         echo 'error on open<br>';
         echo "$errstr ($errno)<br>\n";
         exit;                  
      } else {                            
//         echo 'written<br>';             
         fputs($fpwrite, $HTML);         
         fclose($fpwrite);
      }
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
   <? include("./_private/side-bars.php3") ?>
 </td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
