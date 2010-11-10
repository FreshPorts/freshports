<?php
	#
	# $Id: missing-category.php,v 1.3 2010-11-10 20:04:44 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');

DEFINE('MAX_PAGE_SIZE',     500);
DEFINE('DEFAULT_PAGE_SIZE', 100);

DEFINE('NEXT_PAGE',		'Next');

GLOBAL $g_NOINDEX;

$g_NOINDEX = 1;  // we should not index category pages. too much clutter.

function freshports_CategoryNextPreviousPage($CategoryName, $PortCount, $PageNo, $PageSize) {

	echo "Result Page:";

	$NumPages = ceil($PortCount / $PageSize);

	for ($i = 1; $i <= $NumPages; $i++) {
		if ($i == $PageNo) {
			echo "&nbsp;<b>$i</b>";
			echo "\n";
		} else {
			echo '&nbsp;<a href="/' . $CategoryName . '/?page=' . $i .  '">' . $i . '</a>';
			echo "\n";
		}
	}

	if ($PageNo == $NumPages) {
		echo '&nbsp; ' . NEXT_PAGE;
	} else {
		echo '&nbsp;<a href="/' . $CategoryName . '/?page=' . ($PageNo + 1) .  '">' . NEXT_PAGE . '</a>';
		echo "\n";
	}
}

function str_is_int($str) {
	$var = intval($str);
	return ($str == $var);
}


function freshports_CategoryByID($db, $category_id, $PageNo = 1, $PageSize = DEFAULT_PAGE_SIZE) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
	$category = new Category($db);
	$category->FetchByID($category_id);

	freshports_ConditionalGet($category->last_modified);

	freshports_CategoryDisplay($db, $category, $PageNo, $PageSize);
}


function freshports_CategoryByElementID($db, $element_id, $PageNo = 1, $PageSize = DEFAULT_PAGE_SIZE) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
	$category = new Category($db);
	$category->FetchByElementID($element_id);

	freshports_ConditionalGet($category->last_modified);

	freshports_CategoryDisplay($db, $category, $PageNo, $PageSize);
}


function freshports_CategoryDisplay($db, $category, $PageNo = 1, $PageSize = DEFAULT_PAGE_SIZE) {

	GLOBAL $TableWidth;
	GLOBAL $User;

	header('HTTP/1.1 200 OK');

	$Debug = 0;

	if (IsSet($_SERVER['REDIRECT_QUERY_STRING'])) {
		if (IsSet($_SERVER["REDIRECT_QUERY_STRING"])) {
			parse_str($_SERVER['REDIRECT_QUERY_STRING'], $query_parts);
			if (IsSet($query_parts['page']))      $PageNo   = $query_parts['page'];
			if (IsSet($query_parts['page_size'])) $PageSize = $query_parts['page_size'];
		}
	}

	if (!IsSet($page) || $page == '') {
		$page = 1;
	}

	if (!IsSet($page_size) || $page_size == '') {
		$page_size = $User->page_size;
	}

	if ($Debug) {
		echo "\$page      = '$page'<br>\n";
		echo "\$page_size = '$page_size'<br>\n";
	}

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
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_lists.php');
	
	if ($category->IsPrimary()) {
		$WatchLists = new WatchLists($db);
		$WatchListCount = $WatchLists->IsOnWatchList($User->id, $category->element_id);
	}

	$title = $category->{'name'};

	# find out how many ports are in this category
	$PortCount = $category->PortCount($category->name);

	GLOBAL $User;
	if ($Debug) echo "\$User->id='$User->id'";

	freshports_Start($title,
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$port = new Port($db);

	$numrows = $port->FetchByCategoryInitialise($category->name, $User->id, $PageSize, $PageNo);

	?>

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>

		<tr>
		 <? echo freshports_PageBannerText('Category listing - ' . $category->{'name'}); ?>
		</tr>

	<tr><td>
<?php 
	if ($category->IsPrimary()) {
		if ($WatchListCount) {
			echo freshports_Watch_Link_Remove('', 0, $category->{'element_id'});
		} else {
			echo freshports_Watch_Link_Add   ('', 0, $category->{'element_id'});
		}
	}
?>
	
<BIG><BIG><B><?php
echo $category->{'description'} 
?></B></BIG></BIG>- Number of ports in this category: <?php
echo $PortCount;

?>
<p>
	Ports marked with a <sup>*</sup> actually reside within another category but
	have <b><?php echo $category->{'name'}; ?></b> listed as a secondary category.

<?php

GLOBAL $ShowAds, $BannerAd;

if ($ShowAds && $BannerAd) {
	echo "<br><center>\n" . Ad_728x90() . "\n</center>\n";
}

echo '<div align="center"><br>';
freshports_CategoryNextPreviousPage($category->name, $PortCount, $PageNo, $PageSize);
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


	$HTML = freshports_echo_HTML("<TR>\n<TD>\n");

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');

	$port_display = new port_display($db, $User);
	$port_display->SetDetailsCategory();

	for ($i = 0; $i < $numrows; $i++) {
		$port->FetchNth($i);

		$port_display->port = $port;

		$Port_HTML = $port_display->Display();
		
		$HTML .= $port_display->ReplaceWatchListToken($port->{'onwatchlist'}, $Port_HTML, $port->{'element_id'});

	} // end for

	echo $HTML;

	?>
</TD></TR>
<TR><TD>
<div align="center"><br>
<?php 
freshports_CategoryNextPreviousPage($category->name, $PortCount, $PageNo, $PageSize);
?>
</div> 
</TD></TR>
</TABLE>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
  <?
  echo freshports_SideBar();
  ?>
  </td>
</TR>
</TABLE>

<?
	echo freshports_ShowFooter();
?>

	</body>
	</html>

	<?

	}

?>