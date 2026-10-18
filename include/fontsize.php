<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Let any visitor, logged in or not, choose the text size - see
	# https://github.com/FreshPorts/freshports/issues/690
	#
	# The choice is kept in a cookie, set by www/fontsize.php.  It sets the root
	# font size; freshports.css gives every font size in rem, so all the text
	# scales with it.  Without the cookie, the browser's own text size is used.
	#

define('FONTSIZE_COOKIE_NAME',    'fontsize');
define('FONTSIZE_COOKIE_EXPIRES', 60 * 60 * 24 * 365); # one year
define('FONTSIZE_DEFAULT',        'normal');

#
# The sizes offered, as 'cookie value' => percentage of the browser's text size.
# The labels are in freshports_fontsize_picker(), so they can be translated.
#
function freshports_fontsize_available() {
	return array(
		'smallest' =>  75,
		'small'   =>  87.5,
		'normal'  => 100,
		'large'   => 112.5,
		'larger'  => 125,
		'largest' => 150,
	);
}

function freshports_fontsize_choose() {
	if (IsSet($_COOKIE[FONTSIZE_COOKIE_NAME]) && is_string($_COOKIE[FONTSIZE_COOKIE_NAME])
	    && array_key_exists($_COOKIE[FONTSIZE_COOKIE_NAME], freshports_fontsize_available())) {
		return $_COOKIE[FONTSIZE_COOKIE_NAME];
	}

	return FONTSIZE_DEFAULT;
}

#
# The CSS for the chosen size, or '' for the default, which leaves the
# browser's text size alone.
#
# --fontsize-scale lets freshports.css undo the choice for the parts of the
# page which should not scale - see issue #702
#
function freshports_fontsize_css() {
	$size = freshports_fontsize_choose();
	if ($size == FONTSIZE_DEFAULT) {
		return '';
	}

	$sizes = freshports_fontsize_available();

	return 'html { font-size: ' . $sizes[$size] . '%; --fontsize-scale: ' . ($sizes[$size] / 100) . '; }';
}

#
# A small form to choose the text size, next to the language picker.
#
function freshports_fontsize_picker() {
	$labels = array(
		'smallest' => _('Smallest'),
		'small'   => _('Small'),
		'normal'  => _('Normal'),
		'large'   => _('Large'),
		'larger'  => _('Larger'),
		'largest' => _('Largest'),
	);

	$current = freshports_fontsize_choose();

	$HTML = '<form class="fontsize-picker" action="/fontsize.php" method="post">';
	$HTML .= '<label for="fontsize-picker">' . _('Text size') . '</label> ';
	$HTML .= '<select name="fontsize" id="fontsize-picker">';
	foreach (array_keys(freshports_fontsize_available()) as $size) {
		$HTML .= '<option value="' . $size . '"' . ($size == $current ? ' selected' : '') . '>'
		       . htmlspecialchars($labels[$size]) . '</option>';
	}
	$HTML .= '</select> ';
	$HTML .= '<input type="submit" value="' . htmlspecialchars(_('Change')) . '">';
	$HTML .= '</form>';

	return $HTML;
}
