<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Deter bots from running searches - see
	# https://github.com/FreshPorts/freshports/issues/692
	#
	# A search is allowed when the visitor is logged in, has a valid search_ok
	# cookie, or came from a page on this site (the Referer).  Anyone else gets a
	# small page with a button; pressing it posts to www/search-gate.php, which
	# sets the cookie and sends them back to their search.
	#
	# The cookie and the button's token are signed with SEARCH_GATE_SECRET, from
	# include/constants.local.php.  Without that secret, the gate is off.
	#

define('SEARCH_GATE_COOKIE_NAME',     'search_ok');
define('SEARCH_GATE_COOKIE_LIFETIME', 60 * 60 * 24); # one day
define('SEARCH_GATE_TOKEN_LIFETIME',  60 * 60);      # how long the button on the challenge page works

function freshports_search_gate_enabled() {
	return defined('SEARCH_GATE_SECRET') && SEARCH_GATE_SECRET != '';
}

#
# $purpose keeps a token from being used as a cookie, and the other way around.
#
function freshports_search_gate_sign($purpose, $timestamp) {
	return $timestamp . '.' . hash_hmac('sha256', $purpose . '|' . $timestamp, SEARCH_GATE_SECRET);
}

#
# For a cookie, $timestamp is when it expires; for a token, when it was issued.
# Returns that timestamp, or false if the value was not signed by us.
#
function freshports_search_gate_verify($purpose, $value) {
	if (!is_string($value) || !preg_match('/^(\d{1,12})\.[0-9a-f]{64}$/', $value, $matches)) {
		return false;
	}

	if (!hash_equals(freshports_search_gate_sign($purpose, $matches[1]), $value)) {
		return false;
	}

	return intval($matches[1]);
}

function freshports_search_gate_set_cookie() {
	$expires = time() + SEARCH_GATE_COOKIE_LIFETIME;
	setcookie(SEARCH_GATE_COOKIE_NAME, freshports_search_gate_sign('cookie', $expires), array(
		'expires'  => $expires,
		'path'     => '/',
		'secure'   => ($_SERVER['REQUEST_SCHEME'] ?? '') == 'https',
		'httponly' => true,
		'samesite' => 'Lax',
	));
}

function freshports_search_gate_has_cookie() {
	$expires = freshports_search_gate_verify('cookie', $_COOKIE[SEARCH_GATE_COOKIE_NAME] ?? null);

	return $expires !== false && $expires > time();
}

function freshports_search_gate_referer_is_us() {
	if (empty($_SERVER['HTTP_REFERER'])) {
		return false;
	}

	$referer = parse_url($_SERVER['HTTP_REFERER']);
	$host = ($referer['host'] ?? '') . (IsSet($referer['port']) ? ':' . $referer['port'] : '');

	return $host == $_SERVER['HTTP_HOST'];
}

function freshports_search_gate_token_is_valid($token) {
	$issued = freshports_search_gate_verify('token', $token);

	return $issued !== false && $issued <= time() && time() - $issued < SEARCH_GATE_TOKEN_LIFETIME;
}

#
# Call this before doing any searching.  It returns if the search may go ahead;
# otherwise it shows the challenge page and exits.
#
function freshports_search_gate() {
	GLOBAL $User;

	if (!freshports_search_gate_enabled() || $User->id || freshports_search_gate_has_cookie()) {
		return;
	}

	if (freshports_search_gate_referer_is_us()) {
		freshports_search_gate_set_cookie();
		return;
	}

	syslog(LOG_NOTICE, 'search gate challenge: ' . $_SERVER['REMOTE_ADDR'] . ' ' . ($_SERVER['HTTP_USER_AGENT'] ?? '-'));

	header('HTTP/1.1 403 Forbidden');
	header('Cache-Control: no-store');
	header('X-Robots-Tag: noindex, nofollow');
	header('Content-Type: text/html; charset=UTF-8');

	$token  = freshports_search_gate_sign('token', time());
	$return = $_SERVER['QUERY_STRING'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>FreshPorts -- <?php echo htmlspecialchars(_('Search')); ?></title>
<link rel="stylesheet" href="/css/freshports.css">
</head>
<body>
<h1><?php echo htmlspecialchars(_('Search')); ?></h1>
<p><?php echo htmlspecialchars(_('To keep the search working for people, please confirm you are not a bot.')); ?></p>
<form action="/search-gate.php" method="post">
<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
<input type="hidden" name="return" value="<?php echo htmlspecialchars($return); ?>">
<input type="submit" value="<?php echo htmlspecialchars(_('Continue to search')); ?>">
</form>
<p><?php echo htmlspecialchars(_('This sets a cookie, so you will not be asked again for a day. Logged in users are never asked.')); ?></p>
</body>
</html>
<?php
	exit;
}
