<?php
	#
	# $Id: watch-categories.php,v 1.2 2006-12-17 12:06:18 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/watch-lists.php');

	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	// if we don't know who they are, we'll make sure they login first
	if (IsSet($visitor) && !$visitor) {
		header("Location: /login.php");  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	define('NUMCOLUMNS', 7);

	$Title = 'Watch Categories';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

$Debug = 0;

if ($Debug) # phpinfo();

$visitor = $_COOKIE[USER_COOKIE_NAME] ?? null;
if (!IsSet($visitor)) {

	echo freshports_ShowFooter();

	die('why are you here?');
}

if (IsSet($_REQUEST['wlid']) && $_REQUEST['wlid']) {
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
<p>
This screen contains a list of the port categories. The categories with a * beside them contain ports which are
on your watch list. When a port changes in one of your watch categories, you will be notified by email if you have selected a 
notification frequency within <a href="customize.php">your account</a>.
</p>
</td>

<td>
<table class="borderless">
<?php # ddlb start ?>
<tr><td>Select...</td></tr>
<tr><td>

<?php
if ($Debug) echo 'when calling freshports_WatchListDDLBForm, $wlid = \'' . $wlid . '\'';
echo freshports_WatchListDDLBForm($db, $User->id, $wlid);

?>
</td></tr></table>

<?php # ddlb finish ?>

</td></tr>
</table>
<?php # list of categories table finish ?>

</td></tr>
<?php

if ($wlid != '') {
$sql = "
   select distinct(ports_categories.category_id) as category_id
     from watch_list, watch_list_element, ports, ports_categories
    WHERE watch_list.id      = " . pg_escape_string($db, $wlid) . "
      and watch_list.user_id = $User->id
      and watch_list.id      = watch_list_element.watch_list_id
      and ports.element_id   = watch_list_element.element_id
      AND ports_categories.port_id = ports.id";

if ($Debug) echo "<pre>$sql</pre>";


echo '<tr><td>' . "\n";

$result  = pg_query($db, $sql);
$numrows = pg_num_rows($result);

if ($Debug) echo "num categories being watched = $numrows<BR>";

for ($i = 0; $i < $numrows; $i++) {
	$myrow = pg_fetch_array($result, $i);
	$WatchedCategories[$myrow['category_id']] = ' *';
	if ($Debug) echo "category " . $myrow['category_id'] . " = " . $WatchedCategories[$myrow['category_id']] . '<BR>';
}

# categories list start

$HTML  = "\n" . '<TABLE class="watch-categories bordered">' . "\n";
$HTML .= '<tr>';
// get the list of categories to display
$sql = "
    select categories.id          as category_id, 
           categories.name        as category, 
           categories.description as description
      from categories
  order by category";

$result  = pg_query($db, $sql);
$numrows = pg_num_rows($result);
$NumCategories = 0;
for ($i = 0; $i < $numrows; $i++) {
	$myrow = pg_fetch_array($result, $i);
	$rows[$NumCategories] = $myrow;
	$NumCategories++;
}

# how many rows will we have if we go for NUMCOLUMNS colums?
$RowCount = ceil($NumCategories / (double) NUMCOLUMNS);
$Row = 0;
for ($i = 0; $i < $NumCategories; $i++) {
   $Row++;

   if ($Row > $RowCount) {
      $HTML .= "</td>\n";
      $Row = 1;
   }

   if ($Row == 1) {
      $HTML .= '<td>';
   }

   $HTML .= ' <a href="/port-watch.php?category=' . $rows[$i]['category'] . '&amp;wlid=' . $wlid . '">' . $rows[$i]['category'] . '</a>';

   $HTML .= $WatchedCategories[$rows[$i]['category_id']] ?? '';
   $HTML .= "<br>\n";
}

if ($Row >= 1) {
   $HTML .= "</td></tr>\n";
}

echo $HTML;                                                   

echo "</table>\n";
# categories list finish

} ?>
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
