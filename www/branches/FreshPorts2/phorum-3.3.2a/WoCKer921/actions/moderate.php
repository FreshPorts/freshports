<?php
  if($approved=='Y'){
    $approved='N';
    $word = "hidden";
  }
  elseif($approved=='N'){
    $approved='Y';
    $word = "approved";
  }
  $sSQL="Update $ForumTableName set approved='$approved' where id=$id";
  $q->query($DB, $sSQL);
  $err=$q->error();
  if($err==""){
    QueMessage("Message $id $word.");
  }
  else{
    QueMessage($err);
  }
?>