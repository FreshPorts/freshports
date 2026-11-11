<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Deter bots from pages they hammer: search.php (see
	# https://github.com/FreshPorts/freshports/issues/692), commit.php and
	# vuxml.php (see https://github.com/FreshPorts/freshports/issues/732).
	# This was the search gate until it covered more than search.
	#
	# A gated page is allowed when the visitor is logged in or has a valid
	# bot_gate_ok cookie.  Anyone else gets a small page with a button; pressing
	# it posts to www/bot-gate.php, which sets the cookie and sends them back to
	# the page they asked for.  The Referer is not trusted: bots set it to a page
	# on this site.
	#
	# The challenge page needs no database, so www/bot-gate.php also shows it on
	# a GET.  That lets nginx send cookie-less requests there without running the
	# gated page at all.
	#
	# The cookie and the button's token are signed with BOT_GATE_SECRET, from
	# include/constants.local.php.  SEARCH_GATE_SECRET, its old name, is still
	# read, so existing configurations keep working.  Without a secret, the gate
	# is off and each page falls back to its LOGIN_TO_* setting.
	#

define('BOT_GATE_COOKIE_NAME',     'bot_gate_ok');
define('BOT_GATE_COOKIE_LIFETIME', 60 * 60 * 24); # one day
define('BOT_GATE_TOKEN_LIFETIME',  60 * 60);      # how long the button on the challenge page works

#
# The pages behind the gate.  The gate only ever sends a visitor back to one of
# these, so it cannot be used to redirect anywhere else.  The first is the
# default.
#
function freshports_bot_gate_pages() {
	return array('/search.php', '/commit.php', '/vuxml.php');
}

function freshports_bot_gate_page_or_default($page) {
	$pages = freshports_bot_gate_pages();

	return is_string($page) && in_array($page, $pages, true) ? $page : $pages[0];
}

function freshports_bot_gate_secret() {
	if (defined('BOT_GATE_SECRET')) {
		return BOT_GATE_SECRET;
	}

	return defined('SEARCH_GATE_SECRET') ? SEARCH_GATE_SECRET : '';
}

function freshports_bot_gate_enabled() {
	return freshports_bot_gate_secret() != '';
}

#
# $purpose keeps a token from being used as a cookie, and the other way around.
#
function freshports_bot_gate_sign($purpose, $timestamp) {
	return $timestamp . '.' . hash_hmac('sha256', $purpose . '|' . $timestamp, freshports_bot_gate_secret());
}

#
# For a cookie, $timestamp is when it expires; for a token, when it was issued.
# Returns that timestamp, or false if the value was not signed by us.
#
function freshports_bot_gate_verify($purpose, $value) {
	if (!is_string($value) || !preg_match('/^(\d{1,12})\.[0-9a-f]{64}$/', $value, $matches)) {
		return false;
	}

	if (!hash_equals(freshports_bot_gate_sign($purpose, $matches[1]), $value)) {
		return false;
	}

	return intval($matches[1]);
}

function freshports_bot_gate_set_cookie() {
	$expires = time() + BOT_GATE_COOKIE_LIFETIME;
	setcookie(BOT_GATE_COOKIE_NAME, freshports_bot_gate_sign('cookie', $expires), array(
		'expires'  => $expires,
		'path'     => '/',
		'secure'   => ($_SERVER['REQUEST_SCHEME'] ?? '') == 'https',
		'httponly' => true,
		'samesite' => 'Lax',
	));
}

function freshports_bot_gate_has_cookie() {
	$expires = freshports_bot_gate_verify('cookie', $_COOKIE[BOT_GATE_COOKIE_NAME] ?? null);

	return $expires !== false && $expires > time();
}

function freshports_bot_gate_token_is_valid($token) {
	$issued = freshports_bot_gate_verify('token', $token);

	return $issued !== false && $issued <= time() && time() - $issued < BOT_GATE_TOKEN_LIFETIME;
}

#
# Call this from a gated page, before it does any real work.  $page is that
# page, e.g. '/commit.php'.  It returns if the page may go ahead; otherwise it
# shows the challenge page and exits.
#
function freshports_bot_gate($page) {
	GLOBAL $User;

	if (!freshports_bot_gate_enabled() || $User->id || freshports_bot_gate_has_cookie()) {
		return;
	}

	freshports_bot_gate_challenge($page, $_SERVER['QUERY_STRING'] ?? '');
}

#
# Show the challenge page and exit.  $page and $return (its query string) are
# where the button sends the visitor afterwards.
#
function freshports_bot_gate_challenge($page, $return) {
	$page = freshports_bot_gate_page_or_default($page);

	# only for search, as before #732: commit.php and vuxml.php get thousands of
	# crawler requests an hour, which would flood the log.  The message is
	# unchanged so existing log watching and logcheck rules still match.
	if ($page == '/search.php') {
		syslog(LOG_NOTICE, 'search gate challenge: ' . $_SERVER['REMOTE_ADDR'] . ' ' . ($_SERVER['HTTP_USER_AGENT'] ?? '-'));
	}

	header('HTTP/1.1 403 Forbidden');
	header('Cache-Control: no-store');
	header('X-Robots-Tag: noindex, nofollow');
	header('Content-Type: text/html; charset=UTF-8');

	$heading = _('Just a moment');
	$token   = freshports_bot_gate_sign('token', time());

	# A dark card with one big button.  The styles are inline so the page
	# needs nothing else from the site: no stylesheet, no images, no script.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>FreshPorts -- <?php echo htmlspecialchars($heading); ?></title>
<style>
html, body {
  margin: 0;
  min-height: 100%;
  background: #121821;
  color: #e8eef8;
  font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
body {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 16px;
  box-sizing: border-box;
}
.gate {
  width: 100%;
  max-width: 640px;
  box-sizing: border-box;
  padding: 48px 40px;
  background: #1e2533;
  border: 1px solid #2f3a4c;
  border-radius: 28px;
  text-align: center;
}
.gate h1 {
  margin: 0 0 20px;
  font-size: 2.6rem;
  font-weight: 700;
  line-height: 1.15;
}
.gate p {
  margin: 0 0 28px;
  font-size: 1.15rem;
  line-height: 1.5;
}
.gate form {
  margin: 0;
}
.gate button {
  width: 100%;
  padding: 26px 20px;
  background: #93b4fb;
  color: #141b28;
  font: inherit;
  font-size: 1.4rem;
  font-weight: 700;
  /* the dark ring inside the light blue edge, as in the design */
  border: 5px solid #1e2533;
  border-radius: 20px;
  box-shadow: 0 0 0 4px #93b4fb;
  cursor: pointer;
}
.gate button:hover {
  background: #a9c3fc;
}
.gate button:focus-visible {
  outline: 3px solid #e8eef8;
  outline-offset: 6px;
}
.gate .note {
  margin: 28px 0 0;
  font-size: 0.9rem;
  color: #9aa7bb;
}
@media (max-width: 480px) {
  .gate { padding: 32px 20px; border-radius: 20px; }
  .gate h1 { font-size: 2rem; }
  .gate button { font-size: 1.15rem; padding: 20px 12px; }
}
</style>
</head>
<body>
<main class="gate">
<h1><?php echo htmlspecialchars($heading); ?></h1>
<p><?php echo htmlspecialchars(_("If you're a person, please press the button for a cookie.")); ?></p>
<form action="/bot-gate.php" method="post">
<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
<input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">
<input type="hidden" name="return" value="<?php echo htmlspecialchars($return); ?>">
<button type="submit"><?php echo htmlspecialchars(_('I was a person last time I checked.')); ?></button>
</form>
<p class="note"><?php echo htmlspecialchars(_('This sets a cookie, so you will not be asked again for a day. Logged in users are never asked.')); ?></p>
</main>
</body>
</html>
<?php
	exit;
}
