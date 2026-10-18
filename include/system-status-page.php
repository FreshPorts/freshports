<?php
	#
	# Copyright (c) 2016-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/system_status.php');

	function freshports_SystemStatusPage($dbh) {
		# output the /--/status/ page.
		# shared by www/--/index.php and www/--/new-url-parsing.php so the two cannot drift apart.

		$status_file = '/var/db/freshports/cache/html/backend-status.html';

		$status = new SystemStatus($dbh);

		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		echo '<meta http-equiv="refresh" content="180"> <!-- Refresh every 3 minutes -->' . "\n";
		echo '<style>
.maintenance { background: #c00; color: #fff; font-size: 1.5em; font-weight: bold; padding: 0.75em 1em; border: 4px solid #600; }
.no-logins   { background: #e60; color: #fff; font-size: 1.25em; font-weight: bold; padding: 0.5em 1em; border: 4px solid #940; }
.not-running { background: #fdd; color: #900; font-weight: bold; padding: 0.25em 0.5em; border-left: 6px solid #c00; }
.stale       { background: #ffd; color: #840; font-weight: bold; padding: 0.25em 0.5em; border-left: 6px solid #e90; }
</style>' . "\n";
		echo "</head>\n<body>\n";

		if ($status->InMaintenanceMode()) {
			echo '<p class="maintenance">' . _('We are in maintenance mode.') . '</p>';
		} else {
			echo '<p>' . _('We are not in maintenance mode.') . '</p>';
		}

		if ($status->LoginsAreAllowed()) {
			echo '<p>' . _('Logins are enabled.') . '</p>';
		} else {
			echo '<p class="no-logins">' . _('Nobody is allowed to login right now.') . '</p>';
		}

		# highlight any service the backend reports as not running
		echo preg_replace('/<p>(?=[^<]*is not running)/', '<p class="not-running">', file_get_contents($status_file));

		exec("/usr/sbin/service fp_listen status", $fp_listen_output, $fp_listen_result);
		echo '<p' . ($fp_listen_result == 0 ? '' : ' class="not-running"') . '>' . htmlspecialchars(implode(' ', $fp_listen_output)) . '</p>' . "\n";

		echo '<hr>';

		$status_age = time() - filemtime($status_file);
		echo '<p' . ($status_age > 4 * 60 ? ' class="stale"' : '') . '>' . sprintf(_('This status was last updated at %s'), gmdate('Y-m-d H:i:sO', filemtime($status_file))) . '</p>';
		echo '<p>' . _('It should never be more than 4 minutes old.') . '</p>';
		echo '<p>' . _('This page automatically reloads every 3 minutes.') . '</p>';
		echo '<p>' . _('The contents are generated every 3 minutes by the backend server.') . '</p>';

		echo "</body>\n</html>\n";
	}
