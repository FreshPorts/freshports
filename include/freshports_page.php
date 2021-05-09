<?php
	#
	# $Id: freshports_page.php,v 1.2 2006-12-17 11:55:53 dan Exp $
	#
	# Copyright (c) 2005-2006 DVL Software Limited
	#

	set_include_path('/usr/local/share/pear');
	require_once("HTML/Page2.php");
	require_once('constants.php');

class freshports_page extends HTML_Page2 {

	var $_ShowAds           = 0;
	var $_BannerAd          = 1;
	var $_ShowAnnouncements = 0;
	var $_ShowLogo          = 1;

	var $_db;
	var $_debug             = 0;

	function __construct($attributes = array()) {

		GLOBAL $ShowAds;

		$this->_ShowAds = $ShowAds;
		$this->assignDefaultAttributes($attributes);

		$this->HTML_Page2($attributes);

		$this->setMetaData('author',           COPYRIGHTHOLDER);
		$this->setMetaData('description',      'FreshPorts - new ports, applications');
		$this->setMetaData('keywords',         'FreeBSD, index, applications, ports');

		$this->setMetaData('Pragma',           'no-cache', TRUE);
		$this->setMetaData('Cache-Control',    'no-cache', TRUE);
		$this->setMetaData('Pragma-directive', 'no-cache', TRUE);
		$this->setMetaData('cache-directive',  'no-cache', TRUE);
		$this->setMetaData('Expires',          '0',        TRUE);

		$this->setMetaData('robots', 'noarchive');
		$this->setMetaData('robots', 'noindex');

		$version = substr(hash_file('sha1', $_SERVER['DOCUMENT_ROOT'] . '/css/freshports.css'), 0, 8);
		$this->addStyleSheet('/css/freshports.css?v=' . $version);

		$this->addFavicon('/favicon.ico');
	}

	function setDB($db) {
		$this->_db = $db;
	}

	function toHTML() {
		$HTML = '';

		if ($this->_ShowLogo) {
			$HTML .= freshports_Logo();
		}

		if ($this->_ShowAnnouncements) {

			$Announcement = new Announcement($this->$_db);

			$NumRows = $Announcement->FetchAllActive();
			if ($NumRows > 0) {
				$HTML .= DisplayAnnouncements($Announcement);
			}
		}

		$HTML .= freshports_MainTable() . "\n<tr><td class=\"content\">\n" .
		         freshports_MainContentTable() . "\n<tr>\n" .
		         freshports_PageBannerText($this->_title);

		$this->prependBodyContent($HTML);

		$this->addBodyContent("\n</table><td class=\"sidebar\">" . freshports_SideBar() . "</td></tr></table>\n" . freshports_ShowFooter());

		return parent::toHTML();
	}

	function assignDefaultAttributes(&$attributes) {
		# if no value, give it a default value
		if (!$attributes || !is_array($attributes)) {

			$attributes['doctype'] = 'HTML 4.01 Transitional';
		}

		if (!IsSet($attributes['doctype'])) {
			$attributes['doctype'] = 'HTML 4.01 Transitional';
		}

		if (!IsSet($attributes['cache'])) {
			$attributes['cache'] = 'false';
		}

		if (!IsSet($attributes['prolog'])) {
			$attributes['prolog'] = 'false';
		}
	}

	function getDebug() {
		return $this->_debug;
	}

	function setDebug($Debug) {
		$this->_debug = $Debug;
	}

	function _getDoctype() {
		// HTML_Page2 generates inconsistent doctype with the rest of the site
		// Since we're in quirks mode this breaks the styles for some pages
		return HTML_DOCTYPE;
	}
}
