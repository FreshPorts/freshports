<?php
	#
	# $Id: latest_commits.php,v 1.1.2.1 2003-11-20 14:26:14 dan Exp $
	#
	# Copyright (c) 2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_record.php');

// base class for keeping statistics on page rendering issues
class LatestCommits {

	var $Debug = 0;
	var $dbh;
	var $MaxNumberOfPorts;

	var $LocalResult;
	var $HTML;

	function LatestCommits($dbh, $MaxNumberOfPorts) {
		$this->dbh = $dbh;
		$this->MaxNumberOfPorts = $MaxNumberOfPorts;

		$sql = "select * from LatestCommits($MaxNumberOfPorts, ";
		if ($User->id) {
			$sql .= $User->id;
		} else {
			$sql .= 0;
		}
		$sql .= ');';

		if ($this->Debug) echo "\n<pre>sql=$sql</pre>\n";

		$result = pg_exec($this->dbh, $sql);
		if (!$result) {
            die("read from database failed");
			exit;
		}

		$NumRows = pg_numrows($result);
		if ($this->Debug) echo "Number of rows = $NumRows<br>";
		if (!$NumRows) { 
			$this->HTML = "<P>Sorry, nothing found in the database....</P>\n";
			return 1;
		}
	
		$i=0;
		$GlobalHideLastChange = "N";
		for ($i = 0; $i < $NumRows; $i++) {
			$myrow = pg_fetch_array ($result, $i);
			$mycommit = new CommitRecord($this->dbh);
			$mycommit->PopulateValues($myrow);
			$commits[$i] = $mycommit;
		}
	
		$LastDate = '';

		$this->HTML = "";
		unset($ThisCommitLogID);
		for ($i = 0; $i < $NumRows; $i++) {
			$mycommit = $commits[$i];
			$ThisCommitLogID = $mycommit->commit_log_id;

			if ($LastDate <> $mycommit->commit_date) {
				$LastDate = $mycommit->commit_date;
				$this->HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
				$this->HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($mycommit->commit_date, 0, "D, j M Y") . '</BIG></FONT>' . "\n";
				$this->HTML .= '</TD></TR>' . "\n\n";
			}

			$j = $i;

			$this->HTML .= "<TR><TD>\n";

			// OK, while we have the log change log, let's put the port details here.

			# count the number of ports in this commit
			$NumberOfPortsInThisCommit = 0;
			$MaxNumberPortsToShow      = 10;
			while ($j < $NumRows && $commits[$j]->commit_log_id == $ThisCommitLogID) {
#echo "in NumberOfPortsInThisCommit loop $i, $j<br>";
				$NumberOfPortsInThisCommit++;
				$mycommit = $commits[$j];

				if ($NumberOfPortsInThisCommit == 1) {
					GLOBAL $freshports_mail_archive;

					$this->HTML .= '<SMALL>';
					$this->HTML .= '[ ' . $mycommit->commit_time . ' ' . freshports_CommitterEmailLink($mycommit->committer) . ' ]';
					$this->HTML .= '</SMALL>';
					$this->HTML .= '&nbsp;';
					$this->HTML .= freshports_Email_Link($mycommit->message_id);

					if ($mycommit->EncodingLosses()) {
						$this->HTML .= '&nbsp;' . freshports_Encoding_Errors();
					}

					if (IsSet($mycommit->security_notice_id)) {
						$this->HTML .= ' <a href="/security-notice.php?message_id=' . $mycommit->message_id . '">' . freshports_Security_Icon() . '</a>';
					}

				}

				if ($NumberOfPortsInThisCommit <= $MaxNumberPortsToShow) {

					$this->HTML .= "<BR>\n";

					if (IsSet($mycommit->category) || $mycommit->category != '') {
						$this->HTML .= '<BIG><B>';
						$this->HTML .= '<A HREF="/' . $mycommit->category . '/' . $mycommit->port . '/">';
						$this->HTML .= $mycommit->port;
						$this->HTML .= '</A>';
			
						if (strlen($mycommit->version) > 0) {
							$this->HTML .= ' ' . $mycommit->version;
							if (strlen($mycommit->revision) > 0 && $mycommit->revision != "0") {
								$this->HTML .= '-' . $mycommit->revision;
							}
						}

						$this->HTML .= "</B></BIG>\n";

						$this->HTML .= '<A HREF="/' . $mycommit->category . '/">';
						$this->HTML .= $mycommit->category. "</A>";
						$this->HTML .= '&nbsp;';

						if ($User->id) {
							if ($mycommit->watch) {
								$this->HTML .= ' '. freshports_Watch_Link_Remove($User->watch_list_add_remove, $mycommit->watch, $mycommit->element_id) . ' ';
							} else {
								$this->HTML .= ' '. freshports_Watch_Link_Add   ($User->watch_list_add_remove, $mycommit->watch, $mycommit->element_id) . ' ';
							}
						}

						// indicate if this port has been removed from cvs
						if ($mycommit->status == "D") {
							$this->HTML .= " " . freshports_Deleted_Icon() . "\n";
						}

						// indicate if this port needs refreshing from CVS
						if ($mycommit->needs_refresh) {
							$this->HTML .= " " . freshports_Refresh_Icon() . "\n";
						}

						if ($mycommit->date_added > Time() - 3600 * 24 * $DaysMarkedAsNew) {
							$MarkedAsNew = "Y";
							$this->HTML .= freshports_New_Icon() . "\n";
						}

						if ($mycommit->forbidden) {
							$this->HTML .= ' ' . freshports_Forbidden_Icon() . "\n";
						}

						if ($mycommit->broken) {
							$this->HTML .= ' '. freshports_Broken_Icon() . "\n";
						}

						$this->HTML .= freshports_CommitFilesLink($mycommit->message_id, $mycommit->category, $mycommit->port);
						$this->HTML .= "&nbsp;";

					} else {
#echo 'no category found!<br>';
						$this->HTML .= '<BIG><B>';
						$PathName = preg_replace('|^/?ports/|', '', $mycommit->element_pathname);
						$this->HTML .= '<a href="/' . $PathName . '">' . $PathName . '</a>';
						$this->HTML .= "</B></BIG>\n";
					}
					$this->HTML .= htmlspecialchars($mycommit->short_description) . "\n";
				}

				$j++;
			} // end while

			if ($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) {
				$this->HTML .= '<BR>' . freshports_MorePortsToShow($mycommit->message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
			}
			$i = $j - 1;

			$this->HTML .= "\n<BLOCKQUOTE>";
#echo "freshports_PortDescriptionPrint called $i<br>";

			$this->HTML .= freshports_PortDescriptionPrint($mycommit->commit_description, $mycommit->encoding_losses, $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($mycommit->message_id, $freshports_CommitMsgMaxNumOfLinesToShow));

			$this->HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";
		}
	}
}
?>
