<?php

  // Thanks to slim@vbm.org for funding this script.

  // You will need to change ./ to the path where phorum is located.
  $phorumdir="./";

  // Set this to true and fill in the array section below if you want:
  //    A) only certain forums
  //    B) want them in a certain order
  //    C) or want to use a different name other than what is in the database
  $CUSTOMLIST=false;

  $olddir=getcwd();
  chdir($phorumdir);
  include "common.php";

  if($CUSTOMLIST){
    // Example Array
    // $forums[1]=array("name" => "Users Forum", "table_name" => "phorum");
    // $forums[3]=array("name" => "Wishlist", "table_name" => "phorum_wish");
    // $forums[4]=array("name" => "Phorum Hacks", "table_name" => "phorum_hackers");
  }
  else{
    $sSQL="Select id, name, table_name from ".$pho_main." where active=1 and parent=$f";
    $q->query($DB, $sSQL);
    $rec=$q->getrow();
    while(is_array($rec)){
      $forums[$rec["id"]]["name"]=$rec["name"];
      $forums[$rec["id"]]["table_name"]=$rec["table_name"];
      $rec=$q->getrow();
    }
    $q->free();
  }

  $data="";

  while(list($id, $array)=each($forums)){
    $forum_name=$array["name"];
    $url="$forum_url/$list_page.$ext?f=$id";
    $sSQL="select count(*) as posts from $array[table_name]";
    $q->query($DB, $sSQL);
    $rec=$q->getrow();
    if(isset($rec)){
      $num_posts=$rec["posts"];
    }
    else{
      $num_posts='0';
    }

    // All the output is right here:
    $data.="<a href=\"$url\">$forum_name</a> ($num_posts posts)<br>\n";
  }

  if($RETURN){
    return $data;
  }
  else{
    echo $data;
  }

  chdir($olddir);
?>