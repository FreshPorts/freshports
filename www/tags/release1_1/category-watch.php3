<?
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - watch categories</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<table width="100%">
<tr><td>Welcome to freshports.org. See also <a href="ports.php3">freshports by ports</a>.
</td></tr>
  <tr>
    <td bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - watch categories</font></td>
  </tr>
<tr><td>
<?
if (!$UserID) {
echo '<font size="+1">You are not logged in, perhaps you should <a href="login.php3">do that</a> first.</font>';
echo '</td></tr><tr><td>';
} else {
?>
This screen contains a list of the port categories. The categories on your watch list are those with a tick beside 
them. When a port changes in one of your watch categories, you will be notified by email if you have selected a 
notification frequency within your <a href="customize.php3">personal preferences</a>.
<? } ?>
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

echo '<tr><td>' . "\n";

// find out the watch id for this user's main watch list
$sql_get_watch_ID = "select watch.id ".
                    "from watch ".
                    "where watch.owner_user_id = $UserID ".
                    "and   watch.system        = 'FreeBSD' ".
                    "and   watch.name          = 'main'";

if ($submit) {
/*
   while (list($name, $value) = each($HTTP_POST_VARS)) {
      echo "$name = $value<br>\n";
   }
*/
//   echo "submitting<br>\n";

//   echo "$sql_get_watch_ID<br>\n";

   $result = mysql_query($sql_get_watch_ID, $db);
   if(mysql_numrows($result)) {
//      echo "results were found for that<br>\n";
      $myrow = mysql_fetch_array($result);
      $WatchID = $myrow["id"];
   } else {
      // create their main list for them
      $sql_create = "insert into watch (name, system, owner_user_id) values ('main', 'FreeBSD', $UserID)";
//      echo "creating new watch: $sql_create<br>\n";
      $result = mysql_query($sql_create, $db);
      if ($result) {
         echo "created<br>";
      } else {
         echo "failed<br>";
      }

      // refetch our watch id
      $result = mysql_query($sql_get_watch_ID, $db);

      $myrow = mysql_fetch_array($result);
      $WatchID = $myrow["id"];
      echo "watchid is $WatchID<br>\n";

      $sql_insert = "insert into user_watch (user_id, watch_id) values ($UserID, $WatchID)";
      $result = mysql_query($sql_insert, $db);

//      echo "creating user_watch entry: $sql_insert<br>\n";
   }

   // delete existing watch_category entries for this watch
   $sql = "delete from watch_category where watch_id = $WatchID";

//   echo "deleting existing stuff: $sql<br>\n";

   $result = mysql_query($sql, $db);


   for ($i = 1; $i <= $NumCategories; $i++) {
      if (${"category_".$i}) {
         $sql = "insert into watch_category (watch_id, category_id, changes_new, changes_modify, changes_delete) ". 
                "values ($WatchID, $i, 'Y', 'Y', 'Y')";

//         echo "Category $i has been selected<br>\n";

         $result = mysql_query($sql, $db); 
      }
   }

} else {

   if ($UserID != '') {

   // read the users current watch information from the database
   $sql = "select watch_category.category_id " .
          "from watch_category, watch " .
          "where watch_category.watch_id = watch.id " .
          "  and watch.owner_user_id     = $UserID";

   $result = mysql_query($sql, $db);

   // read each value and set the variable accordingly
   while ($myrow = mysql_fetch_array($result)) {
      ${"category_".$myrow["category_id"]} = "ON";
   }
   }
}

echo "\n</td></tr>\n<tr><td>";

//$UpdateCache = 1;
if ($UpdateCache == 1 && $UserID) {
//   echo 'time to update the cache';

$sql = "select categories.id as category_id, categories.name as category, categories.description as description ".
       "from categories ".
       "WHERE categories.system = 'FreeBSD' " .
       "order by category";

echo $sql, "<br>\n";

$result = mysql_query($sql, $db);

$HTML .= '<tr><td>' . "\n";

if ($UserID) {
  $HTML .= '<form action="' . $PHP_SELF . '" method="POST">';
}

$HTML .= "\n" . '<table border=1 cellpadding=12>' . "\n";

// get the list of topics, which we need to modify the order

$NumCategories = 0;
while ($myrow = mysql_fetch_array($result)) {
   $NumCategories++;
   $rows[$NumCategories-1]=$myrow;
}

// save the number of categories for when we submit
$HTML .= '<input type="hidden" name="NumCategories" value="' . $NumCategories . '">';

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
      $HTML .= '<input type="checkbox" name="category_' . $rows[$i]["category_id"] . '" value="ON"';

      if (${"category_".$rows[$i]["category_id"]}) $HTML .= " checked ";

      $HTML .= '>';
   }

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

</script>
</table>

<input TYPE="submit" VALUE="update watch list" name="submit">
<input TYPE="reset"  VALUE="reset form">
 <tr>           
    <td height="21"></td>
  </tr>
<?
if ($UserID) {
   echo '</form>';
}
?>

</body>
</html>
