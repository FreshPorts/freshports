<?php
	#
	# $Id: date.php,v 1.1.2.18 2003-04-27 14:48:11 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits.php');

	freshports_Start($FreshPortsSlogan,
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');
	$Debug = 0;

	$ArchiveBaseDirectory = $_SERVER['DOCUMENT_ROOT'] . '/archives';

	# NOTE: All dates must be of the form: YYYY/MM/DD
	# this format can be achieved using the date('Y/m/d') function.

	#
	# Get the date we are going to work with.
	#
	$Date = AddSlashes($_GET['date']);

	$DateMessage = '';

	if ($Date == '' || strtotime($Date) == -1) {
		$DateMessage = 'date assumed';
		$Date = date('Y/m/d');
	}
	list($year, $month, $day) = explode('/', $Date);
	if (!CheckDate($month, $day, $year)) {
		$DateMessage = 'date adjusted to something realistic';
		$Date = date('Y/m/d');
	} else {
		$Date = date('Y/m/d', strtotime($Date));
	}

	function ArchiveFileName($Date) {
		$File = $ArchiveBaseDirectory . '/' . $Date . '.daily';
	}

	function ArchiveDirectoryCreate($Date) {
		$SubDir      = date('Y/m', strtotime($Date));
		$DirToCreate = $ArchiveBaseDirectory . '/' . $SubDir;
		system("mkdir -p $DirToCreate");
		
		return $DirToCreate;
	}

	function ArchiveExists($Date) {
		# returns file name for archive if it exists
		# empty string otherwise

		$File = ArchiveFileName($Date);
		if (!file_exists($File)) {
			$File = '';
		}

		return $File;
	}

	function ArchiveSave($Date) {
		# saves the archive away...
		
		ArchiveDirectoryCreate($Date);
		$File = ArchiveFileName($Date);
		
		

		
	}

	function ArchiveCreate($Date, $DateMessage, $db, $User) {
		GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;

		$commits = new Commits($db);
		$NumRows = $commits->Fetch($Date, $User->id);
	
		#echo '<br>NumRows = ' . $NumRows;

		$HTML = '';

		if ($NumRows == 0) {
			$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
			$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($Date, 0, "D, j M Y") . '</BIG></FONT>' . "\n";
			$HTML .= '</TD></TR>' . "\n\n";
			$HTML .= '<TR><TD>No commits found for that date</TD></TR>';
		}
		
		unset($ThisCommitLogID);
		for ($i = 0; $i < $NumRows; $i++) {
			$commit = $commits->FetchNth($i);
			$ThisCommitLogID = $commit->commit_log_id;
		
			if ($LastDate <> $commit->commit_date) {
				$LastDate = $commit->commit_date;
				$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
				$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($commit->commit_date, 0, "D, j M Y") . ' : ' . $NumRows . ' commits found </BIG>';
				if ($DateMessage) {
					$HTML .= ' (' . $DateMessage . ')';
				}
				
				$HTML .= '</FONT>' . "\n";
				$HTML .= '</TD></TR>' . "\n\n";
			}
		
			$j = $i;
		
			$HTML .= "<TR><TD>\n";
		
			// OK, while we have the log change log, let's put the port details here.
		
			# count the number of ports in this commit
			$NumberOfPortsInThisCommit = 0;
			$MaxNumberPortsToShow      = 10;
			while ($j < $NumRows && $commit->commit_log_id == $ThisCommitLogID) {
				$NumberOfPortsInThisCommit++;
		
				if ($NumberOfPortsInThisCommit == 1) {
					$HTML .= '<SMALL>';
					$HTML .= '[ ' . $commit->commit_time . ' ' . freshports_CommitterEmailLink($commit->committer) . ' ]';
					$HTML .= '</SMALL>';
					$HTML .= '&nbsp;';
					$HTML .= freshports_Email_Link($commit->message_id);
		
					if ($commit->encoding_losses == 't') {
						$HTML .= '&nbsp;' . freshports_Encoding_Errors();
					}
				}
		
				if ($NumberOfPortsInThisCommit <= $MaxNumberPortsToShow) {
		
					$HTML .= "<BR>\n";
		
					$HTML .= '<BIG><B>';
					$HTML .= '<A HREF="/' . $commit->category . '/' . $commit->port . '/">';
					$HTML .= $commit->port;
				
					if (strlen($commit->version) > 0) {
						$HTML .= ' ' . $commit->version;
						if (strlen($commit->revision) > 0 && $commit->revision != "0") {
				    		$HTML .= '-' . $commit->revision;
						}
					}
		
					$HTML .= "</A></B></BIG>\n";
		
					$HTML .= '<A HREF="/' . $commit->category . '/">';
					$HTML .= $commit->category. "</A>";
					$HTML .= '&nbsp;';
		
					if ($User->id) {
#						echo '$User->watch_list_add_remove=\'' . $User->watch_list_add_remove . '\'';

						if ($commit->onwatchlist) {
							$HTML .= ' '. freshports_Watch_Link_Remove($User->watch_list_add_remove, $commit->onwatchlist, $commit->element_id) . ' ';
						} else {
							$HTML .= ' '. freshports_Watch_Link_Add   ($User->watch_list_add_remove, $commit->onwatchlist, $commit->element_id) . ' ';
						}
					}
		
					// indicate if this port has been removed from cvs
					if ($commit->status == "D") {
						$HTML .= " " . freshports_Deleted_Icon() . "\n";
					}
		
					// indicate if this port needs refreshing from CVS
					if ($commit->needs_refresh) {
						$HTML .= " " . freshports_Refresh_Icon() . "\n";
					}
		
					if ($commit->date_added > Time() - 3600 * 24 * $DaysMarkedAsNew) {
						$MarkedAsNew = "Y";
						$HTML .= freshports_New_Icon() . "\n";
					}
		
					if ($commit->forbidden) {
						$HTML .= ' ' . freshports_Forbidden_Icon() . "\n";
					}
		
					if ($commit->broken) {
						$HTML .= ' '. freshports_Broken_Icon() . "\n";
					}
		
					$HTML .= freshports_CommitFilesLink($commit->message_id, $commit->category, $commit->port);
					$HTML .= "&nbsp;";
		
					$HTML .= htmlspecialchars($commit->short_description) . "\n";
				}
		
				$j++;
				$PreviousCommit = $commit;
				if ($j < $NumRows) {
					$commit = $commits->FetchNth($j);
				}
			} // end while
		
		
			if ($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) {
				$HTML .= '<BR>' . freshports_MorePortsToShow($PreviousCommit->message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
			}
		
			$i = $j - 1;
		
			$HTML .= "\n<BLOCKQUOTE>";
		
			$HTML .= freshports_PortDescriptionPrint($PreviousCommit->commit_description, $PreviousCommit->encoding_losses, $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($PreviousCommit->message_id, $freshports_CommitMsgMaxNumOfLinesToShow));
		
			$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";
		}

		return $HTML;
	}

?>

<?php
#echo "That date is " . $Date . '<br>';
#echo 'which is ' . strtotime($Date) . '<br>';

$Yesterday = freshports_LinkToDate(strtotime($Date) - 86400, "Previous day");
$Tomorrow  = freshports_LinkToDate(strtotime($Date) + 86400, "Following day");

echo "<center>$Yesterday $Tomorrow</center>";
?>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD VALIGN="top">

<?php

echo '<TABLE WIDTH="100%" BORDER="1" CELLSPACING="0" CELLPADDING="5">';

$HTML = ArchiveCreate($Date, $DateMessage, $db, $User);

echo $HTML;

echo '</table>';

?>

</td>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">

	<?
	freshports_SideBar();
	?>

  </td>	
</tr>
</table>

<?
freshports_ShowFooter();
?>

</body>
</html>