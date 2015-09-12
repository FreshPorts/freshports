<?php
	#
	# $Id: missing-non-port.php,v 1.7 2012-12-21 18:20:53 dan Exp $
	#
	# Copyright (c) 2003-2007 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits_by_tree_location.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-file.php');
	require_once('Pager/Pager.php');
	
function freshports_NonPortDescription($db, $element_record) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;

	$Debug = 0;

	freshports_ConditionalGet(freshports_LastModified());

	header("HTTP/1.1 200 OK");
	$Title    = preg_replace('|^/?ports/|', '', $element_record->element_pathname);
	$FileName = preg_replace('|^/?ports/|', '', $element_record->element_pathname);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start($Title,
	        		"$FreshPortsTitle - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>
<TR>
<? echo freshports_PageBannerText('non port: ' . $Title); ?>
</TR>
<tr><td>
<a HREF="<?php echo FRESHPORTS_FREEBSD_SVN_URL . $element_record->element_pathname; ?>?view=log">SVNWeb</a>
</td></tr>

<?
	GLOBAL $User;

	# these two options must be the last on the line.  And as such are mutually exclusive
	define('BYPASSCACHE',  'bypasscache=1');  # do not read the cache for display
	define('REFRESHCACHE', 'refreshcache=1'); # refresh the cache

	$BypassCache  = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["REQUEST_URI"]) - strlen(BYPASSCACHE))  == BYPASSCACHE;
	$RefreshCache = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["REQUEST_URI"]) - strlen(REFRESHCACHE)) == REFRESHCACHE;

	$PageNumber = 1;
	if (IsSet($_SERVER['REDIRECT_QUERY_STRING'])) {
		parse_str($_SERVER['REDIRECT_QUERY_STRING'], $query_parts);
		if (IsSet($query_parts['page'])  && Is_Numeric($query_parts['page'])) {
			$PageNumber = intval($query_parts['page']);
			if ($PageNumber != $query_parts['page'] || $PageNumber < 1) {
				$PageNumber = 1;
			}
		}
	}

	$NumCommitsPerPage = $User->page_size;
	
	$Cache = new CacheFile();
	$Cache->PageSize = $User->page_size;
	$result = $Cache->Retrieve($FileName, $PageNumber);
	if (!$result && !$BypassCache && !$RefreshCache) {
		if ($Debug) echo "found something from the cache<br>\n";
		$HTML = $Cache->CacheDataGet();
		#
		# we need to know the element_id of this port
		# and the whether or not it is on the person's watch list
		# let's create a special function for that!
		#
		$EndOfFirstLine = strpos($HTML, "\n");
		if ($EndOfFirstLine == false) {
			die('Internal error: I was expecting an ElementID and found nothing');
		}
		# extract the ElementID from the cache
		$ElementID  = intval(substr($HTML, 0, $EndOfFirstLine));
		if ($ElementID == 0) {
			syslog(LOG_ERR, "Extract of ElementID from cache failed.  Is cache corrupt/deprecated? port was $category/$port");
			die('sorry, I encountered a problem with the cache.  Please send the URL and this message to the webmaster.');
		}

		if ($User->id) {
			$OnWatchList = freshports_OnWatchList($db, $User->id, $ElementID);
		} else {
			$OnWatchList = 0;
		}

		$HTML = substr($HTML, $EndOfFirstLine + 1);
	} else {
		if ($Debug) echo "found NOTHING in cache<br>\n";
		$HTML = '';


    $Commits = new CommitsByTreeLocation($db);
    $Commits->SetLimit($Cache->PageSize);
    $Commits->Debug = $Debug;
	$Commits->UserIDSet($User->id);
	$Commits->TreePathConditionSet("= '" . pg_escape_string($element_record->element_pathname) . "'");
    
	#	
	# get the count without excuting the whole query
	# we don't want to pull back all the data.
	#
	$NumCommits = $Commits->GetCountCommits();
	$params = array(
			'mode'        => 'Sliding',
			'perPage'     => $NumCommitsPerPage,
			'delta'       => 5,
			'totalItems'  => $NumCommits,
			'urlVar'      => 'page',
			'currentPage' => $PageNumber,
			'spacesBeforeSeparator' => 1,
			'spacesAfterSeparator'  => 1,
			'append'                => false,
			'path'					=> '/' . preg_replace('|^/?ports/|', '', $element_record->element_pathname),
			'fileName'              => '?page=%d',
			'altFirst'              => 'First Page',
			'firstPageText'         => 'First Page',
			'altLast'               => 'Last Page',
			'lastPageText'          => 'Last Page',
		);
	$Pager = & Pager::factory($params);
	
	$links = $Pager->GetLinks();

	$NumCommitsHTML = '<tr><td><p align="left">Number of commits found: ' . $NumCommits;

	$Offset = 0;
	$PageLinks = $links['all'];
	$PageLinksHTML = str_replace('/?page=1"', '"',      $PageLinks);
	$PageLinksHTML = str_replace('/?page=',   '?page=', $PageLinksHTML);
	if ($PageLinksHTML != '') {
		$offset = $Pager->getOffsetByPageId();
		$NumOnThisPage = $offset[1] - $offset[0] + 1;
		$Offset = $offset[0] - 1;
	    $NumCommitsHTML .= " (showing only $NumOnThisPage on this page)";
		unset($offset);
	}
	
    if ($PageNumber > 1) {
      $Commits->SetOffset($Offset);
    }

	$NumCommitsHTML .= '</p>';
	if ($PageLinksHTML != '') {
		$PageLinksHTML = '<p align="center">' . $PageLinksHTML . '</p>';
	}

	$NumCommitsHTML .= $PageLinksHTML . '</td></tr>';

	$HTML = $NumCommitsHTML;

	if ($Commits->Debug) echo "PageNumber='$PageNumber'<br>Offset='$Offset'<br>";

	$NumFetches = $Commits->Fetch();
	$DisplayCommit = new DisplayCommit($db, $Commits->LocalResult);
	$HTML .= $DisplayCommit->CreateHTML();

	$HTML .= $NumCommitsHTML;

	
	# If we are not reading 
	if (!$BypassCache || $RefreshCache) {
		$Cache->CacheDataSet($element_record->{'id'} . "\n" . $HTML);
		$Cache->Add($FileName, $PageNumber);
	}
}

	
	echo $HTML;
	echo "</table>\n"

?>

</TD>
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
} # end of freshports_NonPortDescription
