<?
	# $Id: files.php,v 1.1.2.14 2002-06-09 21:42:44 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

function freshports_Files($PortID, $CommitID, $WatchListID, $db) {
	GLOBAL $TableWidth;
	GLOBAL $freshports_CVS_URL;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;

	$Debug = 0;


	if ($Debug) echo "\$CommitID = '$CommitID', \$PortID = '$PortID'<BR>";

	if (!$CommitID || $CommitID != strval(intval($CommitID))) {
		$CommitID = 0;
		exit;
	}

	if (!$PortID || $PortID != strval(intval($PortID))) {
		$PortID = 0;
		exit;
	}

	$sql = "
	select element_pathname(element.id) as pathname, commit_log_port_elements.commit_log_id, 
		   commit_log_port_elements.port_id, ports.version as version, ports.revision as revision,
		   to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  as commit_date,
		   to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI:SS')   as commit_time,
		   commit_log_elements.change_type, element.name as filename, categories.name as category, commit_log.committer, 
		   ports.short_description, commit_log.message_id, commit_log.encoding_losses, 
		   commit_log.description, B.name as port, commit_log_elements.revision_name as revision_name,
		   element.status, commit_log_ports.needs_refresh, ports.date_added, ports.forbidden, ports.broken ";

	if ($WatchListID) {
		$sql .= ",
	       CASE when watch_list_element.element_id is null
    	      then 0
        	  else 1
	       END as watch ";
	}


	$sql .="
		   from commit_log, categories, element, commit_log_port_elements, commit_log_elements, 
			    element B, commit_log_ports, ports ";

	#
	# if the watch list id is provided (i.e. they are logged in and have a watch list id...)
	#
	if ($WatchListID) {
		$sql .="
	            left outer join watch_list_element
				on (ports.element_id                 = watch_list_element.element_id 
			   and  watch_list_element.watch_list_id = $WatchListID) ";
	}

	$sql .= "
	 where commit_log.id                                  = $CommitID
	   and commit_log_port_elements.commit_log_id         = commit_log.id 
	   and commit_log_port_elements.commit_log_element_id = commit_log_elements.id 
	   and commit_log_elements.element_id                 = element.id 
	   and commit_log_port_elements.port_id               = ports.id 
	   and ports.category_id                              = categories.id 
	   and ports.element_id                               = B.id 
	   and commit_log.id                                  = commit_log_ports.commit_log_id
	   and ports.id                                       = commit_log_ports.port_id
	   and ports.id                                       = $PortID
	 order by 1";

	if ($Debug) echo '<PRE>' . $sql . '</PRE>';

	$result = pg_exec($db, $sql);

	if (!$result) {
		print pg_errormessage() . "<br>\n";
		exit;
	} else {

		$i = 0;
		$NumRows = pg_numrows($result);
		while ($myrow = pg_fetch_array($result, $i)) {
//			echo "<TR><TD>" . $myrow["port_id"] . "</TD><TD>" . $myrow["port"] . "</TD></TR>";
			$rows[$i] = $myrow;
			$i++;
    	    if ($i >  $NumRows - 1) {
        	    break;
			}
		}

		$myrow = $rows[0];
		$PathNamePrefixToRemove = '/ports/' . $myrow["category"] . '/' . $myrow["port"] . '/';

		freshports_Start($myrow["category"] . '/' . $myrow["port"] . " - commit details",
    	           "freshports - new ports, applications",
        	       "FreeBSD, index, applications, ports");

		?>

		<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
		<TR><TD VALIGN="top" width="100%">
		<?
 		echo '<TABLE BORDER="1" WIDTH="100%" CELLSPACING="0" CELLPADDING="5" BORDERCOLOR="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2">' . "\n";

		freshports_PageBannerText("Commit Details", 3);

		echo '<TR><TD COLSPAN="3">';
		echo '<BIG><B><A HREF="/' . $myrow["category"] . '/">' . $myrow["category"] . '</A>';
		echo '/<A HREF="/' . $myrow["category"] . '/' . $myrow["port"] . '/">' . $myrow["port"] . '</A>';

		if (strlen($myrow["version"]) > 0) {
			echo ' ' . $myrow["version"];
			if (strlen($myrow["revision"]) > 0 && $myrow["revision"] != "0") {
	    		echo'-' . $myrow["revision"];
			}
		}

		echo '</B></BIG>';

		$HTML = '';
		if ($WatchListID) {
			if ($myrow["watch"]) {
				$HTML .= ' '. freshports_Watch_Link_Remove($myrow["element_id"]);
			} else {
				$HTML .= ' '. freshports_Watch_Link_Add($myrow["element_id"]);
			}
		}

		$HTML .= "\n&nbsp;";

		// indicate if this port has been removed from cvs
		if ($myrow["status"] == "D") {
			$HTML .= " " . freshports_Refresh_Icon() . "\n";
		}

		// indicate if this port needs refreshing from CVS
		if ($myrow["needs_refresh"]) {
			$HTML .= " " . freshports_Refresh_Icon() . "\n";
		}

		if ($myrow["date_added"] > Time() - 3600 * 24 * $DaysMarkedAsNew) {
			$MarkedAsNew = "Y";
			$HTML .= freshports_New_Icon() . "\n";
		}

		if ($myrow["forbidden"]) {
			$HTML .= freshports_Forbidden_Icon() . "\n";
		}

		if ($myrow["broken"]) {
			$HTML .= freshports_Broken_Icon() . "\n";
		}

		echo $HTML;

		echo ' <CODE CLASS="code">' . $myrow["short_description"] . '</CODE>';

		echo '</TD></TR>';

		echo "<TR><TD WIDTH='15'><B>Date</B></TD><TD><B>Committer</B></TD><TD><b>Description</b></TD></TR>\n";      

		echo "<TR>";
		echo '    <TD VALIGN="top">' . $myrow["commit_date"] . ' ' . $myrow["commit_time"];
		echo '&nbsp;' . freshports_Email_Link($myrow["message_id"]);

		echo '&nbsp;&nbsp;'. freshports_Commit_Link($myrow["message_id"]);

		if ($myrow["encoding_losses"] == 't') {
			echo '&nbsp;' . freshports_Encoding_Errors();
		}

		echo "</TD>\n";
		echo '    <TD VALIGN="top">' . $myrow["committer"]         . "</TD>\n";
		echo '    <TD VALIGN="top">' . freshports_PortDescriptionPrint($myrow["description"], $myrow["encoding_losses"], $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($myrow["message_id"], $freshports_CommitMsgMaxNumOfLinesToShow)) . "</CODE></TD>\n";
		echo "</TR>";
		?>

		</TABLE>

		<BR>

		<table border="1" width="100%" CELLSPACING="0" CELLPADDING="5">
		<?

		switch ($NumRows) {
			case 0:
				$title = 'no files found';
				break;

			case 1:
				$title = '1 file found';
				break;

			default:
				$title =  $i . ' files found';
		}

		freshports_PageBannerText($title, 3);

		?>
		<TR>
			<TD><b>Action</b></TD><TD><B>Revision</B></TD><TD><b>File</b></TD>
		</TR>
		<?

		for ($i = 0; $i < $NumRows; $i++) {
			$myrow = $rows[$i];
			echo "<TR>\n";

			switch ($myrow["change_type"]) {
				case "M":
					$Change_Type = "modify";
					break;

				case "A":
					$Change_Type = "import"; 
					break;

				case "R":
					$Change_Type = "remove"; 
					break;

				default:
					$Change_Type = $myrow["change_type"] ; 
			}

			echo "  <TD>" . $Change_Type . "</TD>";
			echo "  <TD>" . $myrow["revision_name"] . "</TD>";
			echo '  <TD><A HREF="' . $freshports_CVS_URL . $myrow["pathname"] . '">';

			echo '<CODE CLASS="code">' . str_replace($PathNamePrefixToRemove, '', $myrow["pathname"]) . "</CODE></A></TD>";
			echo "</TR>\n";
		}
	}

	?>

	</script>
	</table>
	</TD>
		<TD VALIGN="top" WIDTH="*" ALIGN="center">
		<? include($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php") ?>
		</TD>
	</TR>
	</table>
	</TR>
	</table>
	<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
	</body>
	</html>

	<?

}