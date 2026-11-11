<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# GET: show the challenge page.  nginx sends cookie-less requests for gated
	# pages here (an internal rewrite, so REQUEST_URI is still the page asked
	# for, e.g. /commit.php?message_id=...), so bots never reach those pages or
	# the database.
	#
	# POST: the challenge page posts here.  With a valid token, set the
	# bot_gate_ok cookie.  Either way, go back to the page; without the cookie,
	# the visitor sees the challenge page again.
	#
	# See include/bot-gate.php,
	# https://github.com/FreshPorts/freshports/issues/692 and
	# https://github.com/FreshPorts/freshports/issues/732
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.local.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/bot-gate.php');

	# Without the gate, the gated pages require a login.  Do not redirect there:
	# if nginx sends cookie-less requests here, that would loop.
	if (!freshports_bot_gate_enabled()) {
		$msg = 'You must be logged in to use this feature.';
		header('HTTP/1.1 401 ' . $msg);
		die($msg);
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$page   = freshports_bot_gate_page_or_default($_POST['page'] ?? '');
		$return = $_POST['return'] ?? '';

		if (freshports_bot_gate_token_is_valid($_POST['token'] ?? '')) {
			freshports_bot_gate_set_cookie();
		}
	} else {
		$page   = freshports_bot_gate_page_or_default(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
		$return = $_SERVER['QUERY_STRING'] ?? '';

		if (!freshports_bot_gate_has_cookie()) {
			freshports_bot_gate_challenge($page, $return);
		}
	}

	# only ever return to a gated page; the form fields cannot change that
	$location = $page;
	if (is_string($return) && $return != '' && !preg_match('/[\r\n]/', $return)) {
		$location .= '?' . $return;
	}

	header('Location: ' . $location, true, 303);
