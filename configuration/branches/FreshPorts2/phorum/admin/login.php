<?
  // login.php
  
  if(!isset($phorum_logged_in)) $phorum_logged_in=0;
  if(!isset($phorum_password)) $phorum_password='';
  if(!isset($login)) $login=0;
  
  function show_login($status){
    GLOBAL $admindir, $admin_page, $phorum_password, $phorum_logged_in, $myname, $phorumver, $HTTP_USER_AGENT, $DB, $fullaccess, $q, $use_security, $forum_url, $QUERY_STRING, $page, $num;
    $title='Phorum Admin Login';
    $login=1;
    include "$admindir/header.php";
?>
<table border="0" cellspacing="0" cellpadding="3">
<tr>
<td align="LEFT" valign="MIDDLE">
<?
  $URL="$forum_url/$admindir/$admin_page";
  if($QUERY_STRING) $URL.="?$QUERY_STRING";
?>
<form action="<? echo $URL; ?>" method="POST">
<input type="hidden" name="login" value="1">
<? if($status==1) { ?>
<b>You entered an invalid password.</b><p>
<? } ?>
<? if(empty($num)) { ?>
<font face="Arial,Helvetica"><b>Access Type/Forum:</b><br></font>
<select name="num">
  	<option value="0">Master Password</option>
<?
  $sSQL="Select id, name from forums order by name";
  $q->query($DB, $sSQL);
  $row=$q->getrow();
  while($row){
  	echo "<option value=\"$row[id]\">$row[name]</option>\n";
    $row=$q->getrow();
  }
?>
</select><p>
<? } ?>
<font face="Arial,Helvetica"><b>Password:</b><br></font>
<input type="password" name="phorum_password" value="<? echo $phorum_password; ?>" size="20" maxlength="20"><input type="submit" value="Login">
</form>
</td></tr></table>
<?
    include "$admindir/footer.php";
    exit();
  }

  function log_in_user($user, $num=0){
    global $phorum_logged_in;
    if($num==0){
      $cookieval='1';
    }
    else{
      $cookieval="f=$num";
    }
    setcookie("phorum_logged_in", $cookieval);
    $phorum_logged_in=$cookieval;
    $user_id=$user;    
  }    

  function check_login(){
    GLOBAL $logout, $login, $phorum_logged_in, $phorum_password, $Password, $ForumName, $ForumModPass, $page, $num, $modpages, $action;
    if(isset($logout)){
      $phorum_logged_in=0;
      setcookie("phorum_logged_in", "0");
      show_login(0);
    }
    elseif($phorum_logged_in=="0"){
      if($phorum_password==$Password){
        log_in_user($login);        
      }
      elseif(empty($ForumModPass)){
        show_login($login);
      }
      elseif($phorum_password==$ForumModPass){
        log_in_user($login, $num);
        $page="managemenu";
      }
      else{
        show_login($login);
      }
    }
    elseif(substr($phorum_logged_in, 0, 1)=="f" && @strstr($modpages,$page)==""){      
      $page="";
      $action="";
    }
  }
  
  
?>
