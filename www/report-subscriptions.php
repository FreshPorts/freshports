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

	if (!IsSet($_COOKIE[USER_COOKIE_NAME])) {
		header('Location: /login.php');  /* Redirect browser to PHP web site */
		exit;  /* Make sure that code below does not get executed when we redirect. */
	}

	if (IN_MAINTENANCE_MODE) {
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
		$result = pg_query_params($dbh, $sql, array());
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
		$sql = "DELETE from report_subscriptions WHERE user_id = $User->id";

		$result = pg_query($db, $sql);

		$reports = ($_REQUEST['reports'] ?? '');
		if (Is_Array($reports)) {
		
			reset($reports);
	
			foreach ($reports as $key => $value) {
				$value = intval($value);
				# if empty, it'll go to zero
				$TheFrequency = intval(pg_escape_string($db, $_POST['reportfrequency_' . $value] ?? ''));

				# if zero, make it an empty string
				$TheFrequency = $TheFrequency == 0 ? '' : $TheFrequency;

				if ($Debug) echo '$TheFrequency=\'' . $TheFrequency . '\' ';
				if ($Debug) echo "\$key='$key' \$value='$value' \$User->id='$User->id' \$frequencies[\$key]='" . $TheFrequency . "'<br>";
				if (IsSet($TheFrequency) && $TheFrequency <> '') {
					$sql = "INSERT INTO report_subscriptions(report_id, user_id, report_frequency_id) values ($1, $2, $3)";
					$params = array($value, $User->id, $TheFrequency);
				} else {
					$sql = "INSERT INTO report_subscriptions(report_id, user_id) values ($1, $2)";
					$params = array($value, $User->id);
				}
				if ($Debug) echo "\$sql='$sql'<br><br>\n";
				$result = pg_query_params($db, $sql, $params);
	
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
			echo 'Dumping the $reports stuff we have:<br>';
		        foreach ($reports as $report_id => $Values) {

				echo '* * * * now processing $report_id = \'' . $report_id . '\'';
				echo ' and $Values = <pre>' . var_dump($Values) . '</pre><br>';
			}
			echo 'end of dump ----<br><br><br>';
		}
		# read the values from the db
		$sql = "SELECT report_id, report_frequency_id
				  FROM report_subscriptions
				 WHERE user_id = $1
				 ORDER BY report_id ";
		$result = pg_query_params($db, $sql, array($User->id));
		$numrows = pg_num_rows($result);

		if ($Debug) echo 'reading report_subscriptions from the database for this user:<br>';
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);

			$Values = $Reports[$myrow['report_id']];

			$Values['frequency'] = $myrow['report_frequency_id'];
			$Values['selected']  = 1;
			if ($Debug) echo '# # # reading from DB: $Values = <pre>' . var_dump($Values) . '</pre> and $Values ["frequency"] = \'' . $Values ["frequency"] . '\'<br>';

			$Reports[$myrow["report_id"]] = $Values;
		}

	if ($Debug) echo "\$frequencies_3='" . ($frequencies_3 ?? '') . "'<br>\n";

	$Frequencies = freshports_ReportFrequencies($db);
?>

<?php echo freshports_MainTable(); ?>

  <tr>

<td class="content">
<table class="fullwidth borderless">
<tr>
<td class="accent"><span><?php echo $ArticleTitle; ?></span></td>
</tr>
<tr>
<td>

<p>
This page allows you to select the reports you wish to receive and the frequency of the report.
</p>

<FORM ACTION="<?php echo $_SERVER["PHP_SELF"] ;?>" METHOD="POST" NAME=f>
	<table class="report-list bordered">
	<tr><td class="element-details">Report Name</td><td class="element-details">Frequency</td><td class="element-details">Description</td></tr>
	<?php


		reset($Reports);
		$numrows = count($Reports);
		foreach ($Reports as $report_id => $Values) {

			if ($Debug) {
				echo 'now processing $report_id = \'' . $report_id . '\'';
			}

			$name            = $Values["name"];
			$needs_frequency = $Values["needs_frequency"];
			$description     = $Values["description"];
			$thefrequency    = ($Values["frequency"] ?? '');
		
			echo '<tr>';
			echo '<td>';
			echo '<label><INPUT TYPE="checkbox" NAME="reports[]" value="' . $report_id . '"';
			if (IsSet($Values["selected"])) {
				echo ' checked';
			}
			echo '> ' . $name;
			echo '</label></td>';

			if ($Debug) {
				echo '$Values["frequency"]=\'' . $Values["frequency"] . '\'';
				echo 'now processing $report_id = \'' . $report_id . '\'';
			}

            echo '<td>';
			if ($needs_frequency == 't') {
				reset($Frequencies);
				$numrows = count($Frequencies);
	
				$DDLB = '<SELECT NAME="reportfrequency_' . $report_id . '" size="1">';

		   		foreach ($Frequencies as $frequency_id => $frequency) {

					if ($Debug) {
						echo '   with $frequency_id = \'' . $frequency_id . '\' and $frequency = \'' . $frequency . '\'<br>';
					}
					$DDLB .= '<OPTION ';
					if (IsSet($Values["frequency"]) && $Values["frequency"] == $frequency_id) {
						$DDLB .= 'SELECTED ';
					}
					$DDLB .= 'VALUE="' . $frequency_id . '">' . $frequency . '</OPTION>' . "\n";
				}

				$DDLB .= '</SELECT>';

				echo $DDLB;
			} else {
			  echo 'nil';
			}
			echo '</td>';
			echo "<td>" . $description . " </td>";
			echo "</tr>\n";
		}

	?>
	</table>

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

<br><br>
	&nbsp;&nbsp;<INPUT TYPE="submit" VALUE="update" NAME="submit">
</FORM>

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

</BODY>
</HTML>

