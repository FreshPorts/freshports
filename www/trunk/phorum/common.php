<?PHP

  if ( !defined( "_COMMON_PHP" ) ){
    define("_COMMON_PHP", 1 );

  error_reporting(1023); 

  $phorumver="3.1 RC3";

  // set some paths and file names.

  $inf_path="/home/dan/freshports.org";  // no ending slash
  
  $inf_file="$inf_path/forums.php";
  $inf_back="$inf_path/forums.bak.php";

  $include_path="./include";  // no ending slash

  $admindir="_icq";
  $admin_page="index.php";
    
  // DB support vars.

  $dbsupport["mysql"]="MySQL 3.21.x or Higher";
  $dbsupport["postgresql65"]="PostgreSQL 6.5 or higher";  
  $dbsupport["postgresql"]="PostgreSQL 6.4.1 or higher";  
  
  // If you have dynamic vars for GET and POST to pass on:
  // AddGetVar("dummy", $dummy);  
  // AddPostVar("session", $session);  

  function undo_htmlspecialchars($string){

    str_replace("&amp;", "&", $string);
    str_replace("&quot;", "\"", $string);
    str_replace("&lt;", "<", $string);
    str_replace("&gt;", ">", $string);
 
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
    return ereg_replace("([^>]\n)","\\1<BR>",$str);
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

  function fastwrap ($body, $breaksAt = 78, $breakStr = "\n") {

  //***********************************************************
  //
  // Wraps a string, inserting line break characters.
  //
  // If you need to break on something other than space 
  // (e.g.  chr(10)  or  chr(13) ) you'll need to do a
  // str_replace on the string either within this function
  // or before passing the string in.
  // 
  //***********************************************************

    $lenBody = strlen($body);
    $pos = strpos($body,' ');
    if (($pos > 0) && ($lenBody > $breaksAt)) {
      $wrapBody = '';
      $charBase = 0;
      while ($pos > 0) {
        $nextPos = strpos($body,' ',$pos + 1);
        if ( (( $nextPos - $charBase ) > $breaksAt ) || (( $nextPos - $pos ) > $breaksAt )  ) {
          if ( !empty($wrapBody)) $wrapBody .= $breakStr;
          $wrapBody .= trim(substr($body,$charBase,($pos - $charBase)));
          $charBase = $pos + 1;
     		}
     		elseif ( empty ($nextPos )){
     		  if (!empty($wrapBody)) $wrapBody .= $breakStr;
          $wrapBody .= trim(substr($body,$charBase,($pos - $charBase)));
          if ( ((($lenBody - $charBase) + $pos) > $breaksAt) && (($lenBody - $charBase) > $breaksAt) ) {
            $wrapBody .= $breakStr.trim(substr($body,$pos,($lenBody - $pos)));
          }
          else{
            $wrapBody .= ' '.trim(substr($body,$pos,($lenBody - $pos)));
          }
        }
        $pos = $nextPos ;
      }
      return $wrapBody;
    }
    else{
      return $body;
    }
  }

  function is_email($email){
    $ret=false;
    if(strstr($email, '@') && strstr($email, '.')){
      if(eregi("^([_a-z0-9]+([\\._a-z0-9-]+)*)@([a-z0-9]{2,}(\\.[a-z0-9-]{2,})*\\.[a-z]{2,3})$", $email)){
        $ret=true;
      }
    }
    return $ret;
  }

  function hexserialize($object){
    $hstr="";
    $str=serialize($object);
    for($t=0;$t<strlen($str);$t++){
      $hstr=$hstr.dechex(ord(substr($str,$t,1)));
    }
    return $hstr;
  }

  function unhexserialize($hstr){
    $str="";
    for($t=0;$t<strlen($hstr);$t=$t+2){
      $str=$str.chr(hexdec(substr($hstr,$t,2)));
    }
    $objekt=unserialize($str);
    return $objekt;
  }

  // variable initialization
  
  initvar("a", 0);
  initvar("action");
  initvar("admin");
  initvar("admview");
  initvar("body");
  initvar("bodies");
  initvar("BodiesTable");
  initvar("check_dup");
  initvar("collapsed", 1);
  initvar("Collapsed");
  initvar("dbType");
  initvar("description");
  initvar("Description");
  initvar("display");
  initvar("down");
  initvar("email_list");
  initvar("email_return");
  initvar("emails");
  initvar("email_reply");
  initvar("EmailModerator");
  initvar("err");
  initvar("f", 0);
  initvar("first_active");
  initvar("folder");
  initvar("forum");
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
  initvar("is_image");
  initvar("IsError");
  initvar("key");
  initvar("lang");
  initvar("limitApproved");
  initvar("loc");
  initvar("MagicQuotes");
  initvar("match");
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
  initvar("read");
  initvar("rflat", 1);
  initvar("sortforums");
  initvar("staff_host");
  initvar("StaffHost");
  initvar("start_num");
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

  require './db/mysql.php';

  // include forums.php
  
  require "$inf_file";

  if($num || $f){
    if($f) $num=$f;
    if(file_exists("$admindir/forums/$num.php")){
      include "$admindir/forums/$num.php";
    }
    else{
      header("Location: $forum_url/$forum_page.$ext");
    }
  }

  if($ForumLang!=""){
    include "./$ForumLang";
  }
  else{
    include "./$default_lang";
  }

//  include "$include_path/auth.php";
//  include "$include_path/auth_db.php";

  }  //close define
?>
