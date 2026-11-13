<?php
	#
	# $Id: category-maintenance.php,v 1.2 2006-12-17 12:06:08 dan Exp $
	#
	# Copyright (c) 2003-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');

	$Title        = _('Category maintenance');
	$CategoryName = $_REQUEST['category'] ?? '';

	$Category = new Category($db);
	$CategoryID = $Category->FetchByName(pg_escape_string($db, $CategoryName));
	if (!$CategoryID) {
		die(sprintf(_("I don't know that category: %s"), htmlspecialchars($CategoryName)));
	}

	if ($Category->IsPrimary() == 't') {
		$IsPrimary = 1;
	} else {
		$IsPrimary = 0;
	}

	$AllowedToEdit = $User->IsTaskAllowed(FRESHPORTS_TASKS_CATEGORY_VIRTUAL_DESCRIPTION_SET);

	# Change the description only for someone allowed to, only by the form's
	# POST, and only for a virtual category (UpdateDescription() refuses a
	# primary one anyway).  This used to run for anyone, before the
	# permission check further down; only two PHP errors stopped it.
	# UpdateDescription() uses a parameterised query, so the text is not
	# escaped for SQL here.
	if ($AllowedToEdit && !$IsPrimary && $_SERVER['REQUEST_METHOD'] === 'POST' && IsSet($_POST['update'])) {
		$Category->description = (string) ($_POST['description'] ?? '');
		$Category->UpdateDescription();
	}



	freshports_Start($Title . ' - ' . htmlspecialchars($CategoryName),
					$Title,
					'FreeBSD, index, applications, ports');

?>
<table class="fullwidth borderless">
<tr><td class="content">
<table class="fullwidth borderless">

<tr>
	<?php echo freshports_PageBannerText($Title . ' - ' . htmlspecialchars($CategoryName)); ?>
</tr>
<tr><td>
<p>
<?php
if ($AllowedToEdit) {
?>
<?php echo _('This page allows you to maintain a category, if you have permission to do so.'); ?>


<p>

<?php
if (!$IsPrimary) {
	echo '<form action="/category-maintenance.php" method="POST" NAME=f>';
}
?>

<table class="cellpadding5 bordered">
<tr><td><b>id</b></td><td><b>is_primary</b></td><td><b>element_id</b></td><td><b>name</b></td><td><b>description</b></td></tr>
<tr><?php
echo '<td>' . $Category->id          . '</td>';

echo '<td>';
if ($IsPrimary) {
	echo 'TRUE';
} else {
	echo 'FALSE';
}
echo '</td>';

echo '<td>';
if (IsSet($Category->element_id)) {
	echo $Category->element_id;
} else {
	echo 'NULL';
}
echo '</td>';

echo '<td>' . htmlspecialchars($Category->name ?? '') . '</td>';
echo '<td>';
if ($IsPrimary) {
	echo htmlspecialchars($Category->description ?? '');
} else {
	echo '<INPUT id="description" name="description" value="' . htmlspecialchars($Category->description ?? '') . '" size="40">' . "\n";
}
echo '</td>';
?>
</tr>

<?php
if (!$IsPrimary) {
?>
<tr><td colspan="5" class="vcentered">
<p>
<INPUT id=default     style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="<?php echo htmlspecialchars(_('Update')); ?>"  name="update">
<INPUT TYPE="hidden" NAME="category" VALUE="<?php echo htmlspecialchars($CategoryName); ?>">
</p>
</td></tr>

<?php
}
?>

</table>

<?php
if (!$IsPrimary) {
	echo '</form>';
}
?>

<p>
<?php echo _('NOTES:'); ?>

<ul>
<li><?php echo sprintf(_('The official list of categories is in the %s.'), '<a href="https://www.freebsd.org/doc/en_US.ISO8859-1/books/porters-handbook/makefile-categories.html#PORTING-CATEGORIES">' . _('Porters Handbook') . '</a>'); ?>
<li><?php echo _('Primary categories are those which physically reside on disk.'); ?>
<li><?php echo _('Only virtual categories need to have their description values set.'); ?>
<li><?php echo _('FreshPorts will automatically obtain the description for physical categories from <code class="code">ports/pkg/COMMENT</code>.'); ?>
<li><?php echo _('See the above link for the description to use.'); ?>
<li><?php echo _('All changes are logged.'); ?>
</ul>


<?php
} else {
?>
<?php echo _("Well, I'm sorry to advise you that this page is intentionally left blank."); ?>
<?php
}
?>
</td></tr>
</table>
</td>

  <td class="sidebar">
  <?php
  echo freshports_SideBar();
  ?>
  </td>

</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
