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

<table width="100%">
<tr><rd>Welcome to the freshports.org test page. This site is not yet in production. We are still
testing. Information found here may be widely out of date and/or inaccurate.  Use at your own risk.
See also <a href="ports.php3">freshports by ports</a>.
</td></tr>
  <tr>
    <td bgcolor="#AD0040" height="29"><big><big><font color="#FFFFFF">freshports - 
<?
   echo $myrow["category"] . "/";
   echo $myrow["port"];
?> 
 </font></big></big></td>
  </tr>
<tr><td>
<?
$HideDescription=1;
include("/www/freshports.org/_private/port-basics.inc");

echo $HTML;

echo "<dl><dd>";
echo $myrow["long_description"];
echo "</dd></dl>";
?>

</td></tr></table>

</body>
</html>
