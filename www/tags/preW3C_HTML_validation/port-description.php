<?
	# $Id: port-description.php,v 1.1.2.1 2002-01-02 02:53:46 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	require("../classes/ports.php");
	require("./missing-port.php");

	if (!$port || $port != strval(intval($port))) {
		$port = 0;
	} else {
		$port = intval($port);
	}

	$id = $port;

	#
	# this seems to an inefficient way to do this
	# we read in the port to get category/name, then redirect
	# them to the category/port/ URL.  Where the port will be 
	# read all over again...
	#
	$port = new Port($db);
	$port->FetchByID($id);

	if (IsSet($port->id)) {
		header("Location: " . $port->category . '/' . $port->port . '/');
	} else {
		GLOBAL $TableWidth;
		GLOBAL $FreshPortsTitle;

		$Title = "nothing found for port=$id";
		freshports_Start($Title,
	    	    		"$FreshPortsTitle - new ports, applications",
						"FreeBSD, index, applications, ports");

?>
<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">
<TR>
<TD VALIGN="top" width="100%">
<TABLE WIDTH="100%" BORDER="0" ALIGN="centre">
<tr>
    <td colspan="3" bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">
<?
   echo $Title;
?> 
 </font></td>
</tr>

<TR><TD>
<P>
Perhaps a <A HREF="/categories.php">list of categories</A> for <A HREF="/search.php">the search page</A> might be helpful.
</P>

</TD></TR>

</TABLE>

<TR><TD>
<?

		include("./include/footer.php");
?>

</TD></TR>
</TABLE>

<?
	}
	
#	freshports_PortDescription($port);
?>