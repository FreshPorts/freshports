<?
   # $Id: port-watch.php,v 1.1.2.2 2002-01-05 03:37:35 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");
   require("./include/getvalues.php");


// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header("Location: login.php?origin=" . $PHP_SELF);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (!$category || $category != strval(intval($category))) {
   $category = 0;                                     
} else {                                              
   $category = intval($category);                     
}

$categoryname = freshports_Category_Name($category, $db);

// find out the watch id for this user's main watch list
$sql_get_watch_ID = "select watch_list.id ".
                    "  from watch_list ".
                    " where watch_list.user_id = $UserID ".
                    "   and watch_list.name    = 'main'";

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
   $result = pg_exec($db, $sql_get_watch_ID);
   $numrows = pg_numrows($result);
   if($numrows) {
//      echo "results were found for that<br>\n";
      $myrow = pg_fetch_array ($result, 0);
      $WatchID = $myrow["id"];
   } else {
      // create their main list for them
      $sql_create = "insert into watch_list (name, owner_user_id) values ('main', $UserID)";
//      echo "creating new watch: $sql_create<br>\n";
      $result = pg_exec($db, $sql_create);
//      if ($result) {
//         echo "created<br>";       
//      } else {
//         echo "failed<br>";
//      }

      // refetch our watch id
      $result = pg_exec ($db, $sql_get_watch_ID);

      $myrow = pg_fetch_array ($result, 0);
      $WatchID = $myrow["id"];
//      echo "watchid is $WatchID<br>\n";

   }

// delete existing watch_category entries for this watch
	$sql = "delete from watch_list_element where exists (
	        select element.id
	          from ports, element
	         where watch_list_element.watch_list_id = $WatchID 
	           and watch_list_element.element_id    = element.id 
	           and ports.element_id                 = element.id 
	           and ports.category_id                = $category)";


	$result = pg_exec ($db, $sql);
     
// insert new stuff
//   echo "inserting new stuff now<br>\n";
    
   // make sure we are pointing at the start of the array.
   
   if ($ports) {
      reset($ports);
      while (list($key, $value) = each($ports)) {
         $sql = "insert into watch_list_element (watch_list_id, element_id) ".
                "values ($WatchID, $value)";
   
//      echo "port $value has been selected<br>\n";

         $result = pg_exec ($db, $sql);
         ${"port_".$value} = 1;
      }
   }
      
   header("Location: watch-categories.php");  /* Redirect browser to PHP web site */
   exit;  /* Make sure that code below does not get executed when we redirect. */
      
} else {
         
   if ($UserID != '') {
         
   // read the users current watch information from the database

   $sql = "select watch_list_element.element_id " .
          "  from watch_list_element, watch_list, ports " .
          " where watch_list_element.watch_list_id = watch_list.id " . 
          "   and watch_list.user_id               = $UserID " .
		  "   and watch_list_element.element_id    = ports.element_id";
      
	$result = pg_exec($db, $sql);
	$numrows = pg_numrows($result);      
   // read each value and set the variable accordingly
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array($result, $i);
		// we use these to see if a particular port is selected
		${"port_".$myrow["element_id"]} = 1;
	}
   }

   freshports_Start($categoryname,
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");
}

?>

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
echo '<font size="+1">You are not logged in, perhaps you should <a href="login.php">do that</a> first.</font>';
echo '</td></tr><tr><td>';
} else {
?>
<p>
This screen contains a list of the ports in category <em><?echo $categoryname ?></em>. 
The ports with a tick beside them are already in your watch list. 
When one of the ports in your watch list changes, you will be notified by email if
you have selected a notification frequency within your <a href="customize.php">personal preferences</a>.
</p>
<p>
[D] indicates a port which has been removed from the tree.
</p>
<? } ?>
<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

//echo "UserID=$UserID";

echo '<tr><td>' . "\n";

echo "\n</td></tr>\n<tr><td>";

//   echo 'time to update the cache';

$sql = "select element.id, element.name as port, element.status, categories.name as category  ".
       "  from ports, element, categories ".
       " WHERE ports.category_id = $category " .
	   "   and ports.element_id  = element.id " .
	   "   and ports.category_id = categories.id " .
       " order by element.name";

//echo $sql, "<br>\n";

$result = pg_exec($db, $sql);

$HTML .= '<tr><td>' . "\n";

$numrows = pg_numrows($result);
if ($numrows) {

   if ($UserID) {
      $HTML .= '<form action="' . $PHP_SELF . "?category=$category". '" method="POST">';
   }

   $HTML .= "\n" . '<table cellpadding=12 border=1>' . "\n";

   // get the list of topics, which we need to modify the order

	$NumPorts = 0;
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array($result, $i);
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

      $HTML .= ' <a href="/' . $rows[$i]["category"] . '/' . $rows[$i]["port"] . '/">' . $rows[$i]["port"] . '</a>';

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

$HTML .= '</td></tr>';

echo $HTML;                                                   

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
