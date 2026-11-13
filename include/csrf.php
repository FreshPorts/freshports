<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Protection against cross-site request forgery (CSRF).
	#
	# A page which changes a user's data (watch lists, settings,
	# announcements) must not act just because the browser sent the login
	# cookie: a link or form on another site, followed by a logged-in user,
	# sends that cookie too.  So those pages act only on a POST which carries
	# a token that only FreshPorts can produce, and only for that user.
	#
	# The token is an HMAC of the user id, keyed with CSRF_SECRET from
	# include/constants.local.php.  It is the same on every device and for
	# every login of that user, which matters because pages showing the
	# watch-list icons (and so the token) are cached per user, not per login.
	# Changing CSRF_SECRET invalidates every token: a form which was open at
	# the time must be reloaded.
	#
	# Without CSRF_SECRET, no token is valid, so those pages change nothing
	# and say why; the error is logged.
	#
	# Usage:
	#   in each form:          echo freshports_CsrfField();
	#   before changing data:  if (freshports_CsrfValid()) { ... }
	#                    or:   freshports_CsrfRequire();
	#

const FRESHPORTS_CSRF_FIELD = 'fp_token';

function freshports_CsrfSecret() : string {
	return defined('CSRF_SECRET') ? (string) CSRF_SECRET : '';
}

# the token for the logged-in user; empty for a visitor who is not logged in
function freshports_CsrfToken() : string {
	GLOBAL $User;

	$UserID = isset($User->id) ? intval($User->id) : 0;
	$Secret = freshports_CsrfSecret();
	if ($UserID <= 0 || $Secret === '') {
		return '';
	}

	return hash_hmac('sha256', 'freshports-csrf|' . $UserID, $Secret);
}

# the hidden form field which carries the token
function freshports_CsrfField() : string {
	return '<input type="hidden" name="' . FRESHPORTS_CSRF_FIELD . '" value="' . freshports_CsrfToken() . '">';
}

# true only for a POST from a logged-in user which carries their token
function freshports_CsrfValid() : bool {
	if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
		return false;
	}

	if (freshports_CsrfSecret() === '') {
		syslog(LOG_ERR, 'CSRF_SECRET is not set in constants.local.php, so ' . ($_SERVER['SCRIPT_NAME'] ?? '') . ' cannot change anything');
		return false;
	}

	$Expected = freshports_CsrfToken();
	$Given    = $_POST[FRESHPORTS_CSRF_FIELD] ?? '';

	return $Expected !== '' && is_string($Given) && hash_equals($Expected, $Given);
}

# the message shown when a change is refused
function freshports_CsrfMessage() : string {
	if (freshports_CsrfSecret() === '') {
		return _('Changes cannot be saved just now: this site is not fully configured. The problem has been logged.');
	}

	return _('Nothing was changed: that request did not come from a FreshPorts page, or the page was too old. Please reload the page and try again.');
}

# stop with a 403 unless the request is a valid POST; for pages with no better way to say no
function freshports_CsrfRequire() : void {
	if (freshports_CsrfValid()) {
		return;
	}

	http_response_code(403);
	header('Content-Type: text/html; charset=UTF-8');
	echo '<!DOCTYPE html><html><head><title>FreshPorts</title></head><body><p>' . htmlspecialchars(freshports_CsrfMessage()) . "</p></body></html>\n";
	exit;
}
