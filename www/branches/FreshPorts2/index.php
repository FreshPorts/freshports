<?php
	#
	# $Id: index.php,v 1.1.2.83 2003-09-26 12:03:52 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_record.php');
	freshports_Start($FreshPortsSlogan,
					$FreshPortsName . ' - new ports, applications',
					'FreeBSD, index, applications, ports');
$Debug = 0;

if ($Debug) echo "\$User->id='$User->id'";

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
$num          = $MaxNumberOfPorts;
$days         = $NumberOfDays;
$dailysummary = 7;

if (In_Array('num',          $_GET)) $num			= AddSlashes($_GET["num"]);
if (In_Array('dailysummary', $_GET)) $dailysummary	= AddSlashes($_GET["dailysummary"]);
if (In_Array('days',         $_GET)) $days			= AddSlashes($_GET["days"]);


if (Is_Numeric($num)) {
	$MaxNumberOfPorts = min($MaxNumberOfPorts, max(10, $num));
} else {
	$num = MaxNumberOfPorts;
}

if (Is_Numeric($days)) {
	$NumberOfDays = min($NumberOfDays, max(0, $days));
} else {
	$days = $NumberOfDays;
}

if (Is_Numeric($dailysummary)) {
	$dailysummary = min($NumberOfDays, max(0, $dailysummary));
} else {
	unset($dailysummary);
}


$database=$db;
if ($database) {

$sql = "select * from LastestCommits($MaxNumberOfPorts, ";
if ($User->id) {
	$sql .= $User->id;
} else {
	$sql .= 0;
}
$sql .= ');';

if ($Debug) echo "\n<pre>sql=$sql</pre>\n";


$result = pg_exec($database, $sql);
if ($result) {
	$numrows = pg_numrows($result);
	if ($numrows) { 
	
		$i=0;
		$GlobalHideLastChange = "N";
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);
			$mycommit = new CommitRecord($database);
			$mycommit->PopulateValues($myrow);
			$commits[$i] = $mycommit;
		}
	
		$NumRows = $numrows;
		$LastDate = '';
?>

<TR><TD VALIGN="top" WIDTH="100%">
<TABLE WIDTH="100%" border="1" CELLSPACING="0" CELLPADDING="8">
<TR>
		<? echo freshports_PageBannerText("$MaxNumberOfPorts most recently changed ports", 3); ?>
        <? //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPorts) ?>
</TR>
<TR><TD>
<P>
Welcome to FreshPorts, where you can find the latest information on your favourite
ports. A port is marked as new for 10 days.
</P>

</TD></TR>

<?
				unset($ThisCommitLogID);
				for ($i = 0; $i < $NumRows; $i++) {
					$HTML = "";
					$mycommit = $commits[$i];
					$ThisCommitLogID = $mycommit->commit_log_id;

					if ($LastDate <> $mycommit->commit_date) {
						$LastDate = $mycommit->commit_date;
						$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
						$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($mycommit->commit_date, 0, "D, j M Y") . '</BIG></FONT>' . "\n";
						$HTML .= '</TD></TR>' . "\n\n";
					}

					$j = $i;

					$HTML .= "<TR><TD>\n";

					// OK, while we have the log change log, let's put the port details here.

					# count the number of ports in this commit
					$NumberOfPortsInThisCommit = 0;
					$MaxNumberPortsToShow = 10;
					while ($j < $NumRows && $commits[$j]->commit_log_id == $ThisCommitLogID) {
						$NumberOfPortsInThisCommit++;
						$mycommit = $commits[$j];

						if ($NumberOfPortsInThisCommit == 1) {
							GLOBAL $freshports_mail_archive;

							$HTML .= '<SMALL>';
							$HTML .= '[ ' . $mycommit->commit_time . ' ' . freshports_CommitterEmailLink($mycommit->committer) . ' ]';
							$HTML .= '</SMALL>';
							$HTML .= '&nbsp;';
							$HTML .= freshports_Email_Link($mycommit->message_id);

							if ($mycommit->EncodingLosses()) {
								$HTML .= '&nbsp;' . freshports_Encoding_Errors();
							}

							if (IsSet($mycommit->security_notice_id)) {
								$HTML .= ' <a href="/security-notice.php?message_id=' . $mycommit->message_id . '">' . freshports_Security_Icon() . '</a>';
							}

						}

						if ($NumberOfPortsInThisCommit <= $MaxNumberPortsToShow) {

							$HTML .= "<BR>\n";

							if (IsSet($mycommit->category) || $mycommit->category != '') {
								$HTML .= '<BIG><B>';
								$HTML .= '<A HREF="/' . $mycommit->category . '/' . $mycommit->port . '/">';
								$HTML .= $mycommit->port;
								$HTML .= '</A>';
			
								if (strlen($mycommit->version) > 0) {
									$HTML .= ' ' . $mycommit->version;
									if (strlen($mycommit->revision) > 0 && $mycommit->revision != "0") {
							    		$HTML .= '-' . $mycommit->revision;
									}
								}

								$HTML .= "</B></BIG>\n";

								$HTML .= '<A HREF="/' . $mycommit->category . '/">';
								$HTML .= $mycommit->category. "</A>";
								$HTML .= '&nbsp;';

								if ($User->id) {
									if ($mycommit->watch) {
										$HTML .= ' '. freshports_Watch_Link_Remove($User->watch_list_add_remove, $mycommit->watch, $mycommit->element_id) . ' ';
									} else {
										$HTML .= ' '. freshports_Watch_Link_Add   ($User->watch_list_add_remove, $mycommit->watch, $mycommit->element_id) . ' ';
									}
								}

								// indicate if this port has been removed from cvs
								if ($mycommit->status == "D") {
									$HTML .= " " . freshports_Deleted_Icon() . "\n";
								}

								// indicate if this port needs refreshing from CVS
								if ($mycommit->needs_refresh) {
								$HTML .= " " . freshports_Refresh_Icon() . "\n";
								}

								if ($mycommit->date_added > Time() - 3600 * 24 * $DaysMarkedAsNew) {
									$MarkedAsNew = "Y";
									$HTML .= freshports_New_Icon() . "\n";
								}

								if ($mycommit->forbidden) {
									$HTML .= ' ' . freshports_Forbidden_Icon() . "\n";
								}

								if ($mycommit->broken) {
									$HTML .= ' '. freshports_Broken_Icon() . "\n";
								}

								$HTML .= freshports_CommitFilesLink($mycommit->message_id, $mycommit->category, $mycommit->port);
								$HTML .= "&nbsp;";

							} else {
								$HTML .= '<BIG><B>';
								$PathName = preg_replace('|^/?ports/|', '', $mycommit->element_pathname);
								$HTML .= '<a href="/' . $PathName . '">' . $PathName . '</a>';
								$HTML .= "</B></BIG>\n";
							}
							$HTML .= htmlspecialchars($mycommit->short_description) . "\n";
						}

						$j++;
					} // end while


					if ($NumberOfPortsInThisCommit > $MaxNumberPortsToShow) {
						$HTML .= '<BR>' . freshports_MorePortsToShow($mycommit->message_id, $NumberOfPortsInThisCommit, $MaxNumberPortsToShow);
					}
					$i = $j - 1;

					$HTML .= "\n<BLOCKQUOTE>";

					$HTML .= freshports_PortDescriptionPrint($mycommit->commit_description, $mycommit->encoding_losses, $freshports_CommitMsgMaxNumOfLinesToShow, freshports_MoreCommitMsgToShow($mycommit->message_id, $freshports_CommitMsgMaxNumOfLinesToShow));

					$HTML .= "\n</BLOCKQUOTE>\n</TD></TR>\n\n\n";

					echo $HTML;
				}

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
   <? freshports_SideBar(); ?>

<BR>
<?

	if ($dailysummary) {
		for ($i = 0; $i < $dailysummary; $i++) {
			freshports_SummaryForDay($i);
		}
	} else {
		if ($NumberOfDays) {
			$Today = time();
			echo '
<TABLE WIDTH="155" BORDER="1" CELLSPACING="0" CELLPADDING="5">
	<TR>
		<TD BGCOLOR="#AD0040" height="30"><FONT COLOR="#FFFFFF"><BIG><B>Previous days</B></BIG></FONT></TD>
	</TR>
	<TR><TD>
';
			for ($i = 1; $i <= $NumberOfDays; $i++) {
				echo freshports_LinkToDate($Today - $i * 86400) . "<br>\n";
			}
			echo '
	</TD></TR>
</TABLE>

';
		}
	}
?>
 </TD>
</TR>
</TABLE>

<BR>

<?
freshports_ShowFooter();
?>

</body>
</html>
