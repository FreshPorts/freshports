<?PHP
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2000  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of the Phorum License Version 1.0.                    //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
////////////////////////////////////////////////////////////////////////////////

  function violation(){
    GLOBAL $num,$author,$email,$subject,$body,$ip,$host,$violation_page,$ext,$GetVars,$ForumName,$ForumModEmail,$PhorumMail;

    Mail($ForumModEmail, "Phorum Violation", "A forum violation has occured:\n\nauthor: $author\nemail:  $email\nhost:   $host ($ip)\nforum:  $ForumName\n\n$body", "From: Phorum <$ForumModEmail>");

    if(!$PhorumMail){
      Header("Location: $violation_page.$ext?f=$num&$GetVars");
    }
    exit();
  }
  
  require "./common.php";
  
  $thread=$t;
  $action=$a;
  $id=$i;
  $parent=$p;

  if($num==0 || $ForumName==''){
    Header("Location: $forum_url?$GetVars");
    exit;
  }

  if(file_exists("$include_path/bad_hosts_$ForumTableName.php")){
    include "$include_path/bad_hosts_$ForumTableName.php";
  }
  else{
    include "$include_path/bad_hosts.php";
  }

  if(!$PhorumMail){
    $ip = getenv('REMOTE_HOST');
    if(!$ip){
      $ip = getenv('REMOTE_ADDR');    
    }
  }
  
  $host = @GetHostByAddr($ip);

  if(is_array($hosts)){
    $cnt=count($hosts);
    for($x=0;$x<$cnt;$x++){
      if(ereg($hosts[$x],$host)){
        violation();
      }
    }
  }

  if(!empty($author)){
    if(trim($author)==""){
      $IsError=$lNoAuthor;
    }
    else{
      if(file_exists("$include_path/bad_names_$ForumTableName.php")){
        include "$include_path/bad_names_$ForumTableName.php";
      }
      else{
        include "$include_path/bad_names.php";
      }
      if(is_array($names)){
        $cnt=count($names);
        for($x=0;$x<$cnt;$x++){
          if(strstr($names[$x],$author)){
            violation();
          }
        }
      }
    }
  }
  else{
    $IsError=$lNoAuthor;
  }

  if(trim($subject)==""){
    $IsError=$lNoSubject;
  }

  if(trim($body)==""){
    $IsError=$lNoBody;
  }

  if(!empty($email)){
    if(!eregi(".+@.+\\..+", $email)  && $email!=$Password && $email!=$ModPass){
      if($email_reply){
        $IsError=$lNoEmail;
      }
    }
    else{
      if(file_exists("$include_path/bad_emails_$ForumTableName.php")){
        include "$include_path/bad_emails_$ForumTableName.php";
      }
      else{
        include "$include_path/bad_emails.php";
      }
      if(is_array($emails)){
        $cnt=count($emails);
        for($x=0;$x<$cnt;$x++){
          if(strstr($emails[$x], $email)){
            violation();
          }
        } 
      }
    }
  }
  elseif($email_reply){
    $IsError=$lNoEmail;
  }
  
  if($IsError || !$action){
    if(file_exists("$include_path/header_$ForumTableName.php")){
      include "$include_path/header_$ForumTableName.php";    
    }
    else{
      include "$include_path/header.php";
    }

    if(count($forums)>1){
      $nav = "<div class=nav><FONT color=\"$ForumNavFontColor\"><a href=\"$forum_page.$ext?f=$Parent$GetVars\"><font color=\"$ForumNavFontColor\">".$lForumList."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><font color=\"$ForumNavFontColor\">".$lGoToTop."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$search_page.$ext?f=$num$GetVars\"><font color=\"$ForumNavFontColor\">".$lSearch."</font></a>&nbsp;</font></div>";
    }
    else{
      $nav = "<div class=nav><FONT color=\"$ForumNavFontColor\"><a href=\"$list_page.$ext?f=$num$GetVars\"><font color=\"$ForumNavFontColor\">".$lGoToTop."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$search_page.$ext?f=$num$GetVars\"><font color=\"$ForumNavFontColor\">".$lSearch."</font></a>&nbsp;</font></div>";
    }

    include "$include_path/form.php";
    if(file_exists("$include_path/footer_$ForumTableName.php")){
      include "$include_path/footer_$ForumTableName.php";    
    }
    else{
      include "$include_path/footer.php";
    }
    exit();
  }

  if($UseCookies && !$PhorumMail){
    $name_cookie="phorum_name";
    $email_cookie="phorum_email";

    if((!IsSet($$name_cookie)) || ($$name_cookie != $author)) {
      SetCookie($name_cookie,stripslashes($author),time()+ 31536000);
    }
    if((!IsSet($$email_cookie)) || ($$email_cookie != $email)) {
      SetCookie($email_cookie,stripslashes($email),time()+ 31536000);
    }
  }

  if(file_exists("$include_path/censor_$ForumTableName.php")){
    include "$include_path/censor_$ForumTableName.php";    
  }
  else{
    include "$include_path/censor.php";
  }

  $blurb = "@!#$";
  $cnt = count($profan);
  if ( $cnt > 0 ){
    $a=0;
    While($a<$cnt){
      $sWord = $profan[$a];

      if(strstr(strtoupper($author), strtoupper($sWord))){
        if(strtoupper($author)==strtoupper($sWord)) $author=$blurb;
        $author = eregi_replace("^$sWord([^a-zA-Z])", "$blurb\\1", $author);
        $author = eregi_replace("([^a-zA-Z])$sWord$", "\\1$blurb", $author);
        while(eregi("([^a-zA-Z])($sWord)([^a-zA-Z])", $author)){
          $author = eregi_replace("([^a-zA-Z])($sWord)([^a-zA-Z])", "\\1$blurb\\3", $author);
        }
      }
      if(strstr(strtoupper($subject), strtoupper($sWord))){
        if(strtoupper($subject)==strtoupper($sWord)) $subject=$blurb;
        $subject = eregi_replace("^$sWord([^a-zA-Z])", "$blurb\\1", $subject);
        $subject = eregi_replace("([^a-zA-Z])$sWord$", "\\1$blurb", $subject);
        while(eregi("([^a-zA-Z])($sWord)([^a-zA-Z])", $subject)){
          $subject = eregi_replace("([^a-zA-Z])($sWord)([^a-zA-Z])", "\\1$blurb\\3", $subject);
        }
      }
      if(strstr(strtoupper($email), strtoupper($sWord))){
        if(strtoupper($email)==strtoupper($sWord)) $email="";
        $email = eregi_replace("^$sWord([^a-zA-Z])", "$blurb\\1", $email);
        $email = eregi_replace("([^a-zA-Z])$sWord$", "\\1$blurb", $email);
        while(eregi("([^a-zA-Z])($sWord)([^a-zA-Z])", $email)){
          $email = eregi_replace("([^a-zA-Z])($sWord)([^a-zA-Z])", "\\1$blurb\\3", $email);
        }
      }
      if(strstr(strtoupper($body), strtoupper($sWord))){
        if(strtoupper($body)==strtoupper($sWord)) $body=$blurb;
        $body = eregi_replace("^$sWord([^a-zA-Z])", "$blurb\\1", $body);
        $body = eregi_replace("([^a-zA-Z])$sWord$", "\\1$blurb", $body);
        while(eregi("([^a-zA-Z])($sWord)([^a-zA-Z])", $body)){
          $body = eregi_replace("([^a-zA-Z])($sWord)([^a-zA-Z])", "\\1$blurb\\3", $body);
        }
      }
      $a++;
    }
  }

  if(!get_cfg_var("magic_quotes_gpc") || $PhorumMail){
    $author = addslashes($author);
    $email = addslashes($email);
    $subject = addslashes($subject);
    $body = addslashes($body);
  }

  $datestamp = date("Y-m-d H:i:s");
  $author = htmlspecialchars($author);
  $email = htmlspecialchars($email);
  $subject = htmlspecialchars($subject);

  if(($email==$ForumModPass && $ForumModPass!="") || ($email==$Password && $Password!="")){
    $ForumModeration='';
    $email=$ForumModEmail;
    $author = "<b>$author</b>";
    $subject = "<b>$subject</b>";      
    $body="<HTML>$body</HTML>";
    $host="<b>$ForumStaffHost</b>";
  }
  else{
    $body=eregi_replace("</*HTML>", "", $body);
    if($ForumAllowHTML=="Y"){
      $body="<HTML>$body</HTML>";
    }
  }

  $id=$DB->nextid($ForumTableName);
  
  if($id==0){
    echo "Error getting nextval.";
    exit();
  }

  if($thread==0){
    $thread=$id;
  }
  else{
    $more = $thread+1;
    $more = "&a=2&t=$more";
  }

  $dup=false;

  if($ForumCheckDup){
    $hours=2;
    $date=explode(",", date("H,i,s,m,d,Y"));
    $dupdate=date("Y-m-d H:i:s", mktime($date[0],$date[1]-$hours,$date[2],$date[3],$date[4],$date[5]));
    $sSQL="Select id from $ForumTableName where author='$author' and subject = '$subject' and datestamp > '$dupdate'";
    $q->query($DB, $sSQL);
    if($q->numrows()>0){      
      $rec=$q->getrow();
      $ids="";
      while($rec){
        if($ids!="") $ids.=",";
        $ids.=$rec["id"];
        $rec=$q->getrow();
      }
      $sSQL="Select id from $ForumTableName"."_bodies where id in ($ids) and body='$body'";
      $q->query($DB, $sSQL);      
      if($q->numrows()>0) $dup=true;
    }
  }

  if(!$dup){

    $sSQL = "Insert Into $ForumTableName"."_bodies values ($id, '$body', '$thread')";

    $q->query($DB, $sSQL);

    if(!$q->result){
      echo $q->error()."<br>$sSQL";
      exit();
    }

    if(isset($image)){
      if($image!="none"){
        $is_image=true;
      }
    }

    switch($ForumModeration){
      case 'a':
        $email_mod=true;
        $approved='N';
        break;
      case 'r':
        $approved='Y';
        $email_mod=true;  
        break;
      default:
        $email_mod=false;
        $approved='Y';
        break;
    }
  
    $sSQL = "Insert Into $ForumTableName (id, author, email, datestamp, subject, host, thread, parent, email_reply, approved) values ('$id', '$author', '$email', '$datestamp', '$subject', '$host', '$thread', '$parent', '$email_reply', '$approved')";

    $q->query($DB, $sSQL);

    if(!$q->result){
     echo $q->error()."<br>$sSQL";
      exit();
    }

    $plain_subject=undo_htmlspecialchars(stripslashes(eregi_replace("<[^>]+>", "", $subject)));
    $plain_body=undo_htmlspecialchars(stripslashes(eregi_replace("<[^>]+>", "", $body)));

    if($email_mod==1){
      $ebody ="Subject: $plain_subject\n";
      $ebody.="Author: ".stripslashes($author)."\n";
      $ebody.="Message: $forum_url/$read_page.$ext?f=$num&i=$id&t=$thread&admview=1\n\n";
      $ebody.=fastwrap($plain_body)."\n\n";
      if($ForumModeration=="a"){
        $ebody.="To approve this message use this URL:\n";
        $ebody.="$forum_url/$admindir/$admin_page?page=easyadmin&action=moderate&approved=$approved&id=$id&num=$num&mythread=$thread\n\n";
      }
      $ebody.="To delete this message use this URL:\n";
      $ebody.="$forum_url/$admindir/$admin_page?page=easyadmin&action=del&type=quick&id=$id&num=$num&thread=$thread\n\n";
      $ebody.="To edit this message use this URL:\n";
      $ebody.="$forum_url/$admindir/$admin_page?page=edit&srcpage=easyadmin&id=$id&num=$num&mythread=$thread\n\n";
      mail($ForumModEmail, "Moderate for $ForumName at $SERVER_NAME Message: $id.", $ebody, "From: Phorum <$ForumModEmail>\nReturn-Path: <$ForumModEmail>\nX-PhorumVer: Phorum $phorumver");
    }

    if($ForumModeration!="a"){
      if(is_email($ForumEmailReturn)){
        $from_email=$ForumEmailReturn;
      }
      elseif(is_email($email)){
        $from_email=$email;
      }
      else{
        $from_email=$ForumModEmail;
      }
      if(is_email($ForumEmailList)){
        $to_email=$ForumEmailList;
        if($PhorumMail){
          if(strstr($toaddress, $ForumEmailList)) $to_email="";
        }          
      }
      else{
        $to_email="";
      }        
      $BCC="";
      if($thread!=0){
        $sSQL = "Select distinct email from $ForumTableName where thread=$thread and email_reply='Y' and email<>'$email'";
        $q->query($DB, $sSQL);
        if($q->numrows()>0){
  	      while($row=$q->getrow()){
            $BCC.=trim($row["email"]).",";
          }
          $BCC=substr($BCC, 0, strlen($BCC)-1);
        }
      }
      if($to_email || $BCC){
        $ebody=fastwrap($plain_body)."\n\n";
        $ebody.="----------------------------------------------------------------";
        $ebody.="<$forum_url/$read_page.$ext?f=$num&i=$id&t=$thread>\n";
        $ebody.="Sent by Phorum $phorumver <http://phorum.org>\n";
        $headers="From: $author <$from_email>\nReturn-Path: <$from_email>\nReply-To: $from_email\nX-Mailer: Phorum $phorumver";
        if($BCC)  $headers.="\nBCC: $BCC";
        mail($to_email, "[$ForumName] $plain_subject [$num:$thread:$id]", $ebody, $headers);      
      }
    }
    
  }

  if(!$PhorumMail){
    Header ("Location: $forum_url/$list_page.$ext?f=$num$more$GetVars");
    exit();
  }
?>