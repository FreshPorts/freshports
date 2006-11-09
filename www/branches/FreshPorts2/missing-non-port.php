<?php
	#
	# $Id: missing-non-port.php,v 1.1.2.9 2006-11-09 17:01:51 dan Exp $
	#
	# Copyright (c) 2003-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits_by_tree_location.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');
	require_once('Pager/Pager.php');

function freshports_NonPortDescription($db, $element_record) {
	GLOBAL $TableWidth;
	GLOBAL $FreshPortsTitle;

	freshports_ConditionalGet(freshports_LastModified());

	header("HTTP/1.1 200 OK");
	$Title = preg_replace('|^/?ports/|', '', $element_record->element_pathname);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

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
<a HREF="<?php echo FRESHPORTS_FREEBSD_CVS_URL . $element_record->element_pathname; ?>">CVSWeb</a>
</td></tr>

<?
	GLOBAL $User;

	$PageNumber = 1;
	parse_str($_SERVER['REDIRECT_QUERY_STRING'], $query_parts);
	if (IsSet($query_parts['page'])  && Is_Numeric($query_parts['page'])) {
		$PageNumber = intval($query_parts['page']);
		if ($PageNumber != $query_parts['page'] || $PageNumber < 1) {
			$PageNumber = 1;
		}
	}

	$NumCommitsPerPage = $User->page_size;

    $Commits = new CommitsByTreeLocation($db);
    $Commits->SetLimit(100);
    $Commits->Debug = 0;
	$Commits->UserIDSet($User->id);
	$Commits->TreePathConditionSet("= '" . $element_record->element_pathname . "'");
    
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
	$DisplayCommit = new DisplayCommit($Commits->LocalResult);
	$HTML .= $DisplayCommit->CreateHTML();

	$HTML .= $NumCommitsHTML;

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
?>