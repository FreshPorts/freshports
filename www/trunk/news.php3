<?php

$text_file	=	"./news.txt";

require("./_private/commonlogin.php3");
$Debug=0;

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

if ($Debug) {
echo '<br>';
echo '$cache_file=', $cache_file, '<br>';
echo '$LastUpdateFile=', $LastUpdateFile , '<br>';
echo '!(file_exists($cache_file))=',     !(file_exists($cache_file)), '<br>';
echo '!(file_exists($LastUpdateFile))=', !(file_exists($LastUpdateFile)), "<br>";
echo 'filectime($cache_file)=',          filectime($cache_file), "<br>";
echo 'filectime($LastUpdateFile)=',      filectime($LastUpdateFile), "<br>";
echo '$cache_time_rnd=',                 $cache_time_rnd, '<br>';
echo 'filectime($cache_file) - filectime($LastUpdateFile) + $cache_time_rnd =', filectime($cache_file) - filectime($LastUpdateFile);
}

if ($Debug) {
   echo "BuildTable = $BuildTable";

}

if ($BuildTable == 1) {
   $HTML .= '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' . "\n";
   $HTML .= '        "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
   $HTML .= '<rss version="0.91">' . "\n";

   $HTML .= "\n";

   $HTML .= '<channel>' . "\n";
   $HTML .= '  <title>FreshPorts - the place for ports</title>' . "\n";
   $HTML .= '  <link>http://freshports.org/</link>' . "\n";
   $HTML .= '  <description>The easiest place to find ports</description>' . "\n";
   $HTML .= '  <language>en-us</language>' . "\n";

$sort ="updated desc, category, version";
$sort ="change_log.commit_date desc, change_log.id asc, ports.name, category, version";

$sql = "select ports.id, ports.name as port, ports.last_update as updated, " .
       "categories.name as category, categories.id as category_id, ports.version as version, ".
       "ports.committer, ports.last_update_description as update_description, " .
       "ports.maintainer, ports.short_description, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage " .
       "from ports, categories  ".
       "WHERE ports.system = 'FreeBSD' ".
       "and ports.primary_category_id = categories.id ";

$sql = "select ports.id, ports.name as port, change_log.commit_date as updated_raw, categories.name as category, " .
       "ports.committer, ports.last_update_description as update_description, ports.version as version, " .
       "ports.maintainer, ports.short_description, UNIX_TIMESTAMP(ports.date_created) as date_created, " .
       "date_format(date_created, '$FormatDate $FormatTime') as date_created_formatted, categories.id as category_id, ".
       "ports.package_exists, ports.extract_suffix, ports.needs_refresh, ports.homepage, ports.status, " .
       "date_format(change_log.commit_date, '$FormatDate') as updated_date, change_log.committer, " .
       "date_format(change_log.commit_date, '$FormatTime') as updated_time, change_log.id as change_log_id," .
       "change_log.update_description, date_format(change_log.commit_date, '%Y-%m-%d') as commit_date, " .
       "ports.last_change_log_id, date_format(change_log.commit_date, '%T') as commit_time " .
       "from ports, categories, change_log, change_log_port  ".
       "WHERE ports.system                    = 'FreeBSD' ".
       "  and ports.primary_category_id       = categories.id " .
       "  and change_log_port.port_id         = ports.id " .
       "  and change_log.id                   = change_log_port.change_log_id " .
       "  and change_log.commit_date          > '" . date("Y-m-d", time() - 60*60*24*7) . "' ";


$sql .= " order by $sort limit 20";

if ($Debug) {
   echo $sql;
}

   $result = mysql_query($sql, $db);
   if (!$result) {
      echo mysql_error();
   }
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
     echo "$errstr ($errno)<br>\n";
     exit;
   } else {
       fputs($fpwrite, $HTML);
       fclose($fpwrite);
   }

   // output the text file now too
   $fpwrite = fopen($text_file, 'w');
   if(!$fpwrite) {
     echo "$errstr ($errno)<br>\n";
     exit;
   } else {
       fputs($fpwrite, $TEXT);
       fclose($fpwrite);
   }
}

if ($OutputFromCach == 1) {
   
   if ($Debug) {
      echo "outputting from cache ==> ", $cache_file, "<br>\n";
   }
   // give them the cach
   if (file_exists($cache_file)) {
      echo '<?xml version="1.0"?>', "\n";
      include($cache_file);
   }
} else {
   if ($Debug) {
      echo "giving them the HTML", "<br>\n";
   }
   // give them the custom output
   echo '<?xml version="1.0"?>', "\n";
   echo $HTML;
}

?>
