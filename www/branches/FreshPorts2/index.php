<?php
	#
	# $Id: index.php,v 1.1.2.76 2003-07-04 14:59:16 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

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
$num          = $MaxNumberOfPorts;;
$days         = $NumberOfDays;
$dailysummary = 7;

if (In_Array('num',          $_GET)) $num				= AddSlashes($_GET["num"]);
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

if ($User->id) {
	$sql = "
SELECT SECURITY.*,
       W.*
FROM (
";
}

$sql = "
SELECT PEC.*,
       security_notice.id  AS security_notice_id
FROM (
SELECT PORTELEMENT.*,
       categories.name AS category
FROM (
SELECT LCPPORTS.*,
       element.name    AS port,
       element.status  AS status

FROM (
SELECT LCPCLLCP.*,
       ports.forbidden,
       ports.broken,
       ports.element_id                     AS element_id,
       CASE when clp_version  IS NULL then ports.version  else clp_version  END as version,
       CASE when clp_revision IS NULL then ports.revision else clp_revision END AS revision,
       ports.version                        AS ports_version,
       ports.revision                       AS ports_revision,
       date_part('epoch', ports.date_added) AS date_added,
       ports.short_description              AS short_description,
       ports.category_id
FROM (
 SELECT LCPCL.*, 
         port_id,
         commit_log_ports.port_version  AS clp_version,
         commit_log_ports.port_revision AS clp_revision,
         commit_log_ports.needs_refresh AS needs_refresh
    FROM 
   (SELECT commit_log.id     AS commit_log_id, 
           commit_date       AS commit_date_raw,
           message_subject,
           message_id,
           committer,
           description       AS commit_description,
           to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  AS commit_date,
           to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI')      AS commit_time,
           encoding_losses
     FROM commit_log JOIN
               (SELECT latest_commits_ports.commit_log_id
                   FROM latest_commits_ports
               ORDER BY latest_commits_ports.commit_date DESC
                 LIMIT $MaxNumberOfPorts) AS LCP
           ON commit_log.id = LCP.commit_log_id) AS LCPCL JOIN commit_log_ports
                         ON commit_log_ports.commit_log_id = LCPCL.commit_log_id
                         AND commit_log_ports.commit_log_id > latest_commits_ports_anchor()) AS LCPCLLCP JOIN ports
on LCPCLLCP.port_id = ports.id) AS LCPPORTS JOIN element
on LCPPORTS.element_id = element.id) AS PORTELEMENT JOIN categories
on PORTELEMENT.category_id = categories.id) AS PEC LEFT OUTER JOIN security_notice
ON PEC.commit_log_id = security_notice.commit_log_id
";

if ($User->id) {
   $sql .= "
) AS SECURITY LEFT OUTER JOIN
(SELECT element_id as wle_element_id, COUNT(watch_list_id) as watch
  FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $User->id
       AND watch_list.in_service
GROUP BY element_id) AS W
ON        W.wle_element_id = SECURITY.element_id
";
}

$sql .= "order by commit_date_raw desc, category, port ";

#$sql .= " limit $MaxNumberOfPorts";

if ($Debug) echo "\n<pre>sql=$sql</pre>\n";


$result = pg_exec($database, $sql);
if ($result) {
   $numrows = pg_numrows($result);
	if ($numrows) { 
	
		$i=0;
		$GlobalHideLastChange = "N";
		for ($i = 0; $i < $numrows; $i++) {
			$myrow = pg_fetch_array ($result, $i);
			$rows[$i] = $myrow;
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
#				print "NumRows = $NumRows\n<BR>";
				$HTML = "";
				unset($ThisCommitLogID);
				for ($i = 0; $i < $NumRows; $i++) {
#echo 'commit_log_id=' . $myrow["commit_log_id"] . '<br>';
					$myrow = $rows[$i];
					$ThisCommitLogID = $myrow["commit_log_id"];

					if ($LastDate <> $myrow["commit_date"]) {
						$LastDate = $myrow["commit_date"];
						$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0">' . "\n";
						$HTML .= '   <FONT COLOR="#FFFFFF"><BIG>' . FormatTime($myrow["commit_date"], 0, "D, j M Y") . '</BIG></FONT>' . "\n";
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

							if (IsSet($myrow["security_notice_id"])) {
								$HTML .= ' <a href="/security-notice.php?message_id=' . $myrow["message_id"] . '">' . freshports_Security_Icon() . '</a>';
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

							if ($User->id) {
								if ($myrow["watch"]) {
									$HTML .= ' '. freshports_Watch_Link_Remove($User->watch_list_add_remove, $myrow["watch"], $myrow["element_id"]) . ' ';
								} else {
									$HTML .= ' '. freshports_Watch_Link_Add   ($User->watch_list_add_remove, $myrow["watch"], $myrow["element_id"]) . ' ';
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
