.<?php
	#
	# $Id: search.php,v 1.1.2.72 2005-01-22 14:48:53 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	
	define('ORDERBYPORT',       'port');
	define('ORDERBYCATEGORY',   'category');
	define('ORDERBYASCENDING',  'asc');
	define('ORDERBYDESCENDING', 'desc');

	$Debug = 0;
#	if ($Debug) phpinfo();

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

	// If these items are missing from the URL, we want them to have a value
	$query				= '';
	$stype				= 'name';
	$num				= '10';
	$category			= '';
	$port				= '';
	$method				= '';
	$deleted			= 'excludedeleted';
	$casesensitivity	= 'caseinsensitive';
	$start				= '1';
	$orderby            = ORDERBYCATEGORY;
	$orderbyupdown		= ORDERBYASCENDING;

	// avoid nasty problems by adding slashes
	if (IsSet($_REQUEST['query']))           $query				= AddSlashes(trim($_REQUEST['query']));
	if (IsSet($_REQUEST['stype']))           $stype				= AddSlashes(trim($_REQUEST['stype']));
	if (IsSet($_REQUEST['num']))             $num				= AddSlashes(trim($_REQUEST['num']));
	if (IsSet($_REQUEST['category']))        $category			= AddSlashes(trim($_REQUEST['category']));
	if (IsSet($_REQUEST['port']))            $port				= AddSlashes(trim($_REQUEST['port']));
	if (IsSet($_REQUEST['method']))          $method			= AddSlashes(trim($_REQUEST['method']));
	if (IsSet($_REQUEST['deleted']))         $deleted			= AddSlashes(trim($_REQUEST['deleted']));
	if (IsSet($_REQUEST['casesensitivity'])) $casesensitivity	= AddSlashes(trim($_REQUEST['casesensitivity']));
	if (IsSet($_REQUEST['start']))           $start				= intval(AddSlashes(trim($_REQUEST['start'])));
	if (IsSet($_REQUEST['orderby']))         $orderby			= AddSlashes(trim($_REQUEST['orderby']));
	if (IsSet($_REQUEST['orderbyupdown']))   $orderbyupdown		= AddSlashes(trim($_REQUEST['orderbyupdown']));

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

<?php echo freshports_MainTable(); ?>
<tr><td valign="top" width="100%">
<?php echo freshports_MainContentTable(); ?>
  <tr>
	<? echo freshports_PageBannerText("Search"); ?>
  </tr>
<tr><td valign="top">
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

if (IsSet($_REQUEST['query'])) {
	$search = $_REQUEST['query'];
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
         ports.deprecated, 
         ports.ignore, 
         ports.forbidden,
         ports.latest_link,
         ports.master_port,
         ports.latest_link,
         ports.no_package,
         ports.package_name,
         ports.no_package  ";

	if ($User->id) {
		$sql .= ",
         onwatchlist";
   }

	$sql .= "
    from ports, categories, commit_log, commit_log_ports_elements, element  ";

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
      and commit_log.id      = commit_log_ports_elements.commit_log_id
      and ports.element_id   = commit_log_ports_elements.element_id ' ;


switch ($method) {
	case 'match':
		if ($casesensitivity == 'casesensitive') {
			$Like = 'LIKE';
		} else {
			$Like = 'ILIKE';
		}
		switch ($stype) {
			case 'name':
				$sql .= "\n     and element.name $Like '%$query%'";
				break;

			case 'package':
				$sql .= "\n     and ports.package_name $Like '%$query%'";
				break;

			case 'latest_link':
				$sql .= "\n     and ports.latest_link $Like '%$query%'";
				break;

			case 'shortdescription':
				$sql .= "\n     and ports.short_description $Like '%$query%'";
				break;
      
			case 'longdescription':
				$sql .= "\n     and ports.long_description $Like '%$query%'";
				break;
      
			case 'depends_build':
				$sql .= "\n     and ports.depends_build $Like '%$query%'";
				break;
      
			case 'depends_lib':
				$sql .= "\n     and ports.depends_lib $Like '%$query%'";
				break;

			case 'depends_run':
				$sql .= "\n     and ports.depends_run $Like '%$query%'";
				break;

			case 'depends_all':
				$sql .= "\n     and (ports.depends_build $Like '%$query%' OR ports.depends_lib $Like '%$query%' OR ports.depends_run $Like '%$query%')";
				break;

			case 'maintainer':
				$sql .= "\n     and ports.maintainer $Like '%$query%'";
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

			case 'package':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.package_name = '$query'";
				} else {
					$sql .= "\n     and lower(ports.package_name) = lower('$query')";
				}
				break;

			case 'latest_link':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.latest_link = '$query'";
				} else {
					$sql .= "\n     and lower(ports.latest_link) = lower('$query')";
				}
				break;

			case 'shortdescription':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.short_description = '$query'";
				} else {
					$sql .= "\n     and lower(ports.short_description) = lower('$query')";
				}
				break;
      
			case 'longdescription':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.long_description = '$query'";
				} else {
					$sql .= "\n     and lower(ports.long_description) = lower('$query')";
				}
				break;
      
			case 'depends_build':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.depends_build = '$query'";
				} else {
					$sql .= "\n     and lower(ports.depends_build) = lower('$query')";
				}
				break;

			case 'depends_lib':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.depends_lib = '$query'";
				} else {
					$sql .= "\n     and lower(ports.depends_lib) = lower('$query')";
				}
				break;

			case 'depends_run':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and ports.depends_run = '$query'";
				} else {
					$sql .= "\n     and lower(ports.depends_run) = lower('$query')";
				}
				break;

			case 'depends_all':
				if ($casesensitivity == 'casesensitive') {
					$sql .= "\n     and (ports.depends_build = '$query' OR ports.depends_lib = '$query' OR ports.depends_run = '$query')";
				} else {
					$sql .= "\n     and (lower(ports.depends_build) = lower('$query') OR lower(ports.depends_lib) = lower('$query') OR lower(ports.depends_run) = lower('$query'))";
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

			case 'package':
				$sql .= "\n     and levenshtein(ports.package_name, '$query') < 4";
				break;

			case 'latest_link':
				$sql .= "\n     and levenshtein(ports.latest_link, '$query') < 4";
				break;

			case 'shortdescription':
				$sql .= "\n     and levenshtein(ports.short_description, '$query') < 4";
				break;

			case 'longdescription':
				$sql .= "\n     and levenshtein(ports.long_description, '$query') < 4";
				break;

			case 'depends_build':
				$sql .= "\n     and levenshtein(ports.depends_build, '$query') < 4";
				break;

			case 'depends_lib':
				$sql .= "\n     and levenshtein(ports.depends_lib, '$query') < 4";
				break;

			case 'depends_run':
				$sql .= "\n     and (levenshtein(ports.depends_build, '$query') < 4";
				break;

			case 'depends_all':
				$sql .= "\n     and (levenshtein(ports.depends_build, '$query') < 4 OR levenshtein(ports.depends_lib, '$query') < 4 OR levenshtein(ports.depends_run, '$query') < 4)";
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

switch ($orderby) {
	case ORDERBYCATEGORY:
		switch ($orderbyupdown) {
			case ORDERBYDESCENDING:
			default:
				$sql .= "\n order by categories.name desc, element.name";
				break;

			case ORDERBYASCENDING:
				$sql .= "\n order by categories.name, element.name";
				break;
		}
		break;

	case ORDERBYPORT:
	default:
		switch ($orderbyupdown) {
			case ORDERBYDESCENDING:
			default:
				$sql .= "\n order by element.name desc, categories.name";
				break;

			case ORDERBYASCENDING:
				$sql .= "\n order by element.name, categories.name";
				break;
		}
		break;
}

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
	echo pg_errormessage() . '<pre>' . $sql . '</pre>';
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
		<OPTION VALUE="package"          <? if ($stype == "package")          echo 'SELECTED'?>>Package Name</OPTION>
		<OPTION VALUE="latest_link"      <? if ($stype == "latest_link")      echo 'SELECTED'?>>Latest Link</OPTION>
		<OPTION VALUE="maintainer"       <? if ($stype == "maintainer")       echo 'SELECTED'?>>Maintainer</OPTION>
		<OPTION VALUE="shortdescription" <? if ($stype == "shortdescription") echo 'SELECTED'?>>Short Description</OPTION>
		<OPTION VALUE="longdescription"  <? if ($stype == "longdescription")  echo 'SELECTED'?>>Long Description</OPTION>
		<OPTION VALUE="depends_build"    <? if ($stype == "depends_build")    echo 'SELECTED'?>>Depends Build</OPTION>
		<OPTION VALUE="depends_lib"      <? if ($stype == "depends_lib")      echo 'SELECTED'?>>Depends Lib</OPTION>
		<OPTION VALUE="depends_run"      <? if ($stype == "depends_run")      echo 'SELECTED'?>>Depends Run</OPTION>
		<OPTION VALUE="depends_all"      <? if ($stype == "depends_all")      echo 'SELECTED'?>>Depends Build/Lib/Run</OPTION>
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

	<BR><br>

<table cellpadding="5" cellspacing="0" border="0">
<tr>
<td>
	<INPUT TYPE=checkbox <? if ($deleted == "includedeleted") echo 'CHECKED'; ?> VALUE=includedeleted NAME=deleted> Include deleted ports
</td>
<td>
	<INPUT TYPE=checkbox <? if ($casesensitivity == "casesensitive")   echo 'CHECKED'; ?> VALUE=casesensitive   NAME=casesensitivity> Case sensitive search
<td>
	Sort by: <SELECT name="orderby">
		<OPTION VALUE="<?php echo ORDERBYPORT;     ?>" <?if ($orderby == ORDERBYPORT        ) echo 'SELECTED' ?>>Port
		<OPTION VALUE="<?php echo ORDERBYCATEGORY; ?>" <?if ($orderby == ORDERBYCATEGORY    ) echo 'SELECTED' ?>>Category
	</SELECT>

	<SELECT name="orderbyupdown">
		<OPTION VALUE="<?php echo ORDERBYASCENDING;  ?>" <?if ($orderbyupdown == ORDERBYASCENDING  ) echo 'SELECTED' ?>>ascending
		<OPTION VALUE="<?php echo ORDERBYDESCENDING; ?>" <?if ($orderbyupdown == ORDERBYDESCENDING ) echo 'SELECTED' ?>>descending
	</SELECT>
</td>
<td>
	<INPUT TYPE="submit" VALUE="Search" NAME="search">
</td>
</tr>
</table>
</form>

<p>
<small>Case sensitivity is ignored for "sounding like".<BR>
When searching on 'Message ID' only exact matches will succeed.</small>
</p>
<?php

if ($User->id != '') {
?>
<p>
Special searches:
</p>
<ul>
<li>	<FORM ACTION="/search.php" NAME="f">
	<INPUT NAME="query"           TYPE="hidden" value="<?php GLOBAL $User; echo $User->email; ?>">
	<INPUT NAME="num"             TYPE="hidden" value="10">
	<INPUT NAME="stype"           TYPE="hidden" value="maintainer">
	<INPUT NAME="method"          TYPE="hidden" value="match">
	<INPUT NAME="deleted"         TYPE="hidden" value="excludedeleted">
	<INPUT NAME="start"           TYPE="hidden" value="1">
  	<INPUT NAME="casesensitivity" TYPE="hidden" value="caseinsensitive">
    <INPUT TYPE="submit" VALUE="Ports I Maintain" NAME="search">
	</FORM>

</ul>
<?php
}
?>
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
		if (preg_match("/start=(\d+)/e", $QueryString)) {
			$QueryString = preg_replace("/start=(\d+)/e", "'start=' . max(1, ($start - $num))", $QueryString);
		} else {
			$QueryString .= '&start=' . max(1, ($start - $num));
		}
		$NumPortsFound .= ' <a href="' . $_SERVER['PHP_SELF'] . '?' . htmlify(htmlspecialchars($QueryString)) . '">Previous page</a>';
	}

	if ($MoreToShow) {
		$QueryString = $_SERVER['QUERY_STRING'];
		if (preg_match("/start=(\d+)/e", $QueryString)) {
			$QueryString = preg_replace("/start=(\d+)/e", "'start=' . ($start + $num)", $QueryString);
		} else {
			$QueryString .= '&start=' . ($start + $num);
		}
		$NumPortsFound .= ' <a href="' . $_SERVER['PHP_SELF'] . '?' . htmlify(htmlspecialchars($QueryString)) . '">Next page</a>';
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
		$Port->LoadVulnerabilities();
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
