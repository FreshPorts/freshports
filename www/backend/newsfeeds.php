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

	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['SERVER_NAME']);

	$URL  = "http://$ServerName/backend/news.php";
	$HREF = "<A HREF=\"$URL\">$URL</A>";

	$page->addBodyContent('
	<OL>
	<LI>An RSS feed : ' . $HREF . '
	<p>Take your pick of different formats:');
	
	$URL  = "http://$ServerName/backend/";
	$HREF = "<A HREF=\"$URL\">$URL</A>";
	$page->addBodyContent($HREF . '
	
	<p>This RSS feed takes the following optional parameters:
	<ul>
	<li><b>MaxArticles</b> : number of ports to report upon (min 1, max 20, default 20)
	<li><b>date=1</b> : show the commit date
	<li><b>committer=1</b> : show the committer name
	<li><b>time=1</b> : show the commit time
	</ul>
	<p>
	A sample URL is ' . $URL . '?MaxArticles=10&amp;committer=1&amp;time=1&amp;date=1
	</p>

	<P>
	<B>NOTE:</B> - As of 13 November 2003, these parameters are no longer available.  The
	values they obtained are now supplied by default.
	</P>
	</LI>');

	$URL  = "http://$ServerName/backend/sidebar.php";
	$HREF = "<A HREF=\"$URL\">$URL</A>";

	$page->addBodyContent('
	<LI>A Netscape 6, SideBar type feed : ' . $HREF . '.  This can be added
		to your browser using the button in the right hand column of this page.</LI>');

	$URL  = "http://$ServerName/backend/ports-new.php";
	$HREF = "<A HREF=\"$URL\">$URL</A>";

	$page->addBodyContent('
	<li><p>An RSS feed that lists only new ports:  ' . $HREF . ' </p></li>

	<li><p>A Personal News feed for each of your watch lists. Look for the link under
		the <code>Login</code> box after you have logged in.</li>

	<li><p>The blog for this website, <a href="http://news.freshports.org/">FreshPorts News</a>.

	</OL>');

	$page->display();
?>