<?
	# $Id: statistics.php,v 1.1.2.1 2002-01-06 07:29:29 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

function freshports_DrawGraph($data, $title, $width, $height, $filesave) {
   $im = new image("png", $width, $height, array(197,194,197));
   $g = new graph(&$im, "bar", $data, $title);

   $g->im->draw_border(5);


   // These all have defaults

   # where are the fonts?  include a trailing /
   $FontDirectory = "/usr/local/etc/freshports/ttf/";

   // title = text_obj
   $g->title->font = $FontDirectory . "Arialb.ttf";
   $g->title->fontsize = 18;
   $g->title->color = $g->im->color['black'];
#   $g->title->format = array("shadow");

   // x_label = text_obj
   $g->x->label->font = $FontDirectory . "trebucbd.ttf";
   $g->x->label->color = $g->im->color['white'];
   $g->x->label->format = array("shadow");
   $g->x->label->text_padding = 15;     // in percent

   // y_label = text_obj
   $g->y->label->font = $FontDirectory . "trebucbd.ttf";
   $g->y->label->color = $g->im->color['black'];

   $g->y->color = $g->im->newcolor(173,0,64);           // this would be the bar color

   $g->draw();

   if ($filesave) {
      $g->save($filesave);
   }

   $g->destroy();
}

function freshports_stats_standard_retrieve($sql, $db) {

   $result = mysql_query($sql, $db);
   if ($result) {
      if(mysql_numrows($result)) {
         $i = 0;
         while ($myrow = mysql_fetch_array($result)) {
            $count[$i] = intval($myrow["count"]);
            $names[$i] = $myrow["name"];
            $i++;
         }
         $data = array($names, $count);
      } else {
      echo "freshports_stats_standard_retrieve found no data<br>";
      exit;
      }
   } else {
      echo "freshports_stats_standard_retrieve failed with error: " . mysql_error() . "<br>";
      exit;
   }

   return $data;
}

function freshports_stats_watched_ports($db, $NumPorts) {

   $sql = "select count(port_id) as count, ports.name " .
          "from watch_port, ports " . 
          "where watch_port.port_id = ports.id " .
          "  and ports.status = 'A' " .
          "group by port_id " . 
          "order by count desc, name " .
          "limit $NumPorts";

   $data = freshports_stats_standard_retrieve($sql, $db);

   return $data;
}

function freshports_stats_committers($db, $NumCommitters) {
   
   $sql = "select count(id) as count, committer as name " .
          "from change_log " .
          "group by committer " .
          "order by count desc, name " .
          "limit $NumCommitters";
      
   $data = freshports_stats_standard_retrieve($sql, $db);

   return $data; 
}

function freshports_stats_biggest_commits($db, $NumCommitters) {

   $sql = "select count(change_log_port.port_id) as count, committer as name " .
          "from change_log, change_log_port " .
          "where change_log.id = change_log_port.change_log_id " .
          "group by change_log_port.change_log_id  " .
          "order by count desc, name " .
          "limit $NumCommitters";

   $data = freshports_stats_standard_retrieve($sql, $db);
 
   return $data;
}

