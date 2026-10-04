<?php
	#
	# $Id: graphs2.php,v 1.2 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$Title = _('Statistics 2 - everyone loves a graph');
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');
?>
	<script src="/javascript/jquery-3.6.0.min.js" defer></script>
	<script src="/javascript/jquery.flot.min.js" defer></script>
	<script src="/javascript/graphs.js" defer></script>

	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>

<tr>
	<?php echo freshports_PageBannerText(_('Statistics - everyone loves a graph')); ?>
</tr>

<tr><td>
<P>
<?php echo _('All of these graphs require javascript.  Please select the graph you would like to view from the dropdown.'); ?>
</P>
<P>
<?php printf(_('If you have suggestions for graphs, please submit them via the %s.'), '<a href="' . ISSUES . '">' . _('issues link') . '</a>'); ?>
</P>


<?php
  if ($ShowAds) echo '<CENTER>' . Ad_728x90() . '</CENTER>';
?>

</td></tr>

<tr><td>
<h2><?php echo _('HEADS UP!'); ?></h2>

<p>
<?php echo _('These graphs are broken. Help is needed to get them working again.'); ?>
<p>

<?php echo _('Some starting points:'); ?>

<ul>
<li><?php echo _('<a href="https://github.com/FreshPorts/freshports/blob/master/www/graphs2.php">This is the source code</a> for this page.'); ?></li>
<li><?php echo _('<a href="https://github.com/FreshPorts/freshports/blob/1.40/www/graphs2.php">This is the last working version</a> of this page.'); ?></li>
<li><?php printf(_('%s used by the above.'), '<a href="https://github.com/FreshPorts/freshports/blob/1.40/www/jquery-1.2.6.min.js">jquery-1.2.6.min.js</a>'); ?></li>
<li><?php printf(_('%s used by the above.'), '<a href="https://github.com/FreshPorts/freshports/blob/1.40/www/jquery.flot.pack.js">jquery.flot.pack.js</a>'); ?></li>
<li><?php printf(_('%s as relocated to /javascript/'), '<a href="https://github.com/FreshPorts/freshports/blob/master/www/javascript/graphs.js">graphs.js</a>'); ?></li>
<li><?php echo _('<a href="https://github.com/FreshPorts/freshports/tree/master/www/javascript">the javascript</a> I thought would appropriately replace jquery.flot.pack.js & jquery-1.2.6.min.js'); ?></li>
</ul>

<?php echo _('Thank you for your help.'); ?>


<p>
</td></tr>

<tr><td>

<table class="graphs fullwidth borderless">
<tr>
<td class="graph-sidebar">
<?php
	$sql = "select title, label from graphs where json=true order by title";
	$result = pg_query($db, $sql);
    if ($result) {
    	$numrows = pg_num_rows($result);
		if ($numrows) { 
			echo '<select>';
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				echo '<option value="' . $myrow["label"] . '">' . $myrow["title"] . '</option>' . "\n";
			}
			echo '</select>';
		} else {
			echo _("Oh. This is rather embarrassing.  I have no idea how this could have happened. I do hope you will understand.  Please don't tell anyone.  But I don't have any data to show you.  For you see, nobody has bothered to populate the graphs table.");
		}
	}
?>
</td>
</tr>
<tr>
<td>
<div id="title"></div>
<div id="overview"></div>
<table>
<tr>
<td>
<div id="list"></div>
</td>
<td>
<div id="holder"></div>
</td>
</tr>
</table>
</td>
</tr>
</table>


</td></tr>

</table>
</td>

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
