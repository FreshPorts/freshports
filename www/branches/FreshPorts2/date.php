<?
	# $Id: date.php,v 1.1.2.2 2002-11-27 21:53:27 dan Exp $
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
#echo 'which is ' . strtotime($Date);

$commits = new Commits($db);
$NumRows = $commits->Fetch($Date);

echo '<TABLE WIDTH="100%" BORDER="1" CELLSPACING="0" CELLPADDING="5">';
$HTML = "";
unset($ThisCommitLogID);
for ($i = 0; $i < $NumRows; $i++) {
	$commits->FetchNth($i);
#	echo 'now processing ' . $i . ' ' . $commits->message_id . '<br>';
	$ThisCommitLogID = $commits->commit_log_id;

	if ($LastDate <> $commits->commit_date) {
#		echo 'we have a new date ' . $commits->commit_date . '<br>';
		$LastDate = $commits->commit_date;
		$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
		$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($commits->commit_date, 0, "D, j M") . '</BIG></FONT>' . "\n";
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
	while ($j < $NumRows && $commits->commit_log_id == $ThisCommitLogID) {
		$NumberOfPortsInThisCommit++;
		$commits->FetchNth($j);

		if ($NumberOfPortsInThisCommit == 1) {
			GLOBAL $freshports_mail_archive;

			$HTML .= '<SMALL>';
			$HTML .= '[ ' . $commits->commit_time . ' ' . freshports_CommitterEmailLink($commits->committer) . ' ]';
			$HTML .= '</SMALL>';
			$HTML .= '&nbsp;';
			$HTML .= freshports_Email_Link($commits->message_id);

			if ($commits->encoding_losses == 't') {
				$HTML .= '&nbsp;' . freshports_Encoding_Errors();
			}
		}

		if ($NumberOfPortsInThisCommit <= $MaxNumberPortsToShow) {

			$HTML .= "<BR>\n";

			$HTML .= '<BIG><B>';
			$HTML .= '<A HREF="/' . $commits->category . '/' . $commits->port . '/">';
			$HTML .= $commits->port;
		
			if (strlen($commits->version) > 0) {
				$HTML .= ' ' . $commits->version;
				if (strlen($commits->revision) > 0 && $commits->revision != "0") {
		    		$HTML .= '-' . $commits->revision;
				}
			}

			$HTML .= "</A></B></BIG>\n";

			$HTML .= '<A HREF="/' . $commits->category . '/">';
			$HTML .= $commits->category. "</A>";
			$HTML .= '&nbsp;';

			if ($WatchListID) {
				if ($commits->watch) {
					$HTML .= ' '. freshports_Watch_Link_Remove($commits->element_id) . ' ';
				} else {
					$HTML .= ' '. freshports_Watch_Link_Add($commits->element_id) . ' ';
				}
			}

			// indicate if this port has been removed from cvs
			if ($commits->status == "D") {
				$HTML .= " " . freshports_Deleted_Icon() . "\n";
			}

			// indicate if this port needs refreshing from CVS
			if ($commits->needs_refresh) {
				$HTML .= " " . freshports_Refresh_Icon() . "\n";
			}

			if ($commits->date_added > Time() - 3600 * 24 * $DaysMarkedAsNew) {
				$MarkedAsNew = "Y";
				$HTML .= freshports_New_Icon() . "\n";
			}

			if ($commits->forbidden) {
				$HTML .= ' ' . freshports_Forbidden_Icon() . "\n";
			}

			if ($commits->broken) {
				$HTML .= ' '. freshports_Broken_Icon() . "\n";
			}

			$HTML .= freshports_CommitFilesLink($commits->message_id, $commits->category, $commits->port);
			$HTML .= "&nbsp;";

			$HTML .= htmlspecialchars($commits->short_description) . "\n";
		}

		$j++;
	} // end while


	if ($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) {
		$HTML .= '<BR>' . freshports_MorePortsToShow($commits->message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
	}

	$i = $j - 1;

	$HTML .= "\n<BLOCKQUOTE>";

	$HTML .= freshports_PortDescriptionPrint($commits->commit_description, $commits->encoding_losses, $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($commits->message_id, $freshports_CommitMsgMaxNumOfLinesToShow));

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