#!/usr/local/bin/php -q
<?php
  // Phorum does not require any trimming of messages.  However if you have a
  // disc space limit or some other strange bug with large tables this could
  // be handy.

  // This script will remove messages from a forum that are older than the
  // set number of days.  Run in cron for best results.

  // You will need to change ./ to the path where phorum is located.
  $phorumdir="./";

  $nDays="90"; // Number of days to keep
  $VERBOSE=false; // If true, info about what is happening
                  // will be sent to output

  $olddir=getcwd();
  chdir($phorumdir);
  include "common.php";

  $sSQL="Select name, table from forums";
  $res = new query($DB, $sSQL);
  $rec=$res->getrow();
  while(is_array($rec)){
    echo $rec["name"];
    $table=$rec["table"];
    $cutoff=date("Y-m-d", mktime(0,0,0,date('m'),date('d')-$nDays));
    $sSQL="Select id from $table where datestamp < $cutoff and thread=id";
    $q->query($DB, $sSQL);
    $numthreads=$q->numrows();
    $instr="";
    while($rec=$q->getrow()){
      $instr.="$rec->id,";
    }
    if($instr) $instr=substr($instr, strlen($instr)-1);
    $sSQL="Select count(*) as cnt from $table where thread in ($instr)";
    $q->query($DB, $sSQL);
    $cnt=$q->getrow();
    $count=$cnt["cnt"];
    $sSQL="Delete from $table where thread in ($instr)";
    $q->query($DB, $sSQL);
    $sSQL="Delete from $table"."_bodies where thread in ($instr)";
    $q->query($DB, $sSQL);
    $rec=$res->getrow();
    if($VERBOSE) echo "Forum: $rec[name], Threads Deleted: $numthreads, Messages Deleted: $count\n";
  }

  chdir($olddir);
?>