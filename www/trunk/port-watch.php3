<?
   # $Id: port-watch.php3,v 1.17 2001-10-02 17:35:59 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");
   require("./include/getvalues.php");


   freshports_Start("title",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>
<?

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header("Location: login.php3?origin=" . $PHP_SELF);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (!$category || $category != strval(intval($category))) {
   $category = 0;                                     
} else {                                              
   $category = intval($category);                     
}

$categoryname = freshports_Category_Name($category, $db);

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
  
   if ($ports) {
     $PortCount = count($ports);
     echo "PortCount= $PortCount<br>\n";
     while (list($key, $value) = each($ports)) {
        echo "element $key = '$value'<br>\n";
     }
   }

   echo "submitting<br>\n";
       
   echo "$sql_get_watch_ID<br>\n";
*/
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
//      if ($result) {
//         echo "created<br>";       
//      } else {
//         echo "failed<br>";
//      }

      // refetch our watch id
      $result = mysql_query($sql_get_watch_ID, $db);

      $myrow = mysql_fetch_array($result);
      $WatchID = $myrow["id"];
//      echo "watchid is $WatchID<br>\n";

      $sql_insert = "insert into user_watch (user_id, watch_id) values ($UserID, $WatchID)";
      $result = mysql_query($sql_insert, $db);

//      echo "creating user_watch entry: $sql_insert<br>\n";
   }

// delete existing watch_category entries for this watch
   $sql = "select watch_port.port_id as id " .
          "from watch_port, ports " .
          "where watch_port.port_id = ports.id " .
          "and ports.primary_category_id = $category";

//   echo "$sql<br>\n";

   $result = mysql_query($sql, $db);
   $NumPorts = 0;
   while ($myrow = mysql_fetch_array($result)) {
      $NumPorts++;
      $rows[$NumPorts-1]=$myrow;
   }  
   
//   echo "deleting<br>\n";       
   for ($i = 0; $i < $NumPorts; $i++) {
      $sql = "delete from watch_port where watch_id = $WatchID and port_id = " . $rows[$i]["id"];
//      echo "$sql<br>\n";
      $result = mysql_query($sql, $db);
   }
     
// insert new stuff
//   echo "inserting new stuff now<br>\n";
    
   // make sure we are pointing at the start of the array.
   
   if ($ports) {
      reset($ports);
      while (list($key, $value) = each($ports)) {
         $sql = "insert into watch_port (watch_id, port_id, changes_new, changes_modify, changes_delete) ".
                "values ($WatchID, $value, 'Y', 'Y', 'Y')";
   
//      echo "port $value has been selected<br>\n";

         $result = mysql_query($sql, $db);
         ${"port_".$value} = 1;
      }
   }
      
   header("Location: watch-categories.php3");  /* Redirect browser to PHP web site */
   exit;  /* Make sure that code below does not get executed when we redirect. */
      
} else {
         
   if ($UserID != '') {
         
   // read the users current watch information from the database
   $sql = "select watch_port.port_id " .
          "from watch_port, watch " .
          "where watch_port.watch_id = watch.id " . 
          "  and watch.owner_user_id = $UserID";
      
   $result = mysql_query($sql, $db);
      
   // read each value and set the variable accordingly
   while ($myrow = mysql_fetch_array($result)) {
      // we use these to see if a particular port is selected
      ${"port_".$myrow["port_id"]} = 1;
   }
   }
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - watch categories</title>
</head>

 <? include("./include/header.php") ?>
<table width="100%" border="0">
</tr>
<tr><td colspan="2">This page shows the ports within a specific category which are in your watch list.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
  <tr>
     <td bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - watch ports (<em><? echo $categoryname ?>)</em></font></td>
  </tr>
<tr><td>
<?
if (!$UserID) {
echo '<font size="+1">You are not logged in, perhaps you should <a href="login.php3">do that</a> first.</font>';
echo '</td></tr><tr><td>';
} else {
?>
<p>
This screen contains a list of the ports in category <em><?echo $categoryname ?></em>. 
The ports with a tick beside them are already in your watch list. 
When one of the ports in your watch list changes, you will be notified by email if
you have selected a notification frequency within your <a href="customize.php3">personal preferences</a>.
</p>
<p>
[D] indicates a port which has been removed from the tree.
</p>
<? } ?>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

//echo "UserID=$UserID";

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

echo "\n</td></tr>\n<tr><td>";

//$UpdateCache = 1;
if ($UpdateCache == 1 && $UserID) {
//   echo 'time to update the cache';

$sql = "select id, name, status  ".
       "from ports ".
       "WHERE primary_category_id = $category " .
       "order by name";

//echo $sql, "<br>\n";

$result = mysql_query($sql, $db);

$HTML .= '<tr><td>' . "\n";

if (mysql_num_rows($result)) {

   if ($UserID) {
      $HTML .= '<form action="' . $PHP_SELF . "?category=$category". '" method="POST">';
   }

   $HTML .= "\n" . '<table cellpadding=12 border=1>' . "\n";

   // get the list of topics, which we need to modify the order

   $NumPorts = 0;
   while ($myrow = mysql_fetch_array($result)) {
      $NumPorts++;
      $rows[$NumPorts-1]=$myrow;
   }

   // save the number of categories for when we submit
   $HTML .= '<input type="hidden" name="NumPorts" value="' . $NumPorts . '">';

   $RowCount = ceil($NumPorts / (double) 4);
   $Row = 0;
   for ($i = 0; $i < $NumPorts; $i++) {
      $Row++;

      if ($Row > $RowCount) {
         $HTML .= "</td>\n";
         $Row = 1;
      }

      if ($Row == 1) {
         $HTML .= '<td valign="top">';
      }

      $HTML .= '<input type="checkbox" name="ports[]" value="'. $rows[$i]["id"] .'"';

      if (${"port_".$rows[$i]["id"]}) {
         $HTML .= " checked ";
      }

      $HTML .= '>';

      $URL_Category = "port-description.php3?port=" . $rows[$i]["id"];

      $HTML .= ' <a href="' . $URL_Category . '">' . $rows[$i]["name"] . '</a>';

      if ($rows[$i]["status"] == 'D') {
         $HTML .= " [D]";
      }

      $HTML .= "<br>\n";
   }

   if ($Row != 1) {
      $HTML .= "</td></tr>\n";
   }

   $HTML .= "</table>\n";

} else {
   echo "no ports found.  perhaps this is an invalid category id.";
}

mysql_free_result($result);


$HTML .= '</td></tr>';

echo $HTML;                                                   

} else {
//   echo 'looks like I\'ll read from cache this time';
   if (file_exists($cache_file)) {
      include($cache_file);
   }
}

</script>
<tr><td>

<input TYPE="submit" VALUE="update watch list" name="submit">
<input TYPE="reset"  VALUE="reset form">
</td></tr>
<?
if ($UserID) {
   echo '</form>';
}
?>

</table>

</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>

</body>
</html>
