<?php
	#
	# $Id: newsfeeds.php,v 1.2 2006-12-17 12:06:22 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports_page.php');

	$page = new freshports_page();

	$page->setDebug(0);

	$page->setDB($db);

	$page->setTitle(_('Newsfeeds'));

	$page->addBodyContent('
	</tr><tr><td>
	' . _('We have five newsfeeds:') . '
	');

	$Protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['HTTP_HOST']);

	$URL  = "$Protocol://$ServerName/backend/news.php";
	$HREF = "<a href=\"$URL\">$URL</a>";

	$page->addBodyContent('
	<ol>
	<li>' . sprintf(_('An RSS feed : %s'), $HREF) . '
	<p>' . _('Take your pick of different formats:'));
	
	$URL  = "$Protocol://$ServerName/backend/";
	$HREF = "<a href=\"$URL\">$URL</a>";
	$page->addBodyContent($HREF . '
	
	<p>' . _('This RSS feed takes the following optional parameters:') . '</p>
	<ul>
	<li><b>flavor=new</b> : ' . _('show only new ports (ignores <b>branch</b>).') . '</li>
	<li><b>flavor=broken</b> : ' . _('show only new ports (ignores <b>branch</b>).') . '</li>
	<li><b>flavor=vuln</b> : ' . _('show only vuln ports (branches should work, let me know if they do not).') . '</li>
	<li><b>branch=2018Q3</b> : ' . _('show only commits on that branch. If not specified, defaults to <b>head</b>.') . '
	</ul>
	<p>
	' . _('Sample URLs include:') . '
	</p>
	<ol>
	<li>' . $URL . 'html.php?branch=2018Q4</li>
	<li>' . $URL . 'html.php?branch=quarterly</li>
	<li>' . $URL . 'html.php?flavor=broken</li>
	<li>' . $URL . 'html.php?flavor=new</li>
	</ol>

	</li>');

	$URL  = "$Protocol://$ServerName/backend/ports-new.php";
	$HREF = "<a href=\"$URL\">$URL</a>";

	$page->addBodyContent('
	<li><p>' . sprintf(_('An RSS feed that lists only new ports:  %s'), $HREF) . ' </p></li>

	<li><p>' . _('A Personal News feed for each of your watch lists. Look for the link under the <code>Watch Lists</code> box after you have logged in.') . '</li>

	<li><p>' . _('The blog for this website, <a href="https://news.freshports.org/">FreshPorts News</a>.') . '

	</ol>');

	$page->display();
