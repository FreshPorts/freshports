<?PHP
  function violation(){
    GLOBAL $num,$author,$email,$subject,$body,$ip,$host,$violation_page,$ext,$GetVars,$ForumName,$ForumModEmail,$PhorumMail;

    Mail($ForumModEmail, "Phorum Violation", "A forum violation has occured:\n\nauthor: $author\nemail:  $email\nhost:   $host ($ip)\nforum:  $ForumName\n\n$body", "From: Phorum <$ForumModEmail>");

    if(!$PhorumMail){
      Header("Location: $violation_page.$ext?f=$num&$GetVars");
    }
    exit();
  }


  // Check host against bad hosts list.
  // Returns true if ok, false if bad.
  function check_host($host) {
    global $include_path, $ForumConfigSuffix;
    if(file_exists("$include_path/bad_hosts_$ForumConfigSuffix.php")){
      include "$include_path/bad_hosts_$ForumConfigSuffix.php";
    } else {
      include "$include_path/bad_hosts.php";
    }
    if(@is_array($hosts)){
      reset($hosts);
      while (list(, $badhost) = each($hosts)) {
        if (ereg($badhost, $host)) {
	  return(false);
        }
      }
    }
    return(true);
  }


  // Check author against bad names list.
  // Returns true if ok, false if bad.
  function check_name($author) {
    global $include_path, $ForumConfigSuffix;
    if(file_exists("$include_path/bad_names_$ForumConfigSuffix.php")){
      include "$include_path/bad_names_$ForumConfigSuffix.php";
    } else {
      include "$include_path/bad_names.php";
    }
    if(@is_array($names)){
      reset($names);
      while (list(, $badname) = each($names)) {
        if (strstr($author, $badname)) {
	  return(false);
        }
      }
    }
    return(true);
  }


  // Check email against bad emails list.
  // Returns true if ok, false if bad.
  function check_email($email) {
    global $include_path, $ForumConfigSuffix;
    if(file_exists("$include_path/bad_emails_$ForumConfigSuffix.php")){
      include "$include_path/bad_emails_$ForumConfigSuffix.php";
    } else {
      include "$include_path/bad_emails.php";
    }
    if(@is_array($emails)){
      reset($emails);
      while (list(, $bademail) = each($emails)) {
        if (strstr($email, $bademail)) {
	  return(false);
        }
      }
    }
    return(true);
  }


  // Does various checks on new message data.
  // Returns an empty string if ok, and error string if a problem exists.
  // May also call violation() and exit.
  function check_data($host, $author, $subject, $body, $email) {
    global $lNoAuthor, $lNoSubject, $lNoBody, $lNoEmail;
    global $Password, $ModPass, $email_reply;
    $IsError = '';

    if (!check_host($host)) {
      violation();
    }

    $author = @trim($author);
    if (empty($author)) {
      $IsError=$lNoAuthor;
    } elseif (!check_name($author)) {
      violation();
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
        if (!check_email($email)) {
          violation();
        }
      }
    }
    elseif($email_reply){
      $IsError=$lNoEmail;
    }

    return($IsError);
  }


  // Applies censoring to message.
  // Returns censored data.
  function censor($author, $subject, $email, $body) {
    global $include_path, $ForumConfigSuffix;
    if(file_exists("$include_path/censor_$ForumConfigSuffix.php")){
      include "$include_path/censor_$ForumConfigSuffix.php";
    } else {
      include "$include_path/censor.php";
    }

    $blurb = "@!#$";
    if (is_array($profan)) {
      reset($profan);
      while (list(, $sWord) = each($profan)) {
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
      }
    }
    return(array($author, $subject, $email, $body));
  }


  // Check for duplicate message.
  // Returns true if dup, false if unique.
  function check_dup() {
    global $q, $DB, $ForumCheckDup, $ForumTableName;
    global $author, $subject, $body;

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
        if($q->numrows()>0) {
	  return(true);
	}
      }
    }
    return(false);
  }


  // Add a message to the Phorum database.
  // Returns an error message on error, empty string otherwise.
  function post_to_database() {
    global $q, $DB, $ForumTableName, $ForumModeration;
    global $ForumModEmail, $ForumName, $PhorumMailCode, $PhorumMail;
    global $phorumver, $more, $SERVER_NAME;
    global $thread, $subject, $inreplyto, $parent, $author, $body, $email;
    global $image, $datestamp, $host, $email_reply, $org_attachment, $msgid;
    global $plain_author, $plain_subject, $plain_body;
    global $admin_url, $admin_page, $forum_url, $read_page, $ext, $num, $id;

    $id=$DB->nextid($ForumTableName);
    if ($id==0 && $DB->type!="mysql") {
      return("Error getting nextval.");
    }

    // If the message is coming from PhorumMail and doesn't have a thread id,
    // we have some work to do...
    if ($PhorumMail && ($thread==0)) {
      // We will try to match a message to its parent using the Subject: field.
      // The basic idea is to remove any "RE: " and search for the result.
      // Things to watch out for:
      //   1.  Occasionally I have seen a space inserted before the "RE: ".
      //   2.  Some list servers will rewrite the subject so that the list
      //       tag (in brackets - []) appears at the beginning of the
      //       subject - BEFORE any "RE: " that appears.
      //   3.  Some people (Germans?) use "AW" instead of "RE".
      //   4.  Some clients will insert a reply level (as in "Re[2]: ").
      eregi('^[[:space:]]*(\[[^]]+\][[:space:]]+)?((re|aw)(\[[[:digit:]]+\])?:[[:space:]]+)*(.+)$', $subject, $threadsubj);
      if (empty($threadsubj[2])) {
        // Sometimes people will start a new thread by replying to an old
        // message.  In this case, there may be an In-Reply-To: header but we
        // need to ignore it.  With no 'Re: ' in the subject, this is probably
        // meant to be a new thread unless the subject is identical to the
        // original, so we just check for an identical subject.
        $sSQL = "Select min(id) as id, min(thread) as thread from $ForumTableName where subject = '$subject'";
      } elseif(!empty($inreplyto)) {
        // If there is a In-Reply-To: header, we search for the message ID...
        $sSQL = "Select id, thread from $ForumTableName where msgid='$inreplyto'";
      } else {
        // ...otherwise, we try to match the subject.
        // (We don't want to get too aggressive with wildcards, since
        //  unrelated messages might have very similar subjects.)
        $sSQL = "Select max(id) as id, max(thread) as thread from $ForumTableName where subject = '";
        $sSQL .= empty($threadsubj[1]) ? '' : $threadsubj[1];
        $sSQL .= empty($threadsubj[5]) ? '' : $threadsubj[5];
        $sSQL .= "' or subject = '$subject'";
      }
      $q->query($DB, $sSQL);
      if($q->numrows()>0){
        $row=$q->getrow();
        $parent=empty($row["id"]) ? 0 : $row["id"];
        $thread=empty($row["thread"]) ? 0 : $row["thread"];
      }
    }

    if($thread==0){
      $thread=$id;
    }

    // Both emails and Window's browsers use \r\n.  Need to strip the \r.
    $body=str_replace("\r", "", $body);

    $sSQL = "Insert Into $ForumTableName"."_bodies (id, body, thread) values ($id, '$body', $thread)";
    $q->query($DB, $sSQL);
    if(!$q->result){
      return($q->error()."<br>$sSQL");
    }

    if($DB->type=="mysql"){
      $id=$DB->lastid();
      if($thread==0) {
        $thread=$id;
        $sSQL = "Update $ForumTableName"."_bodies SET thread = id WHERE id = $id";
        $q->query($DB, $sSQL);
        if(!$q->result){
          return($q->error()."<br>$sSQL");
        }
      }
    }

    if($thread!=$id){
      $more = $thread+1;
      $more = "&a=2&t=$more";
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
        $email_mod=true;
        $approved='Y';
        break;
      default:
        $email_mod=false;
        $approved='Y';
        break;
    }

    $sSQL = "Insert Into $ForumTableName (id, author, email, datestamp, subject, host, thread, parent, email_reply, attachment, approved, msgid) values ('$id', '$author', '$email', '$datestamp', '$subject', '$host', '$thread', '$parent', '$email_reply', '$org_attachment', '$approved', '$msgid')";
    $q->query($DB, $sSQL);
    if(!$q->result){
      return($q->error()."<br>$sSQL");
    }

    if($email_mod==true){
      $ebody ="Subject: $plain_subject\n";
      $ebody.="Author: $plain_author\n";
      $ebody.="Message: $forum_url/$read_page.$ext?f=$num&i=$id&t=$thread&admview=1\n\n";
      $ebody.=textwrap($plain_body)."\n\n";
      if($ForumModeration=="a"){
        $ebody.="To approve this message use this URL:\n";
        $ebody.="$admin_url/$admin_page?page=easyadmin&action=moderate&approved=$approved&id=$id&num=$num&mythread=$thread\n\n";
      }
      $ebody.="To delete this message use this URL:\n";
      $ebody.="$admin_url/$admin_page?page=easyadmin&action=del&type=quick&id=$id&num=$num&thread=$thread\n\n";
      $ebody.="To edit this message use this URL:\n";
      $ebody.="$admin_url/$admin_page?page=edit&srcpage=easyadmin&id=$id&num=$num&mythread=$thread\n\n";
      mail($ForumModEmail, "Moderate for $ForumName at $SERVER_NAME Message: $id.", stripslashes($ebody), "From: Phorum <$ForumModEmail>\nReturn-Path: <$ForumModEmail>\nX-Phorum-$PhorumMailCode-Version: Phorum $phorumver");
    }

    return('');
  }


  // Post a message to email.
  function post_to_email() {
    global $q, $DB, $ForumModeration, $ForumModEmail, $ForumEmailReturnList;
    global $ForumEmailList, $ForumTableName, $ForumName, $PhorumMailCode, $PhorumMail;
    global $email, $thread, $parent, $plain_subject, $plain_body, $plain_author;
    global $forum_url, $read_page, $ext, $num, $id, $phorumver, $msgid;

//FIXME:  Since there is currently no mechanism for holding a post for later
//        emailing to a list, we must disable this check for active moderation.
//        But active moderation is of dubious value in a mailing list
//        environment, because a post via email will go out to all the other
//        subscribers before getting to Phorum anyway.
//    if($ForumModeration!="a"){
      if(is_email($email)){
        $from_email=$email;
      } else {
        $from_email=$ForumModEmail;
      }
      if(is_email($ForumEmailReturnList)){
        $return=$ForumEmailReturnList;
      } else {
        $return=$from_email;
      }
      $replies="";
      if($thread!=0){
        $sSQL = "Select distinct email from $ForumTableName where thread=$thread and email_reply='Y' and email<>'$email'";
        $q->query($DB, $sSQL);
        if($q->numrows()>0){
          while($row=$q->getrow()){
            $replies.=trim($row["email"]).",";
          }
          $replies=substr($replies, 0, strlen($replies)-1);
        }
      }
      // If the message is going to a mailing list, it hasn't gone into the
      // database yet, so there is no point in trying to build a link to it.
      // On the other hand, if it is coming from PhorumMail, then PhorumMail
      // has already put it in the database.
      // We can check whether it is in the database by $id.
      $ebody = '';
      if ($id) {
        $ebody.="This message was sent from: $ForumName.\n";
        $ebody.="<$forum_url/$read_page.$ext?f=$num&i=$id&t=$thread> \n";
        $ebody.="----------------------------------------------------------------\n\n";
      }
      $ebody.=textwrap($plain_body)."\n\n";
      $ebody.="----------------------------------------------------------------\n";
      $ebody.="Sent using Phorum software version $phorumver <http://phorum.org> ";
      $headers="Message-ID: $msgid" .
               "\nFrom: $plain_author <$from_email>" .
               "\nReturn-Path: <$return>" .
               "\nReply-To: $return" .
               "\nX-Phorum-$PhorumMailCode-Version: Phorum $phorumver" .
               "\nX-Phorum-$PhorumMailCode-Forum: $ForumName" .
               "\nX-Phorum-$PhorumMailCode-Thread: $thread" .
               "\nX-Phorum-$PhorumMailCode-Parent: $parent";
      if(!empty($parent)) {
        $sSQL = "Select msgid from $ForumTableName where id='$parent'";
        $q->query($DB, $sSQL);
        if($q->numrows()>0){
          $row=$q->getrow();
          if (!empty($row['msgid'])) {
            $headers .= "\nIn-reply-to: " . $row['msgid'];
          }
        }
      }
      // Only send to mailing list if NOT coming from PhorumMail!
      if(!$PhorumMail && is_email($ForumEmailList) && is_email($ForumEmailReturnList)){
//        mail("$ForumName <$ForumEmailList>", "$plain_subject [$num:$thread:$id]", $ebody, $headers);
        mail("$ForumName <$ForumEmailList>", $plain_subject, $ebody, $headers);
      }
      if($replies){
        $headers.="\nBCC: $replies";
        mail("", "$plain_subject [$num:$thread:$id]", $ebody, $headers);
      }
//    }
  }

?>
