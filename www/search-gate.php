<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# The challenge page from include/search-gate.php posts here.  With a valid
	# token, set the search_ok cookie.  Either way, go back to the search; without
	# the cookie, the visitor sees the challenge page again.
	# See https://github.com/FreshPorts/freshports/issues/692
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.local.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/search-gate.php');

	$token  = $_POST['token']  ?? '';
	$return = $_POST['return'] ?? '';

	if (freshports_search_gate_enabled() && freshports_search_gate_token_is_valid($token)) {
		freshports_search_gate_set_cookie();
	}

	# only ever return to the search page; the query string cannot change that
	$location = '/search.php';
	if (is_string($return) && $return != '' && !preg_match('/[\r\n]/', $return)) {
		$location .= '?' . $return;
	}

	header('Location: ' . $location, true, 303);
