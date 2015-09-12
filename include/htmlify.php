<?php
	#
	# $Id: htmlify.php,v 1.6 2007-10-23 19:01:37 dan Exp $
	#
	# Copyright (c) 1998-2007 DVL Software Limited
	#

define('HTMLIFY_PROCESS_PRS', true);

#
# The code below was donated by Steve Kacsmark <stevek@guide.chi.il.us>.
# With modifications by Marcin Gryszkalis <mg@fork.pl>.
#


function freshports_IsEmailValid($email) {
	# see also convertMail
	if (eregi("^[a-z0-9\._+-]+@[a-z0-9\._-]+$", $email)) {
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

	$addr = "<A HREF=\"mailto:$addr\">$addr</A>";

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

	return '<A HREF="' . $new_html . '">' . $html . '</A>' . $Arr[3];
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

function htmlify($String, $Process_PRs = false) {

#
# URLs to test with: http://www.freshports.org/commit.php?message_id=200206232029.g5NKT1O13181@freefall.freebsd.org
#
	$del_t = array("&quot;", "&#34;", "&gt;", "&#62;", "\/\.\s","\)", ",\s", "\s", "$");
	$delimiters = "(".join("|",$del_t).")";

	$String = preg_replace_callback("/((http|ftp|https):\/\/.*?)($delimiters)/i",                    'url2link',    $String);
	$String = preg_replace_callback("/(<a href=(\"|')(http|ftp|https):\/\/.*?)(\">|'>)(.*?)<\/a>/i", 'url_shorten', $String);
	$String = preg_replace_callback("/([\w+=\-.!]+@[\w\-]+(\.[\w\-]+)+)/",                           'mail2link',   $String);
	if ($Process_PRs) {
		$String = preg_replace_callback("/\bPR[:\#]?\s*(\d+)([,\s\nand]*(\d+))*/",                      'pr2link',     $String);
		$String = preg_replace_callback("/[\w\s]+((advocacy|alpha|bin|conf|docs|gnu|i386|ia64|java|kern|misc|ports|powerpc|sparc64|standards|www)\/\d+)/", 'pr2link', $String);
	}

	return $String;
}


#
# The code above was donated by Steve Kacsmark <stevek@guide.chi.il.us>.
# With modifications by Marcin Gryszkalis <mg@fork.pl>.
#


