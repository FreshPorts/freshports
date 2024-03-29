<?php
	#
	# $Id: htmlify.php,v 1.6 2007-10-23 19:01:37 dan Exp $
	#
	# Copyright (c) 1998-2007 DVL Software Limited
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
use VStelmakh\UrlHighlight\UrlHighlight;

require_once($_SERVER['DOCUMENT_ROOT'] .  '/../include/lib_autolink/lib_autolink.php');

function htmlify($input, $Process_PRs = false) {
	#
	# we have our old code and this new stuff: UrlHighlight
	#
	if (defined('HTMLIFY_USE_URL_HIGHLIGHT') && HTMLIFY_USE_URL_HIGHLIGHT) {
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


