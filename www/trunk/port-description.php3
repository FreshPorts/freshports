<?
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");

if (!$port) {
   $port = 1;
}

$sql = "select ports.id, ports.name as port, ports.id as ports_id, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ports.long_description, UNIX_TIMESTAMP(ports.date_created) as date_created, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, " .
       "ports.depends_run, ports.depends_build, ports.categories, ports.status, " .
       "change_log.commit_date as updated, change_log.committer, change_log.update_description, " .
       "change_log_details.change_type, ports.last_change_log_detail_id " .
       "from ports, categories, change_log, change_log_details  ".
       "WHERE ports.id = $port ".
       "  and ports.primary_category_id       = categories.id " .
       "  and ports.last_change_log_detail_id = change_log_details.id " .
       "  and change_log.id                   = change_log_details.change_log_id";

echo "\nsql = $sql\n";

$result = mysql_query($sql, $db);

$myrow = mysql_fetch_array($result);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - 

<?
   echo $myrow["category"] . "/";
   echo $myrow["port"];
?>
</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">
 <? include("./_private/header.inc") ?>
<table width="100%" border="0">
<tr>
  <td colspan="2">
<p>This page contains the description of a single port.</p>

<p>I've just added <i>Also listed in</i>.  Some ports appear in more than one category.  
If there is no link to a category, that is because that category
is a virtual category, and I haven't catered for those yet. But <a href="changes.php3">I plan to</a></p>

<p>Also recently added is the <i>required to run</i> and <i>required to build</i>.  These two fields
show the prerequisite ports which are required for a particular port.  Note that my list differs
from that shown in the <a href="http://www.freebsd.org/ports/">FreeBSD Ports pages</a>.  I'm not
sure why.  My values are taken from the makefile.  I'm not sure where they get their values from.
They may be doing recursion, but that doesn't account for everything.  I did discuss this on the ports
mailing list, but we were unable to determine the reason for the differences.</p>

</td>
</tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td colspan="2" bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - 
<?
   echo $myrow["category"] . "/";
   echo $myrow["port"];
?> 
 </font></td>
</tr>
<tr><td valign="top" width="100%">
<?
$HideDescription=1;
$ShowCategories=1;
$ShowDepends=1;
include("./_private/port-basics.inc");

echo $HTML;

echo "<dl><dd><pre>";
echo $myrow["long_description"];
echo "</pre></dd></dl>";
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
