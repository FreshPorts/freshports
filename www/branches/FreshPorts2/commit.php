<?php
	#
	# $Id: commit.php,v 1.1.2.30 2003-09-25 20:15:55 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	if (IsSet($_GET['message_id'])) $message_id = AddSlashes($_GET['message_id']);
	if (IsSet($_GET['commit_id']))  $commit_id  = AddSlashes($_GET['commit_id']);

	$Title = 'Commit found by ';
	if ($message_id) {
		$Title .= 'message id';
	} else {
		$Title .= 'commit id';
	}
	freshports_Start($Title,
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');
$Debug = 0;

if ($Debug) echo "UserID='$User->id'";

?>

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">

<?
if (file_exists("announcement.txt") && filesize("announcement.txt") > 4) {
?>
  <TR>
    <TD colspan="2">
       <? include ("announcement.txt"); ?>
    </TD>
  </TR>
<?
}
	if ($message_id != '' || $commit_id != '') {
	
?>

<TR><TD VALIGN="top" WIDTH="100%">
<TABLE WIDTH="100%" border="1" CELLSPACING="0" CELLPADDING="8">
<TR>
	<? echo freshports_PageBannerText($Title, 3); ?>
</TR>

<?php

	$numrows = $MaxNumberOfPorts;
	$database=$db;
	if ($database ) {
#
# we limit the select to recent things by using a date
# otherwise, it joins the whole table and that takes quite a while
#
#$numrows=400;

	$sql = '';

	$sql .= "
SELECT ports.id as port_id,
       ports.short_description,
       categories.name as category,
       commit_log.message_id as message_id,
       case when ports.element_id IS NOT NULL THEN element_pathname(ports.element_id) ELSE element_pathname(E.id) END as element_pathname,
       commit_log.committer,
       commit_log.description as commit_description,
       commit_log_ports.port_version as version,
       COALESCE(commit_log_ports.port_revision, commit_log_elements.revision_name) as revision,
       element.name as port,
       commit_log.id as commit_log_id,

       to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  as commit_date,
       to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI:SS')   as commit_time,
       commit_log.encoding_losses,
       element.name as port,
       element.status as status,
       commit_log.id as commit_log_id,
       commit_log_ports.needs_refresh,

       security_notice.id  AS security_no,
       commit_log_elements.element_id ";

	if ($User->id) {
		$sql .= ",
       onwatchlist";
   }

   $sql .= "
  FROM (commit_log LEFT OUTER JOIN security_notice ON (commit_log.id = security_notice.commit_log_id))
              LEFT OUTER JOIN commit_log_ports     ON (commit_log.id = commit_log_ports.commit_log_id)
              LEFT OUTER JOIN ports                ON commit_log_ports.port_id = ports.id
              LEFT OUTER JOIN element              ON ports.element_id         = element.id
              LEFT OUTER JOIN categories           ON ports.category_id        = categories.id
              LEFT OUTER JOIN commit_log_elements  ON commit_log.id                  = commit_log_elements.commit_log_id
              LEFT OUTER JOIN element E            ON commit_log_elements.element_id = E.id 
";

	if ($User->id) {
				$sql .= "
	      LEFT OUTER JOIN
	 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
	    FROM watch_list JOIN watch_list_element
	        ON watch_list.id      = watch_list_element.watch_list_id
	       AND watch_list.user_id = $User->id
          AND watch_list.in_service
	  GROUP BY wle_element_id) AS TEMP
	       ON TEMP.wle_element_id = element.id";
	      }
	
	if ($message_id) {
		$sql .= "\n         WHERE commit_log.message_id = '$message_id' \n";
	} else {
		$sql .= "\n         WHERE commit_log.id         = $commit_id \n";
	}
	
	$sql .= "ORDER BY category, port, element_pathname";


	if ($Debug) echo "\n<pre>sql=$sql</pre>\n";


   $result = pg_exec($database, $sql);

   if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) { 

			$i=0;
			$GlobalHideLastChange = "N";
#			unset($ThisChangeLogID);
			while ($myrow = pg_fetch_array ($result, $i)) {
				$rows[$i] = $myrow;
				#
				# if we do a limit, it applies to the big result set
				# not the resulting set if we also do a DISTINCT
				# thus, count the commit id's ourselves.
				#
#				if ($ThisChangeLogID <> $myrow["commit_log_id"]) {
#					$ThisChangeLogID  = $myrow["commit_log_id"];
					$i++;
#				}
#				echo "$i, ";
				if ($i >= $numrows) break;
			}

			$NumRows = $numrows;
			$LastDate = '';

#			print "NumRows = $NumRows\n<BR>";
			$HTML = "";
			unset($ThisChangeLogID);
			for ($i = 0; $i < $NumRows; $i++) {
				$myrow = $rows[$i];
				$ThisChangeLogID = $myrow["commit_log_id"];
                $IsPort = $myrow['port_id'] != '';


				if ($LastDate <> $myrow["commit_date"]) {
					$LastDate = $myrow["commit_date"];
					$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
					$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($myrow["commit_date"], 0, "D, j M Y") . '</BIG></FONT>' . "\n";
					$HTML .= '</TD></TR>' . "\n\n";
				}

				$j = $i;

				$HTML .= "<TR><TD>\n";

				// OK, while we have the log change log, let's put the port details here.
				$MultiplePortsThisCommit = 0;
				while ($j < $NumRows && $rows[$j]["commit_log_id"] == $ThisChangeLogID) {
					$myrow = $rows[$j];

					if ($MultiplePortsThisCommit) {
						$HTML .= '<BR>';
					}

					if (!$MultiplePortsThisCommit) {
						GLOBAL $freshports_mail_archive;

						$HTML .= '<SMALL>';
						$HTML .= '[ ' . $myrow["commit_time"] . ' ' . $myrow["committer"] . ' ]';
						$HTML .= '</SMALL>';
						$HTML .= '&nbsp;';
						$HTML .= freshports_Email_Link($myrow["message_id"]);

						if ($myrow["encoding_losses"] == 't') {
							$HTML .= '&nbsp;' . freshports_Encoding_Errors();
						}

						if (IsSet($myrow["security_notice_id"])) {
							$HTML .= ' <a href="/security-notice.php?message_id=' . $myrow["message_id"] . '">' . freshports_Security_Icon() . '</a>';
						}

						$HTML .= "<BR>\n";
					}

					$HTML .= '<BIG><B>';

					if ($IsPort) {
						$HTML .= '<A HREF="/' . $myrow["category"] . '/' . $myrow["port"] . '/">';
						$HTML .= $myrow["port"];
					
						if (strlen($myrow["version"]) > 0) {
							$HTML .= ' ' . $myrow["version"];
							if (strlen($myrow["revision"]) > 0 && $myrow["revision"] != "0") {
				    			$HTML .= '-' . $myrow["revision"];
							}
						}
					} else {
						$ElementPathname = preg_replace('|^/?ports/|', '', $myrow['element_pathname']);
						$HTML .= '<A HREF="/' . $ElementPathname . '">';
						$HTML .= $ElementPathname;
					}
					$HTML .= "</A></B></BIG>\n";

					$HTML .= '<A HREF="/' . $myrow["category"] . '/">';
					$HTML .= $myrow["category"]. "</A>";
					$HTML .= '&nbsp;';

					if ($User->id && $IsPort) {
						if ($myrow["onwatchlist"]) {
							$HTML .= freshports_Watch_Link_Remove($User->watch_list_add_remove, $myrow["onwatchlist"], $myrow["element_id"]);
						} else {
							$HTML .= freshports_Watch_Link_Add   ($User->watch_list_add_remove, $myrow["onwatchlist"], $myrow["element_id"]);
						}
					}
					
					$HTML .= "\n";

					if ($IsPort) {
						$HTML .= freshports_CommitFilesLink($myrow["message_id"], $myrow["category"], $myrow["port"]);
					}

					// indicate if this port has been removed from cvs
					if ($myrow["status"] == "D") {
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
						$HTML .= ' ' . freshports_Forbidden_Icon() . "\n";
					}

					if ($myrow["broken"]) {
						$HTML .= '&nbsp;' . freshports_Broken_Icon() . "\n";
					}

					$HTML .= ' ' . htmlspecialchars($myrow["short_description"]) . "\n";

					$j++;
					$MultiplePortsThisCommit = 1;
				} // end while

				$i = $j - 1;

				$HTML .= "\n<BLOCKQUOTE>\n";
				$HTML .= freshports_PortDescriptionPrint($myrow["commit_description"], $myrow["encoding_losses"]);
				$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";
			}

			echo $HTML;

		} else {
				echo '<tr><TD VALIGN="top"><P>Sorry, nothing found in the database....</P>' . "\n";
				echo "</TD></tr>";
			}
		} else {
			echo "read from test failed <pre>$sql</pre>";
		}
		pg_exec ($database, "end");
	} else {
		echo "no connection";
	}
	echo "</TABLE>\n";
} else {
	echo '<tr><td>nothing supplied, nothing found!</td></tr><tr>';
}


?>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">

	<?
	freshports_SideBar();
	?>

  </td>
</TR>
</TABLE>

<BR>

<?
freshports_ShowFooter();
?>

</body>
</html>
