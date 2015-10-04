<?php
	#
	# $Id: missing.php,v 1.11 2012-10-23 17:08:20 dan Exp $
	#
	# Copyright (c) 2001-2006 DVL Software Limited
	#



	#
	# this is a true 404

	$Title = 'Document not found';
	freshports_Start($Title,
					$FreshPortsTitle . ' - new ports, applications',
					'FreeBSD, index, applications, ports');

?>

<?php echo freshports_MainTable(); ?>
<TR>
<TD WIDTH="100%" VALIGN="top">
<?php echo freshports_MainContentTable(); ?>
<TR>
    <TD BGCOLOR="<?php echo BACKGROUND_COLOUR; ?>" HEIGHT="29"><FONT COLOR="#FFFFFF"><BIG><BIG>
<?
   echo "$FreshPortsTitle -- $Title";
?>
</BIG></BIG></FONT></TD>
</TR>

<TR>
<TD WIDTH="100%" VALIGN="top">
<P>
Sorry, but I don't know anything about that.
</P>

<P>
<? echo htmlentities($result) ?>
</P>

<P>
Perhaps a <A HREF="/categories.php">list of categories</A> or <A HREF="/search.php">the search page</A> might be helpful.
</P>

</TD>
</TR>
</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
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
