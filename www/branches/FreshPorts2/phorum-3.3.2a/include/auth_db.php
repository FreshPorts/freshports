<?php
  ///////////////////////////////////////////////////////////////////
  // This script is a modification of the original auth_db.php from Phorum-3.1.
  // It's  a bit crude, but it does the job - for me, anyways
  //
  // before you can use this file you will need to run the .sql file for your
  // db type (eg: auth_mysql.sql) in the db dir.
  //
  // If you want to restrict one or more forums with the same password change
  // $checkperforum to false, list the forums in $secure and include this script at
  // the end of common.php. If you just want to restrict posting, include it in
  // post.php just after require"./common.php"; instead.
  //
  // If you would like to secure different forums with different passwords, you will
  // need three columns in db-table auth_members, "name","pass","forum_id".
  // In "forum_id" you'll put the same value as in $secure. e.g if the URL to the
  // forum you wish to secure is http://some.sit.org/phorum/list.php?f=3, the value
  // you need is 3. If you just want to restrict posting, include the script in post.php
  // just after require"./common.php"; instead.
  //
  // You will need to create the passwords in the tables with some external tool
  // until an admin screen can be developed.
  //
  // May 24th 2000 Brian Moon (brian@phorum.org)
  // May 24th 2000 Frank M.G. Jørgensen (gajda@hum.sdu.dk)
  ////////////////////////////////////////////////////////////////

$checkperforum=true;  //change this setting to false if you don't want
                      //Phorum to verify the username and password per forum.

$secure["all"]=false; // set this to true to secure all forums.  Otherwise, to
                        // secure only certain forums see the instructions below.

// secure forums:
//  $secure[2]=true;   // If you need some of your forums secured and other open,
//  $secure[3]=true;   // set $secure["all"] false and list the ones you need secured here.



  function authenticate() {
    Header("WWW-authenticate: basic realm=\"Phorum\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo("<H1 ALIGN=\"center\">Access to forum denied</H1>");
    exit;
  }

  function CheckPassword($user,$password,$forum_id){
    global $DB, $q, $checkperforum ;
    $sSQL="select name from auth_members where name='$user' and pass='$password'";
    if ($checkperforum) $sSQL.=" and forum_id='$forum_id'";
    $q->query($DB, $sSQL);
    if($q->numrows()!=0){
      return true;
    }
    else{
      return false;
    }
  }

  if((!empty($secure[$num]) || !empty($secure["all"])) && !empty($f)){
    if(!isset($PHP_AUTH_USER)) {
      authenticate();
    }
    elseif(!CheckPassword($PHP_AUTH_USER,$PHP_AUTH_PW,$f)){
      authenticate();
    }
  }

?>