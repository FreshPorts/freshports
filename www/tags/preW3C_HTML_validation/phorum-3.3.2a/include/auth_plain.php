<?php

  // include this file where ever you want to limit access to a site.  If you
  // want to restrict a whole forum, put it at the end of common.php and list
  // those forums in $secure.  If you want to restrict posting, put it in
  // post.php.

  // This script uses an unencrypted file to store passwords in called
  // called phorum_passwords.  The file should be placed in the same location
  // as forums.php.

  // To have different passords for different forums, create file with the
  // forum id on the end like phorum_passwords-5.

  // If you need encrypted passwords, see auth.php.

  $secure["all"]=false;  // set the array with the forum number being the array
                        // index or set $secure["all"] to true
  //  example:
  //  $secure[1]=true;
  //  $secure[2]=true;


  $password_file="$inf_path/phorum_passwords";

  function authenticate() {
    Header("WWW-authenticate: basic realm=\"Phorum\"");
    Header("HTTP/1.0 401 Unauthorized");
    exit;
  }

  function ReadAccounts(){

    global $password_file, $accounts, $num;

    if(file_exists("$password_file-$num")){
      $passwd=@File("$password_file-$num");
    }
    else{
      $passwd=@File($password_file);
    }
    $cnt=count($passwd);
    for($x=0;$x<$cnt;$x++){
      $line=ereg_replace("\n", "", $passwd[$x]);
      if(strstr($line,":")){
        $parts=explode(":", $line, 2);
        $accounts[$parts[0]]=$parts[1];
      }
    }
  }

  function CheckPassword($user,$password){
    global $accounts;

    if(!IsSet($accounts)) ReadAccounts();
    if(!IsSet($accounts[$user])){
      return false;
    }
    else{
      if($accounts[$user]==$password){
        return true;
      }
      else{
        return false;
      }
    }
  }

  if((!empty($secure[$num]) || !empty($secure["all"])) && !empty($f)){
    if(!isset($PHP_AUTH_USER)) {
      authenticate();
    }
    elseif(!CheckPassword($PHP_AUTH_USER,$PHP_AUTH_PW)){
      authenticate();
    }
  }

?>