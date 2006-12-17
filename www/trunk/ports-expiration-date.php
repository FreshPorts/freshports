<?php
	#
	# $Id: ports-expiration-date.php,v 1.2 2006-12-17 12:06:14 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports_page_expiration_ports.php');

	$page = new freshports_page_expiration_ports();

	$page->setDB($db);

	$page->setTitle('Ports with an expiration date');
	$page->setDescription('These ports have an expiration date, after which they may be removed from the tree');

	$page->setSQL("ports.expiration_date is not null", $User->id);

	$page->display();
?>