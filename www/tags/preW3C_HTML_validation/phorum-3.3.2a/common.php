<?php
  if ( defined( "_COMMON_PHP" ) ) return;
  define("_COMMON_PHP", 1 );

  // These variables may be altered as needed:

  // location where settings are stored
  $settings_dir=$DOCUMENT_ROOT . "/../configuration/phorum";  // no ending slash

  // If you have dynamic vars for GET and POST to pass on:
  // AddGetPostVars("dummy", $dummy);



//////////////////////////////////////////////////////////////////////////////////////////
// End of normally user-defined variables
//////////////////////////////////////////////////////////////////////////////////////////


  // See the FAQ on what this does.  Normally not important.
  // **TODO: make this a define and figure out where we really need it.
  $cutoff = 800;

  $phorumver="3.3.2a";

  // all available db-files
  $dbtypes = array(
           'mysql' => "MySQL",
           'postgresql65' => "PostgreSQL 6.5 or newer",
           'postgresql' => "PostgreSQL (older than 6.5)"
           );

  // handle configs that have register_globals turned off.
  // we use $PHP_SELF as the test since it should always be there.
  // We might need to consider not using globals soon.
  if(!isset($PHP_SELF)) {
     include ("./include/register_globals.php");
  }

  // *** Some Defines ***

  // security
  define("SEC_NONE", 0);
  define("SEC_OPTIONAL", 1);
  define("SEC_POST", 2);
  define("SEC_ALL", 3);

  // signature
  define("PHORUM_SIG_MARKER", "[%sig%]");

  // **TODO: move all this into the admin
  $GetVars="";
  $PostVars="";
  function AddGetPostVars($var, $value){
    global $GetVars;
    global $PostVars;
    $var=urlencode($var);
    $value=urlencode($value);
    $GetVars.="&";
    $GetVars.="$var=$value";
    $PostVars.="<input type=\"hidden\" name=\"$var\" value=\"$value\">\n";
  }

  function AddPostVar($var, $value){
    AddGetPostVars($var, $value);
  }

  function AddGetVar($var, $value){
    AddGetPostVars($var, $value);
  }

  // **TODO: switch to get_html_translation_table
  function undo_htmlspecialchars($string){

    $string = str_replace("&amp;", "&", $string);
    $string = str_replace("&quot;", "\"", $string);
    $string = str_replace("&lt;", "<", $string);
    $string = str_replace("&gt;", ">", $string);

    return $string;
  }

  function htmlencode($string){
    $ret_string="";
    $len=strlen($string);
    for($x=0;$x<$len;$x++){
      $ord=ord($string[$x]);
      $ret_string .= "&#$ord;";
    }
    return $ret_string;
  }

  function my_nl2br($str){
    return str_replace("><br />", ">", nl2br($str));
  }

  function bgcolor($color){
    return ($color!="") ? " bgcolor=\"".$color."\"" : "";
  }

  // **TODO: replace with wordwrap soon. Will require some changes to the calls.
  function textwrap ($String, $breaksAt = 78, $breakStr = "\n", $padStr="") {

    $newString="";
    $lines=explode($breakStr, $String);
    $cnt=count($lines);
    for($x=0;$x<$cnt;$x++){
      if(strlen($lines[$x])>$breaksAt){
        $str=$lines[$x];
        while(strlen($str)>$breaksAt){
          $pos=strrpos(chop(substr($str, 0, $breaksAt)), " ");
          if ($pos == false) {
            break;
          }
          $newString.=$padStr.substr($str, 0, $pos).$breakStr;
          $str=trim(substr($str, $pos));
        }
        $newString.=$padStr.$str.$breakStr;
      }
      else{
        $newString.=$padStr.$lines[$x].$breakStr;
      }
    }
    return $newString;

  } // end textwrap()

  // **TODO: replace with a better function that optionally checks the MX record
  function is_email($email){
    $ret=false;
    if(function_exists("preg_match") && preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email)){
      $ret=true;
    }
    elseif(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$", $email)){
      $ret=true;
    }

    return $ret;
  }

  // passed to array_walk in read.php and list.php
  // **TODO: replace using array_flip
  function explode_haveread($var){
    global $haveread;
    $haveread[$var]=true;
  }

  // these two function would be better served as a class.
  function addnav(&$var, $text, $url){
    $var[$text]=$url;
  }

  function getnav($var, $splitter="&nbsp;&nbsp;|&nbsp;&nbsp;", $usefont=true){
    global $default_nav_font_color, $ForumNavFontColor;
    if(isset($ForumNavFontColor)){
      $color=$ForumNavFontColor;
    }
    else{
      $color=$default_nav_font_color;
    }
    $menu=array();
    while(list($text, $url)=each($var)){
      if($usefont) $text="<FONT color='$color' class=\"PhorumNav\">$text</font>";
      $menu[]="<a href=\"$url\">$text</a>";
    }
    $nav=implode($splitter, $menu);
    if($usefont)
      $nav="<FONT color='$color' class=\"PhorumNav\">&nbsp;".$nav."&nbsp;</font>";
    return $nav;
  }

  // These functions exist in PHP 4.0.3 and up.
  // **TODO: This will go away when we move to PHP4 only.
  if(!function_exists("is_uploaded_file")){

    function is_uploaded_file($filename) {
      $ret=false;
      if(dirname($filename)==dirname(tempnam(get_cfg_var("upload_tmp_dir"), ''))){
        $ret=true;
      }
      return $ret;
    }

    function move_uploaded_file($old_filename, $new_filename) {
      $ret=false;
      if(is_uploaded_file($old_filename) && rename($old_filename,$new_filename)) {
        $ret=true;
      }
      return $ret;
    }

  }

  function phorum_login_user($sessid, $userid=0){
    global $DB, $q, $pho_main, $HTTP_COOKIE_VARS;
    if(!isset($HTTP_COOKIE_VARS["phorum_auth"])){
      AddGetPostVars("phorum_auth", "$sessid");
    }
    // **TODO: We should make this time configurable
    SetCookie("phorum_auth", "$sessid", time()+86400*365);
    if($userid){
      $SQL="update $pho_main"."_auth set sess_id='$sessid' where id=$userid";
      $q->query($DB, $SQL);
    }
  }

  function phorum_get_file_name($type)
  {
    global $PHORUM;
    settype($PHORUM["ForumConfigSuffix"], "string");
    switch($type){
        case "css":
            $file="phorum.css";
            $custom="phorum_$PHORUM[ForumConfigSuffix].css";
            break;
        case "header":
            $file="$PHORUM[include]/header.php";
            $custom="$PHORUM[include]/header_$PHORUM[ForumConfigSuffix].php";
            break;
        case "footer":
            $file="$PHORUM[include]/footer.php";
            $custom="$PHORUM[include]/footer_$PHORUM[ForumConfigSuffix].php";
            break;
    }

    return (file_exists($custom)) ? $custom : $file;
  }


  function phorum_check_login($user, $pass)
  {
    global $q, $DB, $PHORUM;

    if(!get_magic_quotes_gpc()) $user=addslashes($user);

    $md5_pass=md5($pass);

    $id=0;
    $SQL="Select id from $PHORUM[auth_table] where username='$user' and password='$md5_pass'";
    $q->query($DB, $SQL);
    if($q->numrows()==0 && function_exists("crypt")){
        // check for old crypt system
        $crypt_pass=crypt($pass, substr($pass, 0, CRYPT_SALT_LENGTH));
        $SQL="Select id from $PHORUM[auth_table] where username='$user' and password='$crypt_pass'";
        $q->query($DB, $SQL);
        if($q->numrows()>0){
            // update password to md5.
            $SQL="Update $PHORUM[auth_table] set password='$md5_pass' where username='$user'";
            $q->query($DB, $SQL);
        }
    }

    if($q->numrows()>0){
        $id=$q->field("id", 0);
    }

    return $id;
  }

  function phorum_session_id($username, $password)
  {
    return md5($username.$password.microtime());
  }

  // variable initialization function
  // **TODO: need to scrap this function and just use settype()
  function initvar($varname, $value=''){
    global $$varname;
    if(!isset($$varname))
      $$varname=$value;
    return $$varname;
  }

  // set a sensible error level for including some stuff:
  $old_err_level = error_reporting (E_ERROR | E_WARNING | E_PARSE);

  // go ahead and unset/check these to evade hack attempts.
  unset($phorum_user);
  unset($PHORUM);
  settype($f, "integer");
  settype($num, "integer");
  $num = (empty($num)) ? $f : $num;
  $f = (empty($f)) ? $num : $f;

  // include forums.php

  // the most important variables
  $PHORUM["settings"]="$settings_dir/forums.php";
  $PHORUM["settings_backup"]="$settings_dir/forums.bak.php";

  if(!file_exists($PHORUM["settings"])){
    echo "<html><head><title>Phorum Error</title></head><body>Phorum could not load the settings file ($PHORUM[settings]).<br />If you are just installing Phorum, please go to the admin to complete the install.  Otherwise, see the faq for other reasons you could see this message.</body></html>";
    exit();
  }

  include ($PHORUM["settings"]);

  // set some PHORUM vars
  $PHORUM["auth_table"]=$PHORUM["main_table"]."_auth";
  $PHORUM["mod_table"]=$PHORUM["main_table"]."_moderators";
  $PHORUM["settings_dir"]=$settings_dir;
  $PHORUM["include"]="./include";

  // **TODO: remove legacy code
  $include_path=$PHORUM["include"];
  $pho_main=$PHORUM['main_table'];

  // include abstraction layer and check if its defined
  if(!defined("PHORUM_ADMIN") && (empty($PHORUM["dbtype"]) || !file_exists("./db/$PHORUM[dbtype].php"))){
    echo "<html><head><title>Phorum Error</title></head><body>Something is wrong.  You need to edit common.php and select a database.</body></html>";
    exit();
  }

  include ("./db/$dbtype.php");


  // create database classes
  $DB = new db();

  // check if database is already configured or if we are in the admin
  if ( defined( "_DB_LAYER" ) && $PHORUM["DatabaseName"]!=''){
    // this code below has to be this way for some weird reason.  Otherwise\n";
    // connecting on a different port won't work.\n";
    $DB->open($PHORUM["DatabaseName"], implode(':', explode(':', $PHORUM["DatabaseServer"])), $PHORUM["DatabaseUser"], $PHORUM["DatabasePassword"]);
  } elseif(!defined("PHORUM_ADMIN")) {
    echo "<html><head><title>Phorum Error</title></head><body>You need to go to the admin and fix your database settings.</body></html>";
    exit();
  }

  //dummy query for generic operations
  $q = new query($DB);
  if(!is_object($q)){
    echo "<html><head><title>Phorum Error</title></head><body>Unkown error creating $q.</body></html>";
    exit();
  }


  if(!empty($f)){
    if(file_exists("$PHORUM[settings_dir]/$f.php")){
      include "$PHORUM[settings_dir]/$f.php";
      if($ForumLang!=""){
        include ("./".$ForumLang);
      } else {
        include ("./".$default_lang);
      }
    }
    else{
      header("Location: $forum_url/$forum_page.$ext");
      exit();
    }
  }
  else {
    include ("./".$default_lang);
    include ($include_path."/blankset.php");
  }

  if(!$PHORUM["started"] && !defined("PHORUM_ADMIN")){
    Header("Location: $forum_url/$down_page.$ext");
    exit();
  }

  if(!defined("PHORUM_ADMIN") && $DB->connect_id){
     // check security
    if($ForumFolder==1){
        $SQL="Select max(security) as sec from $pho_main";
        $q->query($DB, $SQL);
        $max_sec=$q->field("sec", 0);
    }
    if(($ForumSecurity!=SEC_NONE || (($ForumFolder==1 || $f==0) && $max_sec>0)) && isset($phorum_auth)){
      $SQL="Select * from $PHORUM[auth_table] where sess_id='$phorum_auth'";
      $q->query($DB, $SQL);
      $phorum_user=$q->getrow();
      if(isset($phorum_user["id"])){
        $SQL="Select forum_id from $PHORUM[mod_table] where (forum_id=$f or forum_id=0) and user_id=$phorum_user[id]";
        $q->query($DB, $SQL);
        $phorum_user["moderator"] = ($q->numrows()>0) ? true : false;
        if(!isset($HTTP_COOKIE_VARS["phorum_auth"])){
          AddGetPostVars("phorum_auth", "$phorum_auth");
        }
      }
    }

    if(!isset($phorum_user["id"]) && isset($phorum_auth))  unset($phorum_auth);

    if($ForumSecurity==SEC_ALL && empty($phorum_auth)){
      header("Location: $forum_url/login.$ext?target=".urlencode($REQUEST_URI));
      exit();
    }

    // load plugins
    unset($plugins);
    $plugins = array(
             "read_body"   => array(),
             "read_header" => array()
             );

    if(isset($PHORUM["plugins"])){
      $dir = opendir("./plugin/");
      while($plugindirname = readdir($dir)) {
        if($plugindirname[0] != "." && @file_exists("./plugin/$plugindirname/plugin.php") && !empty($PHORUM["plugins"][$plugindirname])){
          include("./plugin/$plugindirname/plugin.php");
        }
      }
    }
  }

  // set the error level back to what it was.
  error_reporting ($old_err_level);

?>
