<?
	# $Id: index.php,v 1.1.2.31 2002-04-10 17:59:41 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("./include/getvalues.php");

	freshports_Start("the place for ports",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports");
$Debug=0;

if ($Debug) echo "UserID='$UserID'";

function freshports_SummaryForDay($MinusN) {          
   $BaseDirectory = "./archives";                     
   $Now = time();                                    
//   echo "$MinusN<br>\n"; 
   $File = $BaseDirectory . "/" . date("Y/m/d", $Now - 60*60*24*$MinusN) . ".inc";  
//   echo "$File<br>\n";
   if (file_exists($File)) {
      echo '<br><table WIDTH="152" BORDER="1" CELLSPACING="0" CELLPADDING="5"';
      echo '      bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">';
      echo '  <tr>';
      echo '<td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">';
      echo date("l j M", $Now - 60*60*24*$MinusN);
      echo '</font></td>';
      echo '       </tr>';
      echo '        <tr>';
      echo '         <td>';
      include($File);
      echo '   </td>';
      echo '   </tr>';
      echo '   </table>';
   }
}


//$Debug = 1;

if (!$StartAt) {
   if ($Debug) {
      echo "setting StartAt to zero<br>\n";
      echo "UserID = $UserID<br>\n";
   }
   $StartAt = 0;
} else {
   $NewStart = floor($StartAt / $MaxNumberOfPorts) * $MaxNumberOfPorts;
   if ($NewStart != $StartAt) {
      $URL = basename($PHP_SELF);
      if ($NewStart > 0) {
         $URL .= "?StartAt=$NewStart";
      } else {
         $URL = "/";
      }
      header("Location: " . $URL );
      // Make sure that code below does not get executed when we redirect.
      exit;
   }
}

if ($Debug) {
   echo "StartAt = $StartAt<br>\n";
}

?>

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">

<?
if (file_exists("announcement.txt") && filesize("announcement.txt") > 4) {
?>
  <tr>
    <td colspan="2">
       <? include ("announcement.txt"); ?>
    </td>
  </tr>
<?
}
?>

<script language="php">

function StripQuotes($string) {
	$string = str_replace('"', '', $string);

	return $string;
}


function GetPortNameFromFileName($file_name) {

	list($fake, $subtree, $category, $port, $extra) = split('/', $file_name, 4);

#	return $subtree;
	return "$category/$port";

}

      $numrows = $MaxNumberOfPorts;
      $database=$db;
      if ($database) {
#
# we limit the select to recent things by using a date
# otherwise, it joins the whole table and that takes quite a while
#
#$numrows=400;

if ($Debug) {
	echo "\$WatchListID = '$WatchListID'\n";
}

if ($WatchListID) {
	$sql = " SELECT	commits_latest.*, 
				 	CASE when watch_list_element.element_id is null
						then 0
						else 1
					END as watch
			   FROM	commits_latest left outer join watch_list_element
					 ON commits_latest.element_id        = watch_list_element.element_id
					AND watch_list_element.watch_list_id = $WatchListID
		   ORDER BY commit_date_raw desc, category, port";
} else {
	$sql = "select * from commits_latest order by commit_date_raw desc, category, port";
}

if ($Debug) echo "\n<pre>sql=$sql</pre>\n";

#exit;

         $result = pg_exec($database, $sql);
         if ($result) {
            $numrows = pg_numrows($result);
#            echo $numrows . " rows to fetch\n";
			if ($numrows) { 

				$i=0;
				$GlobalHideLastChange = "N";
#				unset($ThisChangeLogID);
				while ($myrow = pg_fetch_array ($result, $i)) {
					$rows[$i] = $myrow;

					#
					# if we do a limit, it applies to the big result set
					# not the resulting set if we also do a DISTINCT
					# thus, count the commit id's ourselves.
					#
#					if ($ThisChangeLogID <> $myrow["commit_log_id"]) {
#						$ThisChangeLogID  = $myrow["commit_log_id"];
						$i++;
#					}
#					echo "$i, ";
					if ($i >= $numrows) break;
				}

				$NumRows = $numrows;
				$LastDate = '';
				if ($NumRows > 1) {
					$LastChangeLogID = $rows[$i]["change_log_id"];
					$LastChangeLogID = -1;
				}

?>

<tr><td VALIGN="top" WIDTH="100%">
<table width="100%" border="1" CELLSPACING="0" CELLPADDING="8"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
<tr>
		<? freshports_PageBannerText("$MaxNumberOfPorts most recent commits", 3); ?>
        <? //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPorts) ?></BIG></B></font>
</tr>
<TR><TD>
<P>
Welcome to FreshPorts, where you can find the latest information on your favourite
ports. A port is marked as new for 10 days.
</P>

</TR></TD>

<?
#				print "NumRows = $NumRows\n<BR>";
				$HTML = "";
				unset($ThisChangeLogID);
				for ($i = 0; $i < $NumRows; $i++) {
					$myrow = $rows[$i];
					$ThisChangeLogID = $myrow["commit_log_id"];


					if ($LastDate <> $myrow["commit_date"]) {
						$LastDate = $myrow["commit_date"];
						$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
						$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($myrow["commit_date"], 0, "D, j M") . '</BIG></FONT>' . "\n";
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
							$HTML .= "<BR>\n";
						}

						$HTML .= '<BIG><B>';
						$HTML .= '<A HREF="/' . $myrow["category"] . '/' . $myrow["port"] . '/">';
						$HTML .= $myrow["port"];
						
						if (strlen($myrow["version"]) > 0) {
							$HTML .= ' ' . $myrow["version"];
							if (strlen($myrow["revision"]) > 0 && $myrow["revision"] != "0") {
					    		$HTML .= '-' . $myrow["revision"];
							}
						}

						$HTML .= "</A></B></BIG>\n";

						$HTML .= '<A HREF="/' . $myrow["category"] . '/">';
						$HTML .= $myrow["category"]. "</A>";
						$HTML .= '&nbsp;';

						if ($WatchListID) {
							if ($myrow["watch"]) {
								$HTML .= ' '. freshports_Watch_Link_Remove($myrow["element_id"]);
							} else {
								$HTML .= ' '. freshports_Watch_Link_Add($myrow["element_id"]);
							}
						}

						$HTML .= "\n&nbsp;";

						$HTML .= freshports_CommitFilesLink($myrow["commit_log_id"], $myrow["category"], $myrow["port"]);
						$HTML .= "&nbsp;";

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

						$HTML .= $myrow["short_description"] . "\n";

						$j++;
						$MultiplePortsThisCommit = 1;
					} // end while

					$i = $j - 1;

					$HTML .= "\n<BLOCKQUOTE>";

					$HTML .= freshports_PortDescriptionPrint($myrow["commit_description"]);


					$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";
				}

				$HTML .= "</td></tr>\n\n";

				echo $HTML;

	            echo "</table>\n";
			} else {
				echo "<P>Sorry, nothing found in the database....</P>\n";
			}
         } else {
            echo "read from test failed";
         }

#         pg_exec ($database, "end");
      } else {
         echo "no connection";
      }

</script>
</td>
  <td valign="top" width="*">
   <? include("./include/side-bars.php") ?>
<?
	freshports_SummaryForDay(0);
	freshports_SummaryForDay(1);
	freshports_SummaryForDay(2);
	freshports_SummaryForDay(3);
	freshports_SummaryForDay(4);
	freshports_SummaryForDay(5);
	freshports_SummaryForDay(6);
	freshports_SummaryForDay(7);
	freshports_SummaryForDay(8);
?>
 </td>
</tr>
</table>

<BR>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
