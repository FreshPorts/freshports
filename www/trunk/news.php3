<?php

$cache_file     =       "/tmp/freshports.org.cache.news";
$cache_time     =       3600;

$text_file	=	"/www/freshports.org/news.txt";

require("/www/freshports.org/_private/commonlogin.php3");

$MyMaxArticles = 10;

if (!$MaxArticles || $MaxArticles < 1 || $MaxArticles > $MyMaxArticles) {
    $MaxArticles = $MyMaxArticles;
}

if ($MaxArticles == $MyMaxArticles) {
   $OutputFromCach = 1;
} else {
   $OutputFromCach = 0;
}

if ($OutputFromCach) {
   $time           =       split(" ", microtime());

   srand((double)microtime()*1000000);
   $cache_time_rnd =       300 - rand(0, 600);

  if ( (!(file_exists($cache_file))) || ((filectime($cache_file) + $cache_time - $time[1]) + $cache_time_rnd < 0) || (!(filesize($cache_file))) ) {
      // we need to build the table and update the cache
      $UpdateCache = 1;
      $BuildTable  = 1;
   } else {
      // the cache is fine, we'll use that.
      $UpdateCache = 0;
      $BuildTable  = 0;
   }
} else {
   $BuildTable     = 1;
   $OutputFromCach = 0;
}

if ($BuildTable == 1) {
   $HTML .= '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' . "\n";
   $HTML .= '        "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
   $HTML .= '<rss version="0.91">' . "\n";

   $HTML .= "\n";

   $HTML .= '<channel>' . "\n";
   $HTML .= '  <title>freshports - the place for ports</title>' . "\n";
   $HTML .= '  <link>http://freshports.org/</link>' . "\n";
   $HTML .= '  <description>The easiest place to find ports</description>' . "\n";
   $HTML .= '  <language>en-us</language>' . "\n";

$sort ="updated desc, category, version";

$sql = "select ports.id, ports.name as port, ports.last_update as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage " .
       "from ports, categories  ".
       "WHERE ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id ";

$sql .= "order by $sort limit 20";

//echo $sql;

   $result = mysql_query($sql, $db);
   while ($myrow = mysql_fetch_array($result)) {
      $HTML .= '  <item>' . "\n";
      $HTML .= '    <title>' . $myrow["category"] . "/" . $myrow["port"] . '</title>' . "\n";
      $HTML .= '    <link>http://freshports.org/port-description.php3?port=' . $myrow["id"] . '</link>' . "\n";
      $HTML .= '    <description>' . trim($myrow["update_description"]) . '</description>' . "\n";
      $HTML .= '  </item>' . "\n";

      $TEXT .= $myrow["category"] . "/" . $myrow["port"] . "\n";
      $TEXT .= 'http://freshports.org/port-description.php3?port=' . $myrow["id"] . "\n";
   }

   $HTML .= '</channel>' . "\n";
   $HTML .= '</rss>' . "\n";
   
}

if ($UpdateCache == 1) {
   // output $HTML the cache
   $fpwrite = fopen($cache_file, 'w');
   if(!$fpwrite) {
//      echo "$errstr ($errno)<br>\n";
//      exit;
   } else {
       fputs($fpwrite, $HTML);
       fclose($fpwrite);
   }

   // output the text file now too
   $fpwrite = fopen($text_file, 'w');
   if(!$fpwrite) {
//      echo "$errstr ($errno)<br>\n";
//      exit;
   } else {
       fputs($fpwrite, $TEXT);
       fclose($fpwrite);
   }
}

if ($OutputFromCach ==1) {
   // give them the cach
   if (file_exists($cache_file)) {
      echo '<?xml version="1.0"?>', "\n";
      include($cache_file);
   }
} else {
   // give them the custom output
   echo '<?xml version="1.0"?>', "\n";
   echo $HTML;
}

?>
