<?php check_security(); ?>
<?php
  // deactivate the current forum
  $sSQL="Update ".$pho_main." set active=0 where id=$num";
  $q->query($DB, $sSQL);
  $ForumActive=0;
  writefile($num, true);
?>