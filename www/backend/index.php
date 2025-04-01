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
<tr><td class="content">
<h1>FreshPorts News Feeds</h1>

<h2>Various RSS formats</h2>

        <p>See the next section for optional parameters for these URLs.</p>
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


<h2>Optional parameters for the above formats</h2>

<p>Each of those formats have optional parameters:</p>
<ul>
    <li><b>flavor=new</b> : show only new ports (ignores <b>branch</b>).</li>
    <li><b>flavor=broken</b> : show only new ports (ignores <b>branch</b>).</li>
    <li><b>flavor=vuln</b> : show only vuln ports (branches should work, let me know if they do not).</li>
    <li><b>branch=2018Q3</b> : show only commits on that branch. If not specified, defaults to <b>head</b>.
</ul>
<p>
    Sample URLs include:
</p>
<?php
    $Protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['HTTP_HOST']);
	$URL  = "$Protocol://$ServerName/backend/";
    $HREF = "<a href=\"$URL\">$URL</a>";

    echo '
<ol>
    <li>' . $URL . 'html.php?branch=2018Q4</li>
    <li>' . $URL . 'html.php?branch=quarterly</li>
    <li>' . $URL . 'html.php?flavor=broken</li>
    <li>' . $URL . 'html.php?flavor=new</li>';
?>
</ol>

<h2>Other feeds</h2>
<ol>
    <li><p>A Personal News feed for each of your watch lists. Look for the link under
            the <code>Watch Lists</code> box after you have logged in.</li>

    <li><p>The blog for this website, <a href="https://news.freshports.org/">FreshPorts News</a>.
</ol>

</table>
</TD>
  <td class="sidebar">
  <?php
  echo freshports_SideBar();
  ?>
  </td>
</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
