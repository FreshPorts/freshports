<?php check_security(); ?>
<?php
  $PHORUM['DatabaseServer']=$new_dbServer;
  $PHORUM['DatabaseName']=$new_dbName;
  $PHORUM['DatabaseUser']=$new_dbUser;
  $PHORUM['DatabasePassword']=$new_dbPass;
  writefile();
  QueMessage("The Database Settings have been updated.");
?>
