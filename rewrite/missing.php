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
<TR>
<TD WIDTH="100%" VALIGN="top">
<?php echo freshports_MainContentTable(); ?>
<TR>
    <td class="accent"><BIG>
<?
   echo "$FreshPortsTitle -- $Title";
?>
</BIG></td>
</TR>

<TR>
<td class="content">
<P>
Sorry, but I don't know anything about that.
</P>

<P>
<? echo htmlentities($result) ?>
</P>

<P>
Perhaps a <A HREF="/categories.php">list of categories</A> or <A HREF="/search.php">the search page</A> might be helpful.
</P>

</td>
</TR>
</TABLE>
</TD>

  <td class="sidebar">
  <?
  echo freshports_SideBar();
  ?>
  </td>

</TR>

</TABLE>

<?
	echo freshports_ShowFooter();
?>

</body>
</html>
