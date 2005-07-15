<?php
	#
	# $Id: missing-port.php,v 1.1.2.58 2005-07-15 03:08:33 dan Exp $
	#
	# Copyright (c) 2001-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/master_slave.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports_updating.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/files.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/watch-lists.php');

#
# tell the robots not to follow links from this page.
# see include/freshports.php for more information
#
GLOBAL $g_NOFOLLOW;

$g_NOFOLLOW = 1;

DEFINE('COMMIT_DETAILS', 'files.php');


function freshports_PortDescription($db, $element_id) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;
	GLOBAL $User;

	$port = new Port($db);
	$port->FetchByElementID($element_id, $User->id);

	freshports_ConditionalGet($port->last_modified);

	header("HTTP/1.1 200 OK");

	$Title = $port->category . "/" . $port->port;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start($Title,
	        		"$FreshPortsTitle - new ports, applications",
					"FreeBSD, index, applications, ports");

?>


<?php echo freshports_MainTable(); ?>

<tr><TD VALIGN="top" width="100%">

<?php echo freshports_MainContentTable(); ?>

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
$ShowMasterSlave		= 1;

GLOBAL $ShowWatchListCount;

	$HTML = freshports_PortDetails($port, $port->dbh, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, 0, '', 1, "N", 1, 1, $ShowWatchListCount, $ShowMasterSlave);
	echo $HTML;

	echo '<DL><DD>';
    echo '<PRE CLASS="code">' . htmlify(htmlspecialchars($port->long_description)) . '</PRE>';
	echo "\n</DD>\n</DL>\n";

	echo "</TD></TR>\n</TABLE>\n\n";


	$PortsUpdating   = new PortsUpdating($port->dbh);
	$NumRowsUpdating = $PortsUpdating->FetchInitialise($port->id);

	freshports_UpdatingOutput($NumRowsUpdating, $PortsUpdating, $port);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports_moved.php');

	$PortsMovedFrom = new PortsMoved($port->dbh);
	$NumRowsFrom    = $PortsMovedFrom->FetchInitialiseFrom($port->id);

	$PortsMovedTo   = new PortsMoved($port->dbh);
	$NumRowsTo      = $PortsMovedTo->FetchInitialiseTo($port->id);

	if ($NumRowsFrom + $NumRowsTo > 0) {
		echo '<TABLE BORDER="1" width="100%" CELLSPACING="0" CELLPADDING="5">' . "\n";
		echo "<TR>\n";
		echo freshports_PageBannerText("Port Moves", 1);
		echo "<tr><td>\n";
		echo "<ul>\n";
	}

	for ($i = 0; $i < $NumRowsFrom; $i++) {
		$PortsMovedFrom->FetchNth($i);
		echo '<li>' . freshports_PortsMoved($port, $PortsMovedFrom);
		if ($i + 1 != $NumRowsFrom) {
			echo '<br>';
		}
		echo "</li>\n";
	}

	for ($i = 0; $i < $NumRowsTo; $i++) {
		$PortsMovedTo->FetchNth($i);
		echo '<li>' . freshports_PortsMoved($port, $PortsMovedTo);
		if ($i + 1 != $NumRowsTo) {
			echo '<br>';
		}
		echo "</li>\n";
	}
	
	if ($NumRowsFrom + $NumRowsTo > 0) {
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
  echo freshports_SideBar();
  ?>
  </td>
</TR>

</TABLE>

<?
	echo freshports_ShowFooter();
?>

</body>
</html>

<?
}

?>