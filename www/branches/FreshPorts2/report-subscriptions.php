<?
	# $Id: report-subscriptions.php,v 1.1.2.1 2002-06-10 20:11:30 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	$ArticleTitle = "Report subscriptions";

	freshports_Start(	$ArticleTitle,
					"",
					"FreeBSD, daemon copyright");

function freshports_ReportFrequencies($dbh) {
	$sql = "select id, description from report_frequency order by id";
	$result = pg_exec($dbh, $sql);
	if ($result) {
		$numrows = pg_numrows($result);
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);
			$Frequencies[$myrow["id"]] = $myrow["description"];
		}
	}

	return $Frequencies;
}

function freshports_ReportNames($dbh) {
	$sql = "select id, name from reports order by id";
	$result = pg_exec($dbh, $sql);
	if ($result) {
		$numrows = pg_numrows($result);
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);
			$Reports[$myrow["id"]] = $myrow["name"];
		}
	}

	return $Reports;
}

?>

<TABLE WIDTH="<? echo $TableWidth; ?>" ALIGN="center" BORDER="0">
  <TR>

<TD WIDTH="100%" VALIGN="top">
<TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
<TR>
<TD BGCOLOR="#AD0040" HEIGHT="29" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG>Customize</BIG></BIG></FONT></TD>
</TR>
<TR BGCOLOR="#ffffff">
<TD>

<FORM ACTION="<?php echo $_SERVER["PHP_SELF"] . "?origin=" . $origin ?>" METHOD="POST" NAME=f>
	<?
		$Frequencies = freshports_ReportFrequencies($db);
		$numrows = count($Frequencies);
	
	$DDLB = '<SELECT NAME="reportfrequency[]" size="1">';

    	while (list($frequency_id, $frequency) = each($Frequencies)) {
			$DDLB .= '<OPTION VALUE="' . $frequency_id . '">' . $frequency . '</OPTION>' . "\n";
		}

	$DDLB .= '</SELECT>';
	?>
	<HR>
	<TABLE BORDER="1">
	<?


		$Reports = freshports_ReportNames($db);
		$numrows = count($Reports);
		while (list($report_id, $name) = each($Reports)) {
			echo '<TR><TD>';
			echo '<INPUT TYPE="checkbox" NAME="reports[]" value="' . $report_id . '"> ' . $name;
			echo '</TD><TD>';
			echo $DDLB;
			echo "</TD></TR>\n";
		}
	?>
	</TABLE>

	<INPUT TYPE="submit" VALUE="update" NAME="submit">
</FORM>

</TD>
</TR>
</TABLE>
	</TD>

  <?
  freshports_SideBar();
  ?>

  </TR>

</TABLE>


<?
freshports_ShowFooter();
?>

</BODY>
</HTML>

