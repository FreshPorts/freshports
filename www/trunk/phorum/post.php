<?PHP
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2000  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of the Phorum License.                                //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
////////////////////////////////////////////////////////////////////////////////

echo 'before common<BR>';
  require "./common.php";
echo 'after common<BR>';
  require "$include_path/post.php";
echo 'after post.php<BR>';

  $thread=$t;
  $action=$a;
  $id=$i;
  $parent=$p;

  if($num==0 || $ForumName==''){
    Header("Location: $forum_url?$GetVars");
    exit;
  }

  $ip = getenv('REMOTE_HOST');
  if(!$ip){
    $ip = getenv('REMOTE_ADDR');
  }
  if(!$ip){
    $ip = $REMOTE_ADDR;
  }
  if(!$ip){
    $ip = $REMOTE_HOST;
  }

  $host = @GetHostByAddr($ip);

  $IsError = @check_data($host, $author, $subject, $body, $email);

  if ($ForumAllowUploads == 'Y' && trim($attachment_name) != '' && trim($uploadDir != '')) {
    if (!ereg("^[-A-Za-z0-9_.]+$", trim($attachment_name))) {
      $IsError=$lInvalidFile;
    } elseif (file_exists($uploadDir.'/'.$ForumTableName.'/'.$attachment_name)) {
      $IsError=$lFileExists;
    }
    if(!is_uploaded_file($attachment)){
      $attachment="";
      $attachment_name="";
    }
  }
  else{
    $attachment="";
    $attachment_name="";
  }

  if($IsError || !$action){
    if(file_exists("$include_path/header_$ForumConfigSuffix.php")){
      include "$include_path/header_$ForumConfigSuffix.php";
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
    if(file_exists("$include_path/footer_$ForumConfigSuffix.php")){
      include "$include_path/footer_$ForumConfigSuffix.php";
    }
    else{
      include "$include_path/footer.php";
    }
    exit();
  }

  $author=trim($author);
  $subject=trim($subject);
  $email=trim($email);
  $body=chop($body);

  if($UseCookies){
    $name_cookie="phorum_name";
    $email_cookie="phorum_email";

    if((!IsSet($$name_cookie)) || ($$name_cookie != $author)) {
      SetCookie($name_cookie,stripslashes($author),time()+ 31536000);
    }
    if((!IsSet($$email_cookie)) || ($$email_cookie != $email)) {
      SetCookie($email_cookie,stripslashes($email),time()+ 31536000);
    }
  }

  list($author, $subject, $email, $body) = censor($author, $subject, $email, $body);

  if(!get_magic_quotes_gpc()){
    $author = addslashes($author);
    $email = addslashes($email);
    $subject = addslashes($subject);
    $body = addslashes($body);
  }

  $datestamp = date("Y-m-d H:i:s");

  $plain_author=stripslashes($author);
  $plain_subject=stripslashes(ereg_replace("<[^>]+>", "", $subject));
  $plain_body=stripslashes(ereg_replace("<[^>]+>", "", $body));

  $author = htmlspecialchars($author);
  $email = htmlspecialchars($email);
  $subject = htmlspecialchars($subject);

  // Attachment handling:
  if ($ForumAllowUploads == 'Y' && trim($attachment_name) != '' && trim($uploadDir != '')) {
    $org_attachment = $attachment_name;
    $new_name = $uploadDir.'/'.$ForumTableName.'/'.$org_attachment;
    if (!file_exists($new_name)) {
      copy($attachment, $new_name);
    } else {
      print $lFileExists;
      exit();
    }
  } else {
    $org_attachment = '';
  }

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

  if (!check_dup()) {
    // generate a message id for the email if needed.
    $msgid="<".md5(uniqid(rand())).".$ForumModEmail>";
    // This will add the message to the database, and email the
    // moderator if required.
echo 'therecc<BR>';
    $error = post_to_database();
    if (!empty($error)) {
      echo $error;
      exit();
    }

    // This will send email to the mailing list, if applicable,
    // and send email replies to earlier posters, if necessary.
    // Note that when posting to a mailing list, active moderation
    // does not apply.
    post_to_email();
  }

  Header ("Location: $forum_url/$list_page.$ext?f=$num$more$GetVars");
?>
