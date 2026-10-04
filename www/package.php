<?php
	#
	# $Id: package.php,v 1.2 2006-12-17 12:06:13 dan Exp $
	#
	# Copyright (c) 2004-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/searches.php');

	$Title = _('Search by package');

	freshports_Start("$Title",
					$Title,
					"FreeBSD, index, applications, ports");

	$notfound = 0;
	if (IsSet($_REQUEST['notfound'])) $notfound = 1;
	if (IsSet($_REQUEST['multiple'])) $multiple = 1;

	if (IsSet($_REQUEST['package'])) {
		# not used in SQL here; escaped on output
		$package  = $_REQUEST['package'];
	} else {
		$package = '';
	}

	$Searches = new Searches($db);

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>

<tr>
	<?php echo freshports_PageBannerText($Title); ?>
</tr>

<tr><td>
<P>

<?php
if ($notfound) {
$packages_html = htmlspecialchars($package);
?>
<?php printf(_("The package specified ('%s') could not be found.  We have a few suggestions."), $packages_html); ?>
<ul>
<li><?php printf(_('%1$s for ports containing \'%2$s\' in their name.'), '<a href="' . htmlspecialchars($Searches->GetDefaultSearchStringPackage($package)) . '">' . _('Search') . '</a>', $packages_html); ?>
</ul>
<?php
} else {
	die(_('I have no idea what I should be doing'));
}
?>
</P>
</td></tr>

</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</table>

<?php
	GLOBAL $ShowPoweredBy;
	$ShowPoweredBy = 1;
?>

<table class="fullwidth borderless">
<tr><td>
<?php echo freshports_ShowFooter(); ?>
</td></tr>
</table>

</BODY>
</HTML>
