<?php
	#
	# $Id: port-display.php,v 1.20 2013-03-25 16:09:08 dan Exp $
	#
	# Copyright (c) 2005-2006 DVL Software Limited
	#
	
require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/master_slave.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port_dependencies.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port_configure_plist.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/package_flavors.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/packages.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/htmlify.php');

define('port_display_WATCH_LIST_ADD_REMOVE', '%%%$$$WATCHLIST$$$%%%');
define('port_display_AD',                    '%%%$$$ADGOESHERE$$$%%%');
define('DEPENDS_SUMMARY', 7 );
define('PLIST_SUMMARY',   0 );

class port_display {

	var $db;

	protected $port;
	protected $Branch;

	var $User;		# used for matching against watch lists
	var $DaysMarkedAsNew;

	var $ShowEverything;

	var $LinkToPort;
	var $ShowAd;
	var $ShowBasicInfo;
	var $ShowCategory;
	var $ShowChangesLink;
	var $ShowConfig;
	var $ShowConflicts;
	var $ShowDateAdded;
	var $ShowDepends;
	var $ShowDescriptionLink;
	var $ShowDescriptionLong;
	var $ShowDistInfo;
	var $ShowDownloadPortLink;
	var $ShowHomepageLink;
	var $ShowLastChange;        # who made the last change - useful for knowing last commit
	var $ShowLastCommitDate;    # when was the last change - useful when searching by committer
	var $ShowMaintainedBy;
	var $ShowMasterSites;
	var $ShowMasterSlave;
	var $ShowPackageLink;
	var $ShowPackages;
	var $ShowPKGMessage;
	var $ShowPortCreationDate;
	var $ShowShortDescription;
	var $ShowUses;
	var $ShowWatchListCount;
	var $ShowWatchListStatus;

	function __construct(&$db, $User = 0, $Branch = BRANCH_HEAD) {
		$this->db     = $db;
		$this->User   = $User;
		$this->Branch = NormalizeBranch($Branch);
		$this->DaysMarkedAsNew = 10;

		$this->SetDetailsNil();
	}

	function _isUCL($text) {
		return substr($text, 0, 1) == '[';
	}

	function _pkgmessage_UCL($pkgmessage) {
		$HTML = '<dt><b>pkg-message:</b></dt><dd><dl>';
		# save the pkgmessage to a temp file
		# from https://www.php.net/manual/en/function.tmpfile.php
		$temp = tmpfile();
		fwrite($temp, $pkgmessage);
		$filename = stream_get_meta_data($temp)['uri']; 
#		syslog(LOG_ERR, '_pkgmessage_UCL temp file is : ' . $filename);

		# convert the file to json
		$json = shell_exec('/usr/local/bin/ucl_tool --in ' . $filename . '  --format json');
#		echo '<pre>' . var_dump($json) . '</pre>';
		if (is_null($json)) {
			syslog(LOG_ERR, 'shell_exec returned null');
			$json = '[ { "message": "WARNING: The FreshPorts parser failed.  ucl_tool failed.  Please report this.", "type": "ERROR" } ]';
		} else {
#			syslog(LOG_ERR, 'shell_exec returned ' . $json);
		}

		$things = json_decode($json);
#		var_dump($things);
		foreach ($things as $thing) {
			if (!empty($thing->type)) {
				switch($thing->type) {
					case 'install':
						$HTML .= '<dt>If installing:</dt><dd class="like-pre">' . htmlspecialchars($thing->message) . '</dd>';
						$HTML .= "\n";
						$HTML .= "\n";
						break;

					case 'upgrade':
						if (!empty($thing->minimum_version) && !empty($thing->maximum_version)) {
							$HTML .= '<dt>If upgrading from &gt; ' . htmlspecialchars($thing->minimum_version) . ' and &lt; ' . htmlspecialchars($thing->maximum_version) . ':</dt>';
							$HTML .= '<dd class="like-pre">' . $thing->message . '</dd>';
						} elseif (!empty($thing->minimum_version)) {
							$HTML .= '<dt>If upgrading from &gt; ' . htmlspecialchars($thing->minimum_version) . ':</dt>';
							$HTML .= '<dd class="like-pre">' . htmlspecialchars($thing->message) . '</dd>';
						} elseif (!empty($thing->maximum_version)) {
							$HTML .= '<dt>If upgrading from &lt; ' . htmlspecialchars($thing->maximum_version) . ':</dt>';
							$HTML .= '<dd class="like-pre">' . htmlspecialchars($thing->message) . '</dd>';
						} else {
							$HTML .= '<dt>If upgrading</dt>';
							$HTML .= '<dd class="like-pre">' . htmlspecialchars($thing->message) . '</dd>';
						}
						$HTML .= "\n";
						$HTML .= "\n";
						break;

					case 'remove':
						$HTML .= '<dt>If removing:</dt><dd class="like-pre">' . htmlspecialchars($thing->message) . '</dd>';
						$HTML .= "\n";
						$HTML .= "\n";
						break;

					default:
						syslog(LOG_ERR, '_pkgmessage_UCL found a type is it not prepared for : ' . $thing->type);
						$HTML .= '<dt>' . htmlspecialchars($thing->type) . '</dt><dd class="like-pre">' . htmlspecialchars($thing->message) . '</dd>';
						$HTML .= "\n";
						$HTML .= "\n";
						break;

				}

			}
		}

		# remove the temp file
		fclose($temp);

		$HTML .= '</dl></dd>';

		return $HTML;
	}

	function _pkgmessage($port) {
		# construct the HTML to display pkg-message (stored as pkgmessage)
		$HTML = '';

		#
		# pkg-message can be plain text or UCL
		# see https://www.freebsd.org/doc/en_US.ISO8859-1/books/porters-handbook/pkg-files.html#porting-message-ucl-short-ex
		#
		if (defined('PKG_MESSAGE_UCL') && PKG_MESSAGE_UCL && $this->_isUCL($port->pkgmessage)) {
			$HTML .= $this->_pkgmessage_UCL($port->pkgmessage);
		} else {
			$HTML .= '<dt><b>pkg-message:</b></dt>' . "\n" . '<dd class="like-pre">';
			$HTML .= htmlspecialchars($port->pkgmessage);
			$HTML .= '</dd>' . "\n" . '</dl>' . "\n" . '<hr>' . "\n" . '<dl>';
		}

		return $HTML;
	}

	function htmlConflicts($conflicts) {
	  $HTML = '';

	  $HTML .= "<ul>\n";
	  $data = preg_split('/\s+/', $conflicts);
	  foreach($data as $item) {
	    $HTML .= '<li>' . $item . "</li>\n";
	  }
	  $HTML .= "</ul>\n";

	  return $HTML;	
	}

	function SetPort($port) {
	  //
	  // We could derived branch from element_pathname(port->element_id) but let's try passing in branch explicity.
	  //
	  $this->port = $port;
	}

	function link_to_repo() {
          # we want something like
          # http://svn.freebsd.org/ports/head/x11-wm/awesome/
          $link_title = 'SVNWeb';
          $link = 'https://';
          if (!empty($this->port->svn_hostname)) {
            $link .= $this->port->svn_hostname;
          } else {
            $link .= DEFAULT_SVN_REPO;
          }

          $link .= $this->port->element_pathname . '/';
          if ($this->port->IsDeleted()) {
            #
	    # If the port has been deleted, let's link to the last commit.
	    # Deleted ports don't change much.  It's easier to do this here
	    # than to do it for ALL ports.
	    #
            require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');

            $commit = new Commit($this->db);
            $commit->FetchById($this->port->last_commit_id);

            if (!empty($commit->svn_revision)) {
              $link .= '?pathrev=' . ($commit->svn_revision - 1);
            } else {
              # if there is no last revision, we can't link to it.
	      $link = null;
            }
          }

          if (!empty($link)) {
            $link = '<a href="' . $link . '">' . $link_title . '</a>';
          } else {
            $link = '<strike>SVNWeb</strike>';
          }

          return $link;
	}

	function SetDetailsNil() {
		$this->ShowEverything          = false;

		$this->LinkToPort              = false;
		$this->ShowAd                  = false;
		$this->ShowBasicInfo           = false;
		$this->ShowCategory            = false;
		$this->ShowChangesLink         = false;
		$this->ShowConfig              = false;
		$this->ShowConfigurePlist      = false;
		$this->ShowConflicts           = false;
		$this->ShowDateAdded           = false;
		$this->ShowDepends             = false;
		$this->ShowDescriptionLink     = false;
		$this->ShowDescriptionLong     = false;
		$this->ShowDistInfo            = false;
		$this->ShowDownloadPortLink    = false;
		$this->ShowHomepageLink        = false;
		$this->ShowLastChange          = false;
		$this->ShowLastCommitDate      = false;
		$this->ShowMaintainedBy        = false;
		$this->ShowMasterSites         = false;
		$this->ShowMasterSlave         = false;
		$this->ShowPackageLink         = false;
		$this->ShowPackages            = false;
		$this->ShowPKGMessage          = false;
		$this->ShowPortCreationDate    = false;
		$this->ShowShortDescription    = false;
		$this->ShowUses                = false;
		$this->ShowWatchListCount      = false;
		$this->ShowWatchListStatus     = false;
	}

	function SetDetailsFull() {
		$this->SetDetailsNil();
		$this->ShowEverything = true;
	}

	function SetDetailsPackages() {
		$this->SetDetailsNil();
		$this->ShowEverything          = false;
		$this->ShowPackages            = true;
	}		

	function SetDetailsBeforePackages() {
		$this->ShowEverything          = false;

		$this->LinkToPort              = false;
		$this->ShowAd                  = false;
		$this->ShowBasicInfo           = true;
		$this->ShowCategory            = true;
		$this->ShowChangesLink         = true;
		$this->ShowConfig              = false;
		$this->ShowConfigurePlist      = false;
		$this->ShowConflicts           = true;
		$this->ShowDateAdded           = true;
		$this->ShowDepends             = false;
		$this->ShowDescriptionLink     = false;
		$this->ShowDescriptionLong     = true;
		$this->ShowDistInfo            = true;
		$this->ShowDownloadPortLink    = false;
		$this->ShowHomepageLink        = true;
		$this->ShowLastChange          = true;
		$this->ShowLastCommitDate      = true;
		$this->ShowMaintainedBy        = true;
		$this->ShowMasterSites         = false;
		$this->ShowMasterSlave         = false;
		$this->ShowPackageLink         = true;
		$this->ShowPackages            = false;
		$this->ShowPKGMessage          = false;
		$this->ShowPortCreationDate    = false;
		$this->ShowShortDescription    = true;
		$this->ShowUses                = false;
		$this->ShowWatchListCount      = true;
		$this->ShowWatchListStatus     = false;
	}

	function SetDetailsAfterPackages() {
		$this->ShowEverything          = false;

		$this->LinkToPort              = true;
		$this->ShowAd                  = true;
		$this->ShowBasicInfo           = false;
		$this->ShowCategory            = false;
		$this->ShowChangesLink         = false;
		$this->ShowConflicts           = false;
		$this->ShowConfig              = true;
		$this->ShowConfigurePlist      = false;
		$this->ShowDateAdded           = false;
		$this->ShowDepends             = true;
		$this->ShowDescriptionLink     = false;
		$this->ShowDescriptionLong     = false;
		$this->ShowDistInfo            = false;
		$this->ShowDownloadPortLink    = true;
		$this->ShowHomepageLink        = false;
		$this->ShowLastChange          = false;
		$this->ShowLastCommitDate      = false;
		$this->ShowMaintainedBy        = false;
		$this->ShowMasterSites         = true;
		$this->ShowMasterSlave         = true;
		$this->ShowPackageLink         = false;
		$this->ShowPackages            = false;
		$this->ShowPKGMessage          = true;
		$this->ShowPortCreationDate    = true;
		$this->ShowShortDescription    = false;
		$this->ShowUses                = true;
		$this->ShowWatchListCount      = false;
		$this->ShowWatchListStatus     = true;
	}

	function SetDetailsSearch() {
		$this->SetDetailsNil();
		$this->LinkToPort              = true;
		$this->ShowBasicInfo           = true;
		$this->ShowCategory            = true;
		$this->ShowChangesLink         = true;
		$this->ShowDescriptionLink     = true;
		$this->ShowDownloadPortLink    = true;
		$this->ShowHomepageLink        = true;
		$this->ShowLastCommitDate      = true;
		$this->ShowMaintainedBy        = true;
		$this->ShowPackageLink         = true;
		$this->ShowPortCreationDate    = true;
		$this->ShowShortDescription    = true;
		$this->ShowWatchListStatus     = true;
	}

	function SetDetailsReports() {
		$this->SetDetailsNil();
		$this->SetDetailsSearch();

		$this->ShowDateAdded = true;
	}

	function SetDetailsWatchList() {
		$this->SetDetailsNil();
		$this->SetDetailsSearch();

		$this->ShowDateAdded  = true;
		$this->ShowLastChange = true;
	}

	function SetDetailsCategory() {
		$this->SetDetailsNil();

		$this->LinkToPort           = true;
		$this->ShowBasicInfo        = true;
		$this->ShowDateAdded        = true;
		$this->ShowDescriptionLink  = true;
		$this->ShowMaintainedBy     = true;
		$this->ShowPortCreationDate = true;
		$this->ShowShortDescription = true;
		$this->ShowWatchListStatus  = true;
	}

	function SetDetailsIndex() {
		$this->SetDetailsNil();

		$this->LinkToPort           = true;
		$this->ShowBasicInfo        = true;
		$this->ShowDateAdded        = true;
		$this->ShowDescriptionLink  = true;
		$this->ShowMaintainedBy     = true;
		$this->ShowPortCreationDate = true;
		$this->ShowShortDescription = true;
		$this->ShowWatchListStatus  = true;
	}
	
	function JavascriptInclude()
	{
	  return '
	  <script type="text/javascript" src="/javascript/jquery-1.5.min.js"></script>
	  <script type="text/javascript" src="/javascript/freshports.js"></script>
';	  
	}

	function DisplayPlainText() {
		$result = $this->port->category . '/' . $this->port->port;

		return $result;
	}

	function packageToolTipText($last_checked, $repo_date, $import_date) {
		# last_checked - when we last checked for an update
		# repo_date    - date on packagesite.txz (e.g. http://pkg.freebsd.org/FreeBSD:11:amd64/latest/
		# import_date  - when the above mentioned data was last parsed into FreshPorts

		$title = "Repo dates\n";
		if (empty($last_checked)) {
			$title .= "never checked\n";
		} else {
			$title .= date('Y-m-d H:i', strtotime($last_checked)) . " - last checked\n";
		}

		if (empty($repo_date)) {
			$title .= "repo not found\n";
		} else {
			$title .= date('Y-m-d H:i', strtotime($repo_date)) . " - repo build date\n";
		}

		if (empty($import_date)) {
			$title .= "never imported\n";
		} else {
			$title .= date('Y-m-d H:i', strtotime($import_date)) . " - import date\n";
		}

		$title .= "All times are UTC";

		return $title;
	}

	function Display() {

		$port = $this->port;

		$HTML = '';
#		$HTML = $this->JavascriptInclude();

		$MarkedAsNew = "N";

		###################################################
		### END of items for SetDetailsBeforePackages() ###
		###################################################

		if ($this->ShowEverything || $this->ShowShortDescription || $this->ShowCategory) {
			# start the description list for this port
	 		$HTML .= "<dl>\n";

			# first term/name, is the port itself		
			$HTML .= "<dt>";

			$HTML .= port_display_WATCH_LIST_ADD_REMOVE;

			$HTML .= '<big><b>';

			if ($this->LinkToPort) {
				$HTML .= $this->LinkToPort();
			} else {
				$HTML .= $port->port;
			}

			$HTML .= "</b></big>";

			// description
			if ($port->short_description && ($this->ShowShortDescription || $this->ShowEverything)) {
				$HTML .= ' <span class="fp_description_short">' . htmlify(_forDisplay($port->short_description)) . '</span>';
				$HTML .= "<br>\n";
			}
			
			$HTML .= "</dt>\n";

			# version
			$HTML .= "<dt><b>";
			$PackageVersion = freshports_PackageVersion($port->{'version'}, $port->{'revision'}, $port->{'epoch'});
			if (strlen($PackageVersion) > 0) {
				$HTML .= ' ' . $PackageVersion;
			}

			if (IsSet($port->category_looking_at)) {
				if ($port->category_looking_at != $port->category) {
					$HTML .= '<sup>*</sup>';
				}
			}

			$HTML .= "</b>";
		}

		if ($this->ShowEverything || $this->ShowCategory) {
			$HTML .= ' <A HREF="/' . $port->category . '/';
			if ($this->Branch != BRANCH_HEAD) {
				$HTML .= '?branch=' . htmlspecialchars($this->Branch);
			}
			$HTML .= '" TITLE="The category for this port">' . $port->category . '</A>';
		}

		if ($this->ShowEverything || $this->ShowBasicInfo) {
			// indicate if this port has been removed from cvs
			if ($port->IsDeleted()) {
				$HTML .= " " . freshports_Deleted_Icon_Link() . "\n";
			}

			// indicate if this port needs refreshing from CVS
			if ($port->{'needs_refresh'}) {
				$HTML .= " " . freshports_Refresh_Icon_Link() . "\n";
			}

			if ($port->{'date_added'} > Time() - 3600 * 24 * $this->DaysMarkedAsNew) {
				$MarkedAsNew = "Y";
				$HTML .= freshports_New_Icon() . "\n";
			}
		}

		if ($this->ShowEverything || $this->ShowWatchListCount) {
			$HTML .= ' ' . freshPorts_WatchListCount_Icon_Link() . '=' . $port->WatchListCount();
		}

		if ($this->ShowEverything || $this->ShowBasicInfo) {
			$HTML .= ' ' . freshports_Search_Depends_All($port->category . '/' . $port->port);


			# if this port is, or every has been, vulnerable, display the right skull
			# and a link to the list of all such vulnerabilities
			if ($port->IsVulnerable() || $port->WasVulnerable()) {
				$HTML .= ' ' . freshports_VuXML_Link($port->package_name, $port->IsVulnerable());
			}

			# search for bugs related to this port
			$HTML .= ' ' . freshports_Search_For_Bugs($port->category . '/' . $port->port);

			# report a bug related to this port
			$HTML .= ' ' . freshports_Report_A_Bug($port->category . '/' . $port->port);

			$HTML .=  ' <span class="tooltip">'. $port->quarterly_revision . '<span class="tooltiptext tooltip-top">Version of this port present on the latest quarterly branch.';
			if ($port->IsSlavePort()) $HTML .= ' NOTE: Slave port - quarterly revision is most likely wrong.';
			$HTML .= '</span></span>';
		}

		# if you add content to this IF statement, you may need to addition more conditions to the if
		if ($port->forbidden || $port->broken || $port->deprecated || $port->expiration_date || $port->ignore || $port->restricted || $port->no_cdrom || $port->is_interactive) {

			$HTML .= "<dt>\n";

			# various details about this port		
			$HTML .= "<dd>";

			# show forbidden and broken
			if ($port->forbidden) {
				$HTML .= freshports_Forbidden_Icon_Link($port->forbidden)   . ' FORBIDDEN: '  . htmlify(_forDisplay($port->forbidden))  . "<br>";
			}

			if ($port->broken) {
				$HTML .= freshports_Broken_Icon_Link($port->broken)         . ' BROKEN: '     . htmlify(_forDisplay($port->broken))     . "<br>"; ;
			}

			if ($port->deprecated) {
				$HTML .= freshports_Deprecated_Icon_Link($port->deprecated) . ' DEPRECATED: ' . htmlify(_forDisplay($port->deprecated)) . "<br>"; ;
			}

			if ($port->expiration_date) {
				if (date('Y-m-d') >= $port->expiration_date) {
					$HTML .= freshports_Expired_Icon_Link($port->expiration_date) . ' This port expired on: ' . $port->expiration_date . '<br>';
					} else {
					$HTML .= freshports_Expiration_Icon_Link($port->expiration_date) . ' EXPIRATION DATE: ' . $port->expiration_date . '<br>';
				}
			}

			if ($port->ignore) {
				$HTML .= freshports_Ignore_Icon_Link($port->ignore)         . ' IGNORE: '     . htmlify(_forDisplay($port->ignore))     . "<br>"; ;
			}

			if ($port->restricted) {
				$HTML .= freshports_Restricted_Icon_Link($port->restricted) . ' RESTRICTED: '     . htmlify(_forDisplay($port->restricted)) . '<br>';
			}

			if ($port->no_cdrom) {
				$HTML .= freshports_No_CDROM_Icon_Link($port->no_cdrom)      . ' NO CDROM: '     . htmlify(_forDisplay($port->no_cdrom))   . '<br>';
			}

			if ($port->is_interactive) {
				$HTML .= freshports_Is_Interactive_Icon_Link($port->is_interactive) . ' IS INTERACTIVE: '  . htmlify(_forDisplay($port->is_interactive)) . '<br>';
			}

			$HTML .= "</dd>";
		}

		// maintainer
		if ($port->maintainer && ($this->ShowMaintainedBy || $this->ShowEverything)) {
			if (strtolower($port->maintainer) == UNMAINTAINTED_ADDRESS) {
				$HTML .= '<dt>There is no maintainer for this port.</dt>';
				$HTML .= '<dd>Any concerns regarding this port should be directed to the FreeBSD ' .
				         'Ports mailing list via ';
				$HTML .= '<A HREF="' . MAILTO . ':' . freshportsObscureHTML($port->maintainer);
				$HTML .= '?subject=FreeBSD%20Port:%20' . $port->category . '/' . $port->port . '" TITLE="email the FreeBSD Ports mailing list">';
				$HTML .= freshportsObscureHTML($port->maintainer) . '</A></dd>';
			} else {
				$HTML .= '<dt><b>';

				$HTML .= 'Maintainer:</b> <A HREF="' . MAILTO . ':' . freshportsObscureHTML($port->maintainer);
				$HTML .= '?subject=FreeBSD%20Port:%20' . $port->category . '/' . $port->port . '" TITLE="email the maintainer">';
				$HTML .= freshportsObscureHTML($port->maintainer) . '</A>';
			}

			$HTML .= ' ' . freshports_Search_Maintainer($port->maintainer) . '</dt>';
		}

		// there are only a few places we want to show the last change.
		// therefore, we do not check ShowEverything here
		if ($this->ShowLastChange) {
			$HTML .= "<dt>\n";
			if ($port->updated != 0) {
				$HTML .= 'last change committed by ' . freshports_CommitterEmailLink($port->committer);  // separate lines in case committer is null

				$HTML .= ' ' . freshports_Search_Committer($port->committer);
 
				$HTML .= ' on <font size="-1">' . $port->updated . '</font>' . "\n";

				$HTML .= freshports_Email_Link($port->message_id);

				if ($port->EncodingLosses()) {
					$HTML .= ' ' . freshports_Encoding_Errors_Link();
				}

				$HTML .= ' ' . freshports_Commit_Link($port->message_id);
				$HTML .= ' ' . freshports_Commit_Link_Port($port->message_id, $port->category, $port->port);
				
				GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;

				$HTML .= freshports_PortDescriptionPrint($port->update_description, $port->encoding_losses, 
			 				$freshports_CommitMsgMaxNumOfLinesToShow, 
			 				freshports_MoreCommitMsgToShow($port->message_id,
	 				       $freshports_CommitMsgMaxNumOfLinesToShow));
			} else {
				$HTML .= "no changes recorded in FreshPorts<br>\n";
			}
			$HTML .= "</dt>\n";
		}

		# show the date added, if asked

		if ($this->ShowDateAdded || $this->ShowEverything) {
			$HTML .= '<dt><b>Port Added:</b> <font size="-1">';
			if ($port->date_added) {
				$HTML .= FormatTime($port->date_added, 0, "Y-m-d H:i:s");
			} else {
				$HTML .= "unknown";
			}
			$HTML .= '</font></dt>' . "\n";
		}

		# show the date modified, if asked

		if ($this->ShowLastCommitDate || $this->ShowEverything) {
			$HTML .= '<dt><b>Last Update:</b> <font size="-1">';
			if ($port->last_commit_date) {
				$HTML .= FormatTime($port->last_commit_date, 0, "Y-m-d H:i:s");
			} else {
				$HTML .= "unknown";
			}
			$HTML .= '</font></dt>' . "\n";
			$HTML .= '<dt><b>SVN Revision:</b> <font size="-1">';
			if ($port->svn_revision) {
				$HTML .= freshports_svnweb_ChangeSet_Link_Text($port->svn_revision, $port->svn_hostname, $port->path_to_repo);
			} else {
				$HTML .= "unknown";
			}
			$HTML .= '</font></dt>' . "\n";
		}

		if ($this->ShowEverything || $this->ShowBasicInfo) {
			# this is interesting... I do not recall writing this.
			# I wonder if it works.
			$HTML .= PeopleWatchingThisPortAlsoWatch($this->db, $port->element_id);

			if ($port->categories) {
				// remove the primary category and remove any double spaces or trailing/leading spaces
				// this ensures that explode gives us the right stuff
				if (IsSet($port->category_looking_at)) {
					$CategoryToRemove = $port->category_looking_at;
				} else {
					$CategoryToRemove = $port->category;
				}
				$Categories = str_replace($CategoryToRemove, '', $port->categories);
				$Categories = str_replace('  ', ' ', $Categories);
				$Categories = trim($Categories);
				if ($Categories) {
					$HTML .= "<dt><b>Also Listed In:</b> ";
					$CategoriesArray = explode(" ", $Categories);
					$Count = count($CategoriesArray);
					for ($i = 0; $i < $Count; $i++) {
						$Category = $CategoriesArray[$i];
						$HTML .= '<a href="/' . $Category . '/';
						if ($this->Branch != BRANCH_HEAD) {
							$HTML .= '?branch=' . htmlspecialchars($this->Branch);
						}
						$HTML .= '">' . $Category . '</a>';
						if ($i < $Count - 1) {
							$HTML .= " ";
						}
					}
					$HTML .= "</dt>\n";
				}
			}

			$HTML .= '<dt><b>License:</b> ';
			if ($port->license) {
			        $HTML .= htmlentities($port->license);
			} else {
			        $HTML .= 'not specified in port';
			}

			$HTML .= "</dt>\n";
		}


		# The ad goes here, but we haven't used ads in a very long time.
		if ($this->ShowAd || $this->ShowEverything) {
			$HTML .= port_display_AD;
		}

		# sometimes the description can get very wide. This causes problems on mobile.
		if ($this->ShowDescriptionLong || $this->ShowEverything) {
			echo 'SHOWING THE DESCRIPTION!';
			$HTML .= '<dt class="description">Description:</dt><dd class="like-pre">' . htmlify(_forDisplay($port->long_description)) . '</dd>';
		}

		# this if covers several items, and wraps them in dt tags
		# this if could be consolidated, but I've just copied the conditions from the if statements contained herein
		if (($this->ShowChangesLink || $this->ShowEverything) || ($port->PackageExists() && ($this->ShowPackageLink || $this->ShowEverything)) || ($port->homepage && ($this->ShowHomepageLink || $this->ShowEverything))) {
			$HTML .= '<dt>';

			if ($this->ShowChangesLink || $this->ShowEverything) {
				$HTML .= $this->link_to_repo();
			}

			if ($port->PackageExists() && ($this->ShowPackageLink || $this->ShowEverything)) {
				// package
				$HTML .= ' <b>:</b> ';
				$HTML .= '<A HREF="' . FRESHPORTS_FREEBSD_FTP_URL . '/' . freshports_PackageVersion($port->version, $port->revision, $port->epoch);
				$HTML .= '.tgz">Package</A>';
			}

			if ($port->homepage && ($this->ShowHomepageLink || $this->ShowEverything)) {
				$HTML .= ' <b>:</b> ';
				$HTML .= '<a HREF="' . _forDisplay($port->homepage) . '" TITLE="Homepage for this port">Homepage</a>';
			}

			$HTML .= '</dt>';
		}

		if (defined('CONFIGUREPLISTSHOW')  && ($this->ShowConfigurePlist || $this->ShowEverything)) {
			$HTML .= $this->ShowConfigurePlist();
		}

		if ($this->ShowEverything || $this->ShowBasicInfo) {

			$HTML .= '<dt class="pkg-plist"><b>Dependency lines</b>:</dt>';
			$HTML .= '<dd class="pkg-plist">' . "\n" . '<ul class="pkg-plist"><li class="file">' . $port->package_name . '>0:' . $this->DisplayPlainText() . '</li>';

			// pkg_plist_library_matches is a JSON array
			$lib_depends = json_decode($port->pkg_plist_library_matches, true);
			if (is_array($lib_depends) && count($lib_depends) > 0) {
				foreach($lib_depends as $library) {
					# XXX this span should be replaced with some CSS
					$HTML .= '<li>' . preg_replace('/^lib\//', '', $library) . ':' . $this->DisplayPlainText() . '</li>';
				}
			}
			$HTML .= '</ul></dd>';
		}

		# if there are conflicts
		if (($this->ShowEverything || $this->ShowConflicts) && ($port->conflicts || $port->conflicts_build || $port->conflicts_install)) {
			$HTML .= "<dt><b>Conflicts:</b></dt>";

			if ($port->conflicts) {
				$HTML .= "<dd>CONFLICTS:";
				$HTML .= $this->htmlConflicts($port->conflicts);
				$HTML .= "\n</dd>\n";
			}

			if ($port->conflicts_build) {
				$HTML .= "<dd>CONFLICTS_BUILD:";
				$HTML .= $this->htmlConflicts($port->conflicts_build);
				$HTML .= "\n</dd>\n";
			}

			if ($port->conflicts_install) {
				$HTML .= "<dd>CONFLICTS_INSTALL:";
				$HTML .= $this->htmlConflicts($port->conflicts_install);
				$HTML .= "\n</dd>\n";
			}

			$HTML .= "\n";

			$HTML .= "<dt><b>Conflicts Matches:</b>\n</dt>";
			$HTML .= "<dd>\n";
			if (!empty($port->conflicts_matches)) {
				$HTML .= "<ul>\n";
				foreach($port->conflicts_matches as $match) {
					$HTML .= "<li>conflicts with " . freshports_link_to_port($match['category'], $match['port']) . '</li>';
				}
				$HTML .= "</ul>\n";
			} else {
				$HTML .= 'There are no Conflicts Matches for this port.  This is usually an error.';
				syslog(LOG_ERR, 'There are no Conflicts Matches for this port: ' . $port->element_pathname);
			}
			$HTML .= '</dd>';
		}




		# only show if we're meant to show, and if the port has not been deleted.
		if ($this->ShowPackageLink || $this->ShowEverything) {
			$HTML .= "\n</dl><dl>\n";
			if ($port->IsDeleted()) {
				$HTML .= '<dt>No installation instructions: this port has been deleted.</dt>';
				$HTML .= '<dt>The package name of this deleted port was: <code class="code">' . $port->latest_link . '</code></dt>';
			} else {
				$HTML .= '<dt><b>To install <a href="/faq.php#port" TITLE="what is a port?">the port</a>:</b> <code class="code">cd /usr/ports/'  . $port->category . '/' . $port->port . '/ && make install clean</code></dt>';
				if (IsSet($port->no_package) && $port->no_package != '') {
					$HTML .= '<dt><b>No <a href="/faq.php#package" TITLE="what is a package?">package</a> is available:</b> ' . $port->no_package . '</dt>';
					} else {
					if ($port->forbidden || $port->broken || $port->ignore || $port->restricted || !$port->PackageIsAvailable()) {
						$HTML .= '<dt><b>A <a href="/faq.php#package" TITLE="what is a package?">package</a> is not available for ports marked as: Forbidden / Broken / Ignore / Restricted</b></dt>';
					} else {
						$HTML .= '<dt><b>To add the <a href="/faq.php#package" TITLE="what is a package?">package</a>:</b> <code class="code">pkg install ' . $port->package_name . '</code></dt>';
					}
				}
			}

			$HTML .= '<dt class="pkgname"><b>PKGNAME:</b> ';
			if ($port->PackageIsAvailable()) {
			  $HTML .= $port->package_name;
			} else {
			  $HTML .= 'there is no package for this port: <span class="file">' . $port->PackageNotAvailableReason() . '</span>';
			}
			$HTML .= '</dt>';

			$HTML .= $this->ShowPackageFlavors();

			if ($port->only_for_archs) {
			  $HTML .= '<dt><b>ONLY_FOR_ARCHS:</b> ';
			  $HTML .= htmlify($port->only_for_archs);
			  $HTML .= '</dt>';
			}

			if ($port->not_for_archs) {
			  $HTML .= '<dt><b>NOT_FOR_ARCHS:</b> ';
			  $HTML .= htmlify($port->not_for_archs);
			  $HTML .= '</dt>';
			}

			if ($this->ShowEverything || $this->ShowDistInfo) {
				$HTML .= '<dt><b>distinfo:</b></dt>';

				if ($port->distinfo) {
					$HTML .= '<dd class="like-pre">' . $port->distinfo . '</dd>';
				} else {
					$HTML .= '<dd>There is no distinfo for this port.</dd>' . "\n";
				}
			}




		}
        ###################################################
		### END of items for SetDetailsBeforePackages() ###
		###################################################


		###############################################
		### START of items for SetDetailsPackages() ###
		###############################################

		if ($this->ShowEverything || $this->ShowPackages) {
			$HTML .= '<dt><b>Packages:</b></dt>';
			
#				require_once('/var/db/freshports/cache/packages/' . $port->category . '/' . $port->port . '.html');

			$packages = new Packages($this->db);
			$numrows = $packages->Fetch($this->port->id);

			if ($numrows > 0) {
				$HTML .= '<dd>';
				$HTML .= '<div class="scrollmenu">';

				# if we have multiple packages, we create an enclosing table
				$MultiplePackageNames = count($packages->packages) > 1;

				if ($MultiplePackageNames) {
#						$HTML .= '<div style="overflow: scroll;"';
#						$HTML .= '<table class="packages"><tr>';
				}

				foreach($packages->packages as $package_name => $package) {

					#echo '<pre>This is the package information for ' . $package_name . ' ***<br>'; var_export($package); echo '</pre>';

					if ($MultiplePackageNames) {
#							$HTML .= '<td valign="top">';
					}

					$HTML .= '<table class="packages"><caption>' . $package_name . '</caption><tr><th>ABI</th><th>latest</th><th>quarterly</th></tr>';
					foreach($package as $package_line) {

						if ($Debug) {
							echo '<pre>'; var_export($package_line); echo '</pre>';
						}

						# All values of active ABI are returned (e.g. FreeBSD:12:amd64
						# package_version will be empty if the port is not build for that ABI

						$package_version_latest    = empty($package_line['package_version_latest'])    ? '-' : $package_line['package_version_latest'];
						$package_version_quarterly = empty($package_line['package_version_quarterly']) ? '-' : $package_line['package_version_quarterly'];

						$HTML .= '<tr><td>' . $package_line['abi'] . '</td>';

						# If showing a - for the version, center align it
						$title = $this->packageToolTipText($package_line['last_checked_latest'], $package_line['repo_date_latest'], $package_line['import_date_latest']);
						$HTML .= '<td class="version" ' . ($package_version_latest == '-' ? ' align ="center"' : '') . '><span title="' . $title . '">' . $package_version_latest . '</span></td>';

						$title = $this->packageToolTipText($package_line['last_checked_quarterly'], $package_line['repo_date_quarterly'], $package_line['import_date_quarterly']);
						$HTML .= '<td class="version" ' . ($package_version_quarterly == '-' ? ' align ="center"' : '') . '><span title="' . $title . '">' . $package_version_quarterly . '</span></td></tr>';
					}
					$HTML .= '</table>';
					if ($MultiplePackageNames) {
#							$HTML .= '</td>';
#							$HTML .= '</div>';
					}

				}	

				if (count($packages->packages) > 1) {
#						$HTML .= '</tr></table>';
				}

				$HTML .= '</div>';
				$HTML .= '</dd>';

			} else {
				$HTML .= '<dd>No package information in database for this port.</dd>' . "\n";
			}
		}

		if ($this->ShowEverything || $this->ShowMasterSlave) {
			#
			# Display our master port
			#

			$MasterSlave = new MasterSlave($port->dbh);
			$NumRows = $MasterSlave->FetchByMaster($port->category . '/' . $port->port);

			if ($port->IsSlavePort() || $NumRows > 0) {
				$HTML .= "\n</dl><hr>\n<dl>";
			}

			if ($port->IsSlavePort()) {
				$HTML .= '<dt><b>Master port:</b> ';
				list($MyCategory, $MyPort) = explode('/', $port->master_port);
				$HTML .= freshports_link_to_port($MyCategory, $MyPort, $this->Branch);
				$HTML .= "</dt>\n";
			}
	
			#
			# Display our slave ports
			#

			if ($NumRows > 0) {
				$HTML .= '<dt><span class="slaveports">Slave ports</span></dt><dd>' . "\n" . '<ol class="slaveports" id="slaveports">';
				for ($i = 0; $i < $NumRows; $i++) {
					$MasterSlave->FetchNth($i);
					$HTML .= '<li>' . freshports_link_to_port($MasterSlave->slave_category_name, $MasterSlave->slave_port_name, $this->Branch) . '</li>';
				}
				$HTML .= "</ol></dd>\n";
			}
		}
	
		if ($this->ShowDepends || $this->ShowEverything) {
			$HTML .= "</dl>\n<hr><dl>\n";
			if ($port->depends_build || $port->depends_run || $port->depends_lib) {
				$HTML .= '<dt class="h2">Dependencies</dt>';
				$HTML .= '<dt class="notice">NOTE: FreshPorts displays only information on required and default dependencies.  Optional dependencies are not covered.</dt>';
			}

			if ($port->depends_build) {
				$HTML .= '<dt class="required">Build dependencies:</dt><dd>' . "\n" . '<ol class="required" id="requiredtobuild">';
				$HTML .= freshports_depends_links($this->db, $port->depends_build, $this->Branch);
				$HTML .= "\n</ol></dd>\n";
			}

			if ($port->depends_run) {
				$HTML .= '<dt class="required">Runtime dependencies:</dt><dd>' . "\n" . '<ol class="required" id="requiredtorun">';
				$HTML .= freshports_depends_links($this->db, $port->depends_run, $this->Branch);
				$HTML .= "\n</ol></dd>\n";
			}

			if ($port->depends_lib) {
				$HTML .= '<dt class="required">Library dependencies:</dt><dd>' . "\n" . '<ol class="required" id="requiredlibraries">';
				$HTML .= freshports_depends_links($this->db, $port->depends_lib, $this->Branch);
				$HTML .= "\n</ol></dd>\n";
			}

			if ($port->fetch_depends) {
				$HTML .= '<dt class="required">Fetch dependencies:</dt><dd>' . "\n" . '<ol class="required" id="requiredfetches">';
				$HTML .= freshports_depends_links($this->db, $port->fetch_depends, $this->Branch);
				$HTML .= "\n</ol></dd>\n";
			}

			if ($port->patch_depends) {
				$HTML .= '<dt class="required">Patch dependencies:</dt><dd>' . "\n" . '<ol class="required" id="requiredpatches">';
				$HTML .= freshports_depends_links($this->db, $port->patch_depends, $this->Branch);
				$HTML .= "\n</ol></dd>\n";
			}

			if ($port->extract_depends) {
				$HTML .= '<dt class="required">Extract dependencies:</dt><dd>' . "\n" . '<ol class="required" id="requiredextracts">';
				$HTML .= freshports_depends_links($this->db, $port->extract_depends, $this->Branch);
				$HTML .= "\n</ol></dd>\n";
			}
			
			# XXX when adding new depends above, be sure to update the array in ShowDependencies()

			$HTML .= $this->ShowDependencies( $port );
		}

		if ($this->ShowEverything || $this->ShowConfig) {
			$HTML .= "</dl>\n<hr>\n<dl>";
			$HTML .= "<dt><b>Configuration Options</b></dt>\n" . '<dd class="like-pre">';
			if ($port->showconfig) {
				$HTML .= $port->showconfig;
			} else {
				$HTML .= '     No options to configure';
			}
			$HTML .= "</dd>";
		}
		
		if (($this->ShowEverything || $this->ShowUses) && $port->uses) {
			$HTML .= "</dl><hr><dl><dt><b>USES:</b></dt>\n" . '<dd class="like-pre">';
			$HTML .= $port->uses;
			$HTML .= "</dd>\n</dl>\n<hr>\n<dl>";
		}

		if (($this->ShowEverything || $this->ShowPKGMessage) && $port->pkgmessage) {
			$HTML .= $this->_pkgmessage($port);
		}

		if ($this->ShowEverything || $this->ShowMasterSites) {
			$HTML .= '<dt><b>Master Sites:</b></dt>' . "\n" . '<dd><ol class="mastersites" id="mastersites">' . "\n";
			if (!empty($port->master_sites)) {

			  $MasterSites = explode(' ', $port->master_sites);
			  asort($MasterSites);
			  foreach ($MasterSites as $Site) {
				$HTML .= '<li>' . htmlify(_forDisplay($Site)) . "</li>\n";
			  }

			} else {
			  $HTML .= '<li>There is no master site for this port.</li>';
			}
			$HTML .= "</ol>\n</dd>\n";

		}

		$HTML .= "</dl>\n";

		return $HTML;
	}

	function LinkToPort() {
		$HTML = '<a href="/' . $this->port->category . '/' . $this->port->port . '/';
		if ($this->Branch != BRANCH_HEAD) {
			$HTML .= '?branch=' . htmlspecialchars($this->Branch);
		}
		$HTML .= '">' . $this->port->port . '</a>';

		return $HTML;
	}
	
	function ReplaceWatchListToken($OnWatchList, $HTML, $ElementID) {
		$Watch_HTML = '';
		
		if ($this->User && $this->User->id && ($this->ShowEverything || $this->ShowWatchListStatus)) {
			if ($OnWatchList) {
				$Watch_HTML .= freshports_Watch_Link_Remove($this->User->watch_list_add_remove, $OnWatchList, $ElementID);
			} else {
				$Watch_HTML .= freshports_Watch_Link_Add   ($this->User->watch_list_add_remove, $OnWatchList, $ElementID);
			}
		}
		
		$Watch_HTML .= ' ';
		
		$HTML = str_replace(port_display_WATCH_LIST_ADD_REMOVE, $Watch_HTML, $HTML);

		return $HTML;
	}

	function ReplaceAdvertismentToken($HTML, $Ad) {
		$HTML = str_replace(port_display_AD, $Ad, $HTML);

		return $HTML;
	}

	function ShowDependencies( $port ) {
		$HTML = '';
		$div  = ''; # we us this empty bit when the first port in the list is a delete port.  It tells us to open the list

		$PortDependencies = new PortDependencies( $this->db );
		$Types = array( 'B' => 'Build', 'E' => 'Extract', 'F' => 'Fetch', 'L' => 'Libraries', 'P' => 'Patch', 'R' => 'Run' );
		foreach ( $Types as $type => $title ) {
			$NumRows = $PortDependencies->FetchInitialise( $port->id, $type );
			if ( $NumRows > 0 ) {
				// if this is our first output, put up our standard header

				# the start of the DT This port is required by: section
				if ( $HTML === '' ) {
					if ( $port->IsDeleted() ) {
						$HTML .= '<dt>NOTE: dependencies for deleted ports are notoriously suspect</dt>';
					}
					$HTML .= '<dt class="required">This port is required by:</dt>';
				}

				# everything "required for" XXX goes under this section.
				$HTML .= '<dd class="required">for ' . $title . "\n";
				
				# Let's fetch the first port, and see if it's deleted.  If it is, we don't need this first loop
				$PortDependencies->FetchNth(0);
				if ($PortDependencies->status != 'D' ) {
					#
					# START OF LIST
					#
					$div = '<dl><dd id="RequiredBy' . $title . '"><ol class="depends" id="requiredfor' . $title . '" style="margin-bottom: 0px">' . "\n";

					$firstDeletedPort = -1;     # we might be able to combine this with deletedPortFound
					$deletedPortFound = false;  # we found a deleted port
					$hidingStarted    = false;  # we can do this only once.
					for ( $i = 0; $i < $NumRows; $i++ ) {
						$PortDependencies->FetchNth($i);

						# if this is a deleted port
						if ($PortDependencies->status == 'D' ) {
							$firstDeletedPort = $i; # we set this so the next loop knows where to start 
							# we found a deleted port
							$deletedPortFound = true;

							# we are done in this loop
							break;
						}

						# if we haven't already starting hiding things and we found a deleted port or we have too many thing to show and we're at the max items to show
						if ( !$hidingStarted && ( ( ( $NumRows > DEPENDS_SUMMARY )  && ( $i == DEPENDS_SUMMARY ) ) ) ) {
							$div .= '</ol>';
							$div .= '<a href="#" id="RequiredBy' . $title . 'Extra-show" class="showLink" onclick="showHide(\'RequiredBy' . 
							        $title . 'Extra\');return false;">Expand this list (' . $NumRows . ' items / ' . ($NumRows - DEPENDS_SUMMARY) . ' hidden)</a>';
							$div .= '<ol id="RequiredBy' . $title . 'Extra" class="depends more" start="' . ($i + 1) . '" style="margin-top: 0px">';
							# yes, we have started hiding things.
							$hidingStarted = true;
						}

						$div .= '<li>' . freshports_link_to_port_single( $PortDependencies->category, $PortDependencies->port, $this->Branch);
						$div .= "</li>\n";

					}

					if ( $hidingStarted ) {
						$div .= '<li class="nostyle"><a href="#" id="RequiredBy' . $title . 'Extra-hide" class="hideLink" onclick="showHide(\'RequiredBy' . $title . 'Extra\');return false;" >Collapse this list.</a></li>';
					}

					$div .= '</ol>';

				} else {
					$deletedPortFound = true;
					$div = '';
				}

				# now deal with deleted ports, perhaps this loop and the one above can be conbined, after the two loops are reduced to 1 - active ports 2 - deleted ports

				if ($deletedPortFound) {
					if ($div == '') {
						# if this is empty, WE ARE the first delete port
						$div = '<dl><dd id="RequiredBy' . $title . '"><ol class="depends" id="requiredfor' . $title . '">' . "\n";
					}

					# is it port or ports?
					$PluralSingularSuffix = ($NumRows - $firstDeletedPort) > 1 ? 's' : '';

					$div .= '<p><b>Deleted ports which required this port:</b></p><a href="#" id="RequiredBy' . $title . 'DeletedExtra-show" class="showLink" onclick="showHide(\'RequiredBy' . $title .
				                'DeletedExtra\');return false;">Expand this list of ' . ($NumRows - $firstDeletedPort) . ' deleted port' . $PluralSingularSuffix . '</a>';
					$div .= '<ol class="depends more" id="RequiredBy' . $title . 'DeletedExtra" style="padding-left: 20px;">' . "\n";
 					for ( $i = $firstDeletedPort; $i < $NumRows; $i++ ) {
						$PortDependencies->FetchNth($i);

						$div .= '<li>' . freshports_link_to_port_single( $PortDependencies->category, $PortDependencies->port, $this->Branch, DELETED_PORT_LINK_COLOR);
						$div .= '<sup>*</sup>';
						$div .= "</li>\n";
					}

					$div .= '<li class="nostyle"><a href="#" id="RequiredBy' . $titled . 'DeletedExtra-hide" class="hideLink" onclick="showHide(\'RequiredBy' . $title . 'DeletedExtra\');return false;">Collapse this list of deleted ports.</a></li>';
					$div .= '</ol>';

				} # end of deletedPortFound

				$div .= '</dl>';

				$HTML .= $div;
			}
		}

		if ( $HTML === '' ) {
			$HTML .= '<dt>There are no ports dependent upon this port</dt>';
		} elseif ($deletedPortFound) {
			$HTML .= '<dt>* - deleted ports are only shown under the <em>This port is required by</em> section.  It was harder to do for the <em>Required</em> section.  Perhaps later...</dt>';
		}

		return $HTML;
	}

	function ShowConfigurePlist() {
		$HTML = '';

		$ConfigurePlist = new PortConfigurePlist( $this->db );
		$NumRows = $ConfigurePlist->FetchInitialise( $this->port->id );
		if ( $NumRows > 0 ) {
			// if this is our first output, put up our standard header
			if ( $HTML === '' ) {
				$div = "\n" . '<dt class="pkg-plist"><b>pkg-plist:</b> as obtained via: <code class="code">make generate-plist</code></dt>';
				$div .= '<dd class="pkg-plist">';
				$div .= '<a href="#" id="configureplist-Extra-show" class="showLink" onclick="showHide(\'configureplist-Extra\');return false;">Expand this list (' . $NumRows . ' items)</a>';
				$div .= '</dd>';
				$div .= '<dd id="configureplist-Extra" class="more pkg-plist">';
				$div .= "\n" . '<ol class="configure" id="configureplist">' . "\n";

				for ( $i = 0; $i < $NumRows; $i++ ) {
					$ConfigurePlist->FetchNth($i);

					$div .= '<li>' . $ConfigurePlist->installed_file . "</li>\n";
				}

				$div .= '</ol>';
				$div .= '<a href="#" id="configureplist-Extra-hide" class="hideLink" onclick="showHide(\'configureplist-Extra\');return false;">Collapse this list.</a>';
				$div .= '</dd>';

				$HTML .= $div;
			}
		}

		if ( $HTML === '' ) {
			$HTML .= '<p>There is no configure plist information for this port.</p>';
		}

		return $HTML;
	}

	function ShowPackageFlavors() {

		$HTML = '';
		$PackageFlavors = new PackageFlavors( $this->db );
		$NumRows = $PackageFlavors->FetchInitialise( $this->port->id );
		if ( $NumRows > 0 ) {
			$HTML = '<dt class="flavors"><b>Package flavors</b> (<span class="file">&lt;flavor&gt;: &lt;package&gt;</span>)</dt>';
			// if this is our first output, put up our standard header
			$HTML .= '<dd><ul>';
			for ( $i = 0; $i < $NumRows; $i++ ) {
				$PackageFlavors->FetchNth($i);

				$HTML .= '<li><span class="file">' . $PackageFlavors->flavor_name . ': ' . $PackageFlavors->name . "</span></li>\n";
			}
			$HTML .= '</ul></dd>';
		}

		if ( $NumRows == 0 ) {
			$HTML .= '<dt class="flavors"><b>Flavors:</b> there is no flavor information for this port.</dt>';
		}

		return $HTML;
	}

	function getShortDescription() {
		return $this->port->short_description;
	}
}
