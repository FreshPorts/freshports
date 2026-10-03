<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Translation support - see https://github.com/FreshPorts/freshports/issues/678
	#
	# Strings are wrapped with gettext's _(), and translations live in
	# locale/<locale>/LC_MESSAGES/freshports.mo (built from the .po files by
	# scripts/i18n-compile.sh).
	#
	# The language is chosen from, in order:
	#   - the language cookie, set by www/language.php
	#   - the browser's Accept-Language header
	#   - English
	#
	# A language is offered only once its .mo file exists, so adding a
	# translation needs no code change.
	#

define('I18N_DOMAIN',               'freshports');
define('I18N_LOCALE_DIRECTORY',     $_SERVER['DOCUMENT_ROOT'] . '/../locale');
define('I18N_DEFAULT_LOCALE',       'en');
define('LANGUAGE_COOKIE_NAME',      'lang');
define('LANGUAGE_COOKIE_EXPIRES',   60 * 60 * 24 * 365); # one year

# without the gettext extension, everything is displayed in English
if (!function_exists('_')) {
	function _($message) {
		return $message;
	}
}

if (!function_exists('ngettext')) {
	function ngettext($singular, $plural, $count) {
		return $count == 1 ? $singular : $plural;
	}
}

#
# The locales we can display, as 'zh_CN' => 'Native name'.
# English is always present; the others are those with a compiled translation.
#
function freshports_i18n_available_locales() {
	static $locales = null;

	if ($locales === null) {
		$locales = array(I18N_DEFAULT_LOCALE => 'English');

		if (function_exists('bindtextdomain')) {
			foreach (glob(I18N_LOCALE_DIRECTORY . '/*/LC_MESSAGES/' . I18N_DOMAIN . '.mo') ?: array() as $mo) {
				$locale = basename(dirname($mo, 2));
				$locales[$locale] = freshports_i18n_native_name($locale);
			}
		}
	}

	return $locales;
}

#
# The name of a language, in that language, for the language picker.
# Add an entry here when a new translation arrives.
#
function freshports_i18n_native_name($locale) {
	$names = array(
		'de'    => 'Deutsch',
		'es'    => 'Español',
		'fr'    => 'Français',
		'ja'    => '日本語',
		'ru'    => 'Русский',
		'zh_CN' => '简体中文',
		'zh_TW' => '繁體中文',
	);

	return $names[$locale] ?? $locale;
}

#
# Map a language tag such as 'zh-CN', 'zh-Hans', or 'zh' onto one of our locales.
# Returns null if there is no match.
#
function freshports_i18n_match($tag, $locales) {
	$tag = strtolower(str_replace('_', '-', trim($tag)));
	if ($tag == '') return null;

	# script subtags, which browsers send instead of a region
	$aliases = array('zh-hans' => 'zh-cn', 'zh-sg' => 'zh-cn', 'zh-hant' => 'zh-tw', 'zh-hk' => 'zh-tw');
	$tag = $aliases[$tag] ?? $tag;

	$language = explode('-', $tag)[0];

	$languageMatch = null;
	foreach (array_keys($locales) as $locale) {
		$candidate = strtolower(str_replace('_', '-', $locale));
		if ($candidate == $tag) {
			return $locale;
		}
		# 'zh' matches 'zh_CN', 'en-GB' matches 'en'
		if ($languageMatch === null && explode('-', $candidate)[0] == $language) {
			$languageMatch = $locale;
		}
	}

	return $languageMatch;
}

#
# Pick the best of our locales from an Accept-Language header,
# e.g. 'zh-CN,zh;q=0.9,en;q=0.8'
#
function freshports_i18n_from_accept_language($header, $locales) {
	$preferences = array();
	foreach (explode(',', $header) as $order => $part) {
		$pieces  = explode(';', $part);
		$quality = 1.0;
		foreach (array_slice($pieces, 1) as $parameter) {
			if (preg_match('/^\s*q\s*=\s*([0-9.]+)\s*$/i', $parameter, $matches)) {
				$quality = (float) $matches[1];
			}
		}
		if ($quality > 0) {
			# keep the browser's order for equal quality values
			$preferences[] = array($quality, $order, $pieces[0]);
		}
	}

	usort($preferences, function($a, $b) {
		return $b[0] <=> $a[0] ?: $a[1] <=> $b[1];
	});

	foreach ($preferences as $preference) {
		$locale = freshports_i18n_match($preference[2], $locales);
		if ($locale !== null) {
			return $locale;
		}
	}

	return null;
}

function freshports_i18n_choose_locale() {
	$locales = freshports_i18n_available_locales();

	if (IsSet($_COOKIE[LANGUAGE_COOKIE_NAME]) && is_string($_COOKIE[LANGUAGE_COOKIE_NAME])
	    && array_key_exists($_COOKIE[LANGUAGE_COOKIE_NAME], $locales)) {
		return $_COOKIE[LANGUAGE_COOKIE_NAME];
	}

	if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$locale = freshports_i18n_from_accept_language($_SERVER['HTTP_ACCEPT_LANGUAGE'], $locales);
		if ($locale !== null) {
			return $locale;
		}
	}

	return I18N_DEFAULT_LOCALE;
}

#
# php-fpm workers are reused between requests, so the locale must be set on
# every request, including back to English.
#
# Only LC_MESSAGES is changed. LC_ALL would also alter number and date
# formatting, which the rest of the code does not expect.
#
function freshports_i18n_start() {
	$locale = freshports_i18n_choose_locale();

	if (function_exists('bindtextdomain')) {
		$system_locale = false;
		if ($locale != I18N_DEFAULT_LOCALE) {
			$system_locale = setlocale(LC_MESSAGES, $locale . '.UTF-8', $locale . '.utf8', $locale);
		}

		if ($system_locale === false) {
			# no translation wanted, or the OS lacks this locale: use English
			$locale = I18N_DEFAULT_LOCALE;
			setlocale(LC_MESSAGES, 'C');
		}

		bindtextdomain(I18N_DOMAIN, I18N_LOCALE_DIRECTORY);
		bind_textdomain_codeset(I18N_DOMAIN, 'UTF-8');
		textdomain(I18N_DOMAIN);
	}

	define('FRESHPORTS_LOCALE', $locale);

	if (!headers_sent()) {
		header('Content-Language: ' . freshports_i18n_html_lang());
		header('Vary: Accept-Language, Cookie', false);
	}
}

# the locale in the form used by <html lang="">, e.g. zh-CN
function freshports_i18n_html_lang() {
	return str_replace('_', '-', FRESHPORTS_LOCALE);
}

#
# A small form to choose the language. Shown only if there is a choice to make.
#
function freshports_i18n_language_picker() {
	$locales = freshports_i18n_available_locales();
	if (count($locales) < 2) {
		return '';
	}

	$HTML = '<form class="language-picker" action="/language.php" method="post">';
	$HTML .= '<label for="language-picker">' . _('Language') . '</label> ';
	$HTML .= '<select name="lang" id="language-picker">';
	foreach ($locales as $locale => $name) {
		$HTML .= '<option value="' . htmlspecialchars($locale) . '" lang="' . htmlspecialchars(str_replace('_', '-', $locale)) . '"'
		       . ($locale == FRESHPORTS_LOCALE ? ' selected' : '') . '>' . htmlspecialchars($name) . '</option>';
	}
	$HTML .= '</select> ';
	$HTML .= '<input type="submit" value="' . htmlspecialchars(_('Change')) . '">';
	$HTML .= '</form>';

	return $HTML;
}

freshports_i18n_start();
