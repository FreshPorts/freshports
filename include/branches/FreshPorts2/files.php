<?php
	#
	# $Id: files.php,v 1.1.2.33 2004-08-09 22:30:41 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');
	
function freshports_Files($User, $ElementID, $MessageID, $db) {
	#
	# $PortId      == ports.id
	# $MessageID   == commit_log.message_id
	#

	GLOBAL $TableWidth;
	GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
	GLOBAL $DaysMarkedAsNew;

	$Debug = 0;

	if ($Debug) echo "\$MessageID = '$MessageID', \$ElementID = '$ElementID'<BR>";

	if (!$ElementID || $ElementID != strval(intval($ElementID))) {
		$ElementID = 0;
		exit;
	}

	$sql = "
select element_pathname(element.id) as pathname, 
       commit_log_port_elements.commit_log_id,
       commit_log_port_elements.port_id, 
       ports.element_id,
       ports.version as version, 
       ports.revision as revision,
       to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  as commit_date,
       to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI:SS')   as commit_time,
       commit_log_elements.change_type, 
       element.name as filename, 
       categories.name as category, 
       commit_log.committer, 
       ports.short_description, 
       commit_log.message_id, 
       commit_log.encoding_losses, 
       commit_log.description, 
       B.name as port, 
       B.status as port_status,
       commit_log_elements.revision_name as revision_name,
       element.status, 
       commit_log_ports.needs_refresh, 
       ports.date_added, 
       ports.forbidden, 
       ports.broken,
       ports.deprecated,
       ports.ignore,
       security_notice.id  AS security_notice_id";

	if ($User->id) {
		$sql .= ",
     onwatchlist";
   }


	$sql .="
		   from commit_log LEFT OUTER JOIN security_notice ON commit_log.id = security_notice.commit_log_id,
			    categories, element, commit_log_port_elements, commit_log_elements, 
			    element B, commit_log_ports, ports ";

	#
	# if the watch list id is provided (i.e. they are logged in and have a watch list id...)
	#
	if ($User->id) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element 
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = $User->id
          AND watch_list.in_service
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = ports.element_id";
	}

	$sql .= "
	 where commit_log.message_id                          = '" . AddSlashes($MessageID) . "'
	   and commit_log_port_elements.commit_log_id         = commit_log.id 
	   and commit_log_port_elements.commit_log_element_id = commit_log_elements.id 
	   and commit_log_elements.element_id                 = element.id 
	   and commit_log_port_elements.port_id               = ports.id 
	   and ports.category_id                              = categories.id 
	   and ports.element_id                               = B.id 
	   and commit_log.id                                  = commit_log_ports.commit_log_id
	   and ports.id                                       = commit_log_ports.port_id
	   and ports.element_id                               = $ElementID
	 order by 1";

	if ($Debug) echo '<PRE>' . $sql . '</PRE>';

	$result = pg_exec($db, $sql);

	if (!$result) {
		print pg_errormessage() . "<br><pre>$sql</pre>\n";
		exit;
	} else {

		$i = 0;
		$NumRows = pg_numrows($result);
		if (!$NumRows) {
			echo 'No such commit found';
			syslog(LOG_NOTICE, 'No such commit found: $ElementID="' . $ElementID . '" $MessageID="' . $MessageID . '"');
			exit;
		}
		for ($i = 0; $i < $NumRows; $i++) {
			$myrow = pg_fetch_array($result, $i);
//			echo "<TR><TD>" . $myrow["port_id"] . "</TD><TD>" . $myrow["port"] . "</TD></TR>";
			$rows[$i] = $myrow;
		}

		$myrow = $rows[0];
		$PathNamePrefixToRemove = '/ports/' . $myrow["category"] . '/' . $myrow["port"] . '/';

		header("HTTP/1.1 200 OK");

		freshports_Start($myrow["category"] . '/' . $myrow["port"] . " - commit details",
    	           "freshports - new ports, applications",
        	       "FreeBSD, index, applications, ports");

		?>

		<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
		<TR><TD VALIGN="top" width="100%">
		<?
 		echo '<TABLE BORDER="1" WIDTH="100%" CELLSPACING="0" CELLPADDING="5">' . "\n<tr>\n";

		echo freshports_PageBannerText("Commit Details", 3);

		echo '<TR><TD COLSPAN="3">';
		echo '<BIG><B><A HREF="/' . $myrow["category"] . '/">' . $myrow["category"] . '</A>';
		echo '/<A HREF="/' . $myrow["category"] . '/' . $myrow["port"] . '/">' . $myrow["port"] . '</A>';

		if (strlen($myrow["version"]) > 0) {
			echo ' ' . $myrow["version"];
			if (strlen($myrow["revision"]) > 0 && $myrow["revision"] != "0") {
	    		echo FRESHPORTS_VERSION_REVISION_JOINER . $myrow["revision"];
			}
		}

		echo '</B></BIG>';

		$HTML = '';
		if ($User->id) {
			if ($myrow["onwatchlist"]) {
				$HTML .= ' '. freshports_Watch_Link_Remove($User->watch_list_add_remove, $myrow["onwatchlist"], $myrow["element_id"]);
			} else {
				$HTML .= ' '. freshports_Watch_Link_Add   ($User->watch_list_add_remove, $myrow["onwatchlist"], $myrow["element_id"]);
			}
		}

		$HTML .= "\n";

		// indicate if this port has been removed from cvs
		if ($myrow["port_status"] == "D") {
			$HTML .= " " . freshports_Deleted_Icon() . "\n";
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

		if ($myrow["deprecated"]) {
			$HTML .= freshports_Deprecated_Icon() . "\n";
		}

		if ($myrow["ignore"]) {
			$HTML .= freshports_Ignore_Icon() . "\n";
		}

		echo $HTML;

		echo ' <CODE CLASS="code">' . $myrow["short_description"] . '</CODE>';

		echo '</TD></TR>';

		echo "<TR><TD nowrap><B>Date</B></TD><TD><B>Committer</B></TD><TD><b>Description</b></TD></TR>\n";      

		echo "<TR>";
		echo '    <TD VALIGN="top" nowrap>' . $myrow["commit_date"] . ' ' . $myrow["commit_time"];
		echo '&nbsp;' . freshports_Email_Link($myrow["message_id"]);

		echo '&nbsp;&nbsp;'. freshports_Commit_Link($myrow["message_id"]);

		if ($myrow["encoding_losses"] == 't') {
			echo '&nbsp;' . freshports_Encoding_Errors();
		}

		if (IsSet($myrow["security_notice_id"])) {
			echo ' <a href="/security-notice.php?message_id=' . $myrow["message_id"] . '">' . freshports_Security_Icon() . '</a>';
		}

		echo "</TD>\n";
		echo '    <TD VALIGN="top">' . $myrow["committer"]         . "</TD>\n";
		echo '    <TD VALIGN="top" WIDTH="100%">' . freshports_PortDescriptionPrint($myrow["description"], $myrow["encoding_losses"], $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($myrow["message_id"], $freshports_CommitMsgMaxNumOfLinesToShow)) . "</TD>\n";
		echo "</TR>";
		?>

		</TABLE>

		<BR>

		<table border="1" width="100%" CELLSPACING="0" CELLPADDING="5">
		<TR>
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

		echo freshports_PageBannerText($title, 3);

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
			echo '  <TD WIDTH="100%"><A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $myrow["pathname"] . '">';

			echo '<CODE CLASS="code">' . str_replace($PathNamePrefixToRemove, '', $myrow["pathname"]) . "</CODE></A></TD>";
			echo "</TR>\n";
		}
	}

	?>

	</table>
	</TD>
		<TD VALIGN="top" WIDTH="*" ALIGN="center">
		<? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/side-bars.php') ?>
		</TD>
	</TR>
	</table>
	<? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/footer.php') ?>
	</body>
	</html>

	<?

}