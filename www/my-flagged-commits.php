<?php
	#
	# $Id: my-flagged-commits.php,v 1.4 2012-07-21 23:23:58 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once('Pager/Pager.php');


	freshports_Start($FreshPortsSlogan,
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');
$Debug = 0;

if ($Debug) echo "\$User->id='$User->id'";

echo freshports_MainTable();

$num          = $MaxNumberOfPortsLong;
$days         = $NumberOfDays;
$dailysummary = 7;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits_my_flagged.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

	$CommitsMyFlagged = new CommitsMyFlagged($db);
	$CommitsMyFlagged->Debug = $Debug;
	$CommitsMyFlagged->UserIDSet($User->id);
	$NumberCommits = $CommitsMyFlagged->Fetch();

    $NumberCommits = $CommitsMyFlagged->GetCountPortCommits();
    if ($Debug) echo 'number of commits = ' . $NumberCommits . "<br>\n";

	$NumFound = $NumberCommits;
	$params = array(
			'mode'        => 'Sliding',
			'perPage'     => $PageSize,
			'delta'       => 5,
			'totalItems'  => $NumFound,
			'urlVar'      => 'page',
			'currentPage' => $PageNumber,
			'spacesBeforeSeparator' => 1,
			'spacesAfterSeparator'  => 1,
		);
	$Pager = & Pager::factory($params);

	$offset = $Pager->getOffsetByPageId();
	$NumOnThisPage = $offset[1] - $offset[0] + 1;

    if ($PageNumber > 1) {
      $Commits->SetOffset($offset[0] - 1);
    }
    $CommitsMyFlagged->SetLimit($PageSize);

	$DisplayCommit = new DisplayCommit($db, $CommitsMyFlagged->LocalResult);
	$DisplayCommit->Debug = $Debug;
	$DisplayCommit->SetUserID($User->id);
	$links = $Pager->GetLinks();
		
	$HTML .= $NumPortsFound . ' ' . $links['all'];
	$HTML .= $DisplayCommit->CreateHTML();


if ($db) {
?>
<TR><TD VALIGN="top" WIDTH="100%">

<?php echo freshports_MainContentTable(); ?>

<TR>
<? echo freshports_PageBannerText("My flagged commits", 3); ?>
        <? //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPortsLong) ?>
</TR>
<TR><TD>
<p><?php echo EVERYTHING; ?>
<p>
A port is marked as new for 10 days.

<?php
	if ($ShowAds && $BannerAd) {
		echo "</td></tr>\n<tr><td>\n<CENTER>\n";
		echo Ad_728x90();
		echo "</CENTER>\n\n";
	}
?>



</TD></TR>
<?php

	$UseCache = FALSE;

	echo $HTML;
}

?>
</TABLE>
</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
   <? echo freshports_SideBar(); ?>

<BR>

 </TD>
</TR>
</TABLE>

<BR>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
