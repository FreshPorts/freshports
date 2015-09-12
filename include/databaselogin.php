<?php
	#
	# $Id: databaselogin.php,v 1.2 2006-12-17 11:55:53 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	# this file is merely a placeholder for the readfile.
	# see common.php for the configuration settings.

	require_once("$PathToDatabaseConfigFile/database.php");

	if (!$AllowUserChanges) {
		#
		# This is a list of pages which allow users to make changes
		# to their data.
		#

		$UserChangesPages['/committer-opt-in.php']       = 1;
		$UserChangesPages['/customize.php']              = 1;
		$UserChangesPages['/new-user.php']               = 1;
		$UserChangesPages['/pkg_upload.php']             = 1;
		$UserChangesPages['/port-watch.php']             = 1;
		$UserChangesPages['/report-subscriptions.php']   = 1;
		$UserChangesPages['/watch-categories.php']       = 1;
		$UserChangesPages['/watch-list-maintenance.php'] = 1;
		$UserChangesPages['/watch-list.php']             = 1;

		if ($UserChangesPages[$_SERVER['PHP_SELF']]) {
			echo 'User changes are not allowed just now.';
			exit;
		}
	}
