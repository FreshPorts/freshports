<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Clearing a user's own cached pages after they change their watch lists.
	#
	# Pages which show watch-list icons are cached per user: the lists of
	# ports (ports-new.php, ports-*.php, the logged-in front page; in the news
	# cache), category pages and sanity_test_failures.php.  Commits empty the
	# news cache, and a category's entries go when one of its ports changes,
	# so after adding or removing a port a user saw the old icon until then:
	# minutes for the lists, possibly days for a quiet category.
	#
	# getvalues.php calls freshports_CacheRemoveUserOnWatchListPage() on every
	# page.  On the pages which change watch lists, it arranges for that
	# user's entries to be removed once the page has finished, i.e. after its
	# changes are in the database.  Removing an entry which had not changed
	# costs only a rebuild of that page for that user.  Anonymous visitors
	# (user 0) have no watch lists, so their shared entries are never touched.
	#

# the pages which add, remove or empty watch lists, or change which lists are defaults
const FRESHPORTS_WATCH_LIST_PAGES = array(
	'watch-list.php',
	'watch.php',
	'port-watch.php',
	'watch-categories.php',
	'watch-list-maintenance.php',
	'filter-setup.php',
	'pkg_upload.php',
);

function freshports_CacheRemoveUser($UserID) {
	$UserID = intval($UserID);
	if ($UserID <= 0) {
		return;
	}

	$patterns = array(
		# news cache keys are cleaned to [a-z0-9-] and end in -<user>-pagesize<n>-pagenum<n>-html
		NEWS_DIRECTORY  . '/*-' . $UserID . '-pagesize*',
		# see CacheCategory::_CategoryKey(): <category>/<category>.<user>.<branch>.PageSize...
		CACHE_DIRECTORY . '/categories/*/*.' . $UserID . '.*',
		# see sanity_test_failures.php: sanity_test_failures[.<message id>].user<user>.PageSize...
		CACHE_DIRECTORY . '/general/sanity_test_failures*.user' . $UserID . '.*',
	);

	foreach ($patterns as $pattern) {
		foreach (glob($pattern) ?: array() as $file) {
			# another request may have removed it already
			@unlink($file);
		}
	}
}

function freshports_CacheRemoveUserOnWatchListPage($UserID) {
	if (intval($UserID) > 0 && in_array(basename($_SERVER['SCRIPT_NAME'] ?? ''), FRESHPORTS_WATCH_LIST_PAGES, true)) {
		register_shutdown_function('freshports_CacheRemoveUser', intval($UserID));
	}
}
