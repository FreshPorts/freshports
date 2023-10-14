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
	
function freshports_NonPortDescription($dbh, $element_record) {
	GLOBAL $FreshPortsTitle;

	$Debug = 0;

	freshports_ConditionalGet(freshports_LastModified());

	header("HTTP/1.1 200 OK");
	$Title    = preg_replace('|^/?ports/head/|', '', $element_record->element_pathname);
	$FileName = preg_replace('|^/?ports/head/|', '', $element_record->element_pathname);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start($Title,
					$Title,
					"FreeBSD, index, applications, ports");

?>

	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>
<tr>
<?php echo freshports_PageBannerText('non port: ' . $Title); ?>
</tr>
<tr><td>
<a href="<?php echo FRESHPORTS_FREEBSD_SVN_URL . $element_record->element_pathname; ?>?view=log" rel="noopener noreferrer">SVNWeb</a>
</td></tr>

<?php
	GLOBAL $User;

	# these two options must be the last on the line.  And as such are mutually exclusive
	define('BYPASSCACHE',  'bypasscache=1');  # do not read the cache for display
	define('REFRESHCACHE', 'refreshcache=1'); # refresh the cache

	$BypassCache  = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["REQUEST_URI"]) - strlen(BYPASSCACHE))  == BYPASSCACHE;
	$RefreshCache = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["REQUEST_URI"]) - strlen(REFRESHCACHE)) == REFRESHCACHE;

	$PageNumber = 1;
	if (IsSet($_SERVER['REQUEST_URI'])) {
		$url_query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		if ($Debug) {
			echo '<pre>url_query is';
			var_dump($url_query);
			echo '</pre>';
		}

		$url_args = array();
		if (IsSet($url_query)) {
			parse_str($url_query, $url_args);
		}
		if ($Debug) {
			echo '<pre>url_args is';
			var_dump($url_args);
			echo '</pre>';
		}

		if (IsSet($url_args['page']))      $PageNo   = $url_args['page'];
		if (IsSet($url_args['page_size'])) $PageSize = $url_args['page_size'];

		if (IsSet($url_args['page'])  && Is_Numeric($url_args['page'])) {
			$PageNumber = intval($url_args['page']);
			if ($PageNumber != $url_args['page'] || $PageNumber < 1) {
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
			$OnWatchList = freshports_OnWatchList($dbh, $User->id, $ElementID);
		} else {
			$OnWatchList = 0;
		}

		$HTML = substr($HTML, $EndOfFirstLine + 1);
	} else {
		if ($Debug) echo "found NOTHING in cache<br>\n";
		$HTML = '';


	$Commits = new CommitsByTreeLocation($dbh);
	$Commits->SetLimit($Cache->PageSize);
	$Commits->Debug = $Debug;
	$Commits->UserIDSet($User->id);
	$Commits->TreePathConditionSet("= '" . pg_escape_string($dbh, $element_record->element_pathname) . "'");
    
	#	
	# get the count without executing the whole query
	# we don't want to pull back all the data.
	#
	
	if ($Debug) echo '$element_record->element_pathname = "' . $element_record->element_pathname . '"<br>';
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
			'path'			=> '/' . preg_replace('|^/?head/|', '', preg_replace('|^/?ports/|', '', $element_record->element_pathname)),
			'fileName'              => '?page=%d',
			'altFirst'              => 'First Page',
			'firstPageText'         => 'First Page',
			'altLast'               => 'Last Page',
			'lastPageText'          => 'Last Page',
		);

	# use @ to suppress: Non-static method Pager::factory() should not be called statically
	$Pager = @Pager::factory($params);
	
	$links = $Pager->GetLinks();

	$NumCommitsHTML = '<tr><td><p>Number of commits found: ' . $NumCommits;

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
		$PageLinksHTML = '<p class="pagination">' . $PageLinksHTML . '</p>';
	}

	$NumCommitsHTML .= $PageLinksHTML . '</td></tr>';

	$HTML = $NumCommitsHTML;

	if ($Commits->Debug) echo "PageNumber='$PageNumber'<br>Offset='$Offset'<br>";

	$NumFetches = $Commits->Fetch();
	$DisplayCommit = new DisplayCommit($dbh, $Commits->LocalResult);
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

<?php
	return false;
} # end of freshports_NonPortDescription
