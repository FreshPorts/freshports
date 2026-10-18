<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Send the visitor back to the page they came from, after a choice such as
	# language or text size has been saved.  Used by www/language.php and
	# www/fontsize.php.
	#

function freshports_redirect_back() {
	# only return to a page on this site
	$return = '/';
	if (!empty($_SERVER['HTTP_REFERER'])) {
		$referer = parse_url($_SERVER['HTTP_REFERER']);
		$host = ($referer['host'] ?? '') . (IsSet($referer['port']) ? ':' . $referer['port'] : '');
		$path = $referer['path'] ?? '/';
		# a path starting with // or /\ would leave this site
		if ($host == $_SERVER['HTTP_HOST'] && preg_match('|^/(?![/\\\\])|', $path)) {
			$return = $path . (IsSet($referer['query']) ? '?' . $referer['query'] : '');
		}
	}

	header('Location: ' . $return, true, 303);
}
