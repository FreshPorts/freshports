<?php
	#
	# $Id: missing-category.php,v 1.1.2.22 2003-03-06 14:20:43 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');

DEFINE('MAX_PAGE_SIZE',     500);
DEFINE('DEFAULT_PAGE_SIZE', 25);

function freshports_CategoryNextPreviousPage($CategoryName, $PortCount, $PageNo, $PageSize) {

	echo "\n";

	if ($PageNo == 1) {
		echo '&lt;-';
	} else {
		echo '<a href="/' . $CategoryName . '/?page=' . ($PageNo - 1) . '">&lt;-</a> ';
		echo "\n";
	}

	$NumPages = ceil($PortCount / $PageSize);
#	echo "Number of Pages = '$NumPages'<br>\n";

	for ($i = 1; $i <= $NumPages; $i++) {
		if ($i == $PageNo) {
			echo " $i";
			echo "\n";
		} else {
			echo ' <a href="/' . $CategoryName . '/?page=' . $i .  '">' . $i . '</a>';
			echo "\n";
		}
	}

	if ($PageNo == $NumPages) {
		echo ' -&gt;';
	} else {
		echo ' <a href="/' . $CategoryName . '/?page=' . $NumPages .  '">-&gt;</a>';
		echo "\n";
	}
}

function str_is_int($str) {
	$var = intval($str);
	return ($str == $var);
}


function freshports_Category($db, $CategoryName, $PageNo = 1, $PageSize = 25) {

	GLOBAL $TableWidth;
	header('HTTP/1.1 200 OK');

	$Debug = 0;

	SetType($PageNo,   "integer");
	SetType($PageSize, "integer"); 

	if (!IsSet($PageNo)   || !str_is_int("$PageNo")   || $PageNo   < 1) {
		$PageNo = 1;
	}

	if (!IsSet($PageSize) || !str_is_int("$PageSize") || $PageSize < 1 || $PageSize > MAX_PAGE_SIZE) {	
		$PageSize = DEFAULT_PAGE_SIZE;
	}

	if ($Debug) {
		echo "\$PageNo   = '$PageNo'<br>\n";
		echo "\$PageSize = '$PageSize'<br>\n";
	}

	

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');

	$category = new Category($db);
	$category->FetchByName($CategoryName);
	$title = $category->{name};

	# find out how many ports are in this category
	$PortCount = $category->PortCount($CategoryName);

	GLOBAL $User;
	if ($Debug) echo "\$User->id='$User->id'";

	freshports_Start($title,
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$DESC_URL = 'ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports';

	$port = new Port($db);

	$numrows = $port->FetchByCategoryInitialise($CategoryName, $User->id, $PageSize, $PageNo);

	?>


	<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
	<tr><td valign="top" width="100%">
	<table width="100%" border="0">
		<tr>
		 <? echo freshports_PageBannerText("Category listing"); ?>
		</tr>

	<tr><td>
	<BIG><BIG><B><?php
echo $category->{description} 
?></B></BIG></BIG> - Number of ports in this category: <?php
echo $PortCount;

echo '<div align="center">';
freshports_CategoryNextPreviousPage($CategoryName, $PortCount, $PageNo, $PageSize);
echo '</div>';

?>
	</td></tr>

<?
	if ($Debug) {
		echo "\$CategoryID = '$CategoryID'<BR>\n";;
		echo "GlobalHideLastChange = $GlobalHideLastChange<BR>\n";
		echo "\$numrows = $numrows<BR>\n";
	}

	$ShowShortDescription	= "Y";


	$HTML .= freshports_echo_HTML("<TR>\n<TD>\n");


$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink= $ShowDescriptionLink= $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
$ShowPortCreationDate = 0;
$HideCategory = 1;
$ShowCategories		= 1;
GLOBAL	$ShowDepends;
$ShowDepends		= 1;
$HideDescription = 1;
$ShowEverything  = 1;
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";
	for ($i = 0; $i < $numrows; $i++) {
		$port->FetchNth($i);

		$HTML .= freshports_PortDetails($port, $db, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription, 1, '', 0);
	} // end for

	echo $HTML;

	?>
</TD></TR>
<TR><TD>
<div align="center">
<?php 
freshports_CategoryNextPreviousPage($CategoryName, $PortCount, $PageNo, $PageSize);
?>
</div> 
</TD></TR>
</TABLE>
  <?
  freshports_SideBar();
  ?>
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