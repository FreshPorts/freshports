<?
   
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
?>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - search</title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<html>
<body>
<? include("/www/freshports.org/_private/header.inc") ?>
<table width="100%">
<tr><td valign="top" td colspan="2">
OK, we have just a very simple search.  Eventually this will be extended. If you find any bugs, please
let <a href="http://freshports.org/phorum/list.php?f=3">me know</a>.
</td></tr>
<tr><td valign="top" width="100%">                    
<table width="100%" border="0">                       
  <tr>                                                
    <td colspan="2" bgcolor="#AD0040" height="30"><font color="#FFFFFF" size="+2">freshports - search</font></td>
  </tr>
<tr><td>
<?
if ($search) {
/*
   while (list($name, $value) = each($HTTP_POST_VARS)) {
      echo "$name = $value<br>\n";
   }

   echo "you submitted<br>\n";
*/

   echo "</td></tr>\n<tr><td>";

$sql = "select ports.id, ports.name as port, ports.last_update as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, ports.status " .
       "from ports, categories  ".
       "WHERE ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id ";

switch ($stype) {
   case "name":
      $sql .= "and ports.name like '%$query%'";
      break;

   case "text":
      $sql .= "and ports.short_description like '%$query%' or ports.long_description like '%$query%'";
      break;

   case "maintainer":
      $sql .= "and ports.maintainer like '%$query%'";
      break;

   case "requires":
      $sql .= "and (ports.depends_build like '%$query%' or ports.depends_run like '%$query%')";
      break;
}

$sql .= " order by ports.name";

//echo "$sql<br>\n";


$result = mysql_query($sql, $db); 
$NumRows = mysql_num_rows($result);

}
?>
<form METHOD="POST" ACTION="<? echo $PHP_SELF ?>">
  <p>Search for: <input NAME="query" size="20"  value="<? echo $query?>"> <select NAME="stype" size="1">
    <option VALUE="name"      <? if ($stype == "name")       echo 'selected'?>>Port Name</option>
    <option VALUE="text"      <? if ($stype == "text")       echo 'selected'?>>Description</option>
    <option VALUE="maintainer"<? if ($stype == "maintainer") echo 'selected'?>>Maintainer</option>
    <option VALUE="requires"  <? if ($stype == "requires")   echo 'selected'?>>Requires</option>
  </select> <input TYPE="submit" VALUE="search"> </p>
  <input type="hidden" name="search" value="1">
</form>

</td></tr>
<?
if ($search) {
echo "<tr><td>\n";
if ($NumRows == 0) {
   $HTML .= " no results found<br>\n";
} else {

//   echo "retrieving $NumRows rows<br>\n";

   for ($i = 0; $i < $NumRows; $i++) {
      $myrow = mysql_fetch_array($result);
      $rows[$i]=$myrow;
//      echo "retrieving row $i<br>\n";
   }

   for ($i = 0; $i < $NumRows; $i++) {
//      echo "displaying row $i<br>\n";
      $myrow = $rows[$i];

      include("/www/freshports.org/_private/port-basics.inc");
   }

}
echo $HTML;
echo "</td></tr>\n";
}
?>
</table>

</td>
  <td valign="top" width="*">
    <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
</table>
<? include("/www/freshports.org/_private/footer.inc") ?>
</body>
</html>
