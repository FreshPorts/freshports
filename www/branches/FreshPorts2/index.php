<?
	# $Id: index.php,v 1.1.2.10 2002-02-11 01:44:24 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("./include/getvalues.php");

	freshports_Start("the place for ports",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports");
$Debug=0;

//echo "UserID='$UserID'";

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
      echo date("l", $Now - 60*60*24*$MinusN);
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
$numrows=400;
$sql = " 
select DISTINCT commit_log.commit_date as commit_date_raw,
       commit_log.id as commit_log_id,
       commit_log.description as commit_description,
       to_char(commit_log.commit_date + INTERVAL '$CVSTimeAdjustment seconds', 'DD Mon YYYY') as commit_date,
       to_char(commit_log.commit_date + INTERVAL '$CVSTimeAdjustment seconds', 'HH24:MI') as commit_time,
	   commit_log_ports.port_id as port_id,
	   categories.name as category,
	   categories.id   as category_id,
	   element.name    as port,
	   commit_log_ports.port_version   as version,
	   element.status    as status,
	   commit_log_ports.needs_refresh  as needs_refresh,
	   ports.forbidden      as forbidden,
	   ports.broken         as broken,
	   date_part('epoch', ports.date_added) as date_added,
	   ports.short_description
  from commit_log_ports, commit_log, ports, element, categories
 where commit_log.commit_date         > '2002-01-01'
   and commit_log_ports.commit_log_id = commit_log.id
   and commit_log_ports.port_id       = ports.id
   and categories.id                  = ports.category_id
   and element.id                     = ports.element_id
order by commit_log.commit_date desc,
         commit_log_id,
         category, 
         port
         limit $numrows";

#echo "\n<pre>sql=$sql</pre>\n";

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
#						$ThisChangeLogID = $myrow["commit_log_id"];
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

<tr><td VALIGN="top">
<table width="<? echo $TableWidth ?>" border="1" CELLSPACING="0" CELLPADDING="8"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
<tr>
    <td colspan="3" bgcolor="#AD0040" height="30">
        <font color="#FFFFFF" size="+1"><? echo $FreshPortsName . ' - ' . $MaxNumberOfPorts ?> most recent commits
        <? //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPorts) ?></font>
    </td>
</tr>
<TR><TD>
<P>
Welcome to FreshPorts, where you can find the latest information on your favourite
ports. A port is marked as new for 10 days.
</P>

<P>
<B>Unless stated otherwise, everything should work now.</B>
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
						$HTML .= '<TR><TD COLSPAN="3" BGCOLOR="#AD0040" HEIGHT="0"><FONT COLOR="#FFFFFF" SIZE="+1">' . FormatTime($myrow["commit_date"], 0, "D, j M") . '</FONT></TD></TR>';
					}

					$j = $i;

					$HTML .= '<TR><TD>';

					// OK, while we have the log change log, let's put the port details here.
					$MultiplePortsThisCommit = 0;
					while ($j < $NumRows && $rows[$j]["commit_log_id"] == $ThisChangeLogID) {
						$myrow = $rows[$j];

						if ($MultiplePortsThisCommit) {
							$HTML .= '<BR>';
						}

						$HTML .= '<A HREF="/' . $myrow["category"] . '/' . $myrow["port"] . '/">';
						$HTML .= '<FONT SIZE="+1">' . $myrow["category"] . '/' . $myrow["port"];
						
						if (strlen($myrow["version"]) > 0) {
							$HTML .= ' ' . $myrow["version"];
						}

						$HTML .= "</FONT></A>";

						// indicate if this port needs refreshing from CVS
						if ($myrow["status"] == "D") {
							$HTML .= '<font size="-1"> [deleted]</font>';
						}
						if ($myrow["needs_refresh"]) {
							$HTML .= ' <font size="-1"> [refresh]</font>';
						}

						if ($myrow["date_added"] > Time() - 3600 * 24 * $DaysMarkedAsNew) {
							$MarkedAsNew = "Y";
							$HTML .= "<img src=\"/images/new.gif\" width=28 height=11 alt=\"new!\" hspace=2 > ";
						}

						if ($myrow["forbidden"]) {
							$HTML .= '<img src="images/forbidden.gif" alt="Forbidden" width="20" height="20" hspace="2">';
						}
						if ($myrow["broken"]) {
							$HTML .= '<img src="images/broken.gif" alt="Broken" width="17" height="16" hspace="2">';
						}

						$HTML .= '&nbsp;&nbsp;' . $myrow["short_description"] . '&nbsp;&nbsp;';

						if (!$MultiplePortsThisCommit) {
							$HTML .= '<FONT SIZE="-1">';
							$HTML .= '[ ' . $myrow["commit_time"] . ' ]';
							$HTML .= '</FONT>';
						}

						$j++;
						$MultiplePortsThisCommit = 1;
					} // end while

					$i = $j - 1;

					$HTML .= '<BLOCKQUOTE>';
					$HTML .= '<PRE CLASS="code">';

					$HTML .= convertAllLinks(htmlspecialchars(freshports_wrap($myrow["commit_description"])));

					$HTML .= '</PRE>';

					$HTML .= "</tr>\n";
				}

				$HTML .= "</td></tr>\n";

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
