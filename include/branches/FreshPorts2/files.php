<?
	# $Id: files.php,v 1.1.2.3 2002-04-02 02:43:02 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once("./include/common.php");
	require_once("./include/freshports.php");
	require_once("./include/databaselogin.php");
	require_once("./include/getvalues.php");


function freshports_Files($PortID, $CommitID, $db) {
	GLOBAL $TableWidth;

	$Debug=0;


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
		   commit_log_port_elements.port_id, 
		   to_char(commit_log.commit_date - SystemTimeAdjust()', 'DD Mon YYYY')  as commit_date,
		   to_char(commit_log.commit_date - SystemTimeAdjust()', 'HH24:MI')      as commit_time,
		   commit_log_elements.change_type, element.name as filename, categories.name as category, commit_log.committer, 
		   ports.short_description,
		   commit_log.description, B.name as port, commit_log_elements.revision_name as revision_name 
		   from commit_log, ports, categories, element, commit_log_port_elements, commit_log_elements, 
			    element B, commit_log_ports
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

	if ($Debug) echo $sql;

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
		echo '<BIG><B><A HREF="' . $myrow["category"] . '">' . $myrow["category"] . '</A>';
		echo '/<A HREF="' . $myrow["category"] . '/' . $myrow["port"] . '/">' . $myrow["port"] . '</A>';
		echo '</B></BIG>';

		echo ' - <CODE CLASS="code">' . $myrow["short_description"] . '</CODE>';

		echo  '<BR>' . $myrow["commit_date"] . ' ' . $myrow["commit_time"];

		echo '</TD></TR>';

		echo "<TR><TD WIDTH='15'><B>Date</B></TD><TD><B>Committer</B></TD><TD><b>Description</b></TD></TR>\n";      

		echo "<TR>";
		echo '    <TD VALIGN="top">' . $myrow["commit_date"] . ' ' . $myrow["commit_time"] . "</TD>\n";
		echo '    <TD VALIGN="top">' . $myrow["committer"]         . "</TD>\n";
		echo '    <TD VALIGN="top"><PRE CLASS="code">' . $myrow["description"] . "</CODE></TD>\n";
		echo "</TR>";
		?>

		</TABLE>

		<BR>

		<table border="1" width="100%" CELLSPACING="0" CELLPADDING="5"bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
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
			<TD><b>Action</b></TD><TD><B>Revision</B></TD><TD colspan="2"><b>File</b></TD>
		</TR>
		<?

#		$NumRows = $i;
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
		<TD VALIGN="top" width="*">
		<? include("./include/side-bars.php") ?>
		</TD>
	</TR>
	</table>
	</TR>
	</table>
	<? include("./include/footer.php") ?>
	</body>
	</html>

	<?

}