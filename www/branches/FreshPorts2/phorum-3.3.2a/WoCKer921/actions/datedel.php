<?php check_security(); ?>
<?php
  if($dateopt=="="){
    $cond="LIKE '$date%'";
  }
  else{
    $cond="$dateopt '$date'";
  }
  $sSQL="Select thread from $ForumTableName where thread=id and datestamp $cond";
  $q->query($DB, $sSQL);
  if($err=$q->error()){
    QueMessage("$err<br>$sSQL");
  }
  elseif($q->numrows()!=0){
    $rec=$q->getrow();
    $threads='';
    while(is_array($rec)){
      if($threads!='') $threads.=", ";
      $threads.="$rec[thread]";
      $rec=$q->getrow();
    }
    $sSQL="Select count(*) as cnt from $ForumTableName where thread in ($threads)";
    $q->query($DB, $sSQL);
    $rec=$q->getrow();
    $count=$rec["cnt"];
    if($err=$q->error()){
      QueMessage("$err<br>$sSQL");
    }
    $sSQL="Delete from $ForumTableName where thread in ($threads)";
    $q->query($DB, $sSQL);
    if($err=$q->error()){
      QueMessage("$err<br>$sSQL");
    }
    else{
      QueMessage("$count message(s) deleted.");
      $sSQL="Delete from $ForumTableName"."_bodies where thread in ($threads)";
      $q->query($DB, $sSQL);
      if($err=$q->error()){
        QueMessage("Problem deleting bodies!");
        QueMessage("$err<br>$sSQL");
      }
    }
  }
  else{
    QueMessage("No messages selected for deletion.");
  }
?>