<?
	# $Id: sidebar.php,v 1.1.2.3 2002-04-19 02:40:33 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("include/getvalues.php");

	freshports_HTML_Start();

?>

<HEAD>
	<TITLE>FreshPorts</TITLE>

<?
	freshports_style();
?>

	<META HTTP-EQUIV="Refresh" CONTENT="1200; URL=http://test.FreshPorts.org/sidebar.php">
	<META http-equiv="Pragma"              content="no-cache">
	<META NAME="MSSmartTagsPreventParsing" content="TRUE">

</HEAD>

<?
	freshports_body();
	$ServerName = str_replace("freshports", "FreshPorts", $SERVER_NAME);
?>

	<CENTER>
	<A HREF="http://<? echo $ServerName; ?>/" TARGET="_content"><IMG SRC="/images/freshports_mini.jpg" ALT="FreshPorts.org - the place for ports" WIDTH="128" HEIGHT="28" BORDER="0"></A>

	<BR>

	<SMALL>
	<script LANGUAGE="JavaScript">
		var d = new Date();  // today's date and time.
	    document.write(d.toLocaleString());
	</script>
	</SMALL>
	</CENTER>

<?

	$sql = "select * from commits_latest order by commit_date_raw desc, category, port";

	$sql .= " limit 40";

	if ($Debug) {
		echo $sql;
		}

	$result = pg_exec ($db, $sql);
	if (!$result) {
		echo $sql . 'error = ' . pg_errormessage();
		exit;
	}
?>

	<UL>
<?
	$numrows = pg_numrows($result);
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array ($result, $i);
		echo '	<LI><SMALL><A HREF="http://' . $ServerName . '/' . $myrow["category"] . '/' . $myrow["port"] . '/" TARGET="_content">';
		echo $myrow["category"] . '/' . $myrow["port"] . '</A>';
		echo '</SMALL></LI>';
	}
?>
	</UL>

<P ALIGN="right">
<? echo freshports_copyright(); ?>
</P>

</body>
</html>
