<?php
	#
	# $Id: commit.php,v 1.1.2.47 2004-12-01 22:56:47 dan Exp $
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

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">

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

<TR><TD VALIGN="top" WIDTH="100%">
<TABLE WIDTH="100%" border="1" CELLSPACING="0" CELLPADDING="8">
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

	$sql = "select * from freshports_commit('$message_id', $PageSize, ($PageNo - 1 ) * $PageSize)";
	if ($Debug) echo "\n<pre>sql=$sql</pre>\n";

	$result = pg_exec($database, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) { 

			$i=0;
			$GlobalHideLastChange = "N";
#			unset($ThisChangeLogID);
			while ($myrow = pg_fetch_array ($result, $i)) {
				$rows[$i] = $myrow;
				#
				# if we do a limit, it applies to the big result set
				# not the resulting set if we also do a DISTINCT
				# thus, count the commit id's ourselves.
				#
#				if ($ThisChangeLogID <> $myrow["commit_log_id"]) {
#					$ThisChangeLogID  = $myrow["commit_log_id"];
					$i++;
#				}
#				echo "$i, ";
				if ($i >= $numrows) break;
			}

			$NumRows = $numrows;
			$LastDate = '';

#			print "NumRows = $NumRows\n<BR>";
			$URL = $_SERVER["PHP_SELF"] . '?message_id=' . $message_id;
			unset($ThisChangeLogID);
			$HTML = '';
			for ($i = 0; $i < $NumRows; $i++) {
				$myrow = $rows[$i];
				$ThisChangeLogID = $myrow["commit_log_id"];
				if ($LastDate <> $myrow["commit_date"]) {
					$LastDate = $myrow["commit_date"];
					$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
					$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($myrow["commit_date"], 0, "D, j M Y") . '</BIG></FONT>' . "\n";
					$HTML .= '</TD></TR>' . "\n\n";
					if ($NumRowsTotal > $PageSize) {
						$HTML .= '<tr><td>' . freshports_CommitNextPreviousPage($URL, $NumRowsTotal, $PageNo, $PageSize) . '</td></tr>';
					}
				}

				$j = $i;

				$HTML .= "<TR><TD>\n";

				// OK, while we have the log change log, let's put the port details here.
				$MultiplePortsThisCommit = 0;
				while ($j < $NumRows && $rows[$j]["commit_log_id"] == $ThisChangeLogID) {
					$myrow = $rows[$j];

	                $IsPort = $myrow['is_port'] == 't';

					if ($MultiplePortsThisCommit) {
						$HTML .= '<BR>';
					}

					if (!$MultiplePortsThisCommit) {
						GLOBAL $freshports_mail_archive;

						$HTML .= '<SMALL>';
						$HTML .= '[ ' . $myrow["commit_time"] . ' ' . freshports_CommitterEmailLink($myrow["committer"]) . ' ]';
						$HTML .= '</SMALL>';
						$HTML .= '&nbsp;';
						$HTML .= freshports_Email_Link($myrow["message_id"]);

						if ($myrow["encoding_losses"] == 't') {
							$HTML .= '&nbsp;' . freshports_Encoding_Errors();
						}

						if (IsSet($myrow["security_notice_id"])) {
							$HTML .= ' <a href="/security-notice.php?message_id=' . $myrow["message_id"] . '">' . freshports_Security_Icon() . '</a>';
						}

						if ($NumRows > 7) {
							$HTML .= " <small>$NumRowsTotal ports touched by this commit</small>\n";
						}

						$HTML .= "<BR>\n";
					}


					if ($IsPort) {
						$HTML .= '<BIG><B>';
						$HTML .= '<A HREF="/' . $myrow["category"] . '/' . $myrow["port"] . '/">';
						$HTML .= $myrow["port"];
					
						$HTML .= '</A>';

						$HTML .= ' '. freshports_PackageVersion($myrow["port_version"], $myrow["port_revision"], $myrow["port_epoch"]);

						$HTML .= "</B></BIG>\n";

					$HTML .= '<A HREF="/' . $myrow["category"] . '/">';
					$HTML .= $myrow["category"]. "</A>";
					$HTML .= '&nbsp;';

					} else {
						$HTML .= '<BIG><B>';
						$ElementPathname = preg_replace('|^/?ports/|', '', $myrow['pathname']);
						$HTML .= '<A HREF="/' . $ElementPathname . '">';
						$HTML .= $ElementPathname;
						$HTML .= '</A>';
						$HTML .= "</B></BIG>\n";
					}

					if ($User->id && $IsPort) {
						if ($myrow["onwatchlist"]) {
							$HTML .= freshports_Watch_Link_Remove($User->watch_list_add_remove, $myrow["onwatchlist"], $myrow["element_id"]);
						} else {
							$HTML .= freshports_Watch_Link_Add   ($User->watch_list_add_remove, $myrow["onwatchlist"], $myrow["element_id"]);
						}
					}

					$HTML .= "\n";

					if ($IsPort) {
						$HTML .= freshports_CommitFilesLink($myrow["message_id"], $myrow["category"], $myrow["port"]);
					}

					// indicate if this port has been removed from cvs
					if ($myrow["status"] == "D") {
						$HTML .= " " . freshports_Deleted_Icon_Link() . "\n";
					}

					if ($IsPort) {
						// indicate if this port needs refreshing from CVS
						if ($myrow["needs_refresh"]) {
							$HTML .= " " . freshports_Refresh_Icon_Link() . "\n";
						}

						if ($myrow["date_added"] > Time() - 3600 * 24 * $DaysMarkedAsNew) {
							$MarkedAsNew = "Y";
							$HTML .= freshports_New_Icon() . "\n";
						}

						if ($myrow["forbidden"]) {
							$HTML .= ' ' . freshports_Forbidden_Icon_Link() . "\n";
						}

						if ($myrow["broken"]) {
							$HTML .= '&nbsp;' . freshports_Broken_Icon_Link() . "\n";
						}

						if ($myrow["deprecated"]) {
							$HTML .= '&nbsp;' . freshports_Deprecated_Icon_Link() . "\n";
						}

						if ($myrow["ignore"]) {
							$HTML .= '&nbsp;' . freshports_Ignore_Icon_Link() . "\n";
						}

						$HTML .= ' ' . htmlspecialchars($myrow["short_description"]) . "\n";
					}

					$j++;
					$MultiplePortsThisCommit = 1;
				} // end while

				$i = $j - 1;

				$HTML .= "\n<BLOCKQUOTE>\n";
				$HTML .= freshports_PortDescriptionPrint($myrow["commit_description"], $myrow["encoding_losses"]);
				$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";
			}

			echo $HTML;

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
	freshports_SideBar();
	?>

  </td>
</TR>
</TABLE>

<BR>

<?
freshports_ShowFooter();
?>

</body>
</html>
