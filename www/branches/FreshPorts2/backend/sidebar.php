<?
	# $Id: sidebar.php,v 1.1.2.12 2002-12-10 04:00:18 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	freshports_HTML_Start();

?>

<HEAD>
	<TITLE><? echo $FreshPortsTitle; ?></TITLE>

	<STYLE TYPE="text/css">
	BODY, TD, TR, P, UL, OL, LI, INPUT, SELECT, DL, DD, DT, FONT
	{
		font-family: Helvetica, Verdana, Arial, Clean, sans-serif;
		font-size: 12px;
	}
	ul { padding-left: 20px;}
	</STYLE>
<?
	freshports_body();
	$ServerName = str_replace("freshports", "FreshPorts", $_SERVER["SERVER_NAME"]);
	GLOBAL $FreshPortsSlogan;
	GLOBAL $FreshPortsName;
?>

	<META HTTP-EQUIV="Refresh" CONTENT="1200; URL=http://<?php echo $ServerName . $_SERVER["PHP_SELF"]; ?>">
	<META http-equiv="Pragma"              content="no-cache">
	<META NAME="MSSmartTagsPreventParsing" content="TRUE">

</HEAD>

	<CENTER>
	<A HREF="http://<? echo $ServerName; ?>/" TARGET="_content"><IMG SRC="/images/freshports_mini.jpg" ALT="<?php echo "$FreshPortsName -- $FreshPortsSlogan"; ?>" TITLE="<?php echo "FreshPorts -- $FreshPortsSlogan"; ?>" WIDTH="128" HEIGHT="28" BORDER="0"></A>

	<BR>

	<SMALL>
	<script LANGUAGE="JavaScript">
		var d = new Date();  // today's date and time.
	    document.write(d.toLocaleString());
	</script>
	</SMALL>
	</CENTER>

<?

	$sql = "select * from commits_latest_ports order by commit_date_raw desc, category, port";

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
		echo "\n";
	}
?>
	</UL>

<P ALIGN="right">
<? echo freshports_copyright(); ?>
</P>

</body>
</html>
