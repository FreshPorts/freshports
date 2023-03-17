<?php
	#
	# $Id: filter.php,v 1.4 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');


	#
	# If they supply a package name, go for it.
	#
	if (IsSet($_REQUEST['package'])) {
		$package = pg_escape_string($db, $_REQUEST['package']);
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
					$Searches = new Searches($dbh);
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

	$Title = 'watch list categories';
	freshports_Start($FreshPortsSlogan . " - $Title",
					$Title,
					'FreeBSD, index, applications, ports');
	$Debug = 0;
if ($Debug) echo "\$User->id='$User->id'";

function freshports_SummaryForDay($MinusN) {
   $BaseDirectory = "./archives";
   $Now = time();
//   echo "$MinusN<br>\n";
   $File = $BaseDirectory . "/" . date("Y/m/d", $Now - 60*60*24*$MinusN) . ".inc";
//   echo "$File<br>\n";
   if (file_exists($File)) {
      echo '<br><table WIDTH="152" class="bordered" CELLPADDING="5">';
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

if (IsSet($_REQUEST['num']))          $num          = pg_escape_string($db, $_REQUEST["num"]);
if (IsSet($_REQUEST['dailysummary'])) $dailysummary = pg_escape_string($db, $_REQUEST["dailysummary"]);
if (IsSet($_REQUEST['days']))         $days         = pg_escape_string($db, $_REQUEST["days"]);

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
<?php echo freshports_PageBannerText("$MaxNumberOfPortsLong most recent commits (all timestamps are UTC)"); ?>
        <?php //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPortsLong) ?>
</tr>
<tr><td>
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



</td></tr>
<?php

	$UseCache = FALSE;

	DEFINE('CACHEFILE', PAGES_DIRECTORY . '/commits.html');

	if ($User->id == '') {
		if (file_exists(CACHEFILE) && is_readable(CACHEFILE)) {
			$UseCache = TRUE;
		}
	}

	if ($UseCache) {
		readfile(CACHEFILE);
	} else {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/latest_commits.php');

		$LatestCommits = new LatestCommits($db);
		
		$LatestCommits->Debug = $Debug;
		
#		$LatestCommits->SetFilter('/ports/sysutils/%');
#		$LatestCommits->SetFilter("(EP.pathname like '/ports/sysutils/%' or EP.pathname like '/ports/net-p2p/%')");
		$LatestCommits->SetFilter("(EP.pathname like '/ports/lang/%')");
#        $LatestCommits->SetFilter($User->filter);
		
		$LatestCommits->SetMaxNumberOfPorts($MaxNumberOfPortsLong);
		$LatestCommits->SetDaysMarkedAsNew ($DaysMarkedAsNew);
		$LatestCommits->SetUserID($User->id);
		$LatestCommits->SetWatchListAsk($User->watch_list_add_remove);
		$LatestCommits->CreateHTML();

		echo $LatestCommits->HTML;
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
<table WIDTH="155" class="bordered" CELLPADDING="5">
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
$Yesterday = freshports_LinkToDate(strtotime($Date) - RELATIVE_DATE_24HOURS, "Yesterday's Commits");

echo '&lt; ' . $Yesterday . ' &gt;';
?>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
