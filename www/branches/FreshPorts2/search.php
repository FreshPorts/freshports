<?
	# $Id: search.php,v 1.1.2.28 2002-06-09 21:42:41 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/../classes/ports.php");

	switch ($_SERVER["REQUEST_METHOD"]) {
		case "POST":
			// avoid nasty problems by adding slashes
			$query		= AddSlashes($_POST["query"]);
			$stype		= AddSlashes($_POST["stype"]);
			$num		= AddSlashes($_POST["num"]);
			$category	= AddSlashes($_POST["category"]);
			$port		= AddSlashes($_POST["port"]);
			$method		= AddSlashes($_POST["method"]);
			$deleted	= AddSlashes($_POST["deleted"]);
			break;

		case "GET":
			// avoid nasty problems by adding slashes
			$query		= AddSlashes($_GET["query"]);
			$stype		= AddSlashes($_GET["stype"]);
			$num		= AddSlashes($_GET["num"]);
			$category	= AddSlashes($_GET["category"]);
			$port		= AddSlashes($_GET["port"]);
			$method		= AddSlashes($_GET["method"]);
			$deleted	= AddSlashes($_GET["deleted"]);
			break;

	}

	if ($stype == 'messageid') {
		header("Location: http://" . $_SERVER["HTTP_HOST"] . "/commit.php?message_id=$query");
		exit;
	}

	#
	# ensure deleted has an appropriate value
	#
	switch ($deleted) {
		case "includedeleted":
			# do nothing
			break;

		default:
			$deleted = "excludedeleted";
			# do not break here...
	}



#phpinfo();

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
<tr><td>
<?

if ($Debug) {
	echo "$query && $stype && $num && $method\n<BR>";

	if ($query && $stype && $num) {
		echo "yes, we have parameters\n<BR>";
	}
}

#
# we can take parameters.  if so, make it look like a post
#
$search = $_POST["search"];
if (!$search && ($query && $stype && $num && $method)) {
	$search = TRUE;
}

if ($search) {

	if ($Debug) echo "into search stuff<BR>\n";

/*
   while (list($name, $value) = each($HTTP_POST_VARS)) {
      echo "$name = $value<br>\n";
   }

   echo "you submitted<br>\n";
*/

$logfile = $_SERVER["DOCUMENT_ROOT"] . "/../dynamic/searchlog.txt";


$sql = "select distinct ports.id, element.name as port, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ports.revision as revision, ".
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

	$sql .= "from ports, categories, commit_log, commit_log_ports, element  ";

if ($WatchListID) {
    $sql .="
            left outer join watch_list_element
            on element.id                       = watch_list_element.element_id
           and watch_list_element.watch_list_id = $WatchListID ";
}




	$sql .= "WHERE ports.category_id  = categories.id
	           and ports.element_id   = element.id 
	           and commit_log.id      = commit_log_ports.commit_log_id
               and commit_log_ports.port_id = ports.id  " ;


switch ($method) {
	case 'match':
		switch ($stype) {
			case "name":
				$sql .= "and element.name like '%$query%'";
				break;

			case "shortdescription":
				$sql .= "and ports.short_description like '%$query%'";
				break;
      
			case "maintainer":
				$sql .= "and ports.maintainer like '%$query%'";
				break;

			case "messageid":
				$sql .= "and commit_log.message_id = '$query'";
		}
		break;

	case 'exact':
		switch ($stype) {
			case "name":
				$sql .= "and element.name = '$query'";
				break;

			case "shortdescription":
				$sql .= "and ports.short_description = '$query'";
				break;
      
			case "maintainer":
				$sql .= "and ports.maintainer = '$query'";
				break;

			case "messageid":
				$sql .= "and commit_log.message_id = '$query'";
		}
		break;

	default:
		switch ($stype) {
			case "name":
				$sql .= "and levenshtein(element.name, '$query') < 4";
				break;

			case "shortdescription":
				$sql .= "and levenshtein(ports.short_description, '$query') < 4";
				break;
      
			case "maintainer":
				$sql .= "and levenshtein(ports.maintainer, '$query') < 4";
				break;

			case "messageid":
				$sql .= "and commit_log.message_id = '$query'";
		}
}

#
# include/exclude deleted ports
#
switch ($deleted) {
	case "includedeleted":
		# do nothing
		break;

	default:
		$deleted = "excludedeleted";
		# do not break here...

	case "excludedeleted":
		$sql .= " and element.status = 'A' ";
}

$sql .= " order by categories.name, element.name";

if ($num < 1 or $num > 500) {
	$num = 10;
}

$sql .= " limit $num";

$AddRemoveExtra  = "&&origin=$SCRIPT_NAME?query=" . $query. "+stype=$stype+num=$num+method=$method";
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
	echo pg_errormessage() . $sql;
	exit;
}
$NumRows = pg_numrows($result);

$fp = fopen($logfile, "a");
if ($fp) {
	switch ($method) {
		case "match":
		case "exact":
		case "soundex":
			fwrite($fp, date("Y-m-d H:i:s") . " $stype : $method : $query : $num : $NumRows : $deleted\n");
			break;

		default: 
			fwrite($fp, date("Y-m-d H:i:s") . " $stype : $method : $category/$port : $num : $NumRows : $deleted\n");
	}
	fclose($fp);
} else {
	print "Please let postmaster@freshports.org know that the search log could not be opened.  This does not affect the search results.\n";
	define_syslog_variables();
	syslog(LOG_ERR, "FreshPorts could not open the search log file: $logfile");
}


$Port = new Port($db);
$Port->LocalResult = $result;

}
?>
<form METHOD="POST" ACTION="<? echo $_SERVER["PHP_SELF"] ?>">
NOTE: All searches are case sensitive.<BR>
Search for:<BR>
	<SELECT NAME="stype" size="1">
		<OPTION VALUE="name"             <? if ($stype == "name")             echo 'SELECTED'?>>Port Name</OPTION>
		<OPTION VALUE="maintainer"       <? if ($stype == "maintainer")       echo 'SELECTED'?>>Maintainer</OPTION>
		<OPTION VALUE="shortdescription" <? if ($stype == "shortdescription") echo 'SELECTED'?>>Short Description</OPTION>
		<OPTION VALUE="messageid"        <? if ($stype == "messageid")        echo 'SELECTED'?>>Message ID</OPTION>
	</SELECT> 

	<SELECT name=method>
		<OPTION VALUE="exact"   <?if ($method == "exact"  ) echo 'SELECTED' ?>>equal to
		<OPTION VALUE="match"   <?if ($method == "match"  ) echo 'SELECTED' ?>>containing
		<OPTION VALUE="soundex" <?if ($method == "soundex") echo 'SELECTED' ?>>sounding like
	</SELECT>

	<INPUT NAME="query" size="20"  VALUE="<? echo stripslashes($query)?>">

	<SELECT name=num>
		<OPTION VALUE="10"  <?if ($num == 10)  echo 'SELECTED' ?>>10 results
		<OPTION VALUE="20"  <?if ($num == 20)  echo 'SELECTED' ?>>20 results
		<OPTION VALUE="30"  <?if ($num == 30)  echo 'SELECTED' ?>>30 results
		<OPTION VALUE="50"  <?if ($num == 50)  echo 'SELECTED' ?>>50 results
		<OPTION VALUE="100" <?if ($num == 100) echo 'SELECTED' ?>>100 results
		<OPTION VALUE="500" <?if ($num == 500) echo 'SELECTED' ?>>500 results
	</SELECT> 

	<BR>

	<INPUT TYPE=radio <? if ($deleted == "excludedeleted") echo 'CHECKED'; ?> VALUE=excludedeleted NAME=deleted> Do not include deleted ports
	<INPUT TYPE=radio <? if ($deleted == "includedeleted") echo 'CHECKED'; ?> VALUE=includedeleted NAME=deleted> Include deleted ports

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

	<INPUT TYPE="submit" VALUE="search">
  <INPUT TYPE="hidden" NAME="search" VALUE="1">
</form>

</td></tr>
<?
if ($search) {
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
$ShowEverything  = 0;
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";
$ShowChangesLink      = "Y";
$ShowDescriptionLink  = "Y";
$ShowHomepageLink     = "Y";
$ShowDownloadPortLink = "Y";

	for ($i = 0; $i < $NumRows; $i++) {
		$Port->FetchNth($i);
		$HTML .= freshports_PortDetails($Port, $Port->dbh, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, 
                     $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, 
                     $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, 
                     $ShowPackageLink, $ShowShortDescription, 1, $AddRemoveExtra, 1);
   }

}

$HTML .= "Number of ports found: $NumRows<BR>";

echo $HTML;
echo "</td></tr>\n";
}
?>
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
