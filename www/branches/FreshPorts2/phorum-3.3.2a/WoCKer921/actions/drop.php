<?php check_security(); ?>
<?php
  // drop the current forum/folder
  if (isset($f) && ($f != 0)) {
    $sSQL = "Select table_name, folder from ".$pho_main." where id = ".$f;
    $q->query($DB, $sSQL);
    $rec=$q->getrow();
    if($rec["folder"]==0) {
      DropForum($f, $rec["table_name"]);
      QueMessage("Forum dropped.");
    } else {
      DropFolder($f);
      QueMessage("Folder dropped.");
    }
    writefile();
  } else {
    QueMessage("Drop failed.  Forum ID not available.");
  }
?>