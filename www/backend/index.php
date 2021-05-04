<?php
	#
	# $Id: faq.php,v 1.7 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	freshports_Start('FreshPorts News Feeds',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	GLOBAL $FreshPortsName;
	GLOBAL $FreshPortsSlogan;

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>


<tr>
	<?php echo freshports_PageBannerText("FreshPorts News Feeds"); ?>
</tr>
<TR><td class="content">
<h1>FreshPorts News Feeds</h1>

<p>
We have a number of newsfeeds for your consumption:

<?php

#echo phpinfo();
#exit;

$Protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';

$Hostname = $_SERVER['HTTP_HOST'];

?>

<ol>
<li><a href="atom0.3.php">ATOM 0.3</a>
<li><a href="html.php">HTML</a>
<li><a href="js.php">Javascript</a>
<li><a href="mbox.php">mbox</a>
<li><a href="opml.php">opml</a>
<li><a href="pie0.1.php">PIE 0.1</a>
<li><a href="rss0.91.php">RSS 0.91</a> [ <a href="https://validator.w3.org/feed/check.cgi?url=<?php echo rawurlencode("{$Protocol}://{$Hostname}/backend/rss0.91.php"); ?>">RSS Feed validator</a> ]
<li><a href="rss1.0.php">RSS 1.0</a>   [ <a href="https://validator.w3.org/feed/check.cgi?url=<?php echo rawurlencode("{$Protocol}://{$Hostname}/backend/rss1.0.php"); ?>">RSS Feed validator</a>  ]
<li><a href="rss2.0.php">RSS 2.0</a>   [ <a href="https://validator.w3.org/feed/check.cgi?url=<?php echo rawurlencode("{$Protocol}://{$Hostname}/backend/rss2.0.php"); ?>">RSS Feed validator</a>  ]
</ol>

<p>
The above feeds are created using <a href="https://github.com/flack/UniversalFeedCreator">UniversalFeedCreator</a>.
</table>
</TD>
  <td class="sidebar">
  <?
  echo freshports_SideBar();
  ?>
  </td>
</TR>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
