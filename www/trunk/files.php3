<?
require( "./_private/commonlogin.php3");
require( "./_private/getvalues.php3");
require( "./_private/freshports.php3");

$sql = "select change_log_port.change_log_id, change_log_port.id, change_log_port.port_id, details, " .
       "change_type, ports.name as port, categories.name as category, change_log.committer, change_log.commit_date, " .
       "change_log.update_description " .
       "from change_log_details, change_log_port, ports, categories, change_log " .
       "where change_log_details.change_log_port_id = $id " .
       "  and change_log_port.id                    = change_log_details.change_log_port_id " .
       "  and change_log_port.port_id               = ports.id " .
       "  and ports.primary_category_id             = categories.id " .
       "  and change_log.id                         = change_log_port.change_log_id " .
       "order by id desc limit 30";

//echo $sql;

$result = mysql_query($sql, $db);

if (!$result) {
   echo mysql_errno().": ".mysql_error()."<BR>";
} else {
   $i = 0;
   while ($myrow = mysql_fetch_array($result)) {
//      echo "<tr><td>" . $myrow["port_id"] . "</td><td>" . $myrow["port"] . "</td></tr>";
      $rows[$i] = $myrow;
      $i++;
   }

   $myrow = $rows[0];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - <? echo $myrow["category"] . '/' . $myrow["port"] ?> - commit details</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">
 <? include("./_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">Welcome to the freshports.org where you can find the latest information on your favourite
ports.
</td></tr>
  <tr>
    <td colspan="2">
This page shows the files associated with one port within a given commit.
    </td>
  </tr>
<tr><td valign="top" width="100%">
<?
   echo '<table border="1" width="100%" CELLSPACING="0" CELLPADDING="5"bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">' . "\n";
   echo '<tr height="20"><td colspan="3" bgcolor="#AD0040"><font color="#FFFFFF" size="+1">Commit Details</font></td></tr>' . "\n";
   echo "<tr><td><b>Date</b></td><td><b>Committer</b></td><td><b>Description</b></td></tr>\n";      

   echo "<tr>";
   echo "    <td valign='top'><font size='-1'>" . $myrow["commit_date"]        . "</font></td>\n";
   echo "    <td valign='top'>" . $myrow["committer"]          . "</td>\n";
   echo '    <td valign="top">' . $myrow["update_description"] . "</td>\n";
   echo "</tr>";

   echo '<tr height="20"><td colspan="3" bgcolor="#AD0040"><font color="#FFFFFF"><font size="+1">' . "$i files found" . '</font></td></tr>';
   ?>
   <tr>
     <td><b>Action</b></td><td colspan="2"><b>File</b></td>
   </tr>
   <?

   $NumRows = $i;
   for ($i = 0; $i < $NumRows; $i++) {
      $myrow = $rows[$i];
      echo "<tr>\n";
//      echo '  <td><a href="port-description.php3?port='. $myrow["port_id"] . '">' . $myrow["port"] . "</a></td>";

      switch ($myrow["change_type"]) {
         case "M":
            $Change_Type = "modify";
            break;

         case "A":
            $Change_Type = "import"; 
            break;

         case "R":
            $Change_Type = "remove"; 
            break;

         default:
            $Change_Type = $myrow["change_type"] ; 
      }

      echo "  <td>" . $Change_Type . "</td>";
      echo '  <td colspan="2"><a href="' . $freshports_CVS_URL . $myrow["category"] . '/' . $myrow["port"] . '/' . $myrow["details"] . '">' .
              '<img src="images/logs.gif" alt="Changes to this file" border="0" WIDTH="15" HEIGHT="22" hspace="2"></a>' . 
               $myrow["details"] . "</td>";
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
