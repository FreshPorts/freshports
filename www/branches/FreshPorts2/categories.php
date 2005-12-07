<?php
	#
	# $Id: categories.php,v 1.1.2.31 2005-12-07 23:04:06 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/user_tasks.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');

	$Commit = new Commit($db);
	$Commit->DateNewestPort();

	freshports_ConditionalGet($Commit->last_modified);

	freshports_Start('Categories',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');
					
	$Debug = 0;

	DEFINE('VIRTUAL', '<sup>*</sup>'); 
	$Primary['t'] = '';
    $Primary['f'] = VIRTUAL;

	$AllowedToEdit = $User->IsTaskAllowed(FRESHPORTS_TASKS_CATEGORY_VIRTUAL_DESCRIPTION_SET);

	if ($AllowedToEdit) {
		$ColSpan = 5;
	} else {
	   	$ColSpan = 4;
	}

?>

<?php
	echo freshports_MainTable();
?>

<tr><td colspan="<?php echo $ColSpan; ?>" valign="top" width="100%">

<?php echo freshports_MainContentTable(BORDER, 6); ?>

  <tr>
	<? echo freshports_PageBannerText("$FreshPortsTitle - list of categories", $ColSpan); ?>
  </tr>
<tr><td COLSPAN="<?php echo $ColSpan; ?>" valign="top">
<P>
This page lists the categories and can be sorted by various criteria.  Virtual
categories are indicated by <?php echo VIRTUAL; ?>.
</P>

<P>
You can sort each column by clicking on the header.  e.g. click on <b>Category</b> to sort by category.
</P>

<?php
	if (file_exists(CACHE_CATEGORIES) && is_readable(CACHE_CATEGORIES)) {
		readfile(CACHE_CATEGORIES);
	} else {
?>
<p>
<big>Oops!</big>
Sorry, the category summary it not available just now.  It should appear within five minutes.
If it does not, please feel free to notify the webmaster who will promptly fix the problem.
</td></tr>
</table>
<?php
	}
?>


  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
