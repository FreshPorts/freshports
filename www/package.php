<?php
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
					$Title,
					"FreeBSD, index, applications, ports");

	if (IsSet($_REQUEST['notfound'])) $notfound = 1;
	if (IsSet($_REQUEST['multiple'])) $multiple = 1;

	$package  = pg_escape_string($db, $_REQUEST['package']);

	$Searches = new Searches($dbh);

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>

<TR>
	<? echo freshports_PageBannerText($Title); ?>
</TR>

<TR><TD>
<P>

<?php
if ($notfound) {
$packages_html = htmlspecialchars($package);
?>
The package specified ('<?php echo $packages_html; ?>') could not be found.  We have a few suggestions.
<ul>
<li><a href="<?php echo $Searches->GetDefaultSearchStringPackage($packages_html); ?>">Search</a> for ports containing '<?php echo $packages_html; ?>' in their name.
</ul>
<?php
} else {
	die('I have no idea what I should be doing');
}
?>
</P>
</TD></TR>

</TABLE>
</td>

  <td class="sidebar">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TABLE>

<?php
	GLOBAL $ShowPoweredBy;
	$ShowPoweredBy = 1;
?>

<TABLE class="fullwidth borderless">
<TR><TD>
<? echo freshports_ShowFooter(); ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
