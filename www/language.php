<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Remember the visitor's choice of language, then send them back where they came from.
	# See https://github.com/FreshPorts/freshports/issues/678
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/i18n.php');

	$locale = $_REQUEST['lang'] ?? '';

	if (is_string($locale) && array_key_exists($locale, freshports_i18n_available_locales())) {
		setcookie(LANGUAGE_COOKIE_NAME, $locale, array(
			'expires'  => time() + LANGUAGE_COOKIE_EXPIRES,
			'path'     => '/',
			'secure'   => ($_SERVER['REQUEST_SCHEME'] ?? '') == 'https',
			'httponly' => true,
			'samesite' => 'Lax',
		));
	}

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
