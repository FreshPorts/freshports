<?PHP
  if ( !defined( "_COMMON_PHP" ) ){
    define("_COMMON_PHP", 1 );

  // These variables may be altered as needed:

  // table name that Phorum uses to access meta-information on forums.
  $pho_main = "forums";

  // location where the configuration information is stored
  $inf_path="./_includes";  // no ending slash
  $inf_file="$inf_path/forums.php";
  $inf_back="$inf_path/forums.bak.php";

  // path to include files
  $include_path="./_includes";  // no ending slash

  // relative path to the admin pages
  $admindir="_icq";
  $admin_page="index.php";

  // Path to database abstraction file:

  // MySQL 3.21.x or Higher (default setting)
  $db_file = './db/mysql.php';

  // PostgreSQL 6.4.1 to 6.5
  // $db_file = './db/postgresql.php';

  // PostgreSQL version 6.5 and higher
  // $db_file = './db/postgresql65.php';

  // SYBASE
  // $db_file = './db/sybase.php';

  // MSSQL 6
  // $db_file = './db/mssql6x.php';

  // MSSQL 7
  // $db_file = './db/mssql.php';

  // If you have dynamic vars for GET and POST to pass on:
  // AddGetVar("dummy", $dummy);
  // AddPostVar("session", $session);

  // End of normally user-defined variables

  // See the FAQ on what this does.  Normally not important.
  $cutoff = 800;

  // For purists, enable this to find any coding errors.
  // error_reporting(E_ALL);

  $phorumver="3.2.10";

  // handle stupid configs that have REGISTER_GLOBALS turned off.
  if(!isset($PHP_SELF)) {
     include ($include_path."/register_globals.php");
  }

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
      $ord=ord(substr($string, $x, 1));
      $ret_string .= "&#$ord;";
    }
    return $ret_string;
  }

  function htmldecode($string){
    $ret_string="";
    $arr=explode("&#", $string);
    $count=count($arr);
    $x=1; //ignore first element. it will be ""
    while($x<$count){
      $asc=ereg_replace("(.*);", "\\1", $arr[$x]);
      $chr = chr($asc);
      $ret_string .= $chr;
      $x++;
    }
    return $ret_string;
  }

  function my_nl2br($str){
    $str=nl2br($str);
    return str_replace("><br>", ">", $str);
  }

  function bgcolor($color){
    if($color!=""){
      return " bgcolor=\"".$color."\"";
    }
    else{
      return'';
    }
  }

  function initvar($varname, $value=''){
    global $$varname;
    if(!isset($$varname))
      $$varname=$value;
  }

  // GET And POST site variables

  function AddGetVar($var, $value){
    GLOBAL $GetVars;
    $var=urlencode($var);
    $value=urlencode($value);
    $GetVars.="&";
    $GetVars.="$var=$value";
  }

  function AddPostVar($var, $value){
    GLOBAL $PostVars;
    $PostVars.="<input type=\"hidden\" name=\"$var\" value=\"$value\">\n";
  }

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

  function is_email($email){
    $ret=false;
    $name="";
    $domain="";
    @list($name, $domain)=@explode("@", $email);
    if(!strstr($email, " ") && @strstr($domain, ".")){
      $ret=true;
    }
    return $ret;
  }

  function explode_haveread($var){
    GLOBAL $haveread;
    $haveread[$var]=true;
  }


  // Don't even ask me how this works.  Apparently it creates a register for the filenme
  // by xoring the characters into a number from right to left.  The top six bits and the
  // bottom six bits are returned as the path name.  I didn't write it though, I just
  // converted it from a perl function that I found in the pair Networks private newsgroups.
  // - Jason

  function hash_file ($strFilename) {
    $n = 0;
    for ($posFilename = strlen($strFilename) -1; $posFilename >= 0; $posFilename-- ) {
      $n *= 2;
      if ($n & 4096) { $n |= 1; }
      $n ^= (ord($strFilename[$posFilename])*11);
      $n &= 4095;
    }
    return sprintf ("%02o/%02o", ($n/64) & 63 , $n&63);
  }

  // This function exists in PHP 4.0.3 and up.
  
  if(!function_exists("is_uploaded_file")){
    function is_uploaded_file($filename) {
      $ret=false;
      if(dirname($filename)==dirname(tempnam(get_cfg_var("upload_tmp_dir"), ''))){
        $ret=true;
      }
      return $ret;
    }
  }

  // variable initialization

  initvar("a", 0);
  initvar("action");
  initvar("admin");
  initvar("admview");
  initvar("attachment_name");
  initvar("body");
  initvar("bodies");
  initvar("BodiesTable");
  initvar("check_dup");
  initvar("collapsed", 1);
  initvar("Collapsed");
  initvar("config_suffix");
  initvar("dbType");
  initvar("description");
  initvar("Description");
  initvar("display");
  initvar("down");
  initvar("email_list");
  initvar("email_return");
  initvar("email_tag");
  initvar("emails");
  initvar("email_reply");
  initvar("EmailModerator");
  initvar("err");
  initvar("f", 0);
  initvar("first_active");
  initvar("folder");
  initvar("forum");
  initvar("ForumConfigSuffix");
  initvar("ForumLang");
  initvar("ForumModEmail");
  initvar("ForumName");
  initvar("forums");
  initvar("GetVars");
  initvar("haveread");
  initvar("html");
  initvar("html_all");
  initvar("html_style");
  initvar("html_font");
  initvar("html_li");
  initvar("html_img");
  initvar("html_a");
  initvar("host");
  initvar("hosts");
  initvar("i", 0);
  initvar("id");
  initvar("inclause");
  initvar("inreplyto");
  initvar("is_image");
  initvar("IsError");
  initvar("key");
  initvar("lang");
  initvar("last_thread");
  initvar("limitApproved");
  initvar("loc");
  initvar("MagicQuotes");
  initvar("max");
  initvar("message");
  initvar("min");
  initvar("mod_email");
  initvar("mod_pass");
  initvar("mod_pass_2");
  initvar("moderation");
  initvar("Moderation");
  initvar("ModPass");
  initvar("more");
  initvar("msgid");
  initvar("multi_level", 1);
  initvar("MultiLevel");
  initvar("name");
  initvar("names");
  initvar("nav_color");
  initvar("nav_font_color");
  initvar("NavColor");
  initvar("NavFontColor");
  initvar("navigate");
  initvar("new_UseCookies");
  initvar("new_sortforums");
  initvar("nRows");
  initvar("num");
  initvar("old_message");
  initvar("option");
  initvar("p", 0);
  initvar("p_author");
  initvar("p_body");
  initvar("p_subject");
  initvar("page", "main");
  initvar("parent");
  initvar("PhorumMail");
  initvar("PostVars");
  initvar("qauthor");
  initvar("qbody");
  initvar("qsubject");
  initvar("quote");
  initvar("quote_button");
  initvar("r", 0);
  initvar("read");
  initvar("rflat", 1);
  initvar("sortforums");
  initvar("staff_host");
  initvar("StaffHost");
  initvar("step");
  initvar("subject");
  initvar("t", 0);
  initvar("table");
  initvar("table_body_color_1");
  initvar("table_body_color_2");
  initvar("table_body_font_color_1");
  initvar("table_body_font_color_2");
  initvar("table_header_color");
  initvar("table_header_font_color");
  initvar("table_exists");
  initvar("table_width");
  initvar("TableBodyColor1");
  initvar("TableBodyColor2");
  initvar("TableBodyFontColor1");
  initvar("TableBodyFontColor2");
  initvar("TableHeaderFontColor");
  initvar("TableHeaderColor");
  initvar("TableName");
  initvar("TableWidth");
  initvar("tcount");
  initvar("thread");
  initvar("threadtotal");
  initvar("title");
  initvar("type");
  initvar("UseCookies", 1);

// include abstraction layer.

  require ($db_file);

  // include forums.php

  require ($inf_file);

  if($num || $f){
    if($f) $num=$f;
    $num=(int) $num;
    if(file_exists("$admindir/forums/$num.php")){
      include "$admindir/forums/$num.php";
      if($ForumLang!=""){
        include ("./".$ForumLang);
      }
    }
    else{
      header("Location: $forum_url/$forum_page.$ext");
    }
  }
  else {
    include ("./".$default_lang);
    include ($include_path."/blankset.php");
  }

  require ("./plugin/plugin.php");

//  include "$include_path/auth.php";
//  include "$include_path/auth_db.php";


  }  //close define

?>
