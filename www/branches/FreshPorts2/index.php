<?php
	#
	# $Id: index.php,v 1.1.2.101 2005-01-22 14:48:51 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');


	#
	# If they supply a package name, go for it.
	#
	if (IsSet($_REQUEST['package'])) {
		$package = AddSlashes($_REQUEST['package']);
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

	freshports_Start($FreshPortsSlogan,
					$FreshPortsName . ' - new ports, applications',
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
      echo '<br><TABLE WIDTH="152" BORDER="1" CELLSPACING="0" CELLPADDING="5">';
      echo '  <TR>';
      echo '<TD bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">';
      echo date("l j M", $Now - 60*60*24*$MinusN);
      echo '</font></TD>';
      echo '       </TR>';
      echo '        <TR>';
      echo '         <TD>';
      include($File);
      echo '   </TD>';
      echo '   </TR>';
      echo '   </TABLE>';
   }
}

echo freshports_MainTable();

$num          = $MaxNumberOfPorts;
$days         = $NumberOfDays;
$dailysummary = 7;

if (In_Array('num',          $_GET)) $num			= AddSlashes($_GET["num"]);
if (In_Array('dailysummary', $_GET)) $dailysummary	= AddSlashes($_GET["dailysummary"]);
if (In_Array('days',         $_GET)) $days			= AddSlashes($_GET["days"]);


if (Is_Numeric($num)) {
	$MaxNumberOfPorts = min($MaxNumberOfPorts, max(10, $num));
} else {
	$num = MaxNumberOfPorts;
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
	echo 
?>
<TR><TD VALIGN="top" WIDTH="100%">

<?php echo freshports_MainContentTable(); ?>

<TR>
<? echo freshports_PageBannerText("$MaxNumberOfPorts most recently changed ports", 3); ?>
        <? //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPorts) ?>
</TR>
<TR><TD>
<p>FreshPorts has everything you want to know about FreeBSD software, ports, packages,
applications, whatever term you want to use.
<p>
A port is marked as new for 10 days.

<p>
If you want to see more try <a href="/commits.php">here</a>.
</TD></TR>
<?php

	$UseCache = FALSE;

	DEFINE('CACHEFILE', $_SERVER['DOCUMENT_ROOT'] . '/../caching/cache/index.html');

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
		$LatestCommits->SetMaxNumberOfPorts($MaxNumberOfPorts);
		$LatestCommits->SetDaysMarkedAsNew ($DaysMarkedAsNew);
		$LatestCommits->SetUserID($User->id);
		$LatestCommits->SetWatchListAsk($User->watch_list_add_remove);
		$LatestCommits->CreateHTML();

		echo $LatestCommits->HTML;
	}

}

?>
</TABLE>
</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
   <? echo freshports_SideBar(); ?>

<BR>
<?

	if ($dailysummary) {
		for ($i = 0; $i < $dailysummary; $i++) {
			freshports_SummaryForDay($i);
		}
	} else {
		if ($NumberOfDays) {
			$Today = time();
			echo '
<TABLE WIDTH="155" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Previous days</B></BIG></FONT></TD>
	</TR>
	<TR><TD>
';
			for ($i = 1; $i <= $NumberOfDays; $i++) {
				echo freshports_LinkToDate($Today - $i * 86400) . "<br>\n";
			}
			echo '
	</TD></TR>
</TABLE>

';
		}
	}
?>
 </TD>
</TR>
</TABLE>

<BR>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
