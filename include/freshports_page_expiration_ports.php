<?php
	#
	# $Id: freshports_page_expiration_ports.php,v 1.2 2006-12-17 11:55:53 dan Exp $
	#
	# Copyright (c) 2005-2026 Dan Langille
	#


	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports_page_list_ports.php');

class freshports_page_expiration_ports extends freshports_page_list_ports {

	function getShowCategoryHeaders() {
		$sort = $this->getSort();

		switch ($sort) {
			case 'port':
			case 'expiration_date':
				$ShowCategoryHeaders = 0;
				break;

			default:
				$ShowCategoryHeaders = 1;
		}

		return $ShowCategoryHeaders;
	}

	function getSort() {
		$HTML = '';

		if (IsSet( $_REQUEST["sort"])) {
			$sort = $_REQUEST["sort"];
		} else {
			$sort = '';
		}

		switch ($sort) {
			case 'expiration_date':
				$sort = 'expiration_date';
				break;

			case 'port':
				$sort = 'port';
				break;

			default:
				$sort ='category, port';
		}

		return $sort;
	}


	function getSortedbyHTML() {
		$HTML = '';

		$sort = $this->getSort();

		switch ($sort) {
			case 'expiration_date':
				$HTML .= sprintf(_('This page is sorted by expiration date.  You can sort by %1$s, or by %2$s.'),
							'<a href="' . $_SERVER["PHP_SELF"] . '?sort=category">' . _('category') . '</a>',
							'<a href="' . $_SERVER["PHP_SELF"] . '?sort=port">' . _('port') . '</a>');
				break;

			case 'port':
				$HTML .= sprintf(_('This page is sorted by port.  You can sort by %1$s, or by %2$s.'),
							'<a href="' . $_SERVER["PHP_SELF"] . '?sort=category">' . _('category') . '</a>',
							'<a href="' . $_SERVER["PHP_SELF"] . '?sort=expiration_date">' . _('expiration date') . '</a>');
				break;

			default:
				$HTML .= sprintf(_('This page is sorted by category.  You can sort by %1$s, or by %2$s.'),
							'<a href="' . $_SERVER["PHP_SELF"] . '?sort=expiration_date">' . _('expiration date') . '</a>',
							'<a href="' . $_SERVER["PHP_SELF"] . '?sort=port">' . _('port') . '</a>');
		}

		return $HTML;
	}
}
