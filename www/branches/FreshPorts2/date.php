<?
	# $Id: date.php,v 1.1.2.4 2002-11-28 04:47:37 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/../classes/commits.php");

	freshports_Start("the place for ports",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports");
	$Debug=0;

	$Date = AddSlashes($_GET["date"]);
	if ($Date == '' || strtotime($Date) == -1) {
		$Date = date("Y-m-d");
	}

?>
<html>
<body>

<?php

#echo "That date is " . $Date . '<br>';
#echo 'which is ' . strtotime($Date) . '<br>';

$commits = new Commits($db);
$NumRows = $commits->Fetch($Date);

#echo '<br>NumRows = ' . $NumRows;

echo '<TABLE WIDTH="100%" BORDER="1" CELLSPACING="0" CELLPADDING="5">';
$HTML = "";
unset($ThisCommitLogID);
for ($i = 0; $i < $NumRows; $i++) {
	$commit = $commits->FetchNth($i);
#	echo 'now processing ' . $i . ' ' . $commit->message_id . '<br>';
	$ThisCommitLogID = $commit->commit_log_id;

	if ($LastDate <> $commit->commit_date) {
#		echo 'we have a new date ' . $commit->commit_date . '<br>';
		$LastDate = $commit->commit_date;
		$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
		$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($commit->commit_date, 0, "D, j M Y") . ' : ' . $NumRows . ' commits today</BIG></FONT>' . "\n";
		$HTML .= '</TD></TR>' . "\n\n";
	} else {
#		echo 'we have the same old date';
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
			GLOBAL $freshports_mail_archive;

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

			if ($WatchListID) {
				if ($commit->watch) {
					$HTML .= ' '. freshports_Watch_Link_Remove($commit->element_id) . ' ';
				} else {
					$HTML .= ' '. freshports_Watch_Link_Add($commit->element_id) . ' ';
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
		$HTML .= '<BR>' . freshports_MorePortsToShow($commit->message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
	}

	$i = $j - 1;

	$HTML .= "\n<BLOCKQUOTE>";

	$HTML .= freshports_PortDescriptionPrint($PreviousCommit->commit_description, $PreviousCommit->encoding_losses, $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($PreviousCommit->message_id, $freshports_CommitMsgMaxNumOfLinesToShow));

	$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";
}

echo $HTML;

echo '</table>';


?>



<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>


</body>
</html>


</body>
</html>