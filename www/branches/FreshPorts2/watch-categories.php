<?
	# $Id: watch-categories.php,v 1.1.2.13 2002-12-09 20:33:53 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	freshports_Start("Watch categories",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

$Debug = 0;

?>
<?
$visitor = $_COOKIE["visitor"];

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
        header("Location: login.php?origin=" . $_SERVER["PHP_SELF"]);  /* Redirect browser to PHP web site */
        exit;  /* Make sure that code below does not get executed when we redirect. */
}

?>
<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
<td valign="top"><table width="100%">
  <tr>
	<? freshports_PageBannerText("Watch List - categories"); ?>
  </tr>
<tr><td valign="top" width="100%">


<table width="100%" border="0">
<tr><td>
<?
if (!$User->id) {
echo '<font size="+1">You are not logged in, perhaps you should <a href="login.php">do that</a> first.</font>';
} else {
?>
This screen contains a list of the port categories. The categories with a * beside them contain ports which are
on your watch list. When a port changes in one of your watch categories, you will be notified by email if you have selected a 
notification frequency within your <a href="customize.php">personal preferences</a>.
<? } ?>

</tr></td>

<?php


echo '<tr><td align="center">' . "\n";

$sql = "select distinct(category_id) as category_id ".
       "from watch_list, watch_list_element, ports ".
       "WHERE watch_list.name    = 'main' ".
       "  and watch_list.user_id = $User->id ".
       "  and watch_list.id      = watch_list_element.watch_list_id ".
       "  and ports.element_id   = watch_list_element.element_id";

if ($Debug) echo $sql, "<br>\n";

$result  = pg_exec ($db, $sql);
$numrows = pg_numrows($result);

if ($Debug) echo "num categories being watched = $numrows<BR>";

for ($i = 0; $i < $numrows; $i++) {
	$myrow = pg_fetch_array($result, $i);
	$WatchedCategories{$myrow["category_id"]} = ' *';
	if ($Debug) echo "category " . $myrow["category_id"] . " = " . $WatchedCategories{$myrow["category_id"]} . '<BR>';
}


$HTML .= "\n" . '<TABLE BORDER="1" CELLSPACING="0" CELLPADDING="5" BORDERCOLOR="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2">' . "\n";

// get the list of categories to display
$sql = "select categories.id as category_id, categories.name as category, categories.description as description ".
       "from categories ".
       "order by category";

$result  = pg_exec($db, $sql);  
$numrows = pg_numrows($result);
$NumCategories = 0;
for ($i = 0; $i < $numrows; $i++) {
	$myrow = pg_fetch_array($result, $i);
	$NumCategories++;
	$rows[$NumCategories-1]=$myrow;
}

$RowCount = ceil($NumCategories / (double) 4);
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

   $HTML .= ' <a href="/port-watch.php?category=' . $rows[$i]["category_id"] . '">' . $rows[$i]["category"] . '</a>';

   $HTML .= $WatchedCategories{$rows[$i]["category_id"]};
   $HTML .= "<br>\n";
}

if ($Row != 1) {
   $HTML .= "</td></tr>\n";
}

echo $HTML;                                                   

echo "</table>\n";

</script>


</table>
</table>
</td>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
   <? include($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php") ?>
 </td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
