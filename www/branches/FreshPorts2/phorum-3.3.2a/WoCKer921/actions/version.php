<?php check_security(); ?>
<?php
  $data="";
  flush();
  $fp=fopen("http://phorum.org/version.php", "r");
  if($fp){
    $data=fgets($fp, 1024);
    fclose($fp);
  }
  if(!strstr($data, "|")){
    QueMessage("Could not contact phorum.org.  To use this feature, you must have compiled in fopen wrappers when setting up PHP.");
    $page="main";
  }
  else{
    $ver_arr=explode("|", $data);
    $page="version";
  }
?>