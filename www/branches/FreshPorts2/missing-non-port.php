<?php
	#
	# $Id: missing-non-port.php,v 1.1.2.3 2004-07-05 19:37:25 dan Exp $
	#
	# Copyright (c) 2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');

function freshports_NonPortDescription($db, $element_record) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;

	header("HTTP/1.1 200 OK");
	$Title = preg_replace('|^/?ports/|', '', $element_record->element_pathname);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start($Title,
	        		"$FreshPortsTitle - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">
<tr><TD VALIGN="top" width="100%">
<TABLE BORDER="1" WIDTH="100%" CELLSPACING="0" CELLPADDING="5">
<TR>
<? echo freshports_PageBannerText($Title); ?>
</TR>
<tr><td>
<a HREF="<?php echo FRESHPORTS_FREEBSD_CVS_URL . $element_record->element_pathname; ?>">CVSWeb</a>
</td></tr>
</table>

<tr><td valign="top" width="100%">

<?

	freshports_Commits($element_record);

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