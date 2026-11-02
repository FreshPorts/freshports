<?php
	#
	# $Id: htmlify.php,v 1.6 2007-10-23 19:01:37 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.local.php');

#
# The code below was donated by Steve Kacsmark <stevek@guide.chi.il.us>.
# With modifications by Marcin Gryszkalis <mg@fork.pl>.
#


function freshports_IsEmailValid($email) {
	# see also convertMail
	if (preg_match("/^[a-z0-9\._+-]+@[a-z0-9\._-]+$/i", $email)) {
		return TRUE;
	} else {
		return FALSE;
	}
}



function pr2link($Arr) {
	return preg_replace("/(\w+\/)?(\d+)/", 
			    "<a href=\"http://bugs.FreeBSD.org/\\2\">\\1\\2</a>",
			    $Arr[0]);  
}

function mail2link($Arr) {
	#
	# in an attempt to reduce spam, encode the mailto
	# so the spambots get rubbish, but it works OK in
	# the browser.
	#

	$addr = $Arr[0];

	$addr = "<a href=\"mailto:$addr\">$addr</a>";

	return $addr;
}

function url2link($Arr) {
	#
	# URLs will be truncated if they are too long. But only
	# the visible part
	#
	$html  = $Arr[1];

	#
	# this changes anything like &amp; back to &
	#
	$new_html  = html_entity_decode($html);
	$new_html  = htmlentities($new_html);

	return '<a href="' . $new_html . '" REL="NOFOLLOW">' . $html . '</a>' . $Arr[3];
}

function url_shorten($Arr) {
	#
	# URLs will be truncated if they are too long. But only
	# the visible part
	#
	$html = $Arr[1];
#	syslog(LOG_NOTICE, "start");
#	syslog(LOG_NOTICE, "0 - $Arr[0]");
#	syslog(LOG_NOTICE, "1 - $Arr[1]");
#	syslog(LOG_NOTICE, "2 - $Arr[2]");
#	syslog(LOG_NOTICE, "3 - $Arr[3]");
#	syslog(LOG_NOTICE, "4 - $Arr[4]");
#	syslog(LOG_NOTICE, "5 - $Arr[5]");
#	syslog(LOG_NOTICE, "6 - $Arr[6]");
#	syslog(LOG_NOTICE, "finish");

	$URL = $Arr[5];
	if (URL2LINK_CUTOFF_LEVEL > 0 && strlen($URL) > URL2LINK_CUTOFF_LEVEL) {
		$URL = substr($URL, 0, URL2LINK_CUTOFF_LEVEL - 5) . "(...)";
	}

	return $Arr[1] . '">' . $URL . '</a>';
}

# I couldn't find a conditional which would allow optional use
require_once($_SERVER['DOCUMENT_ROOT'] .  '/../vendor/autoload.php');
use VStelmakh\UrlHighlight\Encoder\HtmlSpecialcharsEncoder;
use VStelmakh\UrlHighlight\Format;
use VStelmakh\UrlHighlight\Highlighter\CallbackHighlighter;
use VStelmakh\UrlHighlight\Url;
use VStelmakh\UrlHighlight\UrlHighlight;

#
# url-highlight 4.x from the www/php-url-highlight port. There is no composer
# autoloader for it, so register our own, ahead of the vendor one, which
# would otherwise load the bundled 3.x classes of the same name.
#
if (!defined('URL_HIGHLIGHT_PORT_DIR')) {
	define('URL_HIGHLIGHT_PORT_DIR', '/usr/local/share/pear/VStelmakh/UrlHighlight/');
}

function url_highlight_port_autoload() {
	static $registered = false;
	if ($registered) {
		return;
	}

	spl_autoload_register(function ($class) {
		$prefix = 'VStelmakh\\UrlHighlight\\';
		if (str_starts_with($class, $prefix)) {
			require URL_HIGHLIGHT_PORT_DIR . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
		}
	}, true, true);
	$registered = true;
}

require_once($_SERVER['DOCUMENT_ROOT'] .  '/../include/lib_autolink/lib_autolink.php');

#
# $Use_Port: null follows HTMLIFY_USE_URL_HIGHLIGHT_PORT from constants.local.php;
# true or false forces it, so the test pages can compare old and new
#
function htmlify($input, $Process_PRs = false, $Use_Port = null) {
	$Use_Port ??= defined('HTMLIFY_USE_URL_HIGHLIGHT_PORT') && HTMLIFY_USE_URL_HIGHLIGHT_PORT;

	#
	# we have our old code and this new stuff: UrlHighlight
	#
	if ($Use_Port) {
		url_highlight_port_autoload();

		# built once per request; it loads the list of top-level domains
		static $urlHighlight = null;
		static $highlighter  = null;
		$urlHighlight ??= new UrlHighlight();

		# same as url2link() and url_shorten(): NOFOLLOW, and truncate only the visible part
		$highlighter ??= new CallbackHighlighter(function (Url $url) {
			$text = $url->full;
			if (URL2LINK_CUTOFF_LEVEL > 0 && mb_strlen($text) > URL2LINK_CUTOFF_LEVEL) {
				$text = mb_substr($text, 0, URL2LINK_CUTOFF_LEVEL - 5) . '(...)';
			}

			return '<a href="' . htmlspecialchars($url->toHref(), ENT_QUOTES | ENT_HTML5) . '"' .
				($url->isEmail() ? '' : ' REL="NOFOLLOW"') . '>' .
				htmlspecialchars($text, ENT_QUOTES | ENT_HTML5) . '</a>';
		});

		# callers pass text already escaped by _forDisplay(); do not escape it again
		$htmlified = $urlHighlight->highlight($input, $highlighter, Format::HtmlEncoded);
	} elseif (defined('HTMLIFY_USE_URL_HIGHLIGHT') && HTMLIFY_USE_URL_HIGHLIGHT) {
		$encoder = new HtmlSpecialcharsEncoder();
		$urlHighlight = new UrlHighlight(null, null, $encoder);

		$escaped = htmlentities($input);
		$htmlified = $urlHighlight->highlightUrls($escaped);
#		$htmlified = $urlHighlight->highlightUrls($input);
	} elseif (defined('HTMLIFY_USE_LIB_AUTOLINK') && HTMLIFY_USE_LIB_AUTOLINK) {
		$GLOBALS['autolink_options']['strip_protocols'] = false;
		$htmlified = autolink($input, URL2LINK_CUTOFF_LEVEL);
#		$htmlified = autolink_email($htmlified);
	} else {
		#
		# URLs to test with: http://www.freshports.org/commit.php?message_id=200206232029.g5NKT1O13181@freefall.freebsd.org
		#
		$del_t = array("&quot;", "&#34;", "&gt;", "&#62;", "\/\.\s","\)", ",\s", "\s", "$");
		$delimiters = "(".join("|",$del_t).")";

		$htmlified = preg_replace_callback("/((http|ftp|https):\/\/.*?)($delimiters)/i",                    'url2link',    $input);
		$htmlified = preg_replace_callback("/(<a href=(\"|')(http|ftp|https):\/\/.*?)(\">|'>)(.*?)<\/a>/i", 'url_shorten', $htmlified);
		$htmlified = preg_replace_callback("/([\w+=\-.!]+@[\w\-]+(\.[\w\-]+)+)/",                           'mail2link',   $htmlified);
	}

	# this is our own code
	if ($Process_PRs) {
		$htmlified = preg_replace_callback("/\bPR[:\#]?\s+(\d+)([,\s\nand]*(\d+))*/",                      'pr2link',     $htmlified);
		$htmlified = preg_replace_callback("/[\w\s]+((advocacy|alpha|bin|conf|docs|gnu|i386|ia64|java|kern|misc|ports|powerpc|sparc64|standards|www)\/\d+)/", 'pr2link', $htmlified);
	}

	return $htmlified;
}


#
# The code above was donated by Steve Kacsmark <stevek@guide.chi.il.us>.
# With modifications by Marcin Gryszkalis <mg@fork.pl>.
#


