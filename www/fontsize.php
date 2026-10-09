<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Remember the visitor's choice of text size, then send them back where they came from.
	# See https://github.com/FreshPorts/freshports/issues/690
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/fontsize.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/redirect-back.php');

	# not $_REQUEST: depending on request_order, the existing fontsize cookie would override the choice
	$size = $_POST['fontsize'] ?? $_GET['fontsize'] ?? '';

	if (is_string($size) && array_key_exists($size, freshports_fontsize_available())) {
		# the default needs no cookie; removing it lets the browser's text size apply again
		$expires = $size == FONTSIZE_DEFAULT ? time() - 3600 : time() + FONTSIZE_COOKIE_EXPIRES;
		setcookie(FONTSIZE_COOKIE_NAME, $size == FONTSIZE_DEFAULT ? '' : $size, array(
			'expires'  => $expires,
			'path'     => '/',
			'secure'   => ($_SERVER['REQUEST_SCHEME'] ?? '') == 'https',
			'httponly' => true,
			'samesite' => 'Lax',
		));
	}

	freshports_redirect_back();
