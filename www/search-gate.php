<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# GET: show the challenge page for the search in the query string.  nginx
	# sends cookie-less searches here, so bots never reach search.php or the
	# database.
	#
	# POST: the challenge page posts here.  With a valid token, set the search_ok
	# cookie.  Either way, go back to the search; without the cookie, the visitor
	# sees the challenge page again.
	#
	# See https://github.com/FreshPorts/freshports/issues/692
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.local.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/search-gate.php');

	# Without the gate, search.php requires a login.  Do not redirect there:
	# if nginx sends cookie-less searches here, that would loop.
	if (!freshports_search_gate_enabled()) {
		$msg = 'You must be logged in to use this feature.';
		header('HTTP/1.1 401 ' . $msg);
		die($msg);
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$return = $_POST['return'] ?? '';

		if (freshports_search_gate_token_is_valid($_POST['token'] ?? '')) {
			freshports_search_gate_set_cookie();
		}
	} else {
		$return = $_SERVER['QUERY_STRING'] ?? '';

		if (!freshports_search_gate_has_cookie()) {
			freshports_search_gate_challenge($return);
		}
	}

	# only ever return to the search page; the query string cannot change that
	$location = '/search.php';
	if (is_string($return) && $return != '' && !preg_match('/[\r\n]/', $return)) {
		$location .= '?' . $return;
	}

	header('Location: ' . $location, true, 303);
