<?
	# $Id: search.php,v 1.1.2.7 2002-02-21 23:13:55 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	require("../classes/ports.php");

	freshports_Start("Search",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

	$Debug = 0;
?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">                    
<table width="100%" border="0">                       
  <tr>
	<? freshports_PageBannerText("Search"); ?>
  </tr>
<tr><td valign="top">
OK, we have just a very simple search.  Eventually this will be extended. If you find any bugs, please
let <a href="http://freshports.org/phorum/list.php?f=3">me know</a>.
</td></tr>
<tr><td>
<?

if ($Debug) {
	echo "$query && $stype && $num\n<BR>";

	if ($query && $stype && $num) {
		echo "yes, we have parameters\n<BR>";
	}
}


if ($search || ($query && $stype && $num)) {

	if ($Debug) echo "into search stuff<BR>\n";

/*
   while (list($name, $value) = each($HTTP_POST_VARS)) {
      echo "$name = $value<br>\n";
   }

   echo "you submitted<br>\n";
*/

$logfile = $DOCUMENT_ROOT . "/../configuration/searchlog.txt";

$fp = fopen($logfile, "a");
if ($fp) {
	fwrite($fp, date("Y-m-d H:i:s") . " " . $stype . ':' . $query . "\n");
	fclose($fp);
} else {
	print "Please let postmaster@freshports.org know that the search log could not be opened.  This does not affect the search results.\n";
	define_syslog_variables();
	syslog(LOG_ERR, "FreshPorts could not open the search log file: $logfile");
}



	$query = addslashes($query);

$sql = "select ports.id, element.name as port, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.maintainer, ports.short_description, ".
       "ports.package_exists, ports.extract_suffix, ports.homepage, element.status, ports.element_id, " .
       "ports.broken, ports.forbidden ";

if ($WatchListID) {
    $sql .= ",
       CASE when watch_list_element.element_id is null
          then 0
          else 1
       END as onwatchlist ";
}

	$sql .= "from ports, categories, element  ";

if ($WatchListID) {
    $sql .="
            left outer join watch_list_element
            on element.id                       = watch_list_element.element_id
           and watch_list_element.watch_list_id = $WatchListID ";
}




	$sql .= "WHERE ports.category_id = categories.id " .
	        "  and ports.element_id  = element.id " ;

switch ($stype) {
   case "name":
      $sql .= "and element.name like '%$query%'";
      break;

   case "longdescription":
      $sql .= "and ports.long_description like '%$query%'";
      break;

   case "shortdescription":
      $sql .= "and ports.short_description like '%$query%'";
      break;
      
   case "maintainer":
      $sql .= "and ports.maintainer like '%$query%'";
      break;

   case "requires":
      $sql .= "and (ports.depends_build like '%$query%' or ports.depends_run like '%$query%')";
      break;
}

$sql .= " order by categories.name, element.name";

if ($num < 1 or $num > 500) {
	$num = 10;
}

$sql .= " limit $num";

$AddRemoveExtra  = "&&origin=$SCRIPT_NAME?query=" . $query. "+stype=$stype+num=$num";
if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";
$AddRemoveExtra = AddSlashes($AddRemoveExtra);
if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";

if ($Debug) {
	echo "$sql<br>\n";

#	print "now exitting....";
#	exit;
}


$result  = pg_exec($db, $sql);
if (!$result) {
	echo pg_errormessage();
	exit;
}
$NumRows = pg_numrows($result);

$Port = new Port($db);
$Port->LocalResult = $result;

}
?>
<form METHOD="POST" ACTION="<? echo $PHP_SELF ?>">
  <p>Search for: <input NAME="query" size="20"  value="<? echo stripslashes($query)?>"> <SELECT NAME="stype" size="1">
    <option VALUE="name"             <? if ($stype == "name")             echo 'selectd'?>>Port Name</option>
    <option VALUE="maintainer"       <? if ($stype == "maintainer")       echo 'selected'?>>Maintainer</option>
    <option VALUE="shortdescription" <? if ($stype == "shortdescription") echo 'selected'?>>Short Description</option>
  </SELECT> 

	<SELECT name=num>
		<option value="10"  <?if ($num == 10)  echo 'selected' ?>>10 results
		<option value="20"  <?if ($num == 20)  echo 'selected' ?>>20 results
		<option value="30"  <?if ($num == 30)  echo 'selected' ?>>30 results
		<option value="50"  <?if ($num == 50)  echo 'selected' ?>>50 results
		<option value="100" <?if ($num == 100) echo 'selected' ?>>100 results
		<option value="500" <?if ($num == 500) echo 'selected' ?>>500 results
	</SELECT> 

	<input TYPE="submit" VALUE="search"> </p>
  <input type="hidden" name="search" value="1">
</form>

</td></tr>
<?
if ($search || ($query && $stype && $num)) {
echo "<tr><td>\n";
if ($NumRows == 0) {
   $HTML .= " no results found<br>\n";
} else {

//   echo "retrieving $NumRows rows<br>\n";

	$HTML .= "Number of ports found: $NumRows<BR>";

$ShowCategories		= 1;
GLOBAL	$ShowDepends;
$ShowDepends		= 1;
$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink= $ShowDescriptionLink= $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
$HideDescription = 1;
$ShowEverything  = 1;
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";

	for ($i = 0; $i < $NumRows; $i++) {
		$Port->FetchNth($i);
		$HTML .= freshports_PortDetails($Port, $Port->dbh, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, 1, $AddRemoveExtra);
   }

}

$HTML .= "Number of ports found: $NumRows<BR>";

echo $HTML;
echo "</td></tr>\n";
}
?>
</table>

</td>
  <td valign="top" width="*">
    <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
