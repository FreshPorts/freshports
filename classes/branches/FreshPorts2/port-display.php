<?php
	#
	# $Id: port-display.php,v 1.1.2.1 2006-02-04 22:06:52 dan Exp $
	#
	# Copyright (c) 2005 DVL Software Limited
	#

class port_display {
	var $db;

	var $port;
	var $LinkToPort = true;
	var $UserID;
	var $IndicateWatchListStatus;
	var $ShowWatchListCount;
	var $ShowShortDescription;
	var $ShowEverything;
	var $ShowMaintainedBy;
	var $ShowDateAdded;
	var $ShowDepends;
	var $ShowMasterSites;

	var $HideDescription;
	var $ShowChangesLink;
	var $ShowHomepageLink;

	function port_display(&$db) {
		$this->db = $db;
	}

	function SetFullDetails() {
		
	}

	function Display() {
		$port = $this->port;
		$HTML = '';

		$MarkedAsNew = "N";
		$HTML  = "<DL>\n";

		$HTML .= "<DT>";

		$HTML .= '<BIG><B>';

		if ($LinkToPort) {
			$HTML .= $this->LinkToPort();
		} else {
			$HTML .= $port->port;
		}

		$PackageVersion = freshports_PackageVersion($port->{'version'},
		                                            $port->{'revision'},
		                                            $port->{'epoch'});
		if (strlen($PackageVersion) > 0) {
			$HTML .= ' ' . $PackageVersion;
		}

		if (IsSet($port->category_looking_at)) {
			if ($port->category_looking_at != $port->category) {
				$HTML .= '<sup>*</sup>';
			}
		}

		$HTML .= "</B></BIG>";

		if ($this->ShowCategory) {
			$HTML .= ' / <A HREF="/' . $port->category . '/" TITLE="The category for this port">' . $port->category . '</A>';
		}

		if ($this->UserID && $this->IndicateWatchListStatus) {
			if ($port->{'onwatchlist'}) {
				$HTML .= ' '. freshports_Watch_Link_Remove($User->watch_list_add_remove, $port->onwatchlist, $port->{'element_id'});
			} else {
				$HTML .= ' '. freshports_Watch_Link_Add   ($User->watch_list_add_remove, $port->onwatchlist, $port->{'element_id'});
			}
		}

		// indicate if this port has been removed from cvs
		if ($port->{'status'} == "D") {
			$HTML .= " " . freshports_Deleted_Icon_Link() . "\n";
		}

		// indicate if this port needs refreshing from CVS
		if ($port->{'needs_refresh'}) {
			$HTML .= " " . freshports_Refresh_Icon_Link() . "\n";
		}

		if ($port->{'date_added'} > Time() - 3600 * 24 * $DaysMarkedAsNew) {
			$MarkedAsNew = "Y";
			$HTML .= freshports_New_Icon() . "\n";
		}

		if ($this->ShowWatchListCount) {
			$HTML .= '&nbsp; ' . freshPorts_WatchListCount_Icon_Link() . '=' . $port->WatchListCount() . '<br>';
		}

		$HTML .= "</DT>\n<DD>";
		# show forbidden and broken
		if ($port->forbidden) {
			$HTML .= freshports_Forbidden_Icon_Link($port->forbidden)   . ' FORBIDDEN: '  . htmlify(htmlspecialchars($port->forbidden))  . "<br>";
		}

		if ($port->broken) {
			$HTML .= freshports_Broken_Icon_Link($port->broken)         . ' BROKEN: '     . htmlify(htmlspecialchars($port->broken))     . "<br>"; ;
		}

		if ($port->deprecated) {
			$HTML .= freshports_Deprecated_Icon_Link($port->deprecated) . ' DEPRECATED: ' . htmlify(htmlspecialchars($port->deprecated)) . "<br>"; ;
		}

		if ($port->ignore) {
			$HTML .= freshports_Ignore_Icon_Link($port->ignore)         . ' IGNORE: '     . htmlify(htmlspecialchars($port->ignore))     . "<br>"; ;
		}

		// description
		if ($port->short_description && ($this->ShowShortDescription == "Y" || $this->ShowEverything)) {
			$HTML .= htmlify(htmlspecialchars($port->short_description));
			$HTML .= "<br>\n";
		}

		// maintainer
		if ($port->maintainer && ($ShowMaintainedBy == "Y" || $ShowEverything)) {
			$HTML .= '<i>';
			if ($port->status == 'A') {
				$HTML .= 'Maintained';
			} else {
				$HTML .= 'was maintained'; 
			}

			$HTML .= ' by:</i> <A HREF="' . MAILTO . ':' . freshportsObscureHTML($port->maintainer);
			$HTML .= freshportsObscureHTML('?cc=ports@FreeBSD.org') . '&amp;subject=FreeBSD%20Port:%20' . 
			                       $port->port . '-' . freshports_PackageVersion($port->version, $port->revision, $port->epoch) . 
			                       '" TITLE="email the maintainer">';
			$HTML .= freshportsObscureHTML($port->maintainer) . "</A><BR>";
		}

		// there are only a few places we want to show the last change.
		// such places set $GlobalHideLastChange == "Y"
		if ($GlobalHideLastChange != "Y") {
			if ($ShowLastChange == "Y" || $ShowEverything) {
				if ($port->updated != 0) {
					$HTML .= 'last change committed by ' . freshports_CommitterEmailLink($port->committer);  // separate lines in case committer is null
 
					$HTML .= ' on <font size="-1">' . $port->updated . '</font>' . "\n";

					$HTML .= freshports_Email_Link($port->message_id);

					if ($port->EncodingLosses()) {
						$HTML .= '&nbsp;' . freshports_Encoding_Errors_Link();
					}

					$HTML .= ' ' . freshports_Commit_Link($port->message_id);
					$HTML .= ' ' . freshports_CommitFilesLink($port->message_id, $port->category, $port->port);

					$HTML .= freshports_PortDescriptionPrint($port->update_description, $port->encoding_losses, 
 				 				$freshports_CommitMsgMaxNumOfLinesToShow, 
 				 				freshports_MoreCommitMsgToShow($port->message_id,
 				 				       $freshports_CommitMsgMaxNumOfLinesToShow));
				} else {
					$HTML .= "no changes recorded in FreshPorts<br>\n";
				}
			}
		}

		# show the date added, if asked

		if ($ShowDateAdded == "Y" || $ShowEverything) {
			$HTML .= 'port added: <font size="-1">';
			if ($port->date_added) {
				$HTML .= $port->date_added;
			} else {
				$HTML .= "unknown";
			}
			$HTML .= '</font><BR>' . "\n";
		}

		if (IsSet($port->no_package) && $port->no_package != '') {
			$HTML .= '<p><b>No package is available:</b> ' . $port->no_package . '</p>';
		} else {
			$HTML .= '<p><b>To add the package:</b> <code class="code">pkg_add -r ' . $port->latest_link . '</code></p>';
		}


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
				$HTML .= "<i>Also listed in:</i> ";
				$CategoriesArray = explode(" ", $Categories);
				$Count = count($CategoriesArray);
				for ($i = 0; $i < $Count; $i++) {
					$Category = $CategoriesArray[$i];
					$CategoryID = freshports_CategoryIDFromCategory($Category, $this->db);
					if ($CategoryID) {
						// this is a real category
						$HTML .= '<a href="/' . $Category . '/">' . $Category . '</a>';
					} else {
						$HTML .= $Category;
					}
					if ($i < $Count - 1) {
						$HTML .= " ";
					}
				}
				$HTML .= "<br>\n";
			}
		}

		if ($this->ShowDepends) {
			if ($port->depends_build) {
				$HTML .= "<i>required to build:</i> ";
				$HTML .= freshports_depends_links($this->db, $port->depends_build);

				$HTML .= "<br>\n";
			}

			if ($port->depends_run) {
				$HTML .= "<i>required to run:</i> ";
				$HTML .= freshports_depends_links($this->db, $port->depends_run);
				$HTML .= "<BR>\n";
			}

			if ($port->depends_lib) {
				$HTML .= "<i>required libraries:</i> ";
				$HTML .= freshports_depends_links($this->db, $port->depends_lib);

				$HTML .= "<br>\n";
			}

		}

		if ($this->ShowMasterSites) {
			$HTML .= '<dl><dt><i>master sites:</i></dt>' . "\n";

			$MasterSites = explode(' ', $port->master_sites);
			foreach ($MasterSites as $Site) {
				$HTML .= '<dd>' . htmlify(htmlspecialchars($Site)) . "</dd>\n";
			}

			$HTML .= "</dl>\n";

			$HTML .= '<br>';
		}

		if (!$HideDescription && ($ShowDescriptionLink == "Y" || $ShowEverything)) {
			// Long descripion
			$HTML .= '<A HREF="/' . $port->category . '/' . $port->port .'/">Description</a>';

			$HTML .= ' <b>:</b> ';
		}

		if ($ShowChangesLink == "Y" || $ShowEverything) {
			// changes
			$HTML .= '<a HREF="' . FRESHPORTS_FREEBSD_CVS_URL . '/ports/' .
               $port->category . '/' .  $port->port . '/" TITLE="The CVS Repository">CVSWeb</a>';
		}

		// download
		if ($port->status == "A" && ($ShowDownloadPortLink == "Y" || $ShowEverything)) {
			$HTML .= ' <b>:</b> ';
			$HTML .= '<a HREF="http://www.freebsd.org/cgi/pds.cgi?ports/' .
               $port->category . '/' .  $port->port . '" TITLE="The source code">Sources</a>';
		}

		if ($port->PackageExists() && ($ShowPackageLink == "Y" || $ShowEverything)) {
			// package
			$HTML .= ' <b>:</b> ';
			$HTML .= '<A HREF="' . FRESHPORTS_FREEBSD_FTP_URL . '/' . freshports_PackageVersion($port->version, $port->revision, $port->epoch);
			$HTML .= '.tgz">Package</A>';
		}

		if ($port->homepage && ($this->ShowHomepageLink == "Y" || $ShowEverything)) {
			$HTML .= ' <b>:</b> ';
			$HTML .= '<a HREF="' . htmlspecialchars($port->homepage) . '" TITLE="Main web site for this port">Main Web Site</a>';
		}

		if (defined('PORTSMONSHOW')) {
			$HTML .= ' <b>:</b> ' . freshports_PortsMonitorURL($port->category, $port->port);
		}

		if ($this->ShowMasterSlave) {
			#
			# Display our master port
			#

			if ($port->IsSlavePort()) {
				$HTML .= '<dl><dt><b>Master port:</b> ';
				list($MyCategory, $MyPort) = explode('/', $port->master_port);
				$HTML .= freshports_link_to_port($MyCategory, $MyPort);
				$HTML .= "</dt>\n";
				$HTML .= "</dl>\n";
			}
	
			#
			# Display our slave ports
			#

			$MasterSlave = new MasterSlave($port->dbh);
			$NumRows = $MasterSlave->FetchByMaster($port->category . '/' . $port->port);
			if ($NumRows > 0) {
				$HTML .= '<dl><dt><b>Slave ports</b>' . "</dt>\n";
				for ($i = 0; $i < $NumRows; $i++) {
					$MasterSlave->FetchNth($i);
					$HTML .= '<dd>' . freshports_link_to_port($MasterSlave->slave_category_name, $MasterSlave->slave_port_name);
					$HTML .= "</dd>\n";
				}
				$HTML .= "</dl>\n";
			}
		}
	
	   $HTML .= "\n</DD>\n";
	   $HTML .= "</DL>\n";

	   return $HTML;
	}

	function LinkToPort() {
		$HTML .= '<a href="' . $this->port->category . '/' . $this->port->port . 
			            '">' . $this->port->port . '</a>';

		return $HTML;
	}
}

?>