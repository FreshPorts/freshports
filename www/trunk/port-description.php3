<?
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");

$ShowEverything=1;

if (!$port || $port != strval(intval($port))) {
   $port = 0;                                     
} else {                                              
   $port = intval($port);                     
}

$sql = "select ports.id, ports.name as port, ports.id as ports_id, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ports.long_description, UNIX_TIMESTAMP(ports.date_created) as date_created, ".
       "date_format(date_created, '$FormatDate $FormatTime') as date_created_formatted, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, " .
       "ports.depends_run, ports.depends_build, ports.categories, ports.status, " .
       "ports.broken, ports.forbidden " .
       "from ports, categories  ".
       "WHERE ports.id = $port ".
       "  and ports.primary_category_id       = categories.id ";

if ($Debug) {
   echo "\nsql = $sql\n";
}

$result = mysql_query($sql, $db);

if (!$result) {
   print mysql_error() . "<br>\n";
   exit;
}

$myrow = mysql_fetch_array($result);

$NumRows = mysql_num_rows($result);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - 

<?
   if ($NumRows) {
      $Title = $myrow["category"] . "/" . $myrow["port"];
   } else {
      $Title = "error - nothing found";
   }

   echo $Title;
?>
</title>
</head>

 <? include("./_private/header.inc") ?>
<table width="100%" border="0">
<tr>
  <td>
<p>This page contains the description of a single port.</p>

<p>I've just added <i>Also listed in</i>.  Some ports appear in more than one category.  
If there is no link to a category, that is because that category
is a virtual category, and I haven't catered for those yet. But <a href="changes.php3">I plan to</a></p>
<p>
<img src="images/new.gif"  alt="new feature" border="0" width="28" height="11" hspace="2">Click on 
<img src="images/logs.gif" alt="Files within this port affected by this commit" border="0" WIDTH="17" HEIGHT="20" hspace="2"> 
to see what files changed for this port in that commit.</p>
</td>
</tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td colspan="3" bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - 
<?
   echo $Title;
?> 
 </font></td>
</tr>
<tr><td colspan="3" valign="top" width="100%">
<?

if ($NumRows) {

   $HideDescription=1;
   $ShowCategories=1;
   $ShowDepends=1;
   include("./_private/port-basics.inc");

   echo $HTML;

   echo "<dl><dd><pre>";
   echo $myrow["long_description"];
   echo "</pre></dd></dl>\n";

   echo '<tr height="20"><td colspan="3"></td></tr>' . "\n";

   echo '<tr><td><table border="1" width="100%" CELLSPACING="0" CELLPADDING="5"bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">' . "\n";
   echo '<tr height="20"><td colspan="3" bgcolor="#AD0040"><font color="#FFFFFF"><font size="+1">Commit History</font> (may be incomplete: see Changes link above for full details)</font></td></tr>' . "\n";
   echo "<tr><td><b>Date</b></td><td><b>Committer</b></td><td><b>Description</b></td></tr>\n";

   $sql = "select change_log_port.id, commit_date, update_description, committer " .
          "  from change_log, change_log_port " .
          " where change_log.id                     = change_log_port.change_log_id ".
          "   and change_log_port.port_id           =  $port". 
          " order by commit_date desc ";

   $result = mysql_query($sql, $db);
   $numrows = 0;
   while ($myrow = mysql_fetch_array($result)) {
      $numrow++;
      echo "<tr><td valign='top'><font size='-1'>" . $myrow["commit_date"]        . "</font></td>\n";
      echo "    <td valign='top'>" . $myrow["committer"]          . "</td>\n";
      echo '    <td valign="top"><a href="files.php3?id=' . $myrow["id"] .
                      '"><img src="images/logs.gif" alt="Files within this port affected by this commit" border="0" WIDTH="17" HEIGHT="20" hspace="2"></a>' . 
                       $myrow["update_description"] . "</td>\n";
      echo "</tr>\n";
   }
   echo "</table></td></tr>\n";
}

?>

</table>
</td>
<td valign="top">

<? include("./_private/side-bars.php3") ?>

</td>
</tr>
</table>
 <? include("./_private/footer.inc") ?>
</body>
</html>
