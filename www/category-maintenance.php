<?php
	#
	# $Id: category-maintenance.php,v 1.2 2006-12-17 12:06:08 dan Exp $
	#
	# Copyright (c) 2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');

	$Title        = 'Category maintenance';
	$CategoryName = pg_escape_string($db, $_REQUEST['category']);

	$Category = new Category($db);
	$CategoryID = $Category->FetchByName($CategoryName);
	if (!$CategoryID) {
		die("I don't know that category: $CategoryName");
	}

	if ($Category->IsPrimary() == 't') {
		$IsPrimary = 1;
	} else {
		$IsPrimary = 0;
	}

	if (IsSet($_REQUEST['update'])) {
		$Category->{description} = pg_escape_string($db, $_REQUEST['description']);
		$Category->UpdateDescription();
	}



	freshports_Start($Title . ' - ' . $CategoryName,
					$Title,
					'FreeBSD, index, applications, ports');

?>
<table class="fullwidth borderless" ALIGN="center">
<tr><td class="content">
<table class="fullwidth borderless">

<tr>
	<?php echo freshports_PageBannerText($Title . ' - ' . $CategoryName); ?>
</tr>
<tr><td>
<p>
<?php
if ($User->IsTaskAllowed(FRESHPORTS_TASKS_CATEGORY_VIRTUAL_DESCRIPTION_SET)) {
?>
This page allows you to maintain a category, if you have permission to do so.


<p>

<?php
if (!$IsPrimary) {
	echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST" NAME=f>';
}
?>

<table cellpadding="5" class="bordered">
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

echo '<td>' . $Category->name        . '</td>';
echo '<td>';
if ($IsPrimary) {
	echo $Category->description;
} else {
	echo '<INPUT id="description" name="description" value="' . $Category->description  . '" size="40">' . "\n";
}
echo '</td>';
?>
</tr>

<?php
if (!$IsPrimary) {
?>
<tr><td colspan="5" class="vcentered">
<p>
<INPUT id=default     style="WIDTH: 85px; HEIGHT: 24px" type=submit size=29 value="Update"  name="update">
<INPUT TYPE="hidden" NAME="category" VALUE="<?php echo $CategoryName; ?>">
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
NOTES:

<ul>
<li>The official list of categories is in the 
<a href="https://www.freebsd.org/doc/en_US.ISO8859-1/books/porters-handbook/makefile-categories.html#PORTING-CATEGORIES">Porters Handbook</a>.
<li>Primary categories are those which physically reside on disk.
<li>Only virtual categories need to have their description values set.
<li>FreshPorts will automatically obtain the description for physical categoriers from <code class="code">ports/pkg/COMMENT</code>.
<li>See the above link for the description to use.
<li>All changes are logged.
</ul>


<?php
} else {
?>
Well, I'm sorry to advise you that this page is intentionally left blank.
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
