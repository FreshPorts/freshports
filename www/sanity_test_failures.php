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
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-general.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/sanity_test_failure.php');  # for fetching the message for one commit
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/sanity_test_failures.php');
	require_once('Pager/Pager.php');
	

	$message_id = '';
	if (IsSet($_REQUEST['message_id'])) {
		$message_id = pg_escape_string($_REQUEST['message_id']);
		# avoid path manipulation e.g. ../../
		# this will come into play when we fetch / save the cached file
		$message_id = filter_var($message_id, FILTER_SANITIZE_EMAIL);
		define('CACHE_NAME', "sanity_test_failures.$message_id" );
	} else {
		define('CACHE_NAME', 'sanity_test_failures');
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
} /* summary for day */

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
	
	$UseCache = FALSE;

	$Cache = new CacheGeneral();
	$Cache->PageSize = $User->page_size;
	$result = $Cache->Retrieve(CACHE_NAME, $PageNumber);

	if (!$result)  {
		if ($Debug) echo "found something from the cache<br>\n";
		$HTML = $Cache->CacheDataGet();

		echo $HTML;
 	} else {
		if ($Debug) echo "found NOTHING in cache<br>\n";
		$HTML = '';

		$SanityTestFailures = new SanityTestFailures($db);
		$SanityTestFailures->SetMaxNumberOfPorts($MaxNumberOfPorts);
		$SanityTestFailures->SetDaysMarkedAsNew ($DaysMarkedAsNew);
		$SanityTestFailures->SetUserID($User->id);
		$SanityTestFailures->SetWatchListAsk($User->watch_list_add_remove);

		if ($message_id != '') {
			$SanityTestFailures->SetMessageID($message_id);
		}
		$SanityTestFailures->CreateHTML();
		$HTML .= $SanityTestFailures->HTML;

		if ($message_id != '') {
			# when displaying for one commit, we need to pull the sanity test message out and display it.
			# when displaying all sanity test failures, we do not display the failure messages, but instead, show a list
			# commits, each with a link to show the message.

			# note also, the object we create below is singular, whereas the object we created above is plural.
			$HTML .= '<tr><td>';
			$SanityTestFailure = new SanityTestFailure($db);
			if ($SanityTestFailure->FetchByMessageID($message_id) != -1) {
				$HTML .= "\n<h2>Sanity Test Results</h2>\n";
				$HTML .= "\n<blockquote>\n<pre>";
				$HTML .=  $SanityTestFailure->message;
				$HTML .= "</pre>\n</blockquote>\n";
			}
			$HTML .= '</td></tr>';
		}

		echo $HTML;
		$Cache->CacheDataSet($HTML);
		$Cache->Add(CACHE_NAME, $PageNumber);
	} /* no result */
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
