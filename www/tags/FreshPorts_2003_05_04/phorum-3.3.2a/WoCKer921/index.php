<?php

////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2000  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of either the current Phorum License (viewable at     //
//   phorum.org) or the Phorum License that was distributed with this file    //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////

  define("PHORUM_ADMIN", 1);

  // if you move the admin out of the phorum dir, change this below.
  chdir("../");

  include "./common.php";

  $admindir = dirname(__FILE__);

  include "$admindir/functions.php";

  // set a sensible error level:
  error_reporting  (E_ERROR | E_WARNING | E_PARSE);

  if(empty($forum_url)){
    include "$admindir/pages/install.php";
    exit();
  } elseif($page=="setup"){
    $page="main";
  }

  if(isset($page))
    $page=basename($page);
  else
    $page="main";

  $forum_id = 0;

  $myname="$PHP_SELF";

  include "$admindir/login.php";
  if($DB->connect_id) check_login();

  if($action && file_exists("$admindir/actions/$action.php"))
    include "$admindir/actions/$action.php";
 include "$admindir/header.php";

  if($page=="newforum"){
    $page="new";
    $folder="0";
  }elseif($page=="newfolder"){
    $page="new";
    $folder="1";
  }
  // check for an admin

  if(empty($PHORUM["admin_user"]["forums"][0]) && !empty($DB->connect_id)){
    while(list($fid, $value)=each($PHORUM["admin_user"]["forums"])){
        if($f==$fid){
            $ok=true;
            break;
        }
    }
    reset($PHORUM["admin_user"]["forums"]);

    if(!$ok){
        $page="moderate";
        $f=0;
    }

  }

  if(file_exists("$admindir/pages/$page.php")){
    include "$admindir/pages/$page.php";
  }

  include "$admindir/footer.php";

?>
