<?
	# $Id: missing.php,v 1.1.2.8 2002-04-01 21:02:40 dan Exp $
	#
	# Copyright (c) 2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("../classes/elements.php");
	require("../classes/ports.php");


function freshports_Parse404URI($REQUEST_URI, $db) {
	#
	# we have a pending 404
	# if we can parse it, then do so and return 1;
	# otherwise, return 0.

	require("missing-port.php");

	$result = freshports_Parse404CategoryPort($REQUEST_URI, $db);

	return $result;
}

$result = freshports_Parse404URI($REQUEST_URI, $db);

if ($result) {

	#
	# this is a true 404

	$Title = "Document not found";
	freshports_Start($Title,
					"$FreshPortsTitle - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">
<TR>
    <TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="29"><FONT COLOR="#FFFFFF" size="+2"> 
<?
   echo "$FreshPortsTitle -- $Title";
?> 
</FONT></TD>
</TR>
<TR>
<TD>
<P>
Sorry, but I don't know anything about that.
</P>

<P>
<? echo $result ?>
</P>

<P>
Perhaps a <A HREF="/categories.php">list of categories</A> for <A HREF="/search.php">the search page</A> might be helpful.
</P>

</TD>
</TR>

<TR><TD>

<?
	include("./include/footer.php");
?>

</TD></TR>
</TABLE>
</body>
</html>

<?
} else {
#	echo " ummm, not sure what that was: '$result'";
}

?>
