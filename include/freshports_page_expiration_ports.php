<?php
	#
	# $Id: freshports_page_expiration_ports.php,v 1.2 2006-12-17 11:55:53 dan Exp $
	#
	# Copyright (c) 2005-2006 DVL Software Limited
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
				$HTML .= 'sorted by expiration date.  You can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=category">category</a>' .
							', or by <a href="' . $_SERVER["PHP_SELF"] . '?sort=port">port</a>.';
				break;

			case 'port':
				$HTML .= 'sorted by port.  You can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=category">category</a>' .
							', or by <a href="' . $_SERVER["PHP_SELF"] . '?sort=expiration_date">expiration date</a>.';
				break;

			default:
				$HTML .= 'sorted by category.  You can sort by <a href="' . $_SERVER["PHP_SELF"] . '?sort=expiration_date">expiration date</a>' . 
							', or by <a href="' . $_SERVER["PHP_SELF"] . '?sort=port">port</a>.';
		}

		return $HTML;
	}
}
