<?
	# $Id: files.php,v 1.1.2.5 2002-02-13 22:58:31 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	if (!$id || $id != strval(intval($id))) {
		$id = 0;
	}

$sql = "
select element_pathname(element.id) as pathname, commit_log_port_elements.commit_log_id, 
	   commit_log_port_elements.port_id, 
	   to_char(commit_log.commit_date + INTERVAL '$CVSTimeAdjustment seconds', 'DD Mon YYYY')  as commit_date,
	   to_char(commit_log.commit_date + INTERVAL '$CVSTimeAdjustment seconds', 'HH24:MI')      as commit_time,
	   commit_log_elements.change_type, element.name as filename, categories.name as category, commit_log.committer, 
	   ports.short_description,
	   commit_log.description, B.name as port, commit_log_elements.revision_name as revision_name 
	   from commit_log, ports, categories, element, commit_log_port_elements, commit_log_elements, element B 
 where commit_log.id                                  = $id 
   and commit_log_port_elements.commit_log_id         = commit_log.id 
   and commit_log_port_elements.commit_log_element_id = commit_log_elements.id 
   and commit_log_elements.element_id                 = element.id 
   and commit_log_port_elements.port_id               = ports.id 
   and ports.category_id                              = categories.id 
   and ports.element_id                               = B.id 
 order by 1 limit 30";

#echo $sql;

$result = pg_exec($db, $sql);

if (!$result) {
   print pg_errormessage() . "<br>\n";
   exit;
} else {

	$i = 0;
	$NumRows = pg_numrows($result);
	while ($myrow = pg_fetch_array($result, $i)) {
//		echo "<TR><TD>" . $myrow["port_id"] . "</TD><TD>" . $myrow["port"] . "</TD></TR>";
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
	echo '<TR><TD colspan="3" bgcolor="#AD0040">';
	echo '<FONT COLOR="#FFFFFF" SIZE="+1">';
	echo 'Commit Details</FONT></TD></TR>' . "\n";
	echo '<TR><TD COLSPAN="3">';
	echo '<FONT size="+1"><A HREF="' . $myrow["category"] . '">' . $myrow["category"] . '</A>';
	echo '/<A HREF="' . $myrow["category"] . '/' . $myrow["port"] . '">' . $myrow["port"] . '</A>';
	echo '</FONT>';

	echo ' - <CODE CLASS="code">' . $myrow["short_description"] . '</CODE>';

	echo  '<BR>' . $myrow["commit_date"] . ' ' . $myrow["commit_time"];

	echo '</TD></TR>';

	echo "<TR><TD><B>Date</B></TD><TD><B>Committer</B></TD><TD><b>Description</b></TD></TR>\n";      

	echo "<TR>";
	echo "    <TD VALIGN='top'>" .$myrow["commit_date"] . ' ' . $myrow["commit_time"] . "</TD>\n";
	echo "    <TD VALIGN='top'>" . $myrow["committer"]          . "</TD>\n";
	echo '    <TD VALIGN="top"><PRE CLASS="code">' . $myrow["description"] . "</CODE></TD>\n";
	echo "</TR>";
?>

</TABLE>

<BR>

<table border="1" width="100%" CELLSPACING="0" CELLPADDING="5"bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
<?

   echo '<TR height="20"><TD colspan="3" bgcolor="#AD0040"><font color="#FFFFFF"><font size="+1">';

	switch ($NumRows) {
		case 0:
			echo 'no files found';
			break;

		case 1:
			echo '1 file found';
			break;

		default:
			echo $i . ' files found';
	}

	echo  '</font></TD></TR>';
   ?>
   <TR>
     <TD><b>Action</b></TD><TD><B>Revision</B></TD><TD colspan="2"><b>File</b></TD>
   </TR>
   <?

#   $NumRows = $i;
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
