<?
// sample graph

require( "./include/commonlogin.php3");
require( "./include/getvalues.php3");

$MaxGraph = 3;

if (!isset($graph)) {
   $graph = 1;
} else {
   if ($graph < 1 or $graph > $MaxGraph) {
      $graph = 1;
   }
}

switch ($graph) {
   case 1:
      $cache_file .= ".top.watched.ports";
      break;

   case 2:
      $cache_file .= ".top.committers";
      break;

   case 3:
      $cache_file .= ".top.biggest.commits";
      break;
}

$CreateImage = 0;
if (!file_exists($cache_file)) {
//   echo 'cache does not exist<br>';
   // cache does not exist, we create it
   $CreateImage = 1;
} else {
/*
   echo "filectime  = " . filectime($cache_file) . "<br>";
   echo "filectime+ = " . (filectime($cache_file) + $cache_time_rnd + 24*60*60) . "<br>";
   echo "time()     = " . time();
*/
   if ((filectime($cache_file) + $cache_time_rnd + 24*60*60) < time()) {
      $CreateImage = 1;
   }
}

//$CreateImage = 1;
if ($CreateImage) {
   require("./_phpgraph/phpgraph.php");
   require("./include/statistics.php3");
   switch ($graph) {
      case 1:
         $data = freshports_stats_watched_ports($db, 20);
         freshports_DrawGraph($data, "Top 20 Most Watched Ports", 500, 475, $cache_file);
         break;

      case 2:
         $data = freshports_stats_committers($db, 20);
         freshports_DrawGraph($data, "Top 20 Committers - number of commits", 500, 475, $cache_file); 
         break;

      case 3:
         $data = freshports_stats_biggest_commits($db, 20);
         freshports_DrawGraph($data, "Top 20 biggest commits - number of ports", 500, 475, $cache_file);
         break;
   }
} else {
   header("Content-type: image/png");
   $im = ImageCreateFromPng($cache_file);
   ImagePng($im);
}
?>
