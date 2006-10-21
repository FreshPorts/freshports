<?php
	#
	# $Id: commit.php,v 1.1.2.62 2006-10-21 13:37:15 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

DEFINE('MAX_PAGE_SIZE',     1000);
DEFINE('DEFAULT_PAGE_SIZE', 500);

DEFINE('NEXT_PAGE',		'Next');

	$message_id = '';
	$commit_id  = '';
	$page       = '';
	$page_size  = '';

	if (IsSet($_GET['message_id'])) $message_id = AddSlashes($_GET['message_id']);
	if (IsSet($_GET['commit_id']))  $commit_id  = AddSlashes($_GET['commit_id']);

	# I'm quite sure we use only message_id, and never commit_id.
	if ($message_id != '') {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');

		$Commit = new Commit($db);
		$Commit->FetchByMessageId($message_id);
		freshports_ConditionalGet($Commit->last_modified);
	}

	if (IsSet($_REQUEST['page']))      $PageNo   = $_REQUEST['page'];
	if (IsSet($_REQUEST['page_size'])) $PageSize = $_REQUEST['page_size'];

	if ($Debug) {
		echo "\$page      = '$page'<br>\n";
		echo "\$page_size = '$page_size'<br>\n";
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

	SetType($PageNo,   "integer");
	SetType($PageSize, "integer"); 

	if (!IsSet($PageNo)   || !str_is_int("$PageNo")   || $PageNo   < 1) {
		$PageNo = 1;
	}

	if (!IsSet($PageSize) || !str_is_int("$PageSize") || $PageSize < 1 || $PageSize > MAX_PAGE_SIZE) {	
		$PageSize = DEFAULT_PAGE_SIZE;
	}

	if ($Debug) {
		echo "\$PageNo   = '$PageNo'<br>\n";
		echo "\$PageSize = '$PageSize'<br>\n";
	}



	$Title = 'Commit found by ';
	if ($message_id) {
		$Title .= 'message id';

		# if found, this will be > 0
		if (strpos($message_id, MESSAGE_ID_OLD_DOMAIN)) {
			# yes, we found an old message_id.  Convert it,
			# and redirect them to the permanent new location
			#
			$new_message_id = freshports_MessageIDConvertOldToNew($message_id);

			$URL = $_SERVER['SCRIPT_URI'] . '?' .
                   str_replace($_SERVER['QUERY_STRING'], "message_id=$message_id", "message_id=$new_message_id");

			freshports_RedirectPermanent($URL);
			exit;
		}
	} else {
		$Title .= 'commit id';
	}
	freshports_Start($Title,
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');

function str_is_int($str) {
	$var = intval($str);
	return ($str == $var);
}

function freshports_CommitNextPreviousPage($URL, $NumRowsTotal, $PageNo, $PageSize) {

	$HTML .= "Result Page:";

	$NumPages = ceil($NumRowsTotal / $PageSize);

	for ($i = 1; $i <= $NumPages; $i++) {
		if ($i == $PageNo) {
			$HTML .= "&nbsp;<b>$i</b>";
			$HTML .= "\n";
		} else {
			$HTML .= '&nbsp;<a href="' . $URL . '&page=' . $i .  '">' . $i . '</a>';
			$HTML .= "\n";
		}
	}

	if ($PageNo == $NumPages) {
		$HTML .= '&nbsp; ' . NEXT_PAGE;
	} else {
		$HTML .= '&nbsp;<a href="' . $URL . '&page=' . ($PageNo + 1) .  '">' . NEXT_PAGE . '</a>';
		$HTML .= "\n";
	}

	return $HTML;
}

$Debug = 0;

if ($Debug) echo "UserID='$User->id'";

?>

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(BORDER); ?>

<?
if (file_exists("announcement.txt") && filesize("announcement.txt") > 4) {
?>
  <TR>
    <TD colspan="2">
       <? include ("announcement.txt"); ?>
    </TD>
  </TR>
<?
}
	if ($message_id != '' || $commit_id != '') {
	
?>

<TR>
	<? echo freshports_PageBannerText($Title, 3); ?>
</TR>

<?php

	$numrows = $MaxNumberOfPorts;
	$database=$db;
	if ($database ) {
#
# we limit the select to recent things by using a date
# otherwise, it joins the whole table and that takes quite a while
#
#$numrows=400;

	$sql = "select freshports_commit_count_elements('$message_id') as count";

	if ($Debug) echo "\n<pre>sql=$sql</pre>\n";

	$result = pg_exec($database, $sql);
	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows == 1) { 
			$myrow = pg_fetch_array ($result, 0);
		} else {
			die('could not determine the number of commit elements');
		}

		$NumRowsTotal = $myrow['count'];
	}

	$sql ="
SELECT FPC.*, STF.message as stf_message
  FROM freshports_commit('$message_id', $PageSize, ($PageNo - 1 ) * $PageSize, $User->id) FPC
 LEFT OUTER JOIN sanity_test_failures STF
    ON FPC.commit_log_id = STF.commit_log_id";

	if ($Debug) echo "\n<p>sql=$sql</p>\n";

	$result = pg_exec($database, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

			$DisplayCommit = new DisplayCommit($result);
			$DisplayCommit->SetShowAllPorts(true);
			$DisplayCommit->SanityTestFailure = true;
			$RetVal = $DisplayCommit->CreateHTML();
	
			echo $DisplayCommit->HTML;
	
		} else {
				echo '<tr><TD VALIGN="top"><P>Sorry, nothing found in the database....</P>' . "\n";
				echo "</TD></tr>";
			}
		} else {
			echo "read from test failed <pre>$sql</pre>";
		}
	} else {
		echo "no connection";
	}
	echo "</TABLE>\n";
} else {
	echo '<tr><td valign="top" width="100%">nothing supplied, nothing found!</td>';
}


?>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">

	<?
	echo freshports_SideBar();
	?>

  </td>
</TR>
</TABLE>

<BR>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
