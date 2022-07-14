<?php
	#
	# $Id: filter-setup.php,v 1.2 2006-12-17 12:06:10 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_list_element.php');

	if (IN_MAINTENCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	// if we don't know who they are, we'll make sure they login first
	if (!$visitor) {
		header("Location: /login.php");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	define('NUMCOLUMS', 7);

	$submit = 0;
	if (IsSet($_POST['submit'])) {
		$submit = pg_escape_string($db, $_POST['submit']);
	}
		

if (IsSet($_REQUEST['wlid'])) {
		# they clicked on the GO button and we have to apply the 
		# watch staging area against the watch list.
		$wlid = pg_escape_string($db, $_REQUEST["wlid"]);
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

if ($submit) {
  pg_exec($db, "BEGIN");
  $categories = $_POST["categories"];
  if ($categories) {
    $WatchListElement = new WatchListElement($db);
    // make sure we are pointing at the start of the array.
    reset($categories);
    foreach ($categories as $key => $value) {
      $result = $WatchListElement->Add($User->id, $wlid, $value);

#      ${"category_".$value} = 1;
      if ($result != 1) {
        syslog(LOG_ERR, $_SERVER["PHP_SELF"] . ": could not clear watch list '$wlid' owned by '$User->id' of element '$value' in " . __FILE__ . ':' . __LINE__);
        die("error clear list before saving");
      }
    }
  }
   
  pg_exec($db, "COMMIT");
      
  header("Location: filter-setup.php");  /* Redirect browser */
  exit;  /* Make sure that code below does not get executed when we redirect. */
      
}

	$Title = 'Watch categories';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

$Debug = 0;

if ($Debug) # phpinfo();

$visitor = $_COOKIE[USER_COOKIE_NAME];

if ($_REQUEST['wlid']) {
		# they clicked on the GO button and we have to apply the 
		# watch staging area against the watch list.
		$wlid = pg_escape_string($db, $_REQUEST['wlid']);
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

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>
<?php # article table start ?>
  <tr>
	<? echo freshports_PageBannerText("Watch List - categories"); ?>
  </tr>
<tr><td class="content">


<table class="fullwidth borderless">
<?php # list of categories table start ?>
<tr><td>
This screen contains a list of the port categories. A * indicates a category that contains ports which are
on your watch list. When a port changes in one of your watch categories, you will be notified by email if you have selected a 
notification frequency within your <a href="customize.php">account settings</a>.

<p>
Virtual categories cannot be watched and their checkboxes will be disabled.
</td>

<td valign="top">
<table class="borderless">
<?php # ddlb start ?>
<tr><td>Select...</td></tr>
<tr><td align="left">

<?php
if ($Debug) echo 'when calling freshports_WatchListDDLBForm, $wlid = \'' . $wlid . '\'';
echo freshports_WatchListDDLBForm($db, $User->id, $wlid);

?>
</td>
</tr></table>

<?php # ddlb finish ?>

</td></tr>
</table>
<?php # list of categories table finish ?>

</td></tr>


<tr><td>
&nbsp;
</td></tr>
<?php

if ($wlid != '') {
$sql = "
   select distinct(ports_categories.category_id) as category_id
     from watch_list, watch_list_element, ports, ports_categories
    WHERE watch_list.id      = " . $wlid . "
      and watch_list.user_id = $User->id
      and watch_list.id      = watch_list_element.watch_list_id
      and ports.element_id   = watch_list_element.element_id
      AND ports_categories.port_id = ports.id";

if ($Debug) echo "<pre>$sql</pre>";


$result  = pg_exec ($db, $sql);
$numrows = pg_num_rows($result);

if ($Debug) echo "num categories being watched = $numrows<BR>";

for ($i = 0; $i < $numrows; $i++) {
	$myrow = pg_fetch_array($result, $i);
	$WatchedCategories{$myrow["category_id"]} = ' *';
	if ($Debug) echo "category " . $myrow["category_id"] . " = " . $WatchedCategories{$myrow["category_id"]} . '<BR>';
}

# Get a list of the categories that are being watched

$sql = "
   select distinct(categories.element_id) as category_element_id
     from watch_list, watch_list_element, categories
    WHERE watch_list.id         = " . $wlid . "
      and watch_list.user_id    = $User->id
      and watch_list.id         = watch_list_element.watch_list_id
      and categories.element_id = watch_list_element.element_id";

if ($Debug) echo "<pre>$sql</pre>";


echo '<tr><td align="center">' . "\n";

$result  = pg_exec ($db, $sql);
$numrows = pg_num_rows($result);

if ($Debug) echo "num categories being watched = $numrows<BR>";

for ($i = 0; $i < $numrows; $i++) {
	$myrow = pg_fetch_array($result, $i);
	$FilteredCategories{$myrow["category_element_id"]} = ' $';
	if ($Debug) echo "category " . $myrow["category_element_id"] . " = " . $FilteredCategories{$myrow["category_element_id"]} . '<BR>';
}

# categories list start

$HTML .= "\n" . '<TABLE class="bordered" CELLPADDING="5">' . "\n";
$HTML .= '<tr><td>';
// get the list of categories to display
$sql = "
    select categories.id          as category_id, 
           categories.name        as category, 
           categories.description as description,
           categories.element_id  as element_id
      from categories
  order by category";

$result  = pg_exec($db, $sql);  
$numrows = pg_num_rows($result);
$NumCategories = 0;
for ($i = 0; $i < $numrows; $i++) {
	$myrow = pg_fetch_array($result, $i);
	$NumCategories++;
	$rows[$NumCategories-1] = $myrow;
}

$HTML .= '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";



# if we go 
$RowCount = ceil($NumCategories / (double) NUMCOLUMS);
$Row = 0;
for ($i = 0; $i < $NumCategories; $i++) {
	pg_fetch_array ($result, $i);
   $Row++;

   if ($Row > $RowCount) {
      $HTML .= "</td>\n";
      $Row = 1;
   }

   if ($Row == 1) {
      $HTML .= '<td valign="top">';
   }

   $HTML .= '<input type="checkbox" name="categories[]"';
   if (IsSet($rows[$i]['element_id'])) {
     $HTML .= ' value="'. $rows[$i]['element_id'] .'"';
   } else {
     $HTML .= " disabled";
   }
   if (IsSet($FilteredCategories{$rows[$i]['element_id']})) {
     $HTML .= " checked ";
   }
   $HTML .= '> ';

   $HTML .= ' <a href="/port-watch.php?category=' . $rows[$i]['category'] . '&amp;wlid=' . $wlid . '">' . $rows[$i]['category'] . '</a>';

   $HTML .= $WatchedCategories {$rows[$i]['category_id']};
   $HTML .= $FilteredCategories{$rows[$i]['element_id']};

   $HTML .= "<br>\n";
}

if ($Row != 1) {
   $HTML .= "</td></tr>\n";
}

echo $HTML;                                                   

echo "</table>\n";
?>
<br>
<input TYPE="submit" VALUE="Save changes" name="submit">
<input TYPE="reset"  VALUE="reset form">
<input type="hidden" name="watch_list_id" value="<?php echo $wlid; ?>">

</form>

<?php

# categories list finish

} else {
	echo '<tr>';
} // if wlid
?>
</table>
<?php # main article table finish ?>
</td>

  <td class="sidebar">
  <?
  echo freshports_SideBar();
  ?>
  </td>

</tr>
</table>
<?php # main table finish ?>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
