<?php
	#
	# $Id: newsfeeds.php,v 1.2 2006-12-17 12:06:22 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports_page.php');

	$page = new freshports_page();

	$page->setDB($db);

	$page->setTitle('Newsfeeds');

	$page->addBodyContent('
	</tr><TR><TD valign="top">
	We have five newsfeeds:
	');

	$Protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['HTTP_HOST']);

	$URL  = "$Protocol://$ServerName/backend/news.php";
	$HREF = "<A HREF=\"$URL\">$URL</A>";

	$page->addBodyContent('
	<OL>
	<LI>An RSS feed : ' . $HREF . '
	<p>Take your pick of different formats:');
	
	$URL  = "$Protocol://$ServerName/backend/";
	$HREF = "<A HREF=\"$URL\">$URL</A>";
	$page->addBodyContent($HREF . '
	
	<p>This RSS feed takes the following optional parameters:
	<ul>
	<li><b>flavor=new</b> : show only new ports (ignores <b>branch</b>).</li>
	<li><b>flavor=broken</b> : show only new ports (ignores <b>branch</b>).</li>
	<li><b>flavor=vuln</b> : show only vuln ports (branches should work, let me know if they do not).</li>
	<li><b>branch=2018Q3</b> : show only commits on that branch. If not specified, defaults to <b>head</b>.
	</ul>
	<p>
	Sample URLs include:
	<ol>
	<li>' . $URL . 'html.php?branch=2018Q4</li>
	<li>' . $URL . 'html.php?flavor=broken</li>
	<li>' . $URL . 'html.php?flavor=new</li>
	</ol>
	</p>

	</LI>');

	$URL  = "$Protocol://$ServerName/backend/ports-new.php";
	$HREF = "<A HREF=\"$URL\">$URL</A>";

	$page->addBodyContent('
	<li><p>An RSS feed that lists only new ports:  ' . $HREF . ' </p></li>

	<li><p>A Personal News feed for each of your watch lists. Look for the link under
		the <code>Login</code> box after you have logged in.</li>

	<li><p>The blog for this website, <a href="https://news.freshports.org/">FreshPorts News</a>.

	</OL>');

	$page->display();
