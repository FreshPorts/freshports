<?php
	#
	# $Id: sanity_test_failures.php,v 1.2 2006-12-17 12:06:16 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$message_id = '';
	if (IsSet($_GET['message_id'])) $message_id = pg_escape_string($_GET['message_id']);

	#
	# If they supply a package name, go for it.
	#
	if (IsSet($_REQUEST['package'])) {
		$package = pg_escape_string($_REQUEST['package']);
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
      echo '<TD bgcolor="' . BACKGROUND_COLOUR . '" height="30"><font color="#FFFFFF" SIZE="+1">';
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

if (In_Array('num',          $_GET)) $num		= pg_escape_string($_GET["num"]);
if (In_Array('dailysummary', $_GET)) $dailysummary	= pg_escape_string($_GET["dailysummary"]);
if (In_Array('days',         $_GET)) $days		= pg_escape_string($_GET["days"]);


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
?>
<TR><TD VALIGN="top" WIDTH="100%">

<?php echo freshports_MainContentTable(); ?>

<TR>
<?php
$Title = 'Sanity Test Failure';

if ($message_id == '') {
	$Title .= 's';
}
echo freshports_PageBannerText($Title, 3);
?>
</TR>
<?php
if ($message_id == '') {
?>
<TR><TD>
<p>These are the sanity test failures found by FreshPorts.  Sanity tests have
been in place for several years, but have only been saved in the database
since 10 October 2006.
</p>
</TD></TR>
<?php
}
	if ($ShowAds && $BannerAd) {
		echo "<tr><td>\n<CENTER>\n";
		echo Ad_728x90();
		echo "</CENTER>\n</td></tr>\n\n";
	}
?>

<?php
	$UseCache = FALSE;

	DEFINE('CACHEFILE', $_SERVER['DOCUMENT_ROOT'] . '/../caching/cache/sanity_test_failures.html');

	if ($User->id == '') {
		if (file_exists(CACHEFILE) && is_readable(CACHEFILE)) {
			$UseCache = TRUE;
		}
	}

	if ($UseCache) {
		readfile(CACHEFILE);
	} else {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/sanity_test_failures.php');

		$SanityTestFailures = new SanityTestFailures($db);
		$SanityTestFailures->SetMaxNumberOfPorts($MaxNumberOfPorts);
		$SanityTestFailures->SetDaysMarkedAsNew ($DaysMarkedAsNew);
		$SanityTestFailures->SetUserID($User->id);
		$SanityTestFailures->SetWatchListAsk($User->watch_list_add_remove);
		if ($message_id != '') {
			$SanityTestFailures->SetMessageID($message_id);
		}
		$SanityTestFailures->CreateHTML();

		echo $SanityTestFailures->HTML;
	}
	if ($message_id != '') {
		echo '<tr><td>';
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/sanity_test_failure.php');
		$SanityTestFailure = new SanityTestFailure($db);
		if ($SanityTestFailure->FetchByMessageID($message_id) != -1) {
			echo "\n<h2>Sanity Test Results</h2>\n";
			echo "\n<blockquote>\n<pre>";
			echo $SanityTestFailure->message;
			echo "</pre>\n</blockquote>\n";
		} else {
			
		}
		echo '</td></tr>';
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
		<TD BGCOLOR="' . BACKGROUND_COLOUR . '" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Previous days</B></BIG></FONT></TD>
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
