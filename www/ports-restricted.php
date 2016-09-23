<?php
	#
	# $Id: ports-restricted.php,v 1.2 2006-12-17 12:06:16 dan Exp $
	#
	# Copyright (c) 1998-2005 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports_page_list_ports.php');

	# not using this yet, but putting it in here.	
	if (IsSet($_REQUEST['branch'])) {
		$Branch = htmlspecialchars($_REQUEST['branch']);
	} else {
		$Branch = BRANCH_HEAD;
	}
	
	$attributes = array('branch' => $Branch);

	$page = new freshports_page_list_ports($attributes);
	
	$page->setDebug(0);

	$page->setDB($db);

	$page->setTitle('Restricted ports');
	$page->setDescription('These are the restricted ports');


	$page->setSQL("ports.restricted <> ''", $User->id);

	$page->display();
