<?php
	#
	# $Id: commit.php,v 1.1.2.34 2003-11-26 15:56:14 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	if (IsSet($_GET['message_id'])) $message_id = AddSlashes($_GET['message_id']);
	if (IsSet($_GET['commit_id']))  $commit_id  = AddSlashes($_GET['commit_id']);

	$Title = 'Commit found by ';
	if ($message_id) {
		$Title .= 'message id';
	} else {
		$Title .= 'commit id';
	}
	freshports_Start($Title,
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');
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

	$sql = "select * from freshports_commit('$message_id')";

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
			$HTML = "";
			unset($ThisChangeLogID);
			for ($i = 0; $i < $NumRows; $i++) {
				$myrow = $rows[$i];
				$ThisChangeLogID = $myrow["commit_log_id"];
				if ($LastDate <> $myrow["commit_date"]) {
					$LastDate = $myrow["commit_date"];
					$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
					$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($myrow["commit_date"], 0, "D, j M Y") . '</BIG></FONT>' . "\n";
					$HTML .= '</TD></TR>' . "\n\n";
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

						$HTML .= "<BR>\n";
					}


					if ($IsPort) {
						$HTML .= '<BIG><B>';
						$HTML .= '<A HREF="/' . $myrow["category"] . '/' . $myrow["port"] . '/">';
						$HTML .= $myrow["port"];
					
						$HTML .= '</A>';

						if (strlen($myrow["version"]) > 0) {
							$HTML .= ' ' . $myrow["version"];
							if (strlen($myrow["revision"]) > 0 && $myrow["revision"] != "0") {
				    			$HTML .= '-' . $myrow["revision"];
							}
						}
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
						$HTML .= ' ' . $myrow["revision"];
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
						$HTML .= " " . freshports_Deleted_Icon() . "\n";
					}

					// indicate if this port needs refreshing from CVS
					if ($myrow["needs_refresh"]) {
						$HTML .= " " . freshports_Refresh_Icon() . "\n";
					}

					if ($myrow["date_added"] > Time() - 3600 * 24 * $DaysMarkedAsNew) {
						$MarkedAsNew = "Y";
						$HTML .= freshports_New_Icon() . "\n";
					}

					if ($myrow["forbidden"]) {
						$HTML .= ' ' . freshports_Forbidden_Icon() . "\n";
					}

					if ($myrow["broken"]) {
						$HTML .= '&nbsp;' . freshports_Broken_Icon() . "\n";
					}

					if ($IsPort) {
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
	echo '<tr><td>nothing supplied, nothing found!</td></tr><tr>';
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
