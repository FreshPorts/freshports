<?
   # $Id: category.php3,v 1.21.2.1 2001-11-25 20:50:58 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");
   require("./include/getvalues.php");

#$Debug=1;

#
# if no category provided or category is not numeric, try
# category zero.  inval returns zero if non-numeric
#
#echo $category         . "<br>";
#echo intval($category) . "<br>";

#if (!$category) {                          
#   $category = 0;
#}

if (!$category || $category != strval(intval($category))) {
   $category = 0;
} else {
   $category = intval($category);
}

#echo "<br>";
#echo 'intval($category)     = ' . intval($category)     . "<br>";

#
# append the category id to the cache_file
#
$cache_file .= "." . $category;

$title = freshports_Category_Name($category, $db);

   freshports_Start($title,
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");
?>


<table width="100%" border="0">
<tr><td COLSPAN="2">
This page lists all the ports in a given category.
</td></tr>
<tr><td valign="top" width="100%">
<table width="100%" border="0">
  <tr>
    <td bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - <? echo $title ?></font></td>
  </tr>
<script language="php">



$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

// make sure the value for $sort is valid

$LimitRows	= 100;

if (!$start) {
   $start = 1;
}

if ($start < 1) {
   $start = 1;
}

if ($start > 1) {
   $cache_file .= ".$start";

//   echo "adding $start to $cache_file";
}

if ($start > $end) {
   $end = $start + $LimitRows -1;
}

if (!$end) {
   $end = $start + $LimitRows - 1;
}

$sort ="port";

$sql = "select ports.id, element.name as port, ports.id as ports_id, commit_log.commit_date as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "commit_log.description as update_description, " .
       "ports.maintainer, ports.short_description, " .
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, element.status, " .
       "ports.broken, ports.forbidden " .
       "from ports, categories, element, commit_log  ".
       "WHERE ports.category_id    = categories.id " .
       "  and categories.id        = $category " .
       "  and ports.element_id     = element.id " .
       "  and ports.last_commit_id = commit_log.id ";

/*
       "date_format(date_created, '$FormatDate $FormatTime') as date_created_formatted, " .
UNIX_TIMESTAMP(ports.date_created) as date_created, ".

if ($next) {
   $sql .= "and ports.name > '$next' ";
}
*/

$sql .= "order by $sort";

#echo $sql;

//$sql .= " limit $LimitRows";

if ($Debug) {
   echo $sql . '<BR>';
   echo "GlobalHideLastChange = $GlobalHideLastChange\n";
}

$result = pg_exec($db, $sql);
if (!$result) {
   print pg_errormessage() . "<br>\n";
   exit;
}
$NumRows = pg_numrows($result);
if ($end > $NumRows) {
//   echo "end was $end and is now $NumRows";
   $end = $NumRows;
}

if ($NumRows == 0) {
   $HTML .= freshports_echo_HTML("no results found.  Is this a valid category id?<br>\n");
} else {

for ($i = 0; $i < $NumRows; $i++) {
   $myrow = pg_fetch_array($result, $i);
   $rows[$i]=$myrow;
}

$HTML .= freshports_echo_HTML('<tr><td>');

$HTML .= freshports_echo_HTML('<table width="100%" border="0">');

// get the list of topics, which we need to modify the order
$LastPort = '';

$HTML .= freshports_echo_HTML("<tr><td>showing ");
if ($start == 1 and $end == $NumRows) {
   $HTML .= freshports_echo_HTML("all");
} else {
   $HTML .= freshports_echo_HTML($start . " to " . $end);
}

$HTML .= freshports_echo_HTML(" of $NumRows ports</td></tr>\n");

$ShowShortDescription	= "Y";

$HTML .= freshports_echo_HTML("<TR>\n<TD>\n");

for ($i = $start; $i <= $end; $i++) {
	$myrow = $rows[$i-1];

	$HTML .= freshports_PortDetails($myrow, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription);

	$LastPort = $myrow["port"];
} // end for

$HTML .= freshports_echo_HTML('</tr>');

$HTML .= freshports_echo_HTML('</td></tr>');

$HTML .= freshports_echo_HTML('</table>');

} // results found

// here $i will be $end + 1
if ($end < $NumRows) {
   $HTML .= freshports_echo_HTML('</td></tr><tr><td><a href=' . basename($PHP_SELF) . "?category=$category&start=". ($end+1));
   $HTML .= freshports_echo_HTML(">next page</a></td></tr>");
}

if ($start > 1) {
   $HTML .= freshports_echo_HTML('</td></tr><tr><td><a href=' . basename($PHP_SELF) . "?category=$category");
   $temp = $start - $LimitRows - 1;
   if ($temp > 1) {
      $HTML .= freshports_echo_HTML("&start=" . $temp);
   }
   $HTML .= freshports_echo_HTML(">previous page</a></td></tr>"); 
}

$HTML .= freshports_echo_HTML('</td></tr>');
echo $HTML;      

#   if ($NumRows != 0) {
#      $fpwrite = fopen($cache_file, 'w');
#      if(!$fpwrite) {                      
#         echo 'error on open<br>';
#         echo "$errstr ($errno)<br>\n";
#         exit;                  
#      } else {                            
#//         echo 'written<br>';             
#         fputs($fpwrite, $HTML);         
#         fclose($fpwrite);
#      }
#   }
#} else {                                
#//   echo 'looks like I\'ll read from cache this time';                             
#   if (file_exists($cache_file)) {                            
#      include($cache_file);
#   }          
#}

</script>
</table>
</td>
  <td valign="top" width="*">
   <? include("./include/side-bars.php") ?>
 </td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
