.<?php
	#
	# $Id: package.php,v 1.2 2006-12-17 12:06:13 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/searches.php');

	$Title = 'Search by package';

	freshports_Start("$Title",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

	if (IsSet($_REQUEST['notfound'])) $notfound = 1;
	if (IsSet($_REQUEST['multiple'])) $multiple = 1;

	$package  = pg_escape_string($_REQUEST['package']);

	$Searches = new Searches($dbh);

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>

<TR>
	<? echo freshports_PageBannerText($Title); ?>
</TR>

<TR><TD>
<P>

<?php
if ($notfound) {
?>
The package specified ('<?php echo $package; ?>') could not be found.  We have a few suggestions.
<ul>
<li><a href="<?php echo $Searches->GetDefaultSearchString($package); ?>">Search</a> for ports containing '<?php echo $package; ?>' in their name.
<li><a href="<?php echo $Searches->GetDefaultSoundsLikeString($package); ?>">Search</a> for ports which sound like '<?php echo $package; ?>'.
</ul>
<?php
} else {
	die('I have no idea what I should be doing');
}
?>
</P>
</TD></TR>

</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TABLE>

<?php
	GLOBAL $ShowPoweredBy;
	$ShowPoweredBy = 1;
?>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? echo freshports_ShowFooter(); ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
