<?php
	#
	# $Id: missing.php,v 1.11 2012-10-23 17:08:20 dan Exp $
	#
	# Copyright (c) 2001-2006 DVL Software Limited
	#

	#
	# this is a true 404
	header("HTTP/1.1 404 NOT FOUND");

	$Title = 'Document not found';
	freshports_Start($Title . ' 404 page',
					$FreshPortsTitle . ' - 404 page',
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
Sorry, but I don't know anything about that.
</P>

<P>
Perhaps a <a href="/categories.php">list of categories</a> or <a href="/search.php">the search page</a> might be helpful.
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
