<?
	# $Id: report-subscriptions.php,v 1.1.2.9 2002-09-09 18:09:13 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	if (!$_COOKIE["visitor"]) {
		header("Location: login.php?origin=" . $_SERVER["PHP_SELF"]);  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	$ArticleTitle = "Report subscriptions";

	freshports_Start(	$ArticleTitle,
					"",
					"FreeBSD, daemon copyright");

	$Debug = 0;
	if ($Debug) phpinfo();

	function freshports_ReportFrequencies($dbh) {
		$sql = "select id, frequency, description
		          from report_frequency
		         order by id";

		$result = pg_exec($dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				# we don't include don't notify me.
				if ($myrow["frequency"] != 'Z') {
					$Frequencies[$myrow["id"]] = $myrow["description"];
				}
			}
		}

		return $Frequencies;
	}

	function freshports_ReportNames($dbh) {
		$sql = "select id, name, description, needs_frequency from reports order by name";
		$result = pg_exec($dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				$Values["name"]				= $myrow["name"];
				$Values["needs_frequency"]	= $myrow["needs_frequency"];
				$Values["description"]		= $myrow["description"];
				$Reports[$myrow["id"]] = $Values;
			}
		}

		return $Reports;
	}

	if ($_POST["submit"] == 'update') {
		pg_exec($db, "begin");
		$sql = "DELETE from report_subscriptions
    	         WHERE user_id = $UserID";

		$result = pg_exec($db, $sql);

		$reports     = $_POST["reports"];
        $frequencies = $_POST["reportfrequency"];

		reset($reports);
		reset($frequencies);

		while (list($key, $value) = each($reports)) {
			if ($Debug) echo "\$key='$key' \$value='$value' \$UserID='$UserID' \$frequencies[\$key]=$frequencies[$key]<BR>";
			if (IsSet($frequencies[$key])) {
				$sql = "INSERT INTO report_subscriptions(report_id, user_id, report_frequency_id) values ($value, $UserID, $frequencies[$key])";
			} else {
				$sql = "INSERT INTO report_subscriptions(report_id, user_id) values ($value, $UserID)";
			}
			if ($Debug) echo "\$sql='$sql'<BR>\n";
			$result = pg_exec ($db, $sql);
			${"reports_"     . $value} = 1;
			${"frequencies_" . $value} = $frequencies[$key];

			if (!$result) {
				echo "OUCH, that's not very nice.  something went wrong: " . pg_errormessage() . "  $sql";
				pg_exec($db, "rollback");
				exit;
			}
		}

		pg_exec($db, "commit");
	} else {
		# read the values from the db
		$sql = "SELECT report_id, report_frequency_id
				  FROM report_subscriptions
				 WHERE user_id = $UserID";
		$result = pg_exec ($db, $sql);
		$numrows = pg_numrows($result);
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);
			${"reports_"     . $myrow["report_id"]} = 1;
			${"frequencies_" . $myrow["report_id"]} = $myrow["report_frequency_id"];
		}
	}


	function freshports_ReportFrequenciesDDLB($Frequencies, $selected) {
		$numrows = count($Frequencies);
	
		$DDLB = '<SELECT NAME="reportfrequency[]" size="1">';

    		while (list($frequency_id, $frequency) = each($Frequencies)) {
				$DDLB .= '<OPTION ';
				if ($selected == $frequency_id) {
					$DDLB .= 'SELECTED ';
				}
				$DDLB .= 'VALUE="' . $frequency_id . '">' . $frequency . '</OPTION>' . "\n";
			}

		$DDLB .= '</SELECT>';

		return $DDLB;

	}

	$Frequencies = freshports_ReportFrequencies($db);
?>

<TABLE WIDTH="<? echo $TableWidth; ?>" ALIGN="center" BORDER="0">
  <TR>

<TD WIDTH="100%" VALIGN="top">
<TABLE WIDTH="100%" BORDER="0" CELLPADDING="1">
<TR>
<TD BGCOLOR="#AD0040" HEIGHT="29" COLSPAN="1"><FONT COLOR="#FFFFFF"><BIG><BIG><? echo $ArticleTitle; ?></BIG></BIG></FONT></TD>
</TR>
<TR>
<TD>

This page allows you to select the reports you wish to receive and the frequency of the report.

<FORM ACTION="<?php echo $_SERVER["PHP_SELF"] ;?>" METHOD="POST" NAME=f>
	<TABLE CELLPADDING="3" CELLSPACING="6" BORDER="0">
	<TR><TD><BIG><B>Report Name</B></BIG></TD><TD><BIG><B>Frequency</B></BIG></TD><TD><BIG><B>Description</B></BIG></TD></TR>
	<?


		$Reports = freshports_ReportNames($db);
		$numrows = count($Reports);
		while (list($report_id, $Values) = each($Reports)) {
			$name				= $Values["name"];
			$needs_frequency	= $Values["needs_frequency"];
			$description		= $Values["description"];
			echo '<TR><TD VALIGN="top" NOWRAP>';
			echo '<INPUT TYPE="checkbox" NAME="reports[]" value="' . $report_id . '"';
			if (${"reports_" . $report_id}) {
				echo ' checked';
			}
			echo '> ' . $name;
			echo '</TD><TD VALIGN="top" NOWRAP>';
			if ($needs_frequency == 't') {
				echo freshports_ReportFrequenciesDDLB($Frequencies, ${"frequencies_" . $report_id});
			} else {
				echo 'N/A';
			}
			echo "</TD><TD>" . $description . " </TD>";
			echo "</TR>\n";
		}

	?>
	</TABLE>

	&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="update" NAME="submit">
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

