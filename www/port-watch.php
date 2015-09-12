<?php
	#
	# $Id: port-watch.php,v 1.2 2006-12-17 12:06:14 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_list.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_list_element.php');

	$Debug  = 0;
	$submit = 0;
	$HTML   = '';

	if (IsSet($_POST['submit'])) {
		$submit = pg_escape_string($_POST['submit']);
	}
	$visitor = $_COOKIE['visitor'];

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
	header('Location: /login.php?origin=' . $_SERVER['PHP_SELF']);  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if (IsSet($_REQUEST['wlid'])) {
		# they clicked on the GO button and we have to apply the 
		# watch staging area against the watch list.
		$wlid = pg_escape_string($_REQUEST["wlid"]);
		if ($Debug) echo "setting SetLastWatchListChosen => \$wlid='$wlid'";
		$User->SetLastWatchListChosen($wlid);
		if ($Debug) echo "\$wlid='$wlid'";
} else {
	$wlid = $User->last_watch_list_chosen;
	if ($Debug) echo "\$wlid='$wlid'";
	if ($wlid == '') {
		$WatchLists = new WatchLists($db);
		$wlid = $WatchLists->GetDefaultWatchListID($User->id);
		if ($Debug) echo "GetDefaultWatchListID => \$wlid='$wlid'";
	}
}

if (!freshports_IsInt($wlid)) {
	$msg = "\$wlid = '$wlid', which is not an integer as expected.";
	syslog(LOG_ERR, $msg . " User = '" . $User->id . "' in " . __FILE__ . ':' . __LINE__);
	die($msg);
}

$Category = new Category($db);
if (IsSet($_REQUEST['category'])) {
	$category = pg_escape_string($_REQUEST['category']);

	$CategoryID = $Category->FetchByName($category);
	if (!$CategoryID) {
		$msg = "category '$category' not found";
		syslog(LOG_ERR, $msg . " User = '" . $User->id . "' in " . __FILE__ . ':' . __LINE__);
		die($msg);
	}
} else {
		$msg = "category not supplied";
		syslog(LOG_ERR, $msg . " User = '" . $User->id . "' in " . __FILE__ . ':' . __LINE__);
		die($msg);
}

if ($submit) {
	pg_exec($db, "BEGIN");

	$WatchList = new WatchList($db);
	$WatchList->EmptyTheListCategory($User->id, $wlid, $Category->id);

   $ports = $_POST["ports"];
   if ($ports) {
   	$WatchListElement = new WatchListElement($db);
      // make sure we are pointing at the start of the array.
      reset($ports);
      while (list($key, $value) = each($ports)) {
      	$WatchListElement->Add($User->id, $wlid, $value);

      	# I have no idea why this works... seems to be missing the value of $sql
         $result = pg_exec ($db, $sql);
         ${"port_".$value} = 1;
         if (!$result) {
         	syslog(LOG_ERR, $_SERVER["PHP_SELF"] . ": could not clear watch list '$wlid' owned by '$User->id' of element '$value' in " . __FILE__ . ':' . __LINE__);
         	die("error clear list before saving");
         }
      }
   }
   
   pg_exec($db, "COMMIT");
      
   header("Location: port-watch.php?category=$category&wlid=$wlid");  /* Redirect browser to PHP web site */
   exit;  /* Make sure that code below does not get executed when we redirect. */
      
} else {
         
   if ($User->id != '') {
         
	   // read the users current watch information from the database
	
	   $sql = "
   select watch_list_element.element_id 
	  from watch_list_element, watch_list, ports 
	 where watch_list_element.watch_list_id = watch_list.id  
	   and watch_list.user_id               = $User->id 
	   and watch_list.id                    = $wlid
	   and watch_list_element.element_id    = ports.element_id";
	      
		$result = pg_exec($db, $sql);
		$numrows = pg_numrows($result);      
	   // read each value and set the variable accordingly
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array($result, $i);
			// we use these to see if a particular port is selected
			${"port_".$myrow["element_id"]} = 1;
		}
   }

   freshports_Start($category,
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");
}
?>

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>
  <tr>
	<? echo freshports_PageBannerText("Watch List - " . $category) ?>
  </tr>

<?php

$sql = "
  SELECT element.id, 
         element.name    AS port, 
         element.status,
         CASE WHEN ports.category_id = $Category->id THEN '' ELSE '&nbsp;<sup>*</sup>' END AS virtual,
         PRIMARY_CATEGORY.name as primary_category
    FROM ports, ports_categories, element, categories PRIMARY_CATEGORY
   WHERE ports_categories.category_id = $Category->id
     AND ports_categories.port_id     = ports.id
     AND ports.element_id             = element.id
     AND element.status               = 'A'
     AND PRIMARY_CATEGORY.id          = ports.category_id
ORDER BY port, primary_category";

if ($Debug) echo "<pre>$sql</pre>\n";

$result = pg_exec($db, $sql);

$numrows = pg_numrows($result);


if ($numrows) {
   // get the list of ports

	$NumPorts   = 0;
	$NumVirtual = 0;
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array($result, $i);
		$NumPorts++;
		$rows[$NumPorts-1]=$myrow;
		if ($myrow["virtual"] != '') {
			$NumVirtual++;
		}		
	}
}


$HTML .= '<tr><td><b>' . $numrows . ' ports found (';
if ($NumVirtual != 0) {
	$HTML .=  ($numrows - $NumVirtual) . ' primary, ' . $NumVirtual . ' secondary)</b></td></tr>';
}
$HTML .= '<tr><td valign="top" ALIGN="center">' . "\n";


if ($numrows) {
	
	$HTML .= '<table border="0">' . "\n" . '<tr><td>' . "\n";
	$HTML .= '<table border="0">' . "\n" . '<tr><td>' . "\n";

	$HTML .= '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
   // save the number of categories for when we submit
   $HTML .= '<input type="hidden" name="NumPorts" value="' . $NumPorts . '">' . "\n";
   $HTML .= '<input type="hidden" name="category" value="' . $category . '">' . "\n";
   $HTML .= '<input type="hidden" name="wlid"     value="' . $wlid     . '">' . "\n";

   $HTML .= "\n" . '<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="5">' . "\n<tr>\n";
   $RowCount = ceil($NumPorts / (double) 4);
   $Row = 0;
   for ($i = 0; $i < $NumPorts; $i++) {
      $Row++;

      if ($Row > $RowCount) {
         $HTML .= "</td>\n";
         $Row = 1;
      }

      if ($Row == 1) {
         $HTML .= '<td valign="top" nowrap>';
      }

      $HTML .= '<input type="checkbox" name="ports[]" value="'. $rows[$i]["id"] .'"';

      if (IsSet(${"port_".$rows[$i]["id"]})) {
         $HTML .= " checked ";
      }

      $HTML .= '>';

      $HTML .= ' <a href="/' . $rows[$i]["primary_category"] . '/' . $rows[$i]["port"] . '/">' . $rows[$i]["port"] . '</a>';

		if ($rows[$i]["status"] == 'D') {
			$HTML .= " [D]";
		}

		$HTML .= $rows[$i]["virtual"];

      $HTML .= "<br>\n";
   }

   if ($Row != 1) {
      $HTML .= "</td></tr>\n";
   }

   $HTML .= "</table>\n";
   
   echo $HTML;
?>

<div align="center">
<br>
<input TYPE="submit" VALUE="update watch list" name="submit">
<input TYPE="reset"  VALUE="reset form">
<input type="hidden" name="watch_list_id" value="<?php echo $wlid; ?>">

</div>
</form>
</table>

<td valign="top">
<table border="0"><tr><td>Select...</td></tr><tr><td>
   
<?php
	$Extra = '<input type="hidden" name="category" value="' . $category . '">';
	echo freshports_WatchListDDLBForm($db, $User->id, $wlid, $Extra);
?>
  </td></tr></table>
  </td></tr></table>

<?php
} else {
	echo '<tr><td ALIGN="center">' . "\n";
	echo "No ports found.  Perhaps this is an invalid category id.";
	echo "</td></tr>\n";
}


?>
<tr><td align="left" valign="top">
<UL>
<LI>
This page operates on a single watch list at a time.
<LI>This page shows you the ports in a category (<em><?echo $category ?></em>)
that are on your selected watch list.</LI>
<LI>The entries with a tick beside them are your on the selected watch list.</LI>
<LI>When one of the ports in your watch list changes, you will be notified by email if
you have selected a notification frequency within your <a href="customize.php">personal preferences</a>.
</LI>
<LI>[D] indicates a port which has been removed from the tree.</LI>
<LI><sup>*</sup> indicates a port which resides in another cateogory but lists this category as a secondary.</LI>
</UL>
</table>

</td>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
