<?
#
# $Author: dan $ $Date: 2001-03-23 04:36:57 $
# $Header: /home/dan/repositories/freshports-1/www/Attic/index.php3,v 1.21 2001-03-23 04:36:57 dan Exp $
# $Log: not supported by cvs2svn $
# $Name: not supported by cvs2svn $
# $Revision: 1.21 $
#

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
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");

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

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - the place for ports</title>
</head>

  <? include("./_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">Welcome to FreshPorts, where you can find the latest information on your favourite
ports.
</td></tr>
<tr><td colspan="2">Please report any bugs via the <a href="phorum/list.php?f=3">phorum</a>.  A port is marked as new for 10 days.
</td></tr>
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
<tr><td valign="top" width="100%">
<table width="100%" border="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
<tr>
    <td colspan="3" bgcolor="#AD0040" height="30">
        <font color="#FFFFFF" size="+1">freshports - <? echo $MaxNumberOfPorts ?> most recent commits
        <? //echo ($StartAt + 1) . " - " . ($StartAt + $MaxNumberOfPorts) ?></font>
    </td>
</tr>

<script language="php">

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

// make sure the value for $sort is valid

switch ($sort) {
/* sorting by port is disabled. Doesn't make sense to do this
   case "port":
      $sort = "version, commit_date desc";
      $
cache_file .= ".port";
      break;
*/
//   case "updated":
//      $sort = "updated desc, port";
//      break;

   default:
      $sort ="change_log.commit_date desc, change_log.id asc, ports.name, category, version";
      $cache_file .= ".updated";
}

$cache_file .= "." . $StartAt;

srand((double)microtime()*1000000);
$cache_time_rnd =       300 - rand(0, 600);

//$Debug=1;
if ($Debug) {
echo '<br>';
echo '$cache_file=', $cache_file, '<br>';
echo '$LastUpdateFile=', $LastUpdateFile , '<br>';
echo '!(file_exists($cache_file))=',     !(file_exists($cache_file)), '<br>';
echo '!(file_exists($LastUpdateFile))=', !(file_exists($LastUpdateFile)), "<br>";
echo 'filectime($cache_file)=',          filectime($cache_file), "<br>";
echo 'filectime($LastUpdateFile)=',      filectime($LastUpdateFile), "<br>";
echo '$cache_time_rnd=',                 $cache_time_rnd, '<br>';
echo 'filectime($cache_file) - filectime($LastUpdateFile) + $cache_time_rnd =', filectime($cache_file) - filectime($LastUpdateFile) + $cache_time_rnd, '<br>';
}

$UpdateCache = 0;
if (!file_exists($cache_file)) {
   if ($Debug) echo 'cache does not exist<br>';
   // cache does not exist, we create it
   $UpdateCache = 1;
} else {
   if ($Debug) echo 'cache exists<br>';
   if (!file_exists($LastUpdateFile)) {
      // no updates, so cache is fine.
      if ($Debug) echo 'but no update file<br>';
      $UpdateCache = 1;
   } else {
      if ($Debug) echo 'cache file was ';
      // is the cache older than the db?
      if ((filectime($cache_file) + $cache_time_rnd) < filectime($LastUpdateFile)) {
         if ($Debug) echo 'created before the last database update<br>';
         $UpdateCache = 1;
      } else {
         if ($Debug)  echo 'created after the last database update<br>';
      }
   }
}

//$UpdateCache = 1;

if ($UpdateCache == 1) {
   if ($Debug) echo 'time to update the cache';

$sql = "select ports.id, ports.name as port, change_log.commit_date as updated_raw, categories.name as category, " .
       "change_log.committer, ports.last_update_description as update_description, ports.version as version, " .
       "ports.maintainer, ports.short_description, UNIX_TIMESTAMP(ports.date_created) as date_created, " .
       "date_format(date_created, '$FormatDate $FormatTime') as date_created_formatted, categories.id as category_id, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, ports.status, " .
       "date_format(change_log.commit_date, '$FormatDate') as updated_date, change_log.committer, " .
       "date_format(change_log.commit_date, '$FormatTime') as updated_time, change_log.id as change_log_id," .
       "change_log.update_description, date_format(change_log.commit_date, '%Y-%m-%d') as commit_date, " .
       "ports.last_change_log_id, date_format(change_log.commit_date, '%T') as commit_time, " .
       "ports.broken, ports.forbidden " .
       "from ports, categories, change_log, change_log_port  ".
       "WHERE ports.system                    = 'FreeBSD' ".
       "  and ports.primary_category_id       = categories.id " .
       "  and change_log_port.port_id         = ports.id " .
       "  and change_log.id                   = change_log_port.change_log_id " .
       "  and change_log.commit_date          > '" . date("Y-m-d", time() - 60*60*24*7) . "' ";

$sql .= " order by $sort ";

$sql .= " limit $MaxNumberOfPorts ";

//echo $sql;

$result = mysql_query($sql, $db);

if (!$result) {
   echo mysql_errno().": ".mysql_error()."<BR>";
}

//$HTML .= '<tr><td>';

$i=0;
$GlobalHideLastChange = "N";
while ($myrow = mysql_fetch_array($result)) {
   $rows[$i] = $myrow;
   $i++;
//   echo "$i, ";
}

$NumRows = $i;
$LastDate = '';
if ($NumRows > 1) {
   $LastChangeLogID = $rows[$i]["change_log_id"];
   $LastChangeLogID = -1;
}

for ($i = 0; $i < $NumRows; $i++) {
   $myrow = $rows[$i];

   $ThisChangeLogID = $myrow["change_log_id"];

   if ($LastDate <> $myrow["commit_date"]) {
      $LastDate = $myrow["commit_date"];
      $HTML .= "<tr><td colspan='3'><font size='+1'>" . $myrow["updated_date"] . "</font></td></tr>";
   }

   $j = $i;

   $HTML .= "<tr><td valign='top' width='150'>";

   // OK, while we have the log change log, let's put the port details here.
   $MultiplePortsThisCommit = 0;
   while ($j < $NumRows && $rows[$j]["change_log_id"] == $ThisChangeLogID) {
   $myrow = $rows[$j];

//   include("./_private/port-basics.inc");


   if ($MultiplePortsThisCommit) {
      $HTML .= '<br>';
   }
   $HTML .= '<a href="port-description.php3?port=' . $myrow["id"]  . '">';
   $HTML .= "<b>" . $myrow["port"];
   if (strlen($myrow["version"]) > 0) {
      $HTML .= ' ' . $myrow["version"];
   }

   $HTML .= "</b></a>";

   $URL_Category = "category.php3?category=" . $myrow["category_id"];
   $HTML .= ' <font size="-1"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></font>';

   // indicate if this port needs refreshing from CVS
   if ($myrow["status"] == "D") {
      $HTML .= '<br><font size="-1">[deleted]</font>';
   }
   if ($myrow["needs_refresh"]) {
      $HTML .= ' <font size="-1">[refresh]</font>';
   }


   if ($myrow["date_created"] > Time() - 3600 * 24 * $DaysMarkedAsNew) {
      $MarkedAsNew = "Y";
      $HTML .= "<img src=\"/images/new.gif\" width=28 height=11 alt=\"new!\" hspace=2 > ";
   }

//   $HTML .= "<img src=\"/images/stop.gif\" width=16 height=16 alt=\"stop!\" hspace=2 > ";

   $j++;
   $MultiplePortsThisCommit = 1;
   } // end while

   $i = $j - 1;

   $HTML .= "</td><td valign='top'>";
   $HTML .= '<font size="-1">' . $myrow["updated_time"] . '</font>';

   $HTML .= "</td><td valign='top'>";
   if ($myrow["forbidden"]) {
      $HTML .= '<img src="images/forbidden.gif" alt="Forbidden" width="20" height="20" hspace="2">';
   }
   if ($myrow["broken"]) {
      $HTML .= '<img src="images/broken.gif" alt="Broken" width="17" height="16" hspace="2">'; 
   }
   $HTML .= htmlspecialchars($myrow["update_description"]) . "</td>\n";

   $HTML .= "</tr>\n";
}

  $HTML .= "</td></tr>\n";


echo $HTML;

   $fpwrite = fopen($cache_file, 'w');
   if(!$fpwrite) {
      echo 'error on open<br>';
      echo "$errstr ($errno)<br>\n";
      exit;
   } else {
//      echo 'written<br>';
      fputs($fpwrite, $HTML);
      fclose($fpwrite);
   }

} else {
//   echo 'looks like I\'ll read from cache this time';
   if (file_exists($cache_file)) {
      include($cache_file);
   }
}

/*
echo '<tr><td height="40" colspan="2" valign="bottom">';

if ($StartAt == 0) {
   echo 'Previous Page';
} else {
   echo '<a href="' . basename($PHP_SELF);
   if ($StartAt > $MaxNumberOfPorts) {
      echo '?StartAt=' . ($Start + $MaxNumberOfPorts);
   }
   echo '">Previous Page</a>';
}

echo '  <a href="' . basename($PHP_SELF) . "?StartAt=" . ($StartAt + $MaxNumberOfPorts) . '">Next Page</a>';

echo '</td></tr>';
*/

</script>
</table>
</td>
  <td valign="top" width="*">
   <? include("./_private/side-bars.php3") ?>
<?
freshports_SummaryForDay(0);
freshports_SummaryForDay(1);
freshports_SummaryForDay(2);
freshports_SummaryForDay(3);
?>
 </td>
</tr>
</table>
<? include("./_private/footer.inc") ?>
</body>
</html>
