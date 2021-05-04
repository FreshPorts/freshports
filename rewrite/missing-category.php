<?php
	#
	# $Id: missing-category.php,v 1.3 2010-11-10 20:04:44 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-category.php');

DEFINE('MAX_PAGE_SIZE',     500);
DEFINE('DEFAULT_PAGE_SIZE', 100);

DEFINE('NEXT_PAGE',		'Next');

GLOBAL $g_NOINDEX;

$g_NOINDEX = 1;  // we should not index category pages. too much clutter.

function freshports_CategoryNextPreviousPage($CategoryName, $PortCount, $PageNumber, $PageSize, $Branch = BRANCH_HEAD) {

	$HTML .= "Result Page:";

	$queryParms = array();
	if ($Branch != BRANCH_HEAD) {
		$queryParms['branch'] = $Branch;
	}

	$NumPages = ceil($PortCount / $PageSize);

	for ($i = 1; $i <= $NumPages; $i++) {
		if ($i == $PageNumber) {
			$HTML .= "&nbsp;<b>$i</b>";
			$HTML .= "\n";
		} else {
			$queryParms['page'] = $i;
			$HTML .= '&nbsp;<a href="/' . $CategoryName . '/?';
			$HTML .= http_build_query($queryParms, '', '&amp;');
			$HTML .= '">' . $i . '</a>' . "\n";
		}
	}

	if ($PageNumber == $NumPages) {
		$HTML .= '&nbsp; ' . NEXT_PAGE;
	} else {
		$queryParms['page'] = $PageNumber + 1;
		$HTML .= '&nbsp;<a href="/' . $CategoryName . '/?' . http_build_query($queryParms, '', '&amp;') .  '">' . NEXT_PAGE . '</a>';
		$HTML .= "\n";
	}
	
	return $HTML;
}

function str_is_int($str) {
	$var = intval($str);
	return ($str == $var);
}


function freshports_CategoryByID($db, $category_id, $PageNumber = 1, $PageSize = DEFAULT_PAGE_SIZE, $Branch = BRANCH_HEAD) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
	$category = new Category($db, $Branch);
	$category->FetchByID($category_id);

	freshports_ConditionalGet($category->last_modified);

	freshports_CategoryDisplay($db, $category, $PageNumber, $PageSize, $Branch);
}


function freshports_CategoryByElementID($db, $element_id, $PageNumber = 1, $PageSize = DEFAULT_PAGE_SIZE) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
	$category = new Category($db);
	$category->FetchByElementID($element_id);

	freshports_ConditionalGet($category->last_modified);

	freshports_CategoryDisplay($db, $category, $PageNumber, $PageSize);
}


function freshports_CategoryDisplay($db, $category, $PageNumber = 1, $PageSize = DEFAULT_PAGE_SIZE, $Branch = BRANCH_HEAD) {

#		var_dump($category);
#
	GLOBAL $User;

	$Debug = 0;
	
	if (IsSet($_SERVER['REQUEST_URI'])) {
		$url_query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		if ($Debug) {
			echo '<pre>url_query is';
			var_dump($url_query);
			echo '</pre>';
		}
		parse_str($url_query, $url_args);
		if ($Debug) {
			echo '<pre>url_args is';
			var_dump($url_args);
			echo '</pre>';
		}

		if (IsSet($url_args['page']))      $PageNumber = $url_args['page'];
		if (IsSet($url_args['page_size'])) $PageSize   = $url_args['page_size'];
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

	SetType($PageNumber, "integer");
	SetType($PageSize,   "integer"); 

	if (!IsSet($PageNumber) || !str_is_int("$PageNumber") || $PageNumber < 1) {
		$PageNumber = 1;
	}

	if (!IsSet($PageSize) || !str_is_int("$PageSize") || $PageSize < 1 || $PageSize > MAX_PAGE_SIZE) {	
		$PageSize = DEFAULT_PAGE_SIZE;
	}

	if ($Debug) {
		echo "\$PageNumber = '$PageNumber'<br>\n";
		echo "\$PageSize   = '$PageSize'<br>\n";
	}
	
	# these two options must be the last on the line.  And as such are mutually exclusive
	define('BYPASSCACHE',  'bypasscache=1');  # do not read the cache for display
	define('REFRESHCACHE', 'refreshcache=1'); # refresh the cache

	$BypassCache  = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["REQUEST_URI"]) - strlen(BYPASSCACHE))  == BYPASSCACHE;
	$RefreshCache = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["REQUEST_URI"]) - strlen(REFRESHCACHE)) == REFRESHCACHE;

	###
	### Check the cache
	###

	$Cache = new CacheCategory();
	$Cache->PageSize = $User->page_size;
	$result = $Cache->RetrieveCategory($category->name, $User->id, $PageNumber, $Branch);
	if (!$result && !$BypassCache && !$RefreshCache) {
		if ($Debug) echo "found something from the cache<br>\n";
		$HTML = $Cache->CacheDataGet();
	} else {
	
### start building HTML for caching


		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/watch_lists.php');

		if ($category->IsPrimary()) {
			$WatchLists = new WatchLists($db);
			$WatchListCount = $WatchLists->IsOnWatchList($User->id, $category->element_id);
		}

		$Title = $category->name;

		# find out how many ports are in this category
		$PortCount = $category->PortCount($category->name, $Branch);
		
		$port = new Port($db);

		$numrows = $port->FetchByCategoryInitialise($category->name, $User->id, $PageSize, $PageNumber, $Branch);

		$HTML = freshports_MainTable();

		$HTML .= '<tr><td valign="top" width="100%">';

		$HTML .= freshports_MainContentTable() . '

		<tr>
		  ' . freshports_PageBannerText('Category listing - ' . $category->name . ($Branch == BRANCH_HEAD ? '' : ' on branch '. pg_escape_string($Branch))) . '
		</tr>

	<tr><td>';

		if ($category->IsPrimary()) {
			if ($WatchListCount) {
				$HTML .= freshports_Watch_Link_Remove('', 0, $category->element_id);
			} else {
				$HTML .= freshports_Watch_Link_Add   ('', 0, $category->element_id);
			}
		}
		


		$HTML .= '
<BIG><BIG><B>' . 
$category->description . '
</B></BIG></BIG>- Number of ports in this category' . ($Branch == BRANCH_HEAD ? '' : ' with commits on branch ' . pg_escape_string($Branch)) . ': ' . $PortCount . '

<p>
	Ports marked with a <sup>*</sup> actually reside within another category but
	have <b>' . $category->name . '</b> listed as a secondary category.';

		GLOBAL $ShowAds, $BannerAd;

		if ($ShowAds && $BannerAd) {
			$HTML .= "<br><center>\n" . Ad_728x90() . "\n</center>\n";
		}

		$HTML .= '<div align="center"><br>' . 
			freshports_CategoryNextPreviousPage($category->name, $PortCount, $PageNumber, $PageSize, $Branch)  . 
			'</div>';

		$HTML .= '</td></tr>';

	        if ($Debug) {
        	        echo "\$CategoryID = '$CategoryID'<BR>\n";;
        	        echo "GlobalHideLastChange = $GlobalHideLastChange<BR>\n";
	                echo "\$numrows = $numrows<BR>\n";
		}

		$ShowShortDescription	= "Y";

		$HTML .= freshports_echo_HTML("<TR>\n<TD>\n");

		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');

		$port_display = new port_display($db, $User, $Branch);
		$port_display->SetDetailsCategory();

		for ($i = 0; $i < $numrows; $i++) {
			$port->FetchNth($i);

			$port_display->SetPort($port);

			$Port_HTML = $port_display->Display();

			$HTML .= $port_display->ReplaceWatchListToken($port->onwatchlist, $Port_HTML, $port->element_id);

		} // end for

		$HTML .= '
</TD></TR>
<TR><TD>
<div align="center"><br>' . 

			freshports_CategoryNextPreviousPage($category->name, $PortCount, $PageNumber, $PageSize, $Branch) . '

</div> 
</TD></TR>
</TABLE>
';

	### finish building HTML for caching
	}


	GLOBAL $User;
	if ($Debug) echo "\$User->id='$User->id'";

	### page building starts here

	freshports_ConditionalGetUnix($Cache->LastModifiedGet());
	header('HTTP/1.1 200 OK');

	freshports_Start($Title,
					$category->description,
					'FreeBSD, index, applications, ports');


	# We didn't find anything in the cache, and we are not not bypassing cache 
	if ($result && !$BypassCache || $RefreshCache) {
		$Cache->CacheDataSet($HTML);
		$Cache->AddCategory($category->name, $User->id, $PageNumber, $Branch);
	}

	# by here, $HTML has either been fetched from cache or built.


GLOBAL $ShowAds, $BannerAd;

if ($ShowAds && $BannerAd) {
	echo "<br><center>\n" . Ad_728x90() . "\n</center>\n";
}
?>

	</td></tr>

<?php

	echo $HTML;
?>	
  <td class="sidebar">
  <?
  echo freshports_SideBar();
  ?>
  </td>
</TR>
</TABLE>

<?php
	echo freshports_ShowFooter();
?>

	</body>
	</html>

	<?php

	}
