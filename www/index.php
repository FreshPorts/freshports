<?php
	#
	# $Id: index.php,v 1.5 2012-12-21 18:20:53 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-news.php');


	$Debug = 0;

	if (IsSet($_REQUEST['branch'])) {
		$Branch = NormalizeBranch(htmlspecialchars($_REQUEST['branch']));
	} else {
		$Branch = BRANCH_HEAD;
	}
	#
	# at this point, we know Branch has been sanitized.  See NormalizeBranch()
	#

	if ($Debug) echo 'Branch is ' . $Branch . '<br>';

	#
	# If they supply a package name, go for it.
	#
	if (IsSet($_REQUEST['package'])) {
		$package = pg_escape_string($db, $_REQUEST['package']);
		if ($Debug) echo "package is specfied on the URL: '$package'<br>\n";
		if ($package != '') {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/packages.php');

			$Packages = new Packages($db);

			$CategoryPort = $Packages->GetCategoryPortFromPackageName($package);
			switch ($CategoryPort) {
				case "0":
					# no such port found
					header('Location: /package.php?package=' . $package . '&notfound');
					exit;

				case "-1":
					# multiple ports have that package name
					# search for them all and let the users decide which one they want
					require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/searches.php');
					$Searches = new Searches($db);
					$Redirect = $Searches->GetLink($package, FRESHPORTS_SEARCH_METHOD_Exact, 1);
					header('Location: ' . $Redirect);
					exit;

				default:
					# one port found with that name, show that page.
					header('Location: /' . $CategoryPort . '/');
					exit;
			}
		}
	}
	else
	{
	    if ($Debug) echo "package is not specified on the URL<br>\n";
    }

	$Title = 'Most recent commits';
	freshports_Start($FreshPortsSlogan . " - $Title",
					$Title,
					'FreeBSD, index, applications, ports');
if ($Debug) echo "\$User->id='$User->id'";

function freshports_SummaryForDay($MinusN) {
   $BaseDirectory = "./archives";
   $Now = time();
//   echo "$MinusN<br>\n";
   $File = $BaseDirectory . "/" . date("Y/m/d", $Now - 60*60*24*$MinusN) . ".inc";
//   echo "$File<br>\n";
   if (file_exists($File)) {
      echo '<br><table width="152" class="bordered" class="cellpadding5">';
      echo '  <tr>';
      echo '<td class="accent" height="30">';
      echo date("l j M", $Now - 60*60*24*$MinusN);
      echo '</td>';
      echo '       </tr>';
      echo '        <tr>';
      echo '         <td>';
      include($File);
      echo '   </td>';
      echo '   </tr>';
      echo '   </table>';
   }
}

echo freshports_MainTable();

$num          = $MaxNumberOfPortsLong;
$days         = $NumberOfDays;
$dailysummary = 7;

if (In_Array('num',          $_REQUEST)) $num          = pg_escape_string($db, intval($_REQUEST["num"]));
if (In_Array('dailysummary', $_REQUEST)) $dailysummary = pg_escape_string($db, intval($_REQUEST["dailysummary"]));
if (In_Array('days',         $_REQUEST)) $days         = pg_escape_string($db, intval($_REQUEST["days"]));

if (Is_Numeric($num)) {
	$MaxNumberOfPortsLong = min($MaxNumberOfPortsLong, max(10, $num));
} else {
	$num = MaxNumberOfPortsLong;
}

if (Is_Numeric($days)) {
	$NumberOfDays = min($NumberOfDays, max(0, $days));
} else {
	$days = $NumberOfDays;
}

if (Is_Numeric($dailysummary)) {
	$dailysummary = min($NumberOfDays, max(0, $dailysummary));
} else {
	unset($dailysummary);
}


if ($db) {
?>
<tr><td class="content">

<?php echo freshports_MainContentTable(); ?>

<tr>
<?php
 if ( $Branch == BRANCH_HEAD) {
   echo freshports_PageBannerText("$MaxNumberOfPortsLong most recent commits (all timestamps are UTC)");
 } else {
   echo freshports_PageBannerText("Commits from the $Branch branch");
 }
 
?>
        <?php //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPortsLong) ?>
</tr>
<tr><td>
<p><?php echo EVERYTHING; ?>

<?php
	if ($ShowAds && $BannerAd) {
		echo "</td></tr>\n<tr><td>\n<CENTER>\n";
		echo Ad_728x90();
		echo "</CENTER>\n\n";
	}
?>


</td></tr>
<?php
	$UseCache = FALSE;
	$FileName = "index.html.$Branch";
	if ($User->id != '') {
	  if ($Debug) echo 'we should look for the user index page';
	  # if the user is logged in, cache their stuff.
	  $FileName .= '.' . $User->id;
	}

	$Cache = new CacheNews(NEWS_DIRECTORY);
	$Cache->PageSize = $User->page_size;
	$result = $Cache->Retrieve($FileName);
	
	if (!$result) {
	  $UseCache = TRUE;
	}



	if ($UseCache) {
		echo $Cache->CacheDataGet();
	} else {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits.php');
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

		$LatestCommits = new Commits($db);
		$LatestCommits->SetBranch($Branch);

		$LatestCommits->FetchLimit(isset($User) ? $User->id : null, 100);

		$DisplayCommit = new DisplayCommit($db, $LatestCommits->LocalResult);
		$DisplayCommit->SetBranch($Branch);
		$DisplayCommit->ShowLinkToSanityTestFailure = true;
		$RetVal = $DisplayCommit->CreateHTML();

		echo $DisplayCommit->HTML;
		
		$Cache->CacheDataSet($DisplayCommit->HTML);
		$Cache->Add($FileName);
	}

}

?>
</table>
</td>
  <td class="sidebar">
   <?php echo freshports_SideBar(); ?>

<br>
<?php

	if ($dailysummary) {
		for ($i = 0; $i < $dailysummary; $i++) {
			freshports_SummaryForDay($i);
		}
	} else {
		if ($NumberOfDays) {
			$Today = time();
			echo '
<table width="155" class="bordered" class="cellpadding5">
	<tr>
		<td class="accent" height="30"><B>Previous days</B></td>
	</tr>
	<tr><td>
';
			for ($i = 1; $i <= $NumberOfDays; $i++) {
				echo freshports_LinkToDate($Today - $i * 86400) . "<br>\n";
			}
			echo '
	</td></tr>
</table>

';
		}
	}
?>
 </td>
</tr>
</table>

<br>

<?php
define('RELATIVE_DATE_24HOURS', 24 * 60 * 60);	# seconds in a day
$Date = date('Y/m/d');
$Yesterday = freshports_LinkToDate(strtotime($Date) - RELATIVE_DATE_24HOURS, "Yesterday's Commits", $Branch);

echo '&lt; ' . $Yesterday . ' &gt;';
?>

<?php
echo freshports_ShowFooter();
?>
</body>
</html>
