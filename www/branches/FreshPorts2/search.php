<?
	# $Id: search.php,v 1.1.2.3 2002-01-05 23:01:18 dan Exp $
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

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">                    
<table width="100%" border="0">                       
  <tr>                                                
    <td colspan="2" bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+2"><? echo $FreshPortsTitle; ?> - search</font></td>
  </tr>
<tr><td valign="top">
OK, we have just a very simple search.  Eventually this will be extended. If you find any bugs, please
let <a href="http://freshports.org/phorum/list.php?f=3">me know</a>.
</td></tr>
<tr><td>
<?
if ($search) {
/*
   while (list($name, $value) = each($HTTP_POST_VARS)) {
      echo "$name = $value<br>\n";
   }

   echo "you submitted<br>\n";
*/

   echo "</td></tr>\n<tr><td>";

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
       "ports.package_exists, ports.extract_suffix, ports.homepage, element.status, " .
       "ports.broken, ports.forbidden " .
       "from ports, categories, element  ".
       "WHERE ports.category_id = categories.id " .
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

#echo "$sql<br>\n";


$result  = pg_exec($db, $sql);
$NumRows = pg_numrows($result);

$Port = new Port($db);
$Port->LocalResult = $result;

}
?>
<form METHOD="POST" ACTION="<? echo $PHP_SELF ?>">
  <p>Search for: <input NAME="query" size="20"  value="<? echo stripslashes($query)?>"> <select NAME="stype" size="1">
    <option VALUE="name"             <? if ($stype == "name")             echo 'selected'?>>Port Name</option>
    <option VALUE="maintainer"       <? if ($stype == "maintainer")       echo 'selected'?>>Maintainer</option>
    <option VALUE="shortdescription" <? if ($stype == "shortdescription") echo 'selected'?>>Short Description</option>
  </select> <input TYPE="submit" VALUE="search"> </p>
  <input type="hidden" name="search" value="1">
</form>

</td></tr>
<?
if ($search) {
echo "<tr><td>\n";
if ($NumRows == 0) {
   $HTML .= " no results found<br>\n";
} else {

//   echo "retrieving $NumRows rows<br>\n";


$ShowCategories		= 1;
GLOBAL	$ShowDepends;
$ShowDepends		= 1;
$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink= $ShowDescriptionLink= $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
$HideDescription = 0;
$ShowEverything  = 1;
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";

	for ($i = 0; $i < $NumRows; $i++) {
		$Port->FetchNth($i);
		$HTML .= freshports_PortDetails($Port, $Port->dbh, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription);
   }

}
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
<? include("./include/footer.php") ?>
</body>
</html>
