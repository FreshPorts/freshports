<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - watch categories</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<table width="100%">
<tr><rd>Welcome to the freshports.org test page. This site is not yet in production. We are still
testing. Information found here may be widely out of date and/or inaccurate.  Use at your own risk. 
See also <a href="ports.php3">freshports by ports</a>.
</td></tr>
  <tr>
    <td bgcolor="#AD0040" height="29"><big><big><font color="#FFFFFF">freshports - watch categories</font></big></big></td>
  </tr>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

require( "/www/freshports.org/_private/commonlogin.php3"); 
require( "/www/freshports.org/_private/getvalues.php3");

//echo "UserID=$UserID";

$cache_file     =       "/tmp/freshports.org.cache." . basename($PHP_SELF);
$LastUpdateFile =       "/www/freshports.org/work/msgs/lastupdate";

// make sure the value for $sort is valid

switch ($sort) {
   case "category":
   case "count":
   case "description":
      $cache_file .= ".$sort";

/*
   case "updated desc":
      break;
*/
   default:
      $sort ="updated desc";
      $cache_file .= ".updated";
}

if ($UserID) {
  $cache_file .= ".user";
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

// hardcoded sort **************************************
$sort = "category";

$sql .=  " order by $sort";

/*
echo $sql, "\n";
echo $sort, "\n";
*/

$result = mysql_query($sql, $db);

$HTML .= '<tr><td>' . "\n";

if ($UserID) {
  $HTML .= '<form action="<?php echo $PHP_SELF?>" method="POST">';
}

$HTML .= "\n" . '<table width="100%" border=1>' . "\n";

/*

$HTML .= "<tr>";
if ($UserID) {                  
   $HTML .= '<td valign="bottom" width="40" valign="top" align="center"><b>Add to Watch List</b></td>';
} 
$HTML .= '<td valign="bottom" width="*"><b>Category</b></td>';
$HTML .= '<td valign="bottom" width="300"><b>Description</b></td>';

$HTML .= '</tr>' . "\n";
*/

// get the list of topics, which we need to modify the order

$NumCategories = 0;
while ($myrow = mysql_fetch_array($result)) {
   $NumCategories++;
   $rows[$NumCategories-1]=$myrow;
}   

$RowCount = ceil($NumCategories / (double) 4);
$Row = 0;
for ($i = 0; $i < $NumCategories; $i++) {
//while ($myrow = mysql_fetch_array($result)) {
   $Row++;

   if ($Row > $RowCount) {
      $HTML .= "</td>\n";
      $Row = 1;
   }

   if ($Row == 1) {
      $HTML .= '<td valign="top">';
   }

   if ($UserID) {  
   $HTML .= '<input type="checkbox" name="category_' . $rows[$i]["category_id"] . '" value="ON">';
   }

//        $URL_Category = "http://www.freebsd.org/ports/" . $rows[$i]["category"];
   $URL_Category = "category.php3?category=" . $rows[$i]["category_id"];

   $HTML .= ' <a href="' . $URL_Category . '">' . $rows[$i]["category"] . '</a>';
   $HTML .= "<br>\n";
}

if ($Row != 1) {
   $HTML .= "</td></tr>\n";
}

$HTML .= "</table>\n";

mysql_free_result($result);


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

<input TYPE="submit" VALUE="update watch list" name="submit">
<input TYPE="reset"  VALUE="reset form">

<?
if ($UserID) {
   echo '</form>';
}
?>

</body>
</html>
