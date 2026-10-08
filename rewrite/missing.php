<?php
	#
	# $Id: missing.php,v 1.11 2012-10-23 17:08:20 dan Exp $
	#
	# Copyright (c) 2001-2026 Dan Langille
	#

	#
	# this is a true 404
	header("HTTP/1.1 404 NOT FOUND");

	$Title = _('Document not found');
	freshports_Start(sprintf(_('%s 404 page'), $Title),
					sprintf(_('%s - 404 page'), $FreshPortsTitle),
					'FreeBSD, index, applications, ports');
					
?>

<?php echo freshports_MainTable(); ?>
<tr>
<td class="content">
<?php echo freshports_MainContentTable(); ?>
<tr>
    <td class="accent"><span>
<?php
   echo "$FreshPortsTitle -- $Title";
?>
</span></td>
</tr>

<tr>
<td class="content">
<P>
<?php echo _("Sorry, but I don't know anything about that."); ?>
</P>

<?php
	# set by freshports_Parse404URI() - see https://github.com/FreshPorts/freshports/issues/614
	if (!empty($Suggestions)) {
		echo '<P>' . _('Perhaps you meant one of these:') . "</P>\n";
		echo "<ul>\n";
		foreach ($Suggestions as $Suggestion) {
			$CategoryPort = $Suggestion['category'] . '/' . $Suggestion['name'];
			echo '<li><a href="' . htmlspecialchars($Suggestion['link']) . '">' . htmlspecialchars($CategoryPort) . '</a>';
			if (!empty($Suggestion['short_description'])) {
				echo ' - ' . htmlspecialchars($Suggestion['short_description']);
			}
			echo "</li>\n";
		}
		echo "</ul>\n";
	}
?>

<P>
<?php echo _('Perhaps a <a href="/categories.php">list of categories</a> or <a href="/search.php">the search page</a> might be helpful.'); ?>
</P>

</td>
</tr>
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
