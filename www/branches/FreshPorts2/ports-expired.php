<?php
	#
	# $Id: ports-expired.php,v 1.1.2.2 2005-06-26 23:40:19 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports_page_list_ports.php');

	$page = new freshports_page_list_ports();

	$page->setDB($db);

	$page->setTitle('Ports that have expired');
	$page->setDescription('These ports are past their expiration date');

	$page->setSQL("ports.expiration_date is not null and ports.expiration_date <= CURRENT_DATE", $User->id);

	$page->display();
?>