<?php

  // include this file where ever you want to limit access to a site.  If you
  // want to restrict a whole forum, put it at the end of common.php and list
  // those forums in $secure.  If you want to restrict posting, put it in
  // post.php.

  // You will need to use htpasswd to create your password file.  Move to your
  // Phorum dir in a shell.  type: htpasswd -c .htpasswd {username}  Replace
  // {username} with whatever you like.  Once the file is created drop the -c
  // to add other usernames and passwords.  This will create the file for you.
  // You may want to implement some of the same security for this file as you do
  // the forums.php file.

  // To have different passords for different forums, use this command when
  // creating your password file: htpasswd -c .htpasswd-{forum id} {username}
  // example if I was creating a password file for a forum who's id was 5 and I
  // was adding a user named bob I would type: htpasswd -c .htpasswd-5 bob
  // When adding new users I would type: htpasswd .htpasswd-5 tom

  $secure["all"]=false;  // set the array with the forum number being the array
                        // index or set $secure["all"] to true
//  example:
//  $secure[1]=true;
//  $secure[2]=true;


  $password_file=".htpasswd"; // set this to the path and filename of your
                              // passwd file.  This should be a file created
                              // by htpasswd.

  function authenticate() {
    Header("WWW-authenticate: basic realm=\"Phorum\"");
    Header("HTTP/1.0 401 Unauthorized");
    exit;
  }

  Function ReadAccounts(){

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
        $parts=explode(":", $line);
        $accounts[$parts[0]]=$parts[1];
      }
    }
  }

  Function CheckPassword($user,$password){
    global $accounts;

    if(!IsSet($accounts)) ReadAccounts();
    if(!IsSet($accounts[$user])){
      return false;
    }
    else{
      if($accounts[$user]==crypt($password, substr($accounts[$user],0,CRYPT_SALT_LENGTH))){
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