<?php
	#
	# $Id: port-display.php,v 1.1.2.6 2006-06-23 12:26:29 dan Exp $
	#
	# Copyright (c) 2005-2006 DVL Software Limited
	#
	
require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/master_slave.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/htmlify.php');

class port_display {
	var $db;

	var $port;
	var $User;		# used for matching against watch lists
	var $DaysMarkedAsNew;

	var $ShowEverything;

	var $LinkToPort;
	var $ShowCategory;
	var $ShowChangesLink;
	var $ShowDateAdded;
	var $ShowDescriptionShort;
	var $ShowDescriptionLong;
	var $ShowDescriptionLink;
	var $ShowDepends;
	var $ShowDownloadPortLink;
	var $ShowHomepageLink;
	var $ShowLastChange;
	var $ShowMaintainedBy;
	var $ShowMasterSites;
	var $ShowMasterSlave;
	var	$ShowPackageLink;
	var $ShowPortCreationDate;
	var $ShowShortDescription;
	var $ShowWatchListCount;
	var $ShowWatchListStatus;
	
	function port_display(&$db, $User = 0) {
		$this->db   = $db;
		$this->User = $User;
		$this->DaysMarkedAsNew = 10;
		
		$this->SetDetailsNil();
	}
	
	function SetDetailsNil() {
		$this->ShowEverything       = false;

 		$this->LinkToPort           = false;
		$this->ShowCategory         = false;
		$this->ShowChangesLink      = false;
		$this->ShowDateAdded        = false;
		$this->ShowDescriptionShort = false;
		$this->ShowDescriptionLong  = false;
		$this->ShowDescriptionLink  = false;
		$this->ShowDepends          = false;
		$this->ShowDownloadPortLink = false;
		$this->ShowHomepageLink     = false;
		$this->ShowLastChange       = false;
		$this->ShowMaintainedBy     = false;
		$this->ShowMasterSites      = false;
		$this->ShowMasterSlave      = false;
		$this->ShowPackageLink      = false;
		$this->ShowPortCreationDate = false;
		$this->ShowPortsMonLink     = false;
		$this->ShowShortDescription = false;
		$this->ShowWatchListCount   = false;
		$this->ShowWatchListStatus  = false;
	}

	function SetDetailsFull() {
		$this->SetDetailsNil();
		$this->ShowEverything = true;
	}

	function SetDetailsSearch() {
		$this->SetDetailsNil();
 		$this->LinkToPort           = true;
		$this->ShowCategory         = true;
		$this->ShowChangesLink      = true;
		$this->ShowDescriptionLink  = true;
		$this->ShowDownloadPortLink = true;
		$this->ShowHomepageLink     = true;
		$this->ShowMaintainedBy     = true;
		$this->ShowPortCreationDate = true;
		$this->ShowPortsMonLink     = true;
		$this->ShowPackageLink      = true;
		$this->ShowShortDescription = true;
		$this->ShowWatchListStatus  = true;
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
		$this->ShowDescriptionLink  = true;
		$this->ShowMaintainedBy     = true;
		$this->ShowPortCreationDate = true;
		$this->ShowShortDescription = true;
		$this->ShowWatchListStatus  = true;
		$this->ShowDateAdded        = true;
	}

	function SetDetailsIndex() {
		$this->SetDetailsNil();

 		$this->LinkToPort           = true;
		$this->ShowDescriptionLink  = true;
		$this->ShowMaintainedBy     = true;
		$this->ShowPortCreationDate = true;
		$this->ShowShortDescription = true;
		$this->ShowWatchListStatus  = true;
		$this->ShowDateAdded        = true;
	}

	function Display() {
		$port = $this->port;
		$HTML = '';

		$MarkedAsNew = "N";
		$HTML .= "<DL>\n";
		
		$HTML .= "<DT>";

		$HTML .= '<BIG><B>';

		if ($this->ShowEverything || $this->LinkToPort) {
			$HTML .= $this->LinkToPort();
		} else {
			$HTML .= $port->port;
		}

		$PackageVersion = freshports_PackageVersion($port->{'version'}, $port->{'revision'}, $port->{'epoch'});
		if (strlen($PackageVersion) > 0) {
			$HTML .= ' ' . $PackageVersion;
		}

		if (IsSet($port->category_looking_at)) {
			if ($port->category_looking_at != $port->category) {
				$HTML .= '<sup>*</sup>';
			}
		}

		$HTML .= "</B></BIG>";

		if ($this->ShowEverything || $this->ShowCategory) {
			$HTML .= ' / <A HREF="/' . $port->category . '/" TITLE="The category for this port">' . $port->category . '</A>';
		}

		if ($this->User && $this->User->id && ($this->ShowEverything || $this->ShowWatchListStatus)) {
			if ($port->{'onwatchlist'}) {
				$HTML .= ' '. freshports_Watch_Link_Remove($this->User->watch_list_add_remove, $port->onwatchlist, $port->{'element_id'});
			} else {
				$HTML .= ' '. freshports_Watch_Link_Add   ($this->User->watch_list_add_remove, $port->onwatchlist, $port->{'element_id'});
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

		if ($port->{'date_added'} > Time() - 3600 * 24 * $this->DaysMarkedAsNew) {
			$MarkedAsNew = "Y";
			$HTML .= freshports_New_Icon() . "\n";
		}

		if ($this->ShowEverything || $this->ShowWatchListCount) {
			$HTML .= '&nbsp; ' . freshPorts_WatchListCount_Icon_Link() . '=' . $port->WatchListCount();
		}

		$HTML .= ' ' . freshports_Search_Depends_All($port->category . '/' . $port->port);

		if ($port->IsVulnerable()) {
			$HTML .= '&nbsp;' . freshports_VuXML_Icon();
		} else {
			if ($port->WasVulnerable()) {
				$HTML .= '&nbsp;' . freshports_VuXML_Icon_Faded();
			}
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

		if ($port->expiration_date) {
			if (date('Y-m-d') >= $port->expiration_date) {
				$HTML .= freshports_Expired_Icon_Link($port->expiration_date) . ' This port expired on: ' . $port->expiration_date . '<br>';
			} else {
				$HTML .= freshports_Expiration_Icon_Link($port->expiration_date) . ' EXPIRATION DATE: ' . $port->expiration_date . '<br>';
			}
		}

		if ($port->ignore) {
			$HTML .= freshports_Ignore_Icon_Link($port->ignore)         . ' IGNORE: '     . htmlify(htmlspecialchars($port->ignore))     . "<br>"; ;
		}

		if ($port->restricted) {
			$HTML .= freshports_Restricted_Icon_Link($port->restricted) . ' RESTRICTED: '     . htmlify(htmlspecialchars($port->restricted)) . '<br>';
		}

		if ($port->no_cdrom) {
			$HTML .= freshports_No_CDROM_Icon_Link($port->no_cdrom)      . ' NO CDROM: '     . htmlify(htmlspecialchars($port->no_cdrom))   . '<br>';
		}

		if ($port->is_interactive) {
			$HTML .= freshports_Is_Interactive_Icon_Link($port->is_interactive) . ' IS INTERACTIVE: '  . htmlify(htmlspecialchars($port->is_interactive)) . '<br>';
		}

		// description
		if ($port->short_description && ($this->ShowShortDescription || $this->ShowEverything)) {
			$HTML .= htmlify(htmlspecialchars($port->short_description));
			$HTML .= "<br>\n";
		}

		// maintainer
	   if ($port->maintainer && ($this->ShowMaintainedBy || $this->ShowEverything)) {
    	  if (strtolower($port->maintainer) == UNMAINTAINTED_ADDRESS) {
        	 $HTML .= '<br>There is no maintainer for this port.<br>';
	         $HTML .= 'Any concerns regarding this port should be directed to the FreeBSD ' .
	                   'Ports mailing list via ';
    	     $HTML .= '<A HREF="' . MAILTO . ':' . freshportsObscureHTML($port->maintainer);
        	 $HTML .= '?subject=FreeBSD%20Port:%20' . $port->category . '/' . $port->port . '" TITLE="email the FreeBSD Ports mailing list">';
	         $HTML .= freshportsObscureHTML($port->maintainer) . '</A>';
    	  } else {
	         $HTML .= '<i>';
    	     if ($port->status == 'A') {
        	    $HTML .= 'Maintained';
	         } else {
    	        $HTML .= 'was maintained'; 
        	 }

	         $HTML .= ' by:</i> <A HREF="' . MAILTO . ':' . freshportsObscureHTML($port->maintainer);
    	     $HTML .= '?subject=FreeBSD%20Port:%20' . $port->category . '/' . $port->port . '" TITLE="email the maintainer">';
        	 $HTML .= freshportsObscureHTML($port->maintainer) . '</A>';
	      }
      
    	  $HTML .= ' ' . freshports_Search_Maintainer($port->maintainer) . '<br>';
	   }


		// there are only a few places we want to show the last change.
		// therefore, we do not check ShowEverything here
		if ($this->ShowLastChange) {
			if ($port->updated != 0) {
	            $HTML .= 'last change committed by ' . freshports_CommitterEmailLink($port->committer);  // separate lines in case committer is null

       		    $HTML .= ' ' . freshports_Search_Committer($port->committer);
 
				$HTML .= ' on <font size="-1">' . $port->updated . '</font>' . "\n";

				$HTML .= freshports_Email_Link($port->message_id);

				if ($port->EncodingLosses()) {
					$HTML .= '&nbsp;' . freshports_Encoding_Errors_Link();
				}

				$HTML .= ' ' . freshports_Commit_Link($port->message_id);
				$HTML .= ' ' . freshports_CommitFilesLink($port->message_id, $port->category, $port->port);
				
				GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;

				$HTML .= freshports_PortDescriptionPrint($port->update_description, $port->encoding_losses, 
			 				$freshports_CommitMsgMaxNumOfLinesToShow, 
			 				freshports_MoreCommitMsgToShow($port->message_id,
	 				       $freshports_CommitMsgMaxNumOfLinesToShow));
			} else {
				$HTML .= "no changes recorded in FreshPorts<br>\n";
			}
		}

		# show the date added, if asked

		if ($this->ShowDateAdded || $this->ShowEverything) {
			$HTML .= '<i>port added:</i> <font size="-1">';
			if ($port->date_added) {
				$HTML .= $port->date_added;
			} else {
				$HTML .= "unknown";
			}
			$HTML .= '</font><BR>' . "\n";
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

#	   $HTML .= "<br>\n";

	   if ($this->ShowDescriptionLong || $this->ShowEverything) {
		   $HTML .= '<PRE CLASS="code">' . htmlify(htmlspecialchars($port->long_description)) . '</PRE>';
	   }

	   if ($this->ShowChangesLink || $this->ShowEverything) {
		   // changes
		   $HTML .= '<a HREF="' . FRESHPORTS_FREEBSD_CVS_URL . '/ports/' .
			   $port->category . '/' .  $port->port . '/" TITLE="The CVS Repository">CVSWeb</a>';
	   }

	   // download
	   if ($port->status == "A" && ($this->ShowDownloadPortLink || $this->ShowEverything)) {
		   $HTML .= ' <b>:</b> ';
		   $HTML .= '<a HREF="http://www.freebsd.org/cgi/pds.cgi?ports/' .
			   $port->category . '/' .  $port->port . '" TITLE="The source code">Sources</a>';
	   }

	   if ($port->PackageExists() && ($this->ShowPackageLink || $this->ShowEverything)) {
		   // package
		   $HTML .= ' <b>:</b> ';
		   $HTML .= '<A HREF="' . FRESHPORTS_FREEBSD_FTP_URL . '/' . freshports_PackageVersion($port->version, $port->revision, $port->epoch);
		   $HTML .= '.tgz">Package</A>';
	   }

	   if ($port->homepage && ($this->ShowHomepageLink || $this->ShowEverything)) {
		   $HTML .= ' <b>:</b> ';
		   $HTML .= '<a HREF="' . htmlspecialchars($port->homepage) . '" TITLE="Main web site for this port">Main Web Site</a>';
	   }

	   if (defined('PORTSMONSHOW')  && ($this->ShowPortsMonLink || $this->ShowEverything)) {
		   $HTML .= ' <b>:</b> ' . freshports_PortsMonitorURL($port->category, $port->port);
	   }
	   
	   if ($this->ShowEverything || $this->ShowMasterSlave) {
			#
			# Display our master port
			#

			$MasterSlave = new MasterSlave($port->dbh);
			$NumRows = $MasterSlave->FetchByMaster($port->category . '/' . $port->port);

			if ($port->IsSlavePort() || $NumRows > 0) {
				$HTML .= "\n<hr>\n";
			}

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

			if ($NumRows > 0) {
				$HTML .= '<dl><dt><b>Slave ports</b>' . "</dt>\n";
				for ($i = 0; $i < $NumRows; $i++) {
					$MasterSlave->FetchNth($i);
					$HTML .= '<dd>' . freshports_link_to_port($MasterSlave->slave_category_name, $MasterSlave->slave_port_name);
					$HTML .= "</dd>\n";
				}
				$HTML .= "</dl>\n";
#			} else {
#				$HTML .= "<br><br>\n";
			}
		}
	
		if ($this->ShowDepends || $this->ShowEverything) {
			if ($port->depends_build || $port->depends_run || $port->depends_lib) {
				$HTML .= '<hr>';
			}

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

		if ($this->ShowPackageLink || $this->ShowEverything) {
			$HTML .= "\n<hr>\n";
			$HTML .= '<p><b>To install <a href="/faq.php#port" TITLE="what is a port?">the port</a>:</b> <code class="code">cd /usr/ports/'  . $port->category . '/' . $port->port . '/ && make install clean</code><br>';
			if (IsSet($port->no_package) && $port->no_package != '') {
				$HTML .= '<p><b>No <a href="/faq.php#package" TITLE="what is a package?">package</a> is available:</b> ' . $port->no_package . '</p>';
			} else {
				if ($port->forbidden || $port->broken || $port->ignore || $port->restricted) {
					$HTML .= '<p><b>A <a href="/faq.php#package" TITLE="what is a package?">package</a> is not available for ports asked as: Forbidden / Broken / Ignore / Restricted</b></p>';
				} else {
					$HTML .= '<b>To add the <a href="/faq.php#package" TITLE="what is a package?">package</a>:</b> <code class="code">pkg_add -r ' . $port->latest_link . '</code></p>';
				}
			}

			$HTML .= "\n<hr>\n";
		}

		if ($this->ShowDescriptionShort && ($this->ShowDescriptionLink || $this->ShowEverything)) {
			// Long description
			$HTML .= '<A HREF="/' . $port->category . '/' . $port->port .'/">Description</a>';

			$HTML .= ' <b>:</b> ';
		}

		if ($this->ShowEverything || $this->ShowMasterSites) {
			$HTML .= '<dl><dt><i>master sites:</i></dt>' . "\n";

			$MasterSites = explode(' ', $port->master_sites);
			foreach ($MasterSites as $Site) {
				$HTML .= '<dd>' . htmlify(htmlspecialchars($Site)) . "</dd>\n";
			}

			$HTML .= "</dl>\n";

#			$HTML .= '<br>';
		}

#		$HTML .= "\n<hr>\n";

		$HTML .= "\n</DD>\n";
		$HTML .= "</DL>\n";

		return $HTML;
	}

	function LinkToPort() {
		$HTML = '<a href="/' . $this->port->category . '/' . $this->port->port . 
			            '/">' . $this->port->port . '</a>';

		return $HTML;
	}
}

?>