<?php
	#
	# Copyright (c) 1998-2026 Dan Langille
	#
	# the feeds in /--/feeds/, written by laminas-feed - re #721
	# based on www/backend/index.php, which lists the deprecated feeds
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start(_('FreshPorts News Feeds'),
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$Hostname = $_SERVER['HTTP_HOST'];
	$URL      = "https://{$Hostname}/--/feeds/commits.php";

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>


<tr>
	<?php echo freshports_PageBannerText(_('FreshPorts News Feeds')); ?>
</tr>
<tr><td class="content">
<h1><?php echo _('FreshPorts News Feeds'); ?></h1>

<h2><?php echo _('Feed formats'); ?></h2>

        <p><?php echo _('See the next section for optional parameters for these URLs.'); ?></p>

<ol>
<li><a href="commits.php">RSS 2.0</a>   [ <a href="https://validator.w3.org/feed/check.cgi?url=<?php echo rawurlencode($URL); ?>"><?php echo _('RSS Feed validator'); ?></a>  ]
<li><a href="commits.php?format=atom">Atom 1.0</a>   [ <a href="https://validator.w3.org/feed/check.cgi?url=<?php echo rawurlencode($URL . '?format=atom'); ?>"><?php echo _('RSS Feed validator'); ?></a>  ]
</ol>

<p>
<?php echo _('The above feeds are created using <a href="https://github.com/laminas/laminas-feed">laminas-feed</a>.'); ?>


<h2><?php echo _('Optional parameters for the above formats'); ?></h2>

<p><?php echo _('Each of those formats have optional parameters:'); ?></p>
<ul>
    <li><b>format=atom</b> : <?php echo _('Atom 1.0 instead of RSS 2.0.'); ?></li>
    <li><b>flavor=new</b> : <?php echo _('show only new ports (ignores <b>branch</b>).'); ?></li>
    <li><b>flavor=broken</b> : <?php echo _('show only broken ports (ignores <b>branch</b>).'); ?></li>
    <li><b>flavor=vuln</b> : <?php echo _('show only vuln ports (branches should work, let me know if they do not).'); ?></li>
    <li><b>branch=2018Q3</b> : <?php echo _('show only commits on that branch. If not specified, defaults to <b>head</b>.'); ?>
</ul>
<p>
    <?php echo _('Sample URLs include:'); ?>
</p>
<?php
    echo '
<ol>
    <li>' . $URL . '?branch=2026Q4</li>
    <li>' . $URL . '?branch=quarterly</li>
    <li>' . $URL . '?flavor=broken</li>
    <li>' . $URL . '?flavor=new&amp;format=atom</li>';
?>
</ol>

<h2><?php echo _('Other feeds'); ?></h2>
<ol>
    <li><p><?php echo _('A Personal News feed for each of your watch lists. Look for the link under the <code>Watch Lists</code> box after you have logged in.'); ?></li>

    <li><p><?php echo _('The blog for this website, <a href="https://news.freshports.org/">FreshPorts News</a>.'); ?>

    <li><p><?php echo sprintf(_('The deprecated feeds are listed at <a href="%s">%s</a>.'), '/backend/', '/backend/'); ?>
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
