	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>

<head>
<meta name="description" content="freshport">
<meta name="keywords" content="FreeBSD, topics, index">

<?
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
require( "/www/freshports.org/_private/freshports.php3");

if (!$category) {                        
   $category = 1;
}

$title = freshports_Category_Name($category, $db);

?>

<!--// DVL Software is a New Zealand company specializing in database applications. //-->
<title>freshports - <? echo $title ?></title>
</head>

<body bgcolor="#ffffff" link="#0000cc">

<table width="100%">
<tr><rd>Welcome to the freshports.org test page. This site is not yet in production. We are still
testing. Information found here may be widely out of date and/or inaccurate.  Use at your own risk.       
</td></tr>
  <tr>
    <td bgcolor="#AD0040" height="29"><big><big><font color="#FFFFFF">freshports - <? echo $title ?></font></big></big></td>
  </tr>
<script language="php">



$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

// make sure the value for $sort is valid

$cache_file     =       "/tmp/freshports.org.cache.category." . $category;
$LastUpdateFile =       "/www/freshports.org/work/msgs/lastupdate";

$sort ="port";
$LimitRows = 20;

srand((double)microtime()*1000000);
$cache_time_rnd =       300 - rand(0, 600);

$UpdateCache = 0;
if (!file_exists($cache_file)) {
//   echo 'cache does not exist<br>';
   // cache does not exist, we create it
   $UpdateCache = 1;
} else {
//   echo 'cache exists<br>';
   if (!file_exists($LastUpdateFile)) {
      // no updates, so cache is fine.
//      echo 'but no update file<br>';
   } else {
//      echo 'cache file was ';
      // is the cache older than the db?
      if ((filectime($cache_file) + $cache_time_rnd) < filectime($LastUpdateFile)) {
//         echo 'created before the last database update<br>';
         $UpdateCache = 1;
      } else {
//         echo 'crated after the last database update<br>';
      }
   }
}

$UpdateCache = 1;

if ($UpdateCache == 1) {
//   echo 'time to update the cache';

$sql = "select ports.id, ports.name as port, ports.id as ports_id, ports.last_update as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage  " .
       "from ports, categories  ".
       "WHERE ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id " .
       "and categories.id = $category ";

if ($next) {
   $sql .= "and ports.name > '$next' ";
}

$sql .= "order by $sort";

$sql .= " limit $LimitRows";

//echo $sql;

$result = mysql_query($sql, $db);
$NumRows = mysql_num_rows($result);

$HTML .= freshports_echo_HTML('<tr><td>');

$HTML .= freshports_echo_HTML('<table width="*" border=1>');

// get the list of topics, which we need to modify the order
$NumTopics=0;
$LastPort = '';
while ($myrow = mysql_fetch_array($result)) {
   $NumTopics++;
   $HideCategory = 1;
   if ($NumTopics == 1) {
      $FirstPort = $myrow["port"];
   }

//   echo "$NumTopics<br>";
//   require("/www/freshports.org/_private/port-basics.inc");
   $HTML .= freshports_echo_HTML("<dl>");

   $HTML .= freshports_echo_HTML("<b>" . $myrow["port"]);
   if (strlen($myrow["version"]) > 0) {
      $HTML .= freshports_echo_HTML('-' . $myrow["version"]);
   }

   $HTML .= freshports_echo_HTML("</b>");

   // indicate if this port needs refreshing from CVS
   if ($myrow["needs_refresh"] == "Y") {
      $HTML .= freshports_echo_HTML(' <font size="-1">[refresh]</font>');
   }

   if (!$HideCategory) {
      $URL_Category = "category.php3?category=" . $myrow["category_id"];
      $HTML .= freshports_echo_HTML(' <font size="-1"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></font>');
   }

   $HTML .= freshports_echo_HTML("<dd>");

   // description
   $HTML .= freshports_echo_HTML($myrow["short_description"] . "<br>\n");

   // maintainer
   $HTML .= freshports_echo_HTML('Maintained by: <a href="mailto:' . $myrow["maintainer"]);
   $HTML .= freshports_echo_HTML('?cc=ports@FreeBSD.org&amp;subject=FreeBSD%20Port:%20' . $myrow["port"] . "-" . $myrow["version"] . '">');
   $HTML .= freshports_echo_HTML($myrow["maintainer"] . '</a></br>' . "\n");

   if ($myrow["committer"]) {
      $HTML .= freshports_echo_HTML('last change committed by ' . $myrow["committer"]);  // separate lines in case committer is null

      $HTML .= freshports_echo_HTML(' on <font size="-1">' . $myrow["updated"] . '</font><br>' . "\n");

      $HTML .= freshports_echo_HTML($myrow["update_description"] . "<br>" . "\n");
   } else {
      $HTML .= freshports_echo_HTML("no changes recorded in FreshPorts<br>\n");
   }

   if (!$HideDescription) {
      // Long descripion
      $HTML .= freshports_echo_HTML('<a HREF="port-description.php3?port=' . $myrow["id"] .'">Description</a>');

      $HTML .= freshports_echo_HTML(' <b>:</b> ');
   }

   // changes
   $HTML .= freshports_echo_HTML('<a HREF="http://www.FreeBSD.org/cgi/cvsweb.cgi/ports/' .
            $myrow["category"] . '/' .  $myrow["port"] . '">Changes</a>');
   $HTML .= freshports_echo_HTML(' <b>:</b> ');

   // download
   $HTML .= freshports_echo_HTML('<a HREF="ftp://ftp.FreeBSD.org/pub/FreeBSD/branches/-current/ports/' .
            $myrow["category"] . '/' .  $myrow["port"] . '.tar">Download Port</a>');

   if ($myrow["package_exists"] == "Y") {
      // package
      $HTML .= freshports_echo_HTML(' <b>:</b> ');
      $HTML .= freshports_echo_HTML('<a HREF="ftp://ftp.FreeBSD.org/pub/FreeBSD/FreeBSD-stable/packages/' .
               $myrow["category"] . '/' .  $myrow["port"] . "-" . $myrow["version"] . '.tgz">Package</a>');
   }

   if ($myrow["homepage"]) {
      $HTML .= freshports_echo_HTML(' <b>:</b> ');
      $HTML .= freshports_echo_HTML('<a HREF="' . $myrow["homepage"] . '">Homepage</a>');
   }

   $HTML .= freshports_echo_HTML("<p></p></dd>");
   $HTML .= freshports_echo_HTML("</dl>" . "\n");

   $LastPort = $myrow["port"];
}

$HTML .= freshports_echo_HTML('</tr>');

mysql_free_result($result);

$HTML .= freshports_echo_HTML("<p>$NumTopics ports found</p>\n");

$HTML .= freshports_echo_HTML('</td></tr>');

$HTML .= freshports_echo_HTML('</table>');
if ($NumRows == $LimitRows) {
   $HTML .= freshports_echo_HTML('</td></tr><tr><td><a href=' . basename($PHP_SELF) . "?category=$category&begin=$LastPort");
//   if ($previous) {
      $HTML .= freshports_echo_HTML("&end=$FirstPort");
//   }

   $HTML .= freshports_echo_HTML(">next page</a></td></tr>");
}

if ($next) {
   $HTML .= freshports_echo_HTML('</td></tr><tr><td><a href=' . basename($PHP_SELF) . "?category=$category");
   if ($previous) {
      $HTML .= freshports_echo_HTML("&next=$previous");
   }
   $HTML .= freshports_echo_HTML(">previous page</a></td></tr>"); 
}

$HTML .= freshports_echo_HTML('</td></tr>');
//echo $HTML;      
                                
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

</script>
  <tr>
    <td height="21"></td>
  </tr>
</table>
</body>
</html>
