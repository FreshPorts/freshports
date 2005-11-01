<?php
	#
	# $Id: ports-interactive.php,v 1.1.2.1 2005-11-01 22:54:30 dan Exp $
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

	$page->setTitle('Interactive ports');
	$page->setDescription('These are the interactive ports');

	$page->setSQL("ports.is_interactive <> ''", $User->id);

	$page->display();
?>