<?
	# $Id: news.php,v 1.1.2.1 2002-01-02 02:10:44 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("include/getvalues.php");

#$Debug=1;
#echo 'stuff';
#exit;

#phpinfo();

$text_file	=	"./news.txt";

#require("./include/commonlogin.php3");
#$Debug=1;

$ServerName = str_replace("freshports", "FreshPorts", $SERVER_NAME);

$MyMaxArticles = 10;

if (!$MaxArticles || $MaxArticles < 1 || $MaxArticles > $MyMaxArticles) {
    $MaxArticles = $MyMaxArticles;
}

if ($MaxArticles == $MyMaxArticles) {
   $OutputFromCach = 1;
} else {
   $OutputFromCach = 0;
}

#if ($BuildTable == 1) {
   $HTML .= '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' . "\n";
   $HTML .= '        "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
   $HTML .= '<rss version="0.91">' . "\n";

   $HTML .= "\n";

   $HTML .= '<channel>' . "\n";
   $HTML .= '  <title>FreshPorts - the place for ports</title>' . "\n";
   $HTML .= '  <link>http://' . $ServerName . '/</link>' . "\n";
   $HTML .= '  <description>The easiest place to find ports</description>' . "\n";
   $HTML .= '  <language>en-us</language>' . "\n";

$sort ="commit_log.commit_date desc, commit_log.id asc, element.name, category, version";

$sql = "
select DISTINCT commit_log.commit_date as commit_date_raw,
       commit_log.id as commit_log_id,
       commit_log.description as commit_description,
       to_char(commit_log.commit_date + INTERVAL '$CVSTimeAdjustment seconds', 'DD Mon YYYY') as commit_date,
       to_char(commit_log.commit_date + INTERVAL '$CVSTimeAdjustment seconds', 'HH24:MI') as commit_time,
	   commit_log_ports.port_id as port_id,
	   categories.name as category,
	   categories.id   as category_id,
	   element.name    as port,
	   commit_log_ports.port_version   as version,
	   element.status    as status,
	   commit_log_ports.needs_refresh  as needs_refresh,
	   ports.forbidden      as forbidden,
	   ports.broken         as broken,
	   date_part('epoch', ports.date_created) as date_created
  from commit_log_ports, commit_log, ports, element, categories
 where commit_log.commit_date         > '" . date("Y-m-d", time() - 60*60*24*14) . "'
   and commit_log_ports.commit_log_id = commit_log.id
   and commit_log_ports.port_id       = ports.id
   and categories.id                  = ports.category_id
   and element.id                     = ports.element_id";


#$AnnounceFile = "news.announcement.txt";
#if (file_exists($AnnounceFile) && filesize($AnnounceFile) > 4) {
   $sql .= " order by $sort limit 19";
   $HTML .= '  <item>' . "\n";
   $HTML .= '    <title>FreshPorts HEADS UP!</title>' . "\n";
   $HTML .= '    <link>http://' . $ServerName . '/</link>' . "\n";
   $HTML .= '    <description>the place for ports - same great content; far faster delivery</description>' . "\n";
   $HTML .= '  </item>' . "\n";

   $TEXT .= 'Please take our survey' . "\n";
   $TEXT .= 'http://freshports.org/survey/' . "\n";
#} else { */
#   $sql .= " order by $sort limit 20";
#}

if ($Debug) {
   echo $sql;
}

   $result = pg_exec ($db, $sql);
   if (!$result) {
      echo $sql . 'error = ' . pg_errormessage();
      exit;
   }

   $i=0;
   $numrows = pg_numrows($result);
   for ($i = 0; $i < $numrows; $i++) {
      $myrow = pg_fetch_array ($result, $i);
      $HTML .= '  <item>' . "\n";
      $HTML .= '    <title>' . $myrow["category"] . '/' . $myrow["port"] . '</title>' . "\n";
      $HTML .= '    <link>http://' . $ServerName . '/' . $myrow["category"] . '/' . $myrow["port"] . '/</link>' . "\n";
      $HTML .= '    <description>' . htmlspecialchars(trim($myrow["update_description"])) . '</description>' . "\n";
      $HTML .= '  </item>' . "\n";

      $TEXT .= $myrow["category"] . '/' . $myrow["port"] . "\n";
      $TEXT .= 'http://' . $ServerName . '/' . $myrow["category"] . '/' . $myrow["port"] . "/\n";
   }

   $HTML .= '</channel>' . "\n";
   $HTML .= '</rss>' . "\n";
   
#}

/*
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
*/
   echo '<?xml version="1.0"?>', "\n";
   echo $HTML;
#}


?>
