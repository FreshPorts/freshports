<?
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
require( "/www/freshports.org/_private/freshports.php3");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - watch categories</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<table width="100%" border="0">
<tr>
 <td colspan="2">
<? include("/www/freshports.org/_private/header.inc") ?>
 </td>
</tr>
<tr><td colspan="2">
This page shows the various categories and indicates which ones contains ports which are on your watch list.
</td></tr>
<td valign="top"><table width="100%">
  <tr>
    <td bgcolor="#AD0040" height="29"><big><big><font color="#FFFFFF">freshports - watch categories</font></big></big></td>
  </tr>
<tr><td valign="top" width="100%">


<table width="100%" border="0">
<tr><td>
<?
if (!$UserID) {
echo '<font size="+1">You are not logged in, perhaps you should <a href="login.php3">do that</a> first.</font>';
} else {
?>
This screen contains a list of the port categories. The categories on your watch list are those with a tick beside 
them. When a port changes in one of your watch categories, you will be notified by email if you have selected a 
notification frequency within your <a href="customize.php3">personal preferences</a>.
<? } ?>

</tr></td>

<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

//echo "UserID=$UserID";

$cache_file     =       "/tmp/freshports.org.cache." . basename($PHP_SELF);
$LastUpdateFile =       "/www/freshports.org/work/msgs/lastupdate";

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

echo '<tr><td align="center">' . "\n";

// find out the watch id for this user's main watch list
$sql_get_watch_ID = "select watch.id ".
                    "from watch ".
                    "where watch.owner_user_id = $UserID ".
                    "and   watch.system        = 'FreeBSD' ".
                    "and   watch.name          = 'main'";

//$UpdateCache = 1;
if ($UpdateCache == 1 && $UserID) {
//   echo 'time to update the cache';

$sql = "select distinct(primary_category_id) as category_id ".
       "from watch, watch_port, ports ".
       "WHERE watch.name          = 'main' ".
       "  and watch.owner_user_id = $UserID ".
       "  and watch_port.watch_id = watch.id ".
       "  and watch_port.port_id  = ports.id";

//echo $sql, "<br>\n";

$result = mysql_query($sql, $db);
$i = 0;
while ($myrow = mysql_fetch_array($result)) {
   $i++;
   $WatchedCategories[$i] = $myrow["category_id"];
}


//$HTML .= '<tr><td>' . "\n";

$HTML .= "\n" . '<table border=1 cellpadding=12>' . "\n";

// get the list of categories to display
$sql = "select categories.id as category_id, categories.name as category, categories.description as description ".
       "from categories ".
       "WHERE categories.system = 'FreeBSD' " .
       "order by category";

$result = mysql_query($sql, $db);  

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

   $URL_Category = "port-watch.php3?category=" . $rows[$i]["category_id"];

   $HTML .= ' <a href="' . $URL_Category . '">' . $rows[$i]["category"] . '</a>';

   if (freshports_in_array($rows[$i]["category_id"], $WatchedCategories)) {
      $HTML .= " * ";
   }
   $HTML .= "<br>\n";
}

if ($Row != 1) {
   $HTML .= "</td></tr>\n";
}

//$HTML .= "</table>\n";

mysql_free_result($result);


//$HTML .= '</td></tr>';

echo $HTML;                                                   

/*
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
*/
} else {
//   echo 'looks like I\'ll read from cache this time';
   if (file_exists($cache_file)) {
      include($cache_file);
   }
}
echo "</table>\n";   
</script>
</table>
</table>
</td>
  <td valign="top" width="*">
   <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
</table>
</body>
<? include("/www/freshports.org/_private/footer.inc") ?>
</html>
