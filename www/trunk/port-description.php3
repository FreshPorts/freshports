<?
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
require( "/www/freshports.org/_private/freshports.php3");

if (!$port) {
   $port = 1;
}

$sql = "select ports.id, ports.name as port, ports.id as ports_id, ports.last_update as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ports.long_description, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage " .
       "from ports, categories  ".
       "WHERE ports.id = $port ".
       "  and ports.primary_category_id = categories.id";

//echo "\nsql = $sql\n";

$result = mysql_query($sql, $db);

$myrow = mysql_fetch_array($result);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - 

<?
   echo $myrow["category"] . "/";
   echo $myrow["port"];
?>
</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">
 <? include("/www/freshports.org/_private/header.inc") ?>
<table width="100%" border="0">
<tr>
  <td colspan="2">This page contains the description of a single port.</td>
</tr>
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
include("/www/freshports.org/_private/port-basics.inc");

echo $HTML;

echo "<dl><dd><pre>";
echo $myrow["long_description"];
echo "</pre></dd></dl>";
?>

</td>
<td>

<? include("/www/freshports.org/_private/side-bars.php3") ?>

</td>
</tr>
</table>
 <? include("/www/freshports.org/_private/footer.inc") ?>
</body>
</html>
