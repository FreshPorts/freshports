<?php check_security(); ?>
<?php
  $PHORUM["started"]=0;
  writefile();
  QueMessage("Phorum has been stopped.");
?>