<?
	# $Id: index.php,v 1.1.2.48 2002-11-01 20:23:36 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

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
      echo '<br><TABLE WIDTH="152" BORDER="1" CELLSPACING="0" CELLPADDING="5">';
      echo '  <TR>';
      echo '<TD bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">';
      echo date("l j M", $Now - 60*60*24*$MinusN);
      echo '</font></TD>';
      echo '       </TR>';
      echo '        <TR>';
      echo '         <TD>';
      include($File);
      echo '   </TD>';
      echo '   </TR>';
      echo '   </TABLE>';
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
      $URL = basename($_SERVER["PHP_SELF"]);
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

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">

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
?>

<?php

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
	$sql = " SELECT	commits_latest_ports.*, 
				 	CASE when watch_list_element.element_id is null
						then 0
						else 1
					END as watch
			   FROM	commits_latest_ports left outer join watch_list_element
					 ON commits_latest_ports.element_id        = watch_list_element.element_id
					AND watch_list_element.watch_list_id = $WatchListID
		   ORDER BY commit_date_raw desc, category, port";
} else {
	$sql = "select * from commits_latest_ports order by commit_date_raw desc, category, port";
}

if ($Debug) echo "\n<pre>sql=$sql</pre>\n";

#exit;

         $result = pg_exec($database, $sql);
         if ($result) {
            $numrows = pg_numrows($result);
#			echo $numrows . " rows to fetch\n";
			if ($numrows) { 

				$i=0;
				$GlobalHideLastChange = "N";
				for ($i = 0; $i < $numrows; $i++) {
					$myrow = pg_fetch_array ($result, $i);
					$rows[$i] = $myrow;
				}

				$NumRows = $numrows;
				$LastDate = '';
				if ($NumRows > 1) {
					$LastChangeLogID = $rows[$i]["change_log_id"];
					$LastChangeLogID = -1;
				}

?>

<TR><TD VALIGN="top" WIDTH="100%">
<TABLE WIDTH="100%" border="1" CELLSPACING="0" CELLPADDING="8">
<TR>
		<? freshports_PageBannerText("$MaxNumberOfPorts most recent commits", 3); ?>
        <? //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPorts) ?>
</TR>
<TR><TD>
<P>
Welcome to FreshPorts, where you can find the latest information on your favourite
ports. A port is marked as new for 10 days.
</P>

</TD></TR>

<?
#				print "NumRows = $NumRows\n<BR>";
				$HTML = "";
				unset($ThisCommitLogID);
				for ($i = 0; $i < $NumRows; $i++) {
					$myrow = $rows[$i];
					$ThisCommitLogID = $myrow["commit_log_id"];

					if ($LastDate <> $myrow["commit_date"]) {
						$LastDate = $myrow["commit_date"];
						$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
						$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($myrow["commit_date"], 0, "D, j M") . '</BIG></FONT>' . "\n";
						$HTML .= '</TD></TR>' . "\n\n";
					}

					$j = $i;

					$HTML .= "<TR><TD>\n";

					// OK, while we have the log change log, let's put the port details here.

					# count the number of ports in this commit
					$NumberOfPortsInThisCommit = 0;
					$MaxNumberPortsToShow = 10;
					while ($j < $NumRows && $rows[$j]["commit_log_id"] == $ThisCommitLogID) {
						$NumberOfPortsInThisCommit++;
						$myrow = $rows[$j];

						if ($NumberOfPortsInThisCommit == 1) {
							GLOBAL $freshports_mail_archive;

							$HTML .= '<SMALL>';
							$HTML .= '[ ' . $myrow["commit_time"] . ' ' . freshports_CommitterEmailLink($myrow["committer"]) . ' ]';
							$HTML .= '</SMALL>';
							$HTML .= '&nbsp;';
							$HTML .= freshports_Email_Link($myrow["message_id"]);

							if ($myrow["encoding_losses"] == 't') {
								$HTML .= '&nbsp;' . freshports_Encoding_Errors();
							}
						}

						if ($NumberOfPortsInThisCommit <= $MaxNumberPortsToShow) {

							$HTML .= "<BR>\n";

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
									$HTML .= ' '. freshports_Watch_Link_Remove($myrow["element_id"]) . ' ';
								} else {
									$HTML .= ' '. freshports_Watch_Link_Add($myrow["element_id"]) . ' ';
								}
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
								$HTML .= ' '. freshports_Broken_Icon() . "\n";
							}

							$HTML .= freshports_CommitFilesLink($myrow["message_id"], $myrow["category"], $myrow["port"]);
							$HTML .= "&nbsp;";

							$HTML .= htmlspecialchars($myrow["short_description"]) . "\n";
						}

						$j++;
					} // end while


					if ($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) {
						$HTML .= '<BR>' . freshports_MorePortsToShow($myrow["message_id"], $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
					}

					$i = $j - 1;

					$HTML .= "\n<BLOCKQUOTE>";

					$HTML .= freshports_PortDescriptionPrint($myrow["commit_description"], $myrow["encoding_losses"], $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($myrow["message_id"], $freshports_CommitMsgMaxNumOfLinesToShow));

					$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";
				}

				echo $HTML;

	            echo "</TABLE>\n";
			} else {
				echo "<P>Sorry, nothing found in the database....</P>\n";
			}
         } else {
            echo "read from test failed";
         }
      } else {
         echo "no connection";
      }

?>
</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
   <? include($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php") ?>
<?

	for ($i = 0; $i < $NumberOfDays; $i++) {
		freshports_SummaryForDay($i);
	}
?>
 </TD>
</TR>
</TABLE>

<BR>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>


</body>
</html>
