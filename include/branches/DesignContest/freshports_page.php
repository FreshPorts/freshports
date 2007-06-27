<?php
	#
	# $Id: freshports_page.php,v 1.2 2006-12-17 11:55:53 dan Exp $
	#
	# Copyright (c) 2005-2006 DVL Software Limited
	#

	set_include_path('/usr/local/share/pear');
	require_once("HTML/Page2.php");

class freshports_page extends HTML_Page2 {

	var $_ShowAds           = 1;
	var $_BannerAd          = 1;
	var $_ShowAnnouncements = 0;
	var $_ShowLogo          = 1;

	var $_db;
	var $_debug             = 0;

	function freshports_page($attributes = array()) {

		GLOBAL $ShowAds;

		$this->_ShowAds = $ShowAds;
		$this->assignDefaultAttributes($attributes);

		$this->HTML_Page2($attributes);

		$this->setMetaData('author',           'Dan Langille');
		$this->setMetaData('description',      'FreshPorts - new ports, applications');
		$this->setMetaData('keywords',         'FreeBSD, index, applications, ports');

		$this->setMetaData('Pragma',           'no-cache', TRUE);
		$this->setMetaData('Cache-Control',    'no-cache', TRUE);
		$this->setMetaData('Pragma-directive', 'no-cache', TRUE);
		$this->setMetaData('cache-directive',  'no-cache', TRUE);
		$this->setMetaData('Expires',          '0',        TRUE);

		$this->setMetaData('robots', 'noarchive');
		$this->setMetaData('robots', 'noindex');

		$this->addStyleSheet('/css/freshports.css');

		$this->addFavicon('/favicon.ico');

		$this->setBodyAttributes(array('BGCOLOR' => '#FFFFFF', 'TEXT' => '#000000'));
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

		$HTML .= freshports_MainTable() . "\n<tr><td width='100%' valign='top'>\n" .
		         freshports_MainContentTable() . "\n<tr>\n" .
		         freshports_PageBannerText($this->_title);

		$this->prependBodyContent($HTML);

		$this->addBodyContent("\n</table><td valign=\"top\">" . freshports_SideBar() . "</td></tr></table>\n" . freshports_ShowFooter());

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
}
