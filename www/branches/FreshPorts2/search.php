<?php
	#
	# $Id: search.php,v 1.1.2.45 2003-07-30 12:06:38 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');

	$Debug = 0;

	#
	# I became annoyed with people creating their own search pages instead of using
	# mine... If the referrer isn't us, ignore them
	#

	if ($RejectExternalSearches  && $_SERVER["HTTP_REFERER"] != '') {
		$pos = strpos($_SERVER["HTTP_REFERER"], "http://" . $_SERVER["SERVER_NAME"]);
		if ($pos === FALSE || $pos != 0) {
			echo "Ouch, something really nasty is going on.  Error code: UAFC.  Please contact the webmaster with this message.";
			syslog(LOG_NOTICE, "External search form discovered: $_SERVER[HTTP_REFERER] $_SERVER[REMOTE_ADDR]:$_SERVER[REMOTE_PORT]");
			exit;
		}
	}

	$search = FALSE;
	$HTML   = '';

	// avoid nasty problems by adding slashes
	$query				= '';
	$stype				= '';
	$num					= '';
	$category			= '';
	$port					= '';
	$method				= '';
	$deleted				= '';
	$casesensitivity	= '';
	$start				= '';

	if (IsSet($_REQUEST['query']))           $query					= AddSlashes($_REQUEST['query']);
	if (IsSet($_REQUEST['stype']))           $stype					= AddSlashes($_REQUEST['stype']);
	if (IsSet($_REQUEST['num']))             $num					= AddSlashes($_REQUEST['num']);
	if (IsSet($_REQUEST['category']))        $category				= AddSlashes($_REQUEST['category']);
	if (IsSet($_REQUEST['port']))            $port					= AddSlashes($_REQUEST['port']);
	if (IsSet($_REQUEST['method']))          $method				= AddSlashes($_REQUEST['method']);
	if (IsSet($_REQUEST['deleted']))         $deleted				= AddSlashes($_REQUEST['deleted']);
	if (IsSet($_REQUEST['casesensitivity'])) $casesensitivity	= AddSlashes($_REQUEST['casesensitivity']);
	if (IsSet($_REQUEST['start']))           $start					= intval(AddSlashes($_REQUEST['start']));

	if ($stype == 'messageid') {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . "/commit.php?message_id=$query");
		exit;
	}

	if ($start < 1 || $start > 20000) {
		$start = 1;
	}

	#
	# ensure deleted has an appropriate value
	#
	switch ($deleted) {
		case 'includedeleted':
			# do nothing
			break;

		default:
			$deleted = 'excludedeleted';
			# do not break here...
	}


	#
	# ensure casesensitivity has an appropriate value
	#
	switch ($casesensitivity) {
		case 'casesensitive':
			# do nothing
			break;

		default:
			$casesensitivity = 'caseinsensitive';
			# do not break here...
	}


#	if ($Debug) phpinfo();

	freshports_Start('Search',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<tr><td valign="top" width="100%">                    
<table width="100%" border="0">                       
  <tr>
	<? echo freshports_PageBannerText("Search"); ?>
  </tr>
<tr><td valign="top">
<tr><td>
<?

#
# ensure that our parameters have default values
#

if ($num < 1 or $num > 500) {
	$num = 10;
}

if ($stype  == '') $stype  = 'name';
if ($method == '') $method = 'match';

if ($Debug) {
	echo "'$query' && '$stype' && '$num' && '$method'\n<BR>";

	if ($query && $stype && $num) {
		echo "yes, we have parameters\n<BR>";
	}
}

#
# we can take parameters.  if so, make it look like a post
#
if (IsSet($_REQUEST['search'])) {
	$search = $_REQUEST['search'];
}
if (!IsSet($search) && ($query && $stype && $num && $method)) {
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


$sql = "
  select distinct 
         ports.id, 
         element.name as port,
         categories.name as category, 
         categories.id as category_id, 
         ports.version as version, 
         ports.revision as revision, 
         ports.maintainer, 
         ports.short_description, 
         ports.package_exists, 
         ports.extract_suffix, 
         ports.homepage, 
         element.status, 
         ports.element_id, 
         ports.broken, 
         ports.forbidden ";

	if ($User->id) {
		$sql .= ",
         onwatchlist";
   }

	$sql .= "
    from ports, categories, commit_log, commit_log_ports, element  ";

	if ($User->id) {
			$sql .= "
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $User->id
       AND watch_list.in_service
  GROUP BY wle_element_id) AS TEMP
       ON TEMP.wle_element_id = element.id";
	}
	
	$sql .= '
	WHERE ports.category_id  = categories.id
     and ports.element_id   = element.id 
     and commit_log.id      = commit_log_ports.commit_log_id
     and commit_log_ports.port_id = ports.id  ' ;


switch ($method) {
	case 'match':
		switch ($stype) {
			case 'name':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and element.name like '%$query%'";
				} else {
					$sql .= "\n     and lower(element.name) like lower('%$query%')";
				}
				break;

			case 'shortdescription':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.short_description like '%$query%'";
				} else {
					$sql .= "\n     and lower(ports.short_description) like lower('%$query%')";
				}
				break;
      
			case 'maintainer':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.maintainer like '%$query%'";
				} else {
					$sql .= "\n     and lower(ports.maintainer) like lower('%$query%')";
				}
				break;
		}
		break;

	case 'exact':
		switch ($stype) {
			case 'name':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and element.name = '$query'";
				} else {
					$sql .= "\n     and lower(element.name) = lower('$query')";
				}
				break;

			case 'shortdescription':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.short_description = '$query'";
				} else {
					$sql .= "\n     and lower(ports.short_description) = lower('$query')";
				}
				break;
      
			case 'maintainer':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.maintainer = '$query'";
				} else {
					$sql .= "\n     and lower(ports.maintainer) = lower('$query')";
				}
				break;

		}
		break;

	default:
		switch ($stype) {
			case 'name':
				$sql .= "\n     and levenshtein(element.name, '$query') < 4";
				break;

			case 'shortdescription':
				$sql .= "\n     and levenshtein(ports.short_description, '$query') < 4";
				break;
      
			case 'maintainer':
				$sql .= "\n     and levenshtein(ports.maintainer, '$query') < 4";
				break;

		}
}

#
# include/exclude deleted ports
#
switch ($deleted) {
	case 'includedeleted':
		# do nothing
		break;

	default:
		$deleted = 'excludedeleted';
		# do not break here...

	case 'excludedeleted':
		$sql .= " and element.status = 'A' ";
}

$sql .= "\n order by categories.name, element.name";


#$sql .= "\n limit $num";

if ($start > 1) {
	$sql .= "\n OFFSET " . ($start - 1);
}

$AddRemoveExtra  = "&&origin=" . $_SERVER['SCRIPT_NAME'] . "?query=" . $query. "+stype=$stype+num=$num+method=$method";
if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";
$AddRemoveExtra = AddSlashes($AddRemoveExtra);
if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";

if ($Debug) {
	echo "<pre>$sql<pre>\n";

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
			fwrite($fp, date("Y-m-d H:i:s") . " $stype : $method : $query : $num : $NumRows : $deleted : $casesensitivity\n");
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
<form ACTION="<? echo $_SERVER["PHP_SELF"] ?>">
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

	<INPUT NAME="query" size="40"  VALUE="<? echo stripslashes($query)?>">

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

	<BR>

	<INPUT TYPE=radio <? if ($casesensitivity == "casesensitive")   echo 'CHECKED'; ?> VALUE=casesensitive   NAME=casesensitivity> Case sensitive search
	<INPUT TYPE=radio <? if ($casesensitivity == "caseinsensitive") echo 'CHECKED'; ?> VALUE=caseinsensitive NAME=casesensitivity> Case insensitive search

	<BR><BR>
	NOTE: Case sensitivity is ignored for "sounding like".<BR>
	NOTE: When searching on 'Message ID' only exact matches will succeed.

  <INPUT TYPE="hidden" NAME="search" VALUE="1">
  <INPUT TYPE="hidden" NAME="start"  VALUE="<? echo $start; ?>">
</form>

&nbsp;

</td></tr>
<?
if ($search) {
echo "<tr><td>\n";
if ($NumRows == 0) {
   $HTML .= " no results found<br>\n";
} else {

	$NumFetches = min($num, $NumRows);
	if ($NumFetches != $NumRows) {
		$MoreToShow = 1;
	} else {
		$MoreToShow = 0;
	}

	$NumPortsFound = 'Number of matches: ' . ($start + $NumRows - 1);
	if ($MoreToShow || $start > 1) {
		$NumPortsFound .= " (showing only $start - " . ($start + $NumFetches - 1) . ')';
	}

	if ($start > 1) {
		$QueryString = $_SERVER['QUERY_STRING'];
		$QueryString = preg_replace("/start=(\d+)/e", "'start=' . max(1, ($start - $num))", $QueryString);
		$NumPortsFound .= ' <a href="' . $_SERVER['PHP_SELF'] . '?' . $QueryString . '">Previous page</a>';
	}

	if ($MoreToShow) {
		$QueryString = $_SERVER['QUERY_STRING'];
		$QueryString = preg_replace("/start=(\d+)/e", "'start=' . ($start + $num)", $QueryString);
		$NumPortsFound .= ' <a href="' . $_SERVER['PHP_SELF'] . '?' . $QueryString . '">Next page</a>';
	}

	
	$HTML .= $NumPortsFound;

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
$HideCategory         = 'N';

	for ($i = 0; $i < $NumFetches; $i++) {
		$Port->FetchNth($i);
		$HTML .= freshports_PortDetails($Port, $Port->dbh, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, 
                     $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, 
                     $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, 
                     $ShowPackageLink, $ShowShortDescription, 1, $AddRemoveExtra, 1);
   }

	$HTML .= $NumPortsFound;
}


echo $HTML;
echo "</td></tr>\n";
}
?>
</table>

</td>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
  <?
  freshports_SideBar();
  ?>
  </td>

</tr>
</table>

<?
freshports_ShowFooter();
?>

</body>
</html>
