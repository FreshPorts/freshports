<?
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">
 <? include("./_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">Welcome to the freshports.org where you can find the latest information on your favourite
ports.
</td></tr>
  <tr>
    <td colspan="2">Note: <font size="-1">[refresh]</font> indicates a port for which the Makefile, 
                  pkg/DESC, or pkg/COMMENT has changed and has not yet been updated within FreshPorts.
    </td>
  </tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
<tr>
    <td colspan="5" bgcolor="#AD0040" height="30">
        <font color="#FFFFFF" size="+1">freshports - change log
        <? echo ($StartAt + 1) . " - " . ($StartAt + 20) ?></font></td>
  </tr>
<script language="php">

$sql = "select change_log_port.change_log_id, change_log_port.id, change_log_port.port_id, details, change_type, ports.name " .
       "from change_log_details, change_log_port, ports " .
       "where change_log_details.change_log_port_id = $ChangePortID " .
       "  and change_log_port.id                    = change_log_details.change_log_port_id " .
       "  and change_log_port.port_id               = ports.id ".
       "order by id desc limit 30";

echo $sql;

$result = mysql_query($sql, $db);

if (!$result) {
   echo mysql_errno().": ".mysql_error()."<BR>";
} else {

   $i = 0;
   while ($myrow = mysql_fetch_array($result)) {
//      echo "<tr><td>" . $myrow["port_id"] . "</td><td>" . $myrow["name"] . "</td></tr>";
      $rows[$i] = $myrow;
      $i++;
   }
   echo "<tr><td>$i records found</td><td></td></tr>";

   $NumRows = $i;
   for ($i = 0; $i < $NumRows; $i++) {
      $myrow = $rows[$i];
      echo "<tr>\n";
      echo '  <td><a href="port-description.php3?port='. $myrow["port_id"] . '">' . $myrow["name"] . "</a></td>";
      echo "  <td>" . $myrow["change_type"]	. "</td>";
      echo "  <td>" . $myrow["details"]		. "</td>";
      echo "</tr>\n";
   }
}

</script>
</table>
</td>
  <td valign="top" width="*">
   <? include("./_private/side-bars.php3") ?>
 </td>
</tr>
</table>
</tr>
</table>
<? include("./_private/footer.inc") ?>
</body>
</html>
