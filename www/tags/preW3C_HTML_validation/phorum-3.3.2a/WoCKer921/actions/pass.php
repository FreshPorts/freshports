<?php check_security(); ?>
<?php
  // pass.php
  // Change the master password
  if($newPassword==$confirm){
    $crypt_pass=md5($newPassword);
    $SQL="Update $PHORUM[auth_table] set password='$crypt_pass' where id=".$PHORUM["admin_user"]["id"];
    if($q->query($DB, $SQL)){
        QueMessage("Password Changed.");
    } else {
        QueMessage("Password not changed.");
        $page=$frompage;
    }
  } else {
    QueMessage("Passwords do not match.");
    $page=$frompage;
  }
?>