<?
   # $Id: port-description.php3,v 1.25.2.4 2001-11-26 06:50:48 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");
   require("./include/getvalues.php");

$ShowEverything=1;

if (!$port || $port != strval(intval($port))) {
   $port = 0;                                     
} else {                                              
   $port = intval($port);                     
}

$sql = "select ports.id, element.name as port, ports.id as ports_id, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.maintainer, ports.short_description, ports.long_description, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, " .
       "ports.depends_run, ports.depends_build, element.status, " .
       "ports.broken, ports.forbidden " .
       "from ports, categories, element  ".
       "WHERE ports.id = $port ".
       "  and ports.category_id	= categories.id " .
       "  and ports.element_id	= element.id";

if ($Debug) {
   echo "\nsql = $sql\n";
}

$result = pg_exec($db, $sql);

if (!$result) {
   print pg_errormessage() . "<br>\n";
   exit;
}

$myrow = pg_fetch_array ($result, 0);
$NumRows = pg_numrows($result);

   if ($NumRows) {
      $Title = $myrow["category"] . "/" . $myrow["port"];
   } else {
      $Title = "error - nothing found";
   }

   freshports_Start($Title,
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>

<TABLE WIDTH="100%" BORDER="0">
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
<TABLE WIDTH="100%" BORDER="0">
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

	$HideDescription	= 1;
	$ShowCategories		= 1;
GLOBAL	$ShowDepends;
$ShowDepends		= 1;

	$HTML .= freshports_PortDetails($myrow, $db, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription);


   echo $HTML;

   echo '<DL><DD>';
   echo nl2br(convertAllLinks(htmlspecialchars($myrow["long_description"])));
   echo "\n</DD>\n</DL>\n</TD>\n</TR>";

   echo '<tr><td><TABLE BORDER="1" width="100%" CELLSPACING="0" CELLPADDING="5"bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">' . "\n";
   echo '<tr height="20"><td colspan="3" bgcolor="#AD0040"><font color="#FFFFFF"><font size="+1">Commit History</font> (may be incomplete: see Changes link above for full details)</font></td></tr>' . "\n";
   echo "<tr><td><b>Date</b></td><td><b>Committer</b></td><td><b>Description</b></td></tr>\n";

   $sql = "select distinct commit_log.id, commit_date, description, committer " .
          "  from commit_log, commit_log_port " .
          " where commit_log.id            = commit_log_port.commit_log_id ".
          "   and commit_log_port.port_id  =  $port". 
          " order by commit_date desc ";

	$result = pg_exec($db, $sql);
	$numrows = pg_numrows($result);

#echo "sql = $sql\n";
#echo "that's $numrows rows\n";

	$i = 0;
	for ($i = 0; $i <= $NumRows; $i++) {
		$myrow = pg_fetch_array($result, $i);
		$i++;
		echo "<tr><td valign='top'><font size='-1'>" . $myrow["commit_date"]        . "</font></td>\n";
		echo '    <td valign="top">' . $myrow["committer"]          . "</td>\n";
		echo '    <td valign="top" WIDTH="*"><a href="files.php3?id=' . $myrow["id"] .
                      '"><img src="images/logs.gif" alt="Files within this port affected by this commit" border="0" WIDTH="17" HEIGHT="20" hspace="2"></a>' . 
                       nl2br(convertAllLinks(htmlspecialchars($myrow["description"]))) . "</td>\n";
		echo "</tr>\n";
	}

   echo "</TABLE>\n</TD>\n</TR>\n";
}

?>

</TABLE>
</TD>
<TD VALIGN="top">

<? include("./include/side-bars.php") ?>

</TD>
</TR>
</TABLE>
 <? include("./include/footer.php") ?>
</BODY>
</HTML>
