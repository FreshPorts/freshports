<?PHP

  // before you can use this file you will need to run the .sql file for your
  // db type (eg: auth_mysql.sql) in the db dir.  This is done by running a
  // similiar command to the one you ran when you set up Phorum.

  // include this file where ever you want to limit access to a site.  if you
  // want to restrict a whole forum, put it at the end of common.php and list
  // those forums in $secure.  If you want to restrict posting, put it in
  // post.php.
  
  // You will need to create the passwords in the tables with some external tool 
  // until an admin screen can be developed.

  $secure["all"]=false;  // set the array with the forum number being the array
                        // index or set $secure["all"] to true
//  example:
//  $secure[1]=true;

 function authenticate() {
    Header("WWW-authenticate: basic realm=\"Phorum\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo("<H1 ALIGN=\"center\">Access to forum denied</H1>");
    exit;
  }

  function CheckPassword($user,$password){
    global $DB, $q;
    
    $q->query($DB, "select name from auth_members where user='$user' and pass='$password'");

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
    elseif(!CheckPassword($PHP_AUTH_USER,$PHP_AUTH_PW)){
      authenticate();
    } 
  }

?>