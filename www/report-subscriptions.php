<?php
	#
	# $Id: report-subscriptions.php,v 1.2 2006-12-17 12:06:16 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	if (!$_COOKIE[USER_COOKIE_NAME]) {
		header('Location: /login.php');  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	if (IN_MAINTENCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	$ArticleTitle = 'Report subscriptions';

	freshports_Start(	$ArticleTitle,
					'',
					'FreeBSD, daemon copyright');

	$Debug = 0;
	if ($Debug) phpinfo();


	function freshports_ReportFrequencies($dbh) {
		$sql = 'select id, frequency, description
		          from report_frequency
		         order by id';

		$result = pg_query($dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array($result, $i);
				# we don't include don't notify me.
				if ($myrow['frequency'] != 'Z') {
					$Frequencies[$myrow['id']] = $myrow['description'];
				}
			}
		}

		return $Frequencies;
	}

	function freshports_ReportNames($dbh) {
		$sql = 'select id, name, description, needs_frequency from reports order by name';
		$result = pg_exec($dbh, $sql);
		if ($result) {
			$numrows = pg_num_rows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array($result, $i);
				$Values['name']            = $myrow['name'];
				$Values['needs_frequency'] = $myrow['needs_frequency'];
				$Values['description']     = $myrow['description'];
				$Reports[$myrow['id']]     = $Values;
			}
		}

		return $Reports;
	}

	if (IsSet($_POST['submit']) && $_POST['submit'] == 'update') {
		pg_query($db, 'begin');
		$sql = "DELETE from report_subscriptions
    	         WHERE user_id = $User->id";

		$result = pg_query($db, $sql);

		$reports = $_REQUEST['reports'];
		if (Is_Array($reports)) {
		
			reset($reports);
	
			foreach ($reports as $key => $value) {
				$TheFrequency = pg_escape_string($db, $_POST['reportfrequency_' . $value]);
				$value = pg_escape_string($db, $value);

				if ($Debug) echo '$TheFrequency=\'' . $TheFrequency . '\'';
				if ($Debug) echo "\$key='$key' \$value='$value' \$User->id='$User->id' \$frequencies[\$key]=" . $TheFrequency . '<BR>';
				if (IsSet($TheFrequency) && $TheFrequency <> '') {
					$sql = "INSERT INTO report_subscriptions(report_id, user_id, report_frequency_id) values ($value, $User->id, $TheFrequency)";
				} else {
					$sql = "INSERT INTO report_subscriptions(report_id, user_id) values ($value, $User->id)";
				}
				if ($Debug) echo "\$sql='$sql'<BR>\n";
				$result = pg_query($db, $sql);
	
				if (!$result) {
					echo 'OUCH, that\'s not very nice.  something went wrong: ' . pg_last_error($db) . "  $sql";
					pg_exec($db, 'rollback');
					exit;
				}
			}
		}
	
		pg_query($db, 'commit');

	}

		$Reports = freshports_ReportNames($db);

		if ($Debug) {
	        foreach ($reports as $report_id => $Values) {

				echo '* * * * now processing $report_id = \'' . $report_id . '\'';
				echo ' and $Values = \'' . $Values . '\'<BR>';
			}
		}
		# read the values from the db
		$sql = "SELECT report_id, report_frequency_id
				  FROM report_subscriptions
				 WHERE user_id = $User->id
				 ORDER BY report_id ";
		$result = pg_exec ($db, $sql);
		$numrows = pg_num_rows($result);
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);

			$Values = $Reports[$myrow['report_id']];

			$Values['frequency'] = $myrow['report_frequency_id'];
			$Values['selected']  = 1;
			if ($Debug) echo '# # # reading from DB: $Values = \'' . $Values . '\' and $Values ["frequency"] = \'' . $Values ["frequency"] . '\'<BR>';

			$Reports[$myrow["report_id"]] = $Values;
		}

	if ($Debug) echo "\$frequencies_3='$frequencies_3'<BR>\n";

	$Frequencies = freshports_ReportFrequencies($db);
?>

<?php echo freshports_MainTable(); ?>

  <TR>

<TD class="content">
<TABLE class="fullwidth borderless">
<TR>
<td class="accent"><span><? echo $ArticleTitle; ?></span></td>
</TR>
<TR>
<TD>

<p>
This page allows you to select the reports you wish to receive and the frequency of the report.
</p>

<FORM ACTION="<?php echo $_SERVER["PHP_SELF"] ;?>" METHOD="POST" NAME=f>
	<TABLE class="report-list bordered">
	<TR><TD class="element-details">Report Name</TD><TD class="element-details">Frequency</TD><TD class="element-details">Description</TD></TR>
	<?


		reset($Reports);
		$numrows = count($Reports);
		foreach ($Reports as $report_id => $Values) {

			if ($Debug) {
				echo 'now processing $report_id = \'' . $report_id . '\'';
			}

			$name				= $Values["name"];
			$needs_frequency	= $Values["needs_frequency"];
			$description		= $Values["description"];
			$thefrequency		= $Values["frequency"];
		
			echo '<TR>';
			echo '<TD>';
			echo '<label><INPUT TYPE="checkbox" NAME="reports[]" value="' . $report_id . '"';
			if ($Values["selected"]) {
				echo ' checked';
			}
			echo '> ' . $name;
			echo '</label></TD>';

			if ($Debug) {
				echo '$Values["frequency"]=\'' . $Values["frequency"] . '\'';
				echo 'now processing $report_id = \'' . $report_id . '\'';
			}

            echo '<TD>';
			if ($needs_frequency == 't') {
				reset($Frequencies);
				$numrows = count($Frequencies);
	
				$DDLB = '<SELECT NAME="reportfrequency_' . $report_id . '" size="1">';

		   		foreach ($Frequencies as $frequency_id => $frequency) {

					if ($Debug) {
						echo '   with $frequency_id = \'' . $frequency_id . '\' and $frequency = \'' . $frequency . '\'<BR>';
					}
					$DDLB .= '<OPTION ';
					if ($Values["frequency"] == $frequency_id) {
						$DDLB .= 'SELECTED ';
					}
					$DDLB .= 'VALUE="' . $frequency_id . '">' . $frequency . '</OPTION>' . "\n";
				}

				$DDLB .= '</SELECT>';

				echo $DDLB;
			} else {
			  echo 'nil';
			}
			echo '</TD>';
			echo "<TD>" . $description . " </TD>";
			echo "</TR>\n";
		}

	?>
	</TABLE>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/spam-filter-information.php'); ?>

<hr>
<p>
<h2>Beta mailing list</h2>
<p>
You may wish to help me test new FreshPorts features or even just get a sneak
peek at them.  If so, I urge you to join the new Beta mailing list.  This
will be a low volume list which broadcasts details of new features which
you can try out before they hit the main website.  To subscribe, follow
the directions found on the <a href="https://lists.freshports.org/mailman/listinfo/">FreshPorts mailing list website</a>

<hr>

<BR><BR>
	&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="update" NAME="submit">
</FORM>

</TD>
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

</BODY>
</HTML>

