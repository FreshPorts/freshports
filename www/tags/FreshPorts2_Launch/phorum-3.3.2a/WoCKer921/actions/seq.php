<?php check_security(); ?>
<?php
  if($DB->type=="mysql"){
        QueMessage("Not used for MySQL");
  }
  else{
      if($num!=0){
        $table_name=$ForumTableName;
      }
      else{
        $table_name=$pho_main;
      }
      $sSQL="Select max(id) as id from ".$table_name;
      $q->query($DB, $sSQL);
      $row=$q->getrow();
      if(isset($row["id"])){
        $id=$row["id"];
        $ret=$DB->reset_sequence($table_name, $id+1);
        if($ret==0){
          QueMessage("Sequence reset to $id.");
        }
        else{
          QueMessage("There was an error resetting the sequence.");
        }
      }
      else{
        QueMessage("Sequence not set, forum empty.");
      }
  }
?>