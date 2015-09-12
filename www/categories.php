<?php
	#
	# $Id: categories.php,v 1.2 2006-12-17 12:06:08 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');
	
	$Commit = new Commit($db);
	$Commit->DateNewestPort();

	freshports_ConditionalGet($Commit->last_modified);

	freshports_Start('Categories',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');
					
	$Debug = 0;

	DEFINE('VIRTUAL', '<sup>*</sup>'); 
	$Primary['t'] = '';
    $Primary['f'] = VIRTUAL;

	$AllowedToEdit = $User->IsTaskAllowed(FRESHPORTS_TASKS_CATEGORY_VIRTUAL_DESCRIPTION_SET);

	if ($AllowedToEdit) {
		$ColSpan = 5;
	} else {
	   	$ColSpan = 4;
	}
	
	if ($User->id) {
	  # obtain a list of the categories on this users watchlists
      require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/category-listing.php');
      
      $CategoryListing = new Categories($db);
      $NumRows = $CategoryListing->GetAllCategoriesOnWatchLists($User->id);
      for ($i = 0; $i < $NumRows; $i++) {
        $CategoryListing->FetchNth($i);
        $CategoriesWatched[$CategoryListing->category_id] = $CategoryListing->category_id;
      }
	}

?>

<?php
	echo freshports_MainTable();
?>

<tr><td colspan="<?php echo $ColSpan; ?>" valign="top" width="100%">

<?php echo freshports_MainContentTable(BORDER, 6); ?>

  <tr>
	<? echo freshports_PageBannerText("$FreshPortsTitle - list of categories", $ColSpan); ?>
  </tr>
<tr><td COLSPAN="<?php echo $ColSpan; ?>" valign="top">
<P>
This page lists the categories and can be sorted by various criteria.  Virtual
categories are indicated by <?php echo VIRTUAL; ?>.
</P>

<P>
You can sort each column by clicking on the header.  e.g. click on <b>Category</b> to sort by category.
</P>

<center>
<?php
  if ($ShowAds && $BannerAd) {
    echo Ad_728x90();
  }
?>
</center>


<?php
$sort = IsSet($_REQUEST['sort']) ? pg_escape_string($_REQUEST['sort']) : '';

switch ($sort) {
   case 'category':
   case 'count':
   case 'description':
      $sort = $sort;
      break;

   case 'lastupdate':
      $sort = 'last_update';
      break;

   default:
      $sort = 'category';
}

$sql = "
  SELECT C.id                   AS category_id,
         C.name                 AS category,
         C.element_id           AS element_id,
         C.description          AS description,
         C.is_primary           AS is_primary,
         C.element_id           AS element_id,
         to_char(CS.last_update - SystemTimeAdjust(), 'DD Mon YYYY HH24:MI:SS') AS lastupdate,
         CS.port_count          AS count
    FROM categories C JOIN category_stats CS ON (C.id = CS.category_id)";

$sql .=  " ORDER BY " . pg_escape_string($sort);

if ($Debug) echo '<pre>' . $sql, "</pre>\n";
//echo $sort, "\n";

$result = pg_exec($db, $sql);

$HTML = freshports_echo_HTML('<tr>');

if ($sort == "category") {
   $HTML .= freshports_echo_HTML('<td><b>Category</b> ' . freshports_Ascending_Icon() . '</td>');
} else {
   $HTML .= freshports_echo_HTML('<td><a href="categories.php?sort=category"><b>Category<b></a></td>');
}


if ($AllowedToEdit) {
	$HTML .= freshports_echo_HTML('<td><b>Action</b></td>');
}
	

if ($sort == "count") {
   $HTML .= freshports_echo_HTML('<td align="center"><b>Count</b> ' . freshports_Ascending_Icon() . '</td>');
} else {
   $HTML .= freshports_echo_HTML('<td><a href="categories.php?sort=count"><b>Count</b></a></td>');
}

if ($sort == "description") {
   $HTML .= freshports_echo_HTML('<td><b>Description</b> ' . freshports_Ascending_Icon() . '</td>');
} else {
   $HTML .= freshports_echo_HTML('<td><a href="categories.php?sort=description"><b>Description</b></a></td>');
}

if ($sort == "last_update") {
   $HTML .= freshports_echo_HTML('<td nowrap><b>Last Update</b> ' . freshports_Ascending_Icon() . '</td>');
} else {
   $HTML .= freshports_echo_HTML('<td nowrap><a href="categories.php?sort=lastupdate"><b>Last Update</b></a></td>');
}

$HTML .= freshports_echo_HTML('</tr>');

if (!$result) {
   print pg_errormessage() . "<br>\n";
   exit;
} else {
	$NumTopics	   = 0;
	$NumPorts      = 0;
	$CategoryCount = 0;
	$NumRows = pg_numrows($result);
    if ($NumRows) {
      for ($i = 0; $i < $NumRows; $i++) {
        $myrow = pg_fetch_array($result, $i);
		$HTML .= freshports_echo_HTML('<tr>');
		$HTML .= '<td align="top" nowrap>';
        if ($User->id) {
          if ($Primary[$myrow["is_primary"]]) {
            $HTML .= freshports_Watch_Icon_Empty();
          } else {
		    if (IsSet($CategoriesWatched[$myrow['category_id']])) {
              $HTML .= freshports_Watch_Link_Remove('', 0, $myrow['element_id']);
			} else {
              $HTML .= freshports_Watch_Link_Add   ('', 0, $myrow['element_id']);
			}
          }
		}
		$HTML .= ' ';

		$HTML .= freshports_echo_HTML('<a href="/' . $myrow["category"] . '/">' . $myrow["category"] . '</a>' . $Primary[$myrow["is_primary"]]);
		
		$HTML .= '</td>';
		if ($AllowedToEdit) {
			$HTML .= freshports_echo_HTML('<td valign="top"><a href="/category-maintenance.php?category=' . $myrow["category"] . '">update</a></td>');
		}

		$HTML .= freshports_echo_HTML('<td valign="top" ALIGN="right">' . $myrow["count"] . '</td>');
		$HTML .= freshports_echo_HTML('<td valign="top">' . $myrow["description"] . '</td>');
		$HTML .= freshports_echo_HTML('<td valign="top" nowrap><font size="-1">' . $myrow["lastupdate"] . '</font></td>');
		$HTML .= freshports_echo_HTML("</tr>\n");


		# count only the ports in primary categories
		# as non-primary categories contain only ports which appear in primary categories.
		if ($myrow["is_primary"] == 't') {
			$NumPorts += $myrow["count"];
			$CategoryCount++;
		}
      }
    } else {
        $HTML .= "<tr><td colspan=5>No categories statistics found.  I bet the refresh script is not running.</td></tr>";
    }
}

$HTML .= freshports_echo_HTML('<tr><td><b>port count:</b></td>');
if ($AllowedToEdit) {
	$HTML .= freshports_echo_HTML('<td>&nbsp;</td>');
}

$HTML .= freshports_echo_HTML("<td ALIGN=\"right\"><b>$NumPorts</b></td><td colspan=\"2\">($CategoryCount categories)</td></tr>");

$HTML .= freshports_echo_HTML("<tr><td colspan=\"5\">Hmmm, I'm not so sure this port count is accurate. Dan Langille 27 April 2003</td></tr>");

$HTML .= freshports_echo_HTML('</table>');

freshports_echo_HTML_flush();

echo $HTML;                                                   
?>

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
