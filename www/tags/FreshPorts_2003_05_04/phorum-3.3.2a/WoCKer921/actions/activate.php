<?php check_security(); ?>
<?php
  // activate the current forum
  $sSQL="Update ".$pho_main." set active=1 where id=$num";
  $q->query($DB, $sSQL);
  $ForumActive=1;
  writefile($num, true);
?>