<?php
	#
	# $Id: display_commit.php,v 1.14 2012-12-21 18:20:53 dan Exp $
	#
	# Copyright (c) 2003-2007 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_ports.php');

// base class for displaying commits
class DisplayCommit
{

	var $Debug = 0;
	var $dbh;

	var $result;
	var $MaxNumberOfPorts;

	var $BranchName;
	var $WatchListAsk = '';    // either default or ask.  the watch list to which add/remove works.
	var $UserID = 0;
	var $DaysMarkedAsNew = 10;
	var $LocalResult;
	var $HTML;

	var $FlaggedCommits;

	var $ShowAllPorts = FALSE;    # by default we show only the first few ports.
	var $ShowEntireCommit = 0;    # by default we show only the first few lines of the commit message.

	var $ShowLinkToSanityTestFailure = FALSE;

	# the message_id for all the emails which originated from subversion contain freebsd.org
	# For git commits, we put the full has into message_id. Commits from git do not contain that value.
	# This is used to decide if commits are from svn or from git.
	# Commits imported before we started saving message_id are in the null.freshports.org domain,
	# so we just look for .org
	const MESSAGE_ID_DOMAIN = '.org';

	function __construct($dbh, $result, $BranchName = BRANCH_HEAD)
	{
		$this->dbh        = $dbh;
		$this->result     = $result;
		$this->BranchName = $BranchName;
	}

	function IsGitCommit($message_id)
	{
		return strpos($message_id, self::MESSAGE_ID_DOMAIN) == false;
	}

	function SetDaysMarkedAsNew($DaysMarkedAsNew)
	{
		$this->DaysMarkedAsNew = $DaysMarkedAsNew;
	}

	function SetUserID($UserID)
	{
		$this->UserID = $UserID;
	}

	function SetWatchListAsk($WatchListAsk)
	{
		$this->WatchListAsk = $WatchListAsk;
	}

	function SetShowAllPorts($ShowAllPorts)
	{
		$this->ShowAllPorts = $ShowAllPorts;
	}

	function SetShowEntireCommit($ShowEntireCommit)
	{
		$this->ShowEntireCommit = $ShowEntireCommit;
	}

	function _DisplayDateBanner($mycommit) {
		$HTML = '';

		$HTML .= '<tr><td class="accent">' . "\n";
		$HTML .= '   ' . FormatTime($mycommit->commit_date, 0, "l, j M Y") . "\n";
		$HTML .= "</td></tr>\n\n";

		return $HTML;
	}

	function _DisplayStartofCommit($mycommit)
	{
		global $freshports_mail_archive;

		$HTML = '';

		$this->HTML .= "<tr><td class=\"commit-details\">\n";

		$this->HTML .= '<span class="meta">';
		$this->HTML .= $mycommit->commit_time . ' ';

		#
		# THIS CODE IS SIMILAR TO THAT IN classes/display_commit.php & classes/port-display.php
		#
		#
		# the committer may not be the author
		# committer name and author name came into the database with git.
		# For other commits, such as git or cvs, those fields will not be present.
		# committer will always be present.
		#
		$CommitterIsNotAuthor = !empty($mycommit->author_name) && !empty($mycommit->committer_name) && $mycommit->author_name != $mycommit->committer_name;

		# if no author name, it's an older commit, and we have only committer
		if (empty($mycommit->committer_name)) {
			$HTML .= freshports_CommitterEmailLink_Old($mycommit->committer);
		} else {
			$HTML .= freshports_AuthorEmailLink($mycommit->committer_name, $mycommit->committer_email);
			# display the committer id, just because
			$HTML .= '&nbsp;(' . $mycommit->committer . ')';
		}

		# after the committer, display a search-by-committer link
		$HTML .= '&nbsp;' . freshports_Search_Committer($mycommit->committer);

		if ($CommitterIsNotAuthor) {
			$HTML .= '&nbsp;Author:&nbsp;' . freshports_AuthorEmailLink($mycommit->author_name, $mycommit->author_email);
		}
		$HTML .= '</span>';

		# The first comparison was here before the second, which was added as part of
		# https://github.com/FreshPorts/freshports/issues/221 - date.php is not quarterly aware
		if (!empty($mycommit->branch) && $this->BranchName != $mycommit->branch || $this->BranchName != BRANCH_HEAD) {
			$HTML .= ' <span class="commit-branch">' . $mycommit->branch . '</span>';
		}

		return $HTML;
	}

	function _DisplayPortDetails($mycommit, $QueryArgs)
	{
		$Debug = $this->Debug;

		$HTML_Array = array();
		$Max_line_length = 0; # The number of elements on this line, which becomes the number of columns in the tab.

		$HTML = '';
		# This is a non-port element...
		$HTML .= '<span class="element-details">';

		$PathName = preg_replace('|^/?ports/|', '', $mycommit->element_pathname);
		if ($Debug) echo "PathName='$PathName' " . " reponame='" . $mycommit->repo_name . "'<br>";
		switch ($mycommit->repo_name) {
			case 'ports':
				// strip off the leading directories
				if ($Debug && !empty($mycommit->branch)) echo 'Branch is ' . $mycommit->branch . '<br>';
				if (empty($mycommit->branch) || $mycommit->branch == BRANCH_HEAD) {
					if ($Debug) echo 'replacing head<br>';
					$PathName = preg_replace('|^head/|', '', $PathName);
				} else {
					if ($Debug) echo 'replacing branches<br>';
					$PathName = preg_replace('|^branches/' . $mycommit->branch . '/|', '', $PathName);
				}
				break;
		}

		if ($PathName != $mycommit->element_pathname) {
			# the replace changes encoded / to plain text / - not sure why may have been present
			$HTML .= '<a href="/' . str_replace('%2F', '/', $PathName);
			if (!empty($mycommit->port)) $HTML .= '/';
			$HTML .= $QueryArgs . '"';
			$HTML .= ' title="' . $PathName;
			if (IsSet($mycommit->short_description)) {
				$HTML .= ': ' . htmlentities($mycommit->short_description);
			}
			$HTML .= '"';
			$HTML .= '>' . $PathName . '</a>';
		} else {
			#$HTML .= '<a href="' . FRESHPORTS_FREEBSD_CVS_URL . $PathName . '#rev' . $mycommit->revision . '">' . $PathName . '</a>';
			$HTML .= $PathName;
		}

		$HTML .= ' ';
		$HTML .= freshports_PackageVersion($mycommit->version, $mycommit->revision, $mycommit->epoch);
		$HTML .= "</span>\n"; /* element-details*/

		$HTML .= ' ';
		$HTML .= $mycommit->short_description;

		if (isset($mycommit->category) && $mycommit->category != '') {
			// indicate if this port has been removed from cvs
			if ($mycommit->status == "D") {
				$HTML .= " " . freshports_Deleted_Icon_Link() . "\n";
			}

			// indicate if this port needs refreshing from the repo
			if ($mycommit->needs_refresh) {
				$HTML .= " " . freshports_Refresh_Icon_Link() . "\n";
			}
			if ($mycommit->branch == BRANCH_HEAD && $mycommit->date_added > Time() - 3600 * 24 * $this->DaysMarkedAsNew) {
				$MarkedAsNew = "Y";
				$HTML .= freshports_New_Icon() . "\n";
			}

			if ($mycommit->forbidden) {
				$HTML .= ' ' . freshports_Forbidden_Icon_Link() . "\n";
			}

			if ($mycommit->broken) {
				$HTML .= ' ' . freshports_Broken_Icon_Link() . "\n";
			}

			if ($mycommit->deprecated) {
				$HTML .= ' ' . freshports_Deprecated_Icon_Link() . "\n";
			}

			if ($mycommit->expiration_date) {
				if (date('Y-m-d') >= $mycommit->expiration_date) {
					$HTML .= freshports_Expired_Icon_Link($mycommit->expiration_date) . "\n";
				} else {
					$HTML .= freshports_Expiration_Icon_Link($mycommit->expiration_date) . "\n";
				}
			}

			if ($mycommit->ignore) {
				$HTML .= ' ' . freshports_Ignore_Icon_Link() . "\n";
			}

			if ($mycommit->vulnerable_current) {
				$HTML .= '&nbsp;' . freshports_VuXML_Icon() . '&nbsp;';
			}

			if ($mycommit->restricted) {
				$HTML .= freshports_Restricted_Icon_Link($mycommit->restricted) . '&nbsp;';
			}

			if ($mycommit->no_cdrom) {
				$HTML .= freshports_No_CDROM_Icon_Link($mycommit->no_cdrom) . '&nbsp;';
			}

			if ($mycommit->is_interactive) {
				$HTML .= freshports_Is_Interactive_Icon_Link($mycommit->is_interactive) . '&nbsp;';
			}

		}

		return $HTML;
	}

	function _DisplayEndOfCommit($mycommit, $Lines, $DetailsWillBePresented, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow)
	{
		$Debug = $this->Debug;
		$HTML = '';

		# if we had too many ports, let them know we aren't showing them all.
		# we show this only with the end of commit message.
		if (($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) && !$this->ShowAllPorts) {
			if ($DetailsWillBePresented) {
				$HTML .= '</ul>';
			}
			$HTML .= freshports_MorePortsToShow($mycommit->message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
		} else {
			if ($DetailsWillBePresented) {
				$HTML .= '</ul>';
			}
		}

		# display the commit message
		$HTML .= "\n<blockquote class=\"description\">";
		$HTML .= freshports_CommitDescriptionPrint(
			$mycommit->commit_description,
			$mycommit->encoding_losses,
			$Lines,
			freshports_MoreCommitMsgToShow($mycommit->message_id, $Lines));
		# close off the previous commit first
		$HTML .= "\n</blockquote>\n";


		# display a link to the old mailing list
		if ($this->IsGitCommit($mycommit->message_id)) {
			# do nothing
		} else {
			$HTML .= freshports_Email_Link($mycommit->message_id);
		}


		# we use element-details so the icons on the hash align with the icons on the elements which appear below it.
		$HTML .= '<span class="hash">';

		if ($this->UserID) {
			if (isset($this->FlaggedCommits[$mycommit->commit_log_id])) {
				$HTML .= freshports_Commit_Flagged_Link($mycommit->message_id);
			} else {
				$HTML .= freshports_Commit_Flagged_Not_Link($mycommit->message_id);
			}
		}

		if ($mycommit->EncodingLosses()) {
			$HTML .= '&nbsp;' . freshports_Encoding_Errors();
		}

		if ($mycommit->svn_revision != '') {
			if ($this->IsGitCommit($mycommit->message_id)) {
				$HTML .= freshports_git_commit_Link_freebsd($mycommit->svn_revision, $mycommit->repo_hostname, $mycommit->path_to_repo) . '&nbsp;';
				$HTML .= freshports_git_commit_Link_codeberg($mycommit->svn_revision, $mycommit->repo_hostname, $mycommit->path_to_repo) . '&nbsp;';
				$HTML .= freshports_git_commit_Link_github($mycommit->svn_revision, $mycommit->repo_hostname, $mycommit->path_to_repo) . '&nbsp;';
				$HTML .= freshports_git_commit_Link_gitlab($mycommit->svn_revision, $mycommit->repo_hostname, $mycommit->path_to_repo) . '&nbsp;';
				$HTML .= freshports_git_commit_Link_Hash($mycommit->svn_revision, $mycommit->commit_hash_short, $mycommit->repo_hostname, $mycommit->path_to_repo);
			} else {
				$HTML .= freshports_svnweb_ChangeSet_Link($mycommit->svn_revision, $mycommit->repo_hostname) . '&nbsp;';
			}
		}

		if ($mycommit->stf_message != '' && $this->ShowLinkToSanityTestFailure) {
			$HTML .= '&nbsp;' . freshports_SanityTestFailure_Link($mycommit->message_id);
		}

		$HTML .= '</span>'; /* hash */

		return $HTML;
	}

	function CreateHTML() {
		global $freshports_CommitMsgMaxNumOfLinesToShow;

		$Debug = $this->Debug;

		$URLBranchSuffix = BranchSuffix($this->BranchName);

		if (!$this->result) {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': no result set supplied');
			die("read from database failed");
			exit;
		}

		$NumRows = pg_num_rows($this->result);
		if ($this->Debug) echo __FILE__ . ':' . __LINE__ . " Number of rows = $NumRows<br>\n";
		if (!$NumRows) {
			$this->HTML = "<tr><td>\n<P>Sorry, nothing found in the database....</P>\n</td></tr>\n";
			return $this->HTML;
		}

		# if we have a UserID, but no flagged commits, grab them
		#
		if ($this->UserID && !isset($this->FlaggedCommits)) {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_flag.php');

			$FlaggedCommits = new CommitFlag($this->dbh);
			$NumFlaggedCommits = $FlaggedCommits->Fetch($this->UserID);
			for ($i = 0; $i < $NumFlaggedCommits; $i++) {
				$FlaggedCommits->FetchNth($i);
				$this->FlaggedCommits[$FlaggedCommits->commit_log_id] = $FlaggedCommits->commit_log_id;
				if ($this->Debug) echo "fetching record # $i -> $FlaggedCommits->commit_log_id<br>";
			}
		}


		$GlobalHideLastChange = "N";

		$this->HTML = "";

		# leave it all empty as a comparison point
		$PreviousCommit = new Commit_Ports($this->dbh);

		$NumberOfPortsInThisCommit = 0;
		$MaxNumberPortsToShow = 10;
		$TooManyPorts = false;    # we might not show all of a commit, just for the big ones.
		for ($i = 0; $i < $NumRows; $i++) {
			$myrow = pg_fetch_array($this->result, $i);
			if ($Debug) echo '<pre>processing row ' . $i . ' ' . $myrow['commit_log_id'] . ' ' . $myrow['message_id'] . ' ' . $myrow['element_pathname'] . "</pre><br>\n";
			unset($mycommit);
			$mycommit = new Commit_Ports($this->dbh);
			$mycommit->PopulateValues($myrow);

			if ($mycommit->branch && $mycommit->branch != BRANCH_HEAD) {
				$QueryArgs = '?branch=' . $mycommit->branch;
			} else {
				$QueryArgs = '';
			}

			$DetailsWillBePresented = !empty($mycommit->element_pathname);

			// OK, while we have the log change log, let's put the port details here.

			# not sure if this should be here or elsewhere
			$Lines = 0;
			if ($mycommit->commit_log_id != $PreviousCommit->commit_log_id) {
				if (!empty($PreviousCommit->commit_log_id)) {
					# display the end of the previous commit
					$this->HTML .= $this->_DisplayEndOfCommit($PreviousCommit, $Lines, $DetailsWillBePresented, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
				}

				$TooManyPorts = false;
				# count the number of ports in this commit.
				# first time into the loop, this will be executed.
				$NumberOfPortsInThisCommit = 0;
				$MaxNumberPortsToShow = 10;

				if ($mycommit->commit_date != $PreviousCommit->commit_date) {
					$this->HTML .= $this->_DisplayDateBanner($mycommit);
				}

				$this->HTML .= $this->_DisplayStartofCommit($mycommit);

				if ($DetailsWillBePresented) {
					$this->HTML .= '<ul class="element-list">' . "\n";
				}
			}

			$NumberOfPortsInThisCommit++;
			if (($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) && !$this->ShowAllPorts) {
				$TooManyPorts = true;
			}

			if ($Debug) echo 'at too many<br>';

			if (!$TooManyPorts) {
				if ($DetailsWillBePresented) {
					$this->HTML .= '<li>';
				}
				if (!$DetailsWillBePresented) {
					# we do nothing
					# this is a non-port. All the rest of the stuff is not displayed
					if ($Debug) echo 'details will not be presented<br>';
				} else {
					# we might be here for something like:
					# /commit.php?category=science&port=metaf2xml&files=yes&message_id=201407160318.s6G3IQKY090479@svn.freebsd.org
					syslog(LOG_NOTICE, 'We have non-port where element_pathname is not empty - first location');

					# for future consideration
					# we could display this as a table
					# get _DisplayPortDetails() to return an array of arrays, one for each port to be displayed
					# create a table, with enough columns to accommodate the longest line
					# display that resulting table just before displaying the end of the commit message.
					#
					$this->HTML .= $this->_DisplayPortDetails($mycommit, $QueryArgs);
				}
				if ($DetailsWillBePresented) {
					$this->HTML .= "</li>\n";
				}

				global $freshports_CommitMsgMaxNumOfLinesToShow;
				if ($this->ShowEntireCommit) {
					$Lines = 0;
				} else {
					$Lines = $freshports_CommitMsgMaxNumOfLinesToShow;
				}
			} # !$TooManyPorts

			$PreviousCommit = $mycommit;
		}

		$this->HTML .= $this->_DisplayEndOfCommit($PreviousCommit, $Lines, $DetailsWillBePresented, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);

		unset($mycommit);

		return $this->HTML;
	}

	function CreateHTML1() {
		GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
		
		$Debug = $this->Debug;

		$URLBranchSuffix = BranchSuffix($this->BranchName);

		if (!$this->result) {
			syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . ': no result set supplied');
			die("read from database failed");
			exit;
		}

		$NumRows = pg_num_rows($this->result);
		if ($this->Debug) echo __FILE__ . ':' . __LINE__ . " Number of rows = $NumRows<br>\n";
		if (!$NumRows) { 
			$this->HTML = "<tr><td>\n<P>Sorry, nothing found in the database....</P>\n</td></tr>\n";
			return $this->HTML;
		}
		
		# if we have a UserID, but no flagged commits, grab them
		#
		if ($this->UserID && !IsSet($this->FlaggedCommits)) {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_flag.php');

			$FlaggedCommits = new CommitFlag($this->dbh);
			$NumFlaggedCommits = $FlaggedCommits->Fetch($this->UserID);
			for ($i = 0; $i < $NumFlaggedCommits; $i++) {
				$FlaggedCommits->FetchNth($i);
				$this->FlaggedCommits[$FlaggedCommits->commit_log_id] = $FlaggedCommits->commit_log_id;
				if ($this->Debug) echo "fetching record # $i -> $FlaggedCommits->commit_log_id<br>";
			}
		}
	
		$GlobalHideLastChange = "N";

		$this->HTML = "";

		# leave it all empty as a comparison point
		$PreviousCommit = new Commit_Ports($this->dbh);

		$NumberOfPortsInThisCommit = 0;
		$MaxNumberPortsToShow      = 10;
		$TooManyPorts = false;	# we might not show all of a commit, just for the big ones.
		for ($i = 0; $i < $NumRows; $i++) {
			$myrow = pg_fetch_array($this->result, $i);
			if ($Debug) echo '<pre>processing row ' . $i . ' ' . $myrow['commit_log_id'] . ' ' . $myrow['message_id'] .  ' ' . $myrow['element_pathname'] . "</pre><br>\n";
			unset($mycommit);
			$mycommit = new Commit_Ports($this->dbh);
			$mycommit->PopulateValues($myrow);

			if ($mycommit->branch && $mycommit->branch != BRANCH_HEAD) {
				$QueryArgs= '?branch=' . $mycommit->branch;
			} else {
				$QueryArgs = '';
			}

			$DetailsWillBePresented = !empty($mycommit->element_pathname);

			// OK, while we have the log change log, let's put the port details here.

			# not sure if this should be here or elsewhere
			$Lines = 0;
			if ($mycommit->commit_log_id != $PreviousCommit->commit_log_id) {
				if ($Debug) echo 'This commit_log_id is different<br>';
				if (($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) && !$this->ShowAllPorts) {
					if ($DetailsWillBePresented) {
						$this->HTML .= '</ul>';
					}
					$this->HTML .= freshports_MorePortsToShow($PreviousCommit->message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
				} else if ($i > 0 && $DetailsWillBePresented) {
					$this->HTML .= '</ul>';
				}
				$TooManyPorts = false;
				# count the number of ports in this commit.
				# first time into the loop, this will be executed.
				$NumberOfPortsInThisCommit = 0;
				$MaxNumberPortsToShow = 10;

				if ($mycommit->commit_date != $PreviousCommit->commit_date) {
					$this->HTML .= '<tr><td class="accent">' . "\n";
					$this->HTML .= '   ' . FormatTime($mycommit->commit_date, 0, "l, j M Y") . "\n";
					$this->HTML .= "</td></tr>\n\n";
				}

				global $freshports_mail_archive;

				$this->HTML .= "<tr><td class=\"commit-details\">\n";

				$this->HTML .= '<span class="meta">';
				$this->HTML .= $mycommit->commit_time . ' ';

				#
				# THIS CODE IS SIMILAR TO THAT IN classes/display_commit.php & classes/port-display.php
				#
				#
				# the committer may not be the author
				# committer name and author name came into the database with git.
				# For other commits, such as git or cvs, those fields will not be present.
				# committer will always be present.
				#
				$CommitterIsNotAuthor = !empty($mycommit->author_name) && !empty($mycommit->committer_name) && $mycommit->author_name != $mycommit->committer_name;

				# if no author name, it's an older commit, and we have only committer
				if (empty($mycommit->committer_name)) {
					$this->HTML .= freshports_CommitterEmailLink_Old($mycommit->committer);
				} else {
					$this->HTML .= freshports_AuthorEmailLink($mycommit->committer_name, $mycommit->committer_email);
					# display the committer id, just because
					$this->HTML .= '&nbsp;(' . $mycommit->committer . ')';
				}

				# after the committer, display a search-by-committer link
				$this->HTML .= '&nbsp;' . freshports_Search_Committer($mycommit->committer);

				if ($CommitterIsNotAuthor) {
					$this->HTML .= '&nbsp;Author:&nbsp;' . freshports_AuthorEmailLink($mycommit->author_name, $mycommit->author_email);
				}
				$this->HTML .= '</span>';
			}

				$NumberOfPortsInThisCommit++;
				if (($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) && !$this->ShowAllPorts) {
					$TooManyPorts = true;
				}

				if ($Debug) echo 'at too many<br>';

				if ($DetailsWillBePresented) {
					$this->HTML .= '<ul class="element-list">' . "\n";
				}




				if (!$TooManyPorts) {
					if ($DetailsWillBePresented) {
						$this->HTML .= '<li>';
					}
					if (!$DetailsWillBePresented) {
						# we do nothing
						# this is a non-port. All the rest of the stuff is not displayed
						if ($Debug) echo 'details will not be presented<br>';
					} else {
						syslog(LOG_NOTICE, 'We have non-port where element_pathname is not empty - 2nd location: ' . $mycommit->element_pathname);

						# This is a non-port element...
						$this->HTML .= '<span class="element-details">';

						$PathName = preg_replace('|^/?ports/|', '', $mycommit->element_pathname);
						if ($Debug) echo "PathName='$PathName' " . " reponame='" . $mycommit->repo_name . "'<br>";
						switch ($mycommit->repo_name)
						{
							case 'ports':
								// strip off the leading directories
								if ($Debug && !empty($mycommit->branch)) echo 'Branch is ' . $mycommit->branch . '<br>';
								if (empty($mycommit->branch) || $mycommit->branch == BRANCH_HEAD) {
									if ($Debug) echo 'replacing head<br>';
									$PathName = preg_replace('|^head/|', '', $PathName);
								} else {
									if ($Debug) echo 'replacing branches<br>';
									$PathName = preg_replace('|^branches/' . $mycommit->branch . '/|', '', $PathName);
								}
								break;
						}

						if ($PathName != $mycommit->element_pathname) {
							# the replace changes encoded / to plain text / - not sure why may have been present
							$this->HTML .= '<a href="/' . str_replace('%2F', '/', $PathName);
							if (!empty($mycommit->port)) $this->HTML .= '/';
							$this->HTML .= $QueryArgs . '"';
							$this->HTML .= ' title="' . $PathName . ': ' . $mycommit->short_description . '"';
							$this->HTML .= '>' . $PathName. '</a>';
						} else {
							#$this->HTML .= '<a href="' . FRESHPORTS_FREEBSD_CVS_URL . $PathName . '#rev' . $mycommit->revision . '">' . $PathName . '</a>';
							$this->HTML .= $PathName;
						}

						$this->HTML .= "</span>\n"; /* element-details*/

						if (IsSet($mycommit->category) && $mycommit->category != '') {
							// indicate if this port has been removed from cvs
							if ($mycommit->status == "D") {
								$this->HTML .= " " . freshports_Deleted_Icon_Link() . "\n";
							}

							// indicate if this port needs refreshing from CVS
							if ($mycommit->needs_refresh) {
								$this->HTML .= " " . freshports_Refresh_Icon_Link() . "\n";
							}
							if ($mycommit->date_added > Time() - 3600 * 24 * $this->DaysMarkedAsNew) {
								$MarkedAsNew = "Y";
								$this->HTML .= freshports_New_Icon() . "\n";
							}

							if ($mycommit->forbidden) {
								$this->HTML .= ' ' . freshports_Forbidden_Icon_Link() . "\n";
							}

							if ($mycommit->broken) {
								$this->HTML .= ' '. freshports_Broken_Icon_Link() . "\n";
							}

							if ($mycommit->deprecated) {
								$this->HTML .= ' '. freshports_Deprecated_Icon_Link() . "\n";
							}

							if ($mycommit->expiration_date) {
								if (date('Y-m-d') >= $mycommit->expiration_date) {
									$this->HTML .= freshports_Expired_Icon_Link($mycommit->expiration_date) . "\n";
								} else {
									$this->HTML .= freshports_Expiration_Icon_Link($mycommit->expiration_date) . "\n";
								}
							}

							if ($mycommit->ignore) {
								$this->HTML .= ' '. freshports_Ignore_Icon_Link() . "\n";
							}

							if ($mycommit->vulnerable_current) {
								$this->HTML .= '&nbsp;' . freshports_VuXML_Icon() . '&nbsp;';
							}

							if ($mycommit->restricted) {
								$this->HTML .= freshports_Restricted_Icon_Link($mycommit->restricted) . '&nbsp;';
							}

							if ($mycommit->no_cdrom) {
								$this->HTML .= freshports_No_CDROM_Icon_Link($mycommit->no_cdrom) . '&nbsp;';
							}

							if ($mycommit->is_interactive) {
								$this->HTML .= freshports_Is_Interactive_Icon_Link($mycommit->is_interactive) . '&nbsp;';
							}

						}

					if ($DetailsWillBePresented) {
						$this->HTML .= "</li>\n";
					}

					GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
					if ($this->ShowEntireCommit) {
						$Lines = 0;
					} else {
						$Lines = $freshports_CommitMsgMaxNumOfLinesToShow;
					}
				} # !$TooManyPorts

			}

			$PreviousCommit = $mycommit;
		}

		$this->HTML .= _DisplayEndOfCommit($PreviousCommit, $Lines);

		unset($mycommit);
		
		return $this->HTML;
	}

	function SetBranch($BranchName) {
		# usually, this is set during __construct
		$this->BranchName = $BranchName;
	}
	
}
