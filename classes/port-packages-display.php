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

class port_packages_display {

	var $db;
	var $port_id;

	function __construct(&$db, $port_id) {
		$this->db      = $db;
		$this->port_id = $port_id;
	}

	function Display($verbosity_level = 1) {

		# verbosity_level has been defined, but not used.
		$port = $this->port;

		$HTML = '';
				$packages = new Packages($this->db);
				$numrows = $packages->Fetch($this->port_id);

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

}
