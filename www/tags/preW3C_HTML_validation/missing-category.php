<?
	# $Id: missing-category.php,v 1.1.2.9 2002-03-19 14:29:47 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited


function freshports_Category($CategoryID, $db) {

	GLOBAL $TableWidth;
	header("HTTP/1.1 200 OK");


	$Debug=0;

	GLOBAL	$DOCUMENT_ROOT;
	require($DOCUMENT_ROOT . "/../classes/categories.php");

	$category = new Category($db);
	$category->FetchByID($CategoryID);
	$title = $category->{name};

	require("./include/getvalues.php");

	freshports_Start($title,
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

	$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

	$port = new Port($db);

	$numrows = $port->FetchByCategoryInitialise($CategoryID);

	?>


	<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
	<tr><td valign="top" width="100%">
	<table width="100%" border="0">
		<tr>
		 <? freshports_PageBannerText("Category listing"); ?>
		</tr>

	<tr><td>
	<BIG><BIG><B><? echo $category->{description} ?></B></BIG></BIG> - Number of ports in this category: <? echo $numrows?>
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

#	$HTML .= freshports_echo_HTML('</tr>');
#
#	$HTML .= freshports_echo_HTML('</td></tr>');
#
#	$HTML .= freshports_echo_HTML('</table>');
#
#	$HTML .= freshports_echo_HTML('</td></tr>');
	echo $HTML;      

	?>
</TD>
</TABLE>
<TD VALIGN="top">
   <? include("./include/side-bars.php") ?>
</TD>
</TR>
</TABLE>
	</td>
	</tr>
	</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

	</body>
	</html>

	<?

	}

?>