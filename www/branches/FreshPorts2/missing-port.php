<?php
	#
	# $Id: missing-port.php,v 1.1.2.48 2003-12-31 16:06:20 dan Exp $
	#
	# Copyright (c) 2001-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');

DEFINE('COMMIT_DETAILS', 'files.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/files.php');

function freshports_PortDescription($db, $element_id) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;
	GLOBAL $User;

	$port = new Port($db);
	$port->FetchByElementID($element_id, $User->id);

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

	$PortsMoved = new PortsMoved($port->dbh);
	$numrows = $PortsMoved->FetchInitialiseTo($port->id);

	if ($numrows > 0) {
		echo '<TABLE BORDER="1" width="100%" CELLSPACING="0" CELLPADDING="5">' . "\n";
		echo "<TR>\n";
		echo freshports_PageBannerText("Port Moves", 1);
		echo "<tr><td>\n";
		echo "<ul>\n";
	}

	for ($i = 0; $i < $numrows; $i++) {
		$PortsMoved->FetchNth($i);
		echo '<li>' . freshports_PortsMoved($port, $PortsMoved) . "</li>\n";
		if ($i + 1 != $numrows) {
			echo '<br>';
		}
	}
	
	if ($numrows > 0) {
		echo "</ul>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
	}

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

?>