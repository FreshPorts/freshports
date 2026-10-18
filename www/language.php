<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Remember the visitor's choice of language, then send them back where they came from.
	# See https://github.com/FreshPorts/freshports/issues/678
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/i18n.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/redirect-back.php');

	# not $_REQUEST: depending on request_order, the existing lang cookie would override the choice
	$locale = $_POST['lang'] ?? $_GET['lang'] ?? '';

	if (is_string($locale) && array_key_exists($locale, freshports_i18n_available_locales())) {
		setcookie(LANGUAGE_COOKIE_NAME, $locale, array(
			'expires'  => time() + LANGUAGE_COOKIE_EXPIRES,
			'path'     => '/',
			'secure'   => ($_SERVER['REQUEST_SCHEME'] ?? '') == 'https',
			'httponly' => true,
			'samesite' => 'Lax',
		));
	}

	freshports_redirect_back();
