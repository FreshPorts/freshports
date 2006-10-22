<?php
	#
	# $Id: missing-non-port.php,v 1.1.2.8 2006-10-22 16:17:38 dan Exp $
	#
	# Copyright (c) 2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

function freshports_NonPortDescription($db, $element_record) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;

	freshports_ConditionalGet(freshports_LastModified());

	header("HTTP/1.1 200 OK");
	$Title = preg_replace('|^/?ports/|', '', $element_record->element_pathname);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start($Title,
	        		"$FreshPortsTitle - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>
<TR>
<? echo freshports_PageBannerText('non port: ' . $Title); ?>
</TR>
<tr><td>
<a HREF="<?php echo FRESHPORTS_FREEBSD_CVS_URL . $element_record->element_pathname; ?>">CVSWeb</a>
</td></tr>

<?

    $Commits = new Commits($db);
    
    $Commits->SetLimit(100);
    $Commits->Debug = 1;
	$Commits->UserIDSet($User->id);
	$Commits->TreePathConditionSet("= '" . $element_record->element_pathname . "'");
	$NumFetches = $Commits->FetchByTreePath();
	$DisplayCommit = new DisplayCommit($Commits->LocalResult);
	$HTML .= $DisplayCommit->CreateHTML();
	echo $HTML;
	echo "</table>\n"

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