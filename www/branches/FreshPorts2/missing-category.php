<?
   # $Id: missing-category.php,v 1.1.2.2 2001-12-30 23:25:56 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited


function freshports_Category($category, $db) {

	GLOBAL $TableWidth;
	header("HTTP/1.1 200 OK");


	#$Debug=1;

	#
	# if no category provided or category is not numeric, try
	# category zero.  inval returns zero if non-numeric
	#
	#echo $category         . "<br>";
	#echo intval($category) . "<br>";

	#if (!$category) {                          
	#   $category = 0;
	#}

	if (!$category || $category != strval(intval($category))) {
		$category = 0;
	} else {
	$category = intval($category);
	}

	#echo "<br>";
	#echo 'intval($category)     = ' . intval($category)     . "<br>";

	#
	# append the category id to the cache_file
	#
	$cache_file .= "." . $category;

	$title = freshports_Category_Name($category, $db);

	freshports_Start($title,
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");
	?>


	<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
	<tr><td COLSPAN="2">
	This page lists all the ports in a given category.
	</td></tr>
	<tr><td valign="top" width="100%">
	<table width="100%" border="0">
		<tr>
	    <td bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - <? echo $title ?></font></td>
		</tr>

	<?

	$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

	// make sure the value for $sort is valid

	$LimitRows	= 100;

	if (!$start) {
		$start = 1;
	}

	if ($start < 1) {
	   $start = 1;
	}

	if ($start > 1) {
		$cache_file .= ".$start";

		// echo "adding $start to $cache_file";
	}

	if ($start > $end) {
		$end = $start + $LimitRows -1;
	}

	if (!$end) {
		$end = $start + $LimitRows - 1;
	}

	$sort ="port";

	$port = new Port($db);

	$numrows = $port->FetchByCategoryInitialise($category);

	if ($Debug) {
		echo $sql . '<BR>';
		echo "GlobalHideLastChange = $GlobalHideLastChange\n";
	}

	$ShowShortDescription	= "Y";

	$HTML .= freshports_echo_HTML("<TR>\n<TD>\n");


$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink= $ShowDescriptionLink= $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
$ShowPortCreationDate = 0;
$HideCategory = 1;
$ShowCategories		= 1;
GLOBAL	$ShowDepends;
$ShowDepends		= 1;
#$HideDescription = 1;
$ShowEverything  = 1;
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";
	for ($i = 0; $i < $numrows; $i++) {
		$port->FetchNth($i);

		$HTML .= freshports_PortDetails($port, $db, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription);
	} // end for

	$HTML .= freshports_echo_HTML('</tr>');

	$HTML .= freshports_echo_HTML('</td></tr>');

	$HTML .= freshports_echo_HTML('</table>');

	$HTML .= freshports_echo_HTML('</td></tr>');
	echo $HTML;      

	?>

	</td>
	</tr>
	</table>
	</body>
	</html>

	<?

	}

?>