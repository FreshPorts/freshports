<?php
	#
	# $Id: missing-port.php,v 1.1.2.42 2003-07-04 14:59:17 dan Exp $
	#
	# Copyright (c) 2001-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');

DEFINE('COMMIT_DETAILS', 'files.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/files.php');


function freshports_Parse404CategoryPort($REQUEST_URI, $db) {
	GLOBAL $User;

	$Debug = 0;

	unset($CategoryName);
	unset($PortName);
	unset($FileName);

	if ($Debug) echo "you asked for $REQUEST_URI<BR>";

	$result = "";
	$url_Array = explode('/', $REQUEST_URI);
	if ($Debug) echo "count(\$url_Array) = '" . sizeof($url_Array) .  "' : '" . $url_Array[4] . "'<BR>";
	if (count($url_Array) >= 1) {
		$CategoryName = AddSlashes($url_Array[1]);
		$CategoryID   = freshports_CategoryId($CategoryName, $db);
		if ($CategoryID == '') {
			Unset($CategoryID);
		}

		if ($Debug) {
			echo "\$CategoryID='$CategoryID'";
			if (IsSet($CategoryID)) {
				echo ' great!, category is found';
			} else {
				echo ' damn!, category not found';
			}
			echo "<br>\n";
		}

		if (count($url_Array) >= 3) {
			$url_Item2 = AddSlashes($url_Array[2]);
			if (substr($url_Item2, 0, 1) == '?') {
				$Parms = $url_Item2;
			} else {
				$PortName = $url_Item2;
			}
		}

		if (count($url_Array) >= 4) {
			if ($Debug) echo "getting FileName<BR>";
			$FileName = AddSlashes($url_Array[3]);
		}

		if ($Debug) {
			echo "\$CategoryName = '$CategoryName'<BR>";
			echo "\$PortName     = '$PortName'<BR>";
			echo "\$FileName     = '$FileName'<BR>";
			echo "\$Parms        = '$Parms'<BR>";
		}

		if ($Debug) {
			echo "\$CategoryName = '$CategoryName' ($CategoryID)<BR>";
			echo "\$PortName     = '$PortName'<BR>";
		}

		if (IsSet($PortName) && $PortName != '') {
			$port = new Port($db);
			GLOBAL $User;

			$port->Fetch($CategoryName, $PortName, $User->id);

			if ($Debug) {
				if (IsSet($port->id)) {
					echo "port was found with id = $port->id<BR>";
				} else {
					echo "that port was not found<BR>";
				}
			}
		}

		if (IsSet($CategoryID)) {
#			echo "<A HREF=\"/category.php?category=$CategoryID\">this link</A> should take you to the category details<BR>";
			if (IsSet($port->id)) {
				if ($FileName != '') {
					if (substr($FileName, 0, strlen(COMMIT_DETAILS)) == COMMIT_DETAILS) {
						if ($Debug) echo '$_SERVER["REDIRECT_QUERY_STRING"]="' . $_SERVER["REDIRECT_QUERY_STRING"] . '"<BR>';
						parse_str($_SERVER["REDIRECT_QUERY_STRING"], $query_parts);
						$message_id = $query_parts['message_id'];

						if ($Debug) echo '$message_id="' . $message_id . '"<br>';

						freshports_Files($User, $port->id, $message_id, $db);
					} else {
						$result = 'The category and port you specified both exist, but that extra bit I don\'t recognize: \'' . $FileName . '\'';
					}
				} else {
					freshports_PortDescription($port);
				}

			} else {
				if (IsSet($PortName) && $PortName != '' && !IsSet($port->id)) {
					$result = "The category <A HREF=\"/$CategoryName/\"><b>$CategoryName</b></A> exists but not the port <b>$PortName</b>.";
				} else {
					if (In_Array("REDIRECT_QUERY_STRING", $_SERVER)) {
						if (IsSet($_SERVER["REDIRECT_QUERY_STRING"])) {
							parse_str($_SERVER['REDIRECT_QUERY_STRING'], $query_parts);
							if (IsSet($query_parts['page']))      $page      = $query_parts['page'];
							if (IsSet($query_parts['page_size'])) $page_size = $query_parts['page_size'];
						}
					}

					if (!IsSet($page) || $page == '') {
						$page = 1;
					}

					if (!IsSet($page_size) || $page_size == '') {
						$page_size = $User->page_size;
					}

					if ($Debug) {
						echo "\$page      = '$page'<br>\n";
						echo "\$page_size = '$page_size'<br>\n";
					}
					
					require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-category.php');
					freshports_Category($db, $CategoryName, $page, $page_size);
				}
			}
		} else {
#			echo "no category '$CategoryName' found";
			$result = "There is no document by that name ('$REQUEST_URI')";
		}
	}

	return $result;
}


function freshports_PortDescription($port) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;

	header("HTTP/1.1 200 OK");
	$Title = $port->category . "/" . $port->port;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start($Title,
	        		"$FreshPortsTitle - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">
<tr><TD VALIGN="top" width="100%">
<TABLE BORDER="1" WIDTH="100%" CELLSPACING="0" CELLPADDING="5">
<TR>
<? echo freshports_PageBannerText("Port details"); ?>
</TR>

<tr><td valign="top" width="100%">

<?
	GLOBAL $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription;


$ShowCategories			= 1;
GLOBAL	$ShowDepends;
$ShowDepends				= 1;
$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink= $ShowDescriptionLink= $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
$HideDescription			= 1;
$ShowEverything			= 1;
$ShowShortDescription	= "Y";
$ShowMaintainedBy			= "Y";
$GlobalHideLastChange	= "Y";
$ShowDescriptionLink		= "N";

GLOBAL $ShowWatchListCount;

	$HTML = freshports_PortDetails($port, $port->dbh, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, 0, '', 1, "N", 1, 1, $ShowWatchListCount);
	echo $HTML;

	echo '<DL><DD>';
    echo '<PRE CLASS="code">' . htmlify(htmlspecialchars($port->long_description)) . '</PRE>';
	echo "\n</DD>\n</DL>\n";

	echo "</TD></TR>\n</TABLE>\n\n";
#	echo 'about to call freshports_PortCommits #############################';

	freshports_PortCommits($port);

?>

</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
  <?
  freshports_SideBar();
  ?>
  </td>
</TR>

</TABLE>

<?
	freshports_ShowFooter();
?>

</body>
</html>

<?
}

function freshports_CategoryId($category, $database) {
	#
	# we could improve efficiency here with a cache
	# if we had need...
	#
	$CategoryID = '';

	$sql = "select * from categories where name = '$category'";
	$result = pg_exec($database, $sql);
	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows == 1) {
			$myrow = pg_fetch_array ($result, 0);
			$CategoryID = $myrow["id"];
		}
	} else {
		echo 'pg_exec failed: ' . $sql;
	}

	return $CategoryID;
}

?>