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

  $read=true;

  require "./common.php";

  $thread=$t;
  $action=$a;
  $id=$i;
  if(isset($v)) $v=='f' ? $flat=1 : $flat=0;   

  $cutoff = 800; // See the faq.
  
  if($num==0 || $ForumName==''){
		Header("Location: $forum_page.$ext?$GetVars");
		exit;
	}
  if($id==0 && $action==0){
		Header("Location: $list_page.$ext?f=$num");
		exit;
	}

  $phcollapse="phorum-collapse-$ForumTableName";
  $phflat="phorum-flat-$ForumTableName";
  $new_cookie="phorum-new-$ForumTableName";
  $haveread_cookie="phorum-haveread-$ForumTableName";

  if($UseCookies){
 
    if(IsSet($flat)){
      $$phflat=$flat;
      SetCookie("phorum-flat-$ForumTableName",$flat,time()+ 31536000);
    }
    elseif(!isset($$phflat)){
      $$phflat=$ForumFlat;
    } 
    
    if(IsSet($$new_cookie)){
      $old_message=$$new_cookie;
    }
    else{
      $old_message="0";
    }

    if(IsSet($$haveread_cookie)) {
      $haveread=unhexserialize(urldecode($$haveread_cookie));
    }
    else{
      $haveread[0] = $old_message;
    }

    if(!IsSet($$new_cookie) || $$new_cookie<$id){
      SetCookie("phorum-new-$ForumTableName",$id,time()+ 31536000);
    }
    else{
      $$new_cookie="";
    }

    if(!IsSet($$haveread_cookie) || !IsSet($haveread[$id])){
      $haveread[$id] = 1;
      SetCookie("phorum-haveread-$ForumTableName",urlencode(hexserialize($haveread)),0);
    }
    else{
      $$haveread_cookie="";
    }

  }
  else{
    if(IsSet($flat)){
      $$phflat=$flat;
    } 
    else{
      $$phflat=$ForumFlat;
    }
    if(IsSet($collapse)){
      $$phcollapse=$collapse;
    } 
    else{
      $$phcollapse=$ForumCollapse;
    }
  }

  if($admview!=1) {
    $limitApproved=" and approved='Y'";
  } else {
    $limitApproved="";
  }

  if($action!=0 && ($action==1 || $action==2)){
    if($DB->type=="postgresql"){
      $limit="";
      $q->query($DB, "set QUERY_LIMIT TO 1");
    }
    else{
      $limit=" limit 1";
    }
    switch($action){
      case 2:
        $cutoff_thread=$thread-$cutoff;
        $sSQL="Select thread, id from $ForumTableName where thread<$thread and thread>$cutoff_thread and id=thread".$limitApproved." order by thread desc".$limit;
        break;
      case 1:
        $cutoff_thread=$thread+$cutoff;
        $sSQL="Select thread, id from $ForumTableName where thread<$cutoff_thread and thread>$thread and id=thread".$limitApproved." order by thread asc".$limit;
        break;
    }

    $msg = new query($DB, $sSQL);
    if($DB->type=="postgresql"){
      $q->query($DB, "set QUERY_LIMIT TO '0'");
    }

    if($msg->numrows()==0){
	  	Header("Location: $list_page.$ext?f=$num$GetVars");
		  exit;
  	}

    $id = $msg->field("id", 0);
    $thread = $msg->field("thread", 0);
  }

  $sSQL = "Select * from $ForumTableName where thread=$thread".$limitApproved." order by id";

  $msg_list = new query($DB, $sSQL);
  
  $sSQL = "Select * from $ForumTableName"."_bodies where ";
  $$phflat ? $sSQL.="thread=$thread" : $sSQL.="id=$id";
  $sSQL.= " order by id";

  $msg_body = new query($DB, $sSQL);
  
  $msg_list->firstrow();
  $rows=$msg_list->numrows();
  $bodyrows=$msg_body->numrows();
  
  $next_thread = "f=$num&t=$thread&a=2$GetVars";
  $prev_thread = "f=$num&t=$thread&a=1$GetVars";

  if(!$$phflat){
    if($loc<0){
      $loc=$rows-1;
    }
    elseif($loc==0 && $id!=$thread){
      $x=0;
      While($x!=$id && $loc<=$rows){
        $loc++;
        $msg_list->getrow();
        $x=$msg_list->field("id");
      }
    }

    if($loc+1==$rows){
      $next_link = $next_thread."&loc=0"; // was =-1
    }
    else{
      $next_loc = $loc+1;
      $next_id = $msg_list->field("id", $next_loc);
      $next_link = "f=$num&i=$next_id&t=$thread$GetVars";
    }

    if($loc==0){
      $prev_link = $prev_thread."&loc=0"; // was =-1
    }
    else{
      $prev_loc = $loc-1;
      $prev_id = $msg_list->field("id", $prev_loc);
      $prev_link = "f=$num&i=$prev_id&t=$thread$GetVars";
    }

  }
  else{
    $prev_link=$prev_thread;
    $next_link=$next_thread;
    $lNextMessage=$lNextTopic;
    $lPreviousMessage=$lPreviousTopic;
    $row=$msg_list->getrow();
    while($row){
      $haveread[$row["id"]] = 1;
      $row=$msg_list->getrow();
    }
    SetCookie("phorum-haveread-$ForumTableName",urlencode(hexserialize($haveread)),0);
    $msg_list->firstrow();
  }
  
  $subject = chop($msg_list->field("subject", $loc));

  $rawsub=ereg_replace("<b>|</b>", "", $subject);
  $title = " - ".$rawsub;

  if(file_exists("$include_path/header_$ForumTableName.php")){
    include "$include_path/header_$ForumTableName.php";    
  }
  else{
    include "$include_path/header.php";
  }

  $toThread = $thread + 1;

  if($$phflat==0){
    $flat_link = "<a href=\"$read_page.$ext?f=$num&i=$id&t=$thread&v=f$GetVars\"><FONT color='$ForumNavFontColor'>".$lReadFlat."</font></a>";
  }
  else{
    $flat_link = "<a href=\"$read_page.$ext?f=$num&i=$id&t=$thread&v=t$GetVars\"><FONT color='$ForumNavFontColor'>".$lReadThreads."</font></a>";
  }

  if($ActiveForums>1){
    $nav = "<div class=nav><FONT color='$ForumNavFontColor'><a href=\"$forum_page.$ext?f=$ForumParent$GetVars\"><FONT color='$ForumNavFontColor'>".$lForumList."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$post_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num&t=$toThread&a=2$GetVars\"><FONT color='$ForumNavFontColor'>".$lGoToTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;$flat_link&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$search_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lSearch."</font></a>&nbsp;</font></div>";
  }
  else{
    $nav = "<div class=nav><FONT color='$ForumNavFontColor'><a href=\"$post_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num&t=$toThread&a=2$GetVars\"><FONT color='$ForumNavFontColor'>".$lGoToTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;$flat_link&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$search_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lSearch."</font></a>&nbsp;</font></div>";
  }

?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td width="60%" align="left" <?PHP echo bgcolor($ForumNavColor); ?>><?PHP echo $nav; ?></td>
    <td width="40%" align="right" <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav>&nbsp;<a href="<?PHP echo  "$read_page.$ext?$prev_link"; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lPreviousMessage;?></a></font>&nbsp;&nbsp;<FONT color='<?PHP echo $ForumNavFontColor; ?>'>|</font>&nbsp;&nbsp;<a href="<?PHP echo "$read_page.$ext?$next_link"; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lNextMessage;?></font></a></div></td>
</tr>
</table>
<?PHP if ($rows==0 || $bodyrows==0) { ?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="2" border="0">
<tr>
    <td <?PHP echo bgcolor($ForumTableHeaderColor); ?>><FONT color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo $lViolationTitle; ?></font></td>
</tr>
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor2); ?> valign="TOP"><table width="100%" cellspacing="0" cellpadding="5" border="0">
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor2); ?> width="100%" valign="top"><font color="<?PHP echo $ForumTableBodyFontColor2; ?>"><?PHP echo $lNotFound; ?></td>
</tr>
</table>
</td>
</tr>
</table>

<?PHP }else{ ?>
<?PHP
  $rec=$msg_body->getrow();
  while($rec){
    if($msg_list->field("id")==$msg_body->field("id")){
      $rec_id=$msg_list->field("id");
      $subject = chop($msg_list->field("subject"));
      $author = chop($msg_list->field("author"));
      $datestamp = date_format($msg_list->field("datestamp"));
      $email = $msg_list->field("email");
      $real_host=chop($msg_list->field("host"));
      if($real_host){
        $host_arr=explode(".", $real_host);
        $count=count($host_arr);
        if($count > 1){
          if(intval($host_arr[$count-1])!=0){
            $host=substr($real_host,0,strrpos($real_host,".")).".---";
          }          
          else{
            $host = "---".strstr($real_host, ".");
          }
        }
        else{
          $host=$real_host;
        }
      }
      else{
        $host="";
      }
    
      $body = $msg_body->field("body");
  
      $qauthor=ereg_replace("<b>|</b>", "", $author);
      $qsubject=ereg_replace("<b>|</b>", "", $subject);

      if($email!=""){
        $author = "<a href=\"mailto:".htmlencode($email)."?subject=$rawsub\">$author</a>";
      }
?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="2" border="0">
<tr>
    <td <?PHP echo bgcolor($ForumTableHeaderColor); ?>><FONT size="+1" color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo $subject; ?></font></td>
</tr>
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor2); ?> valign="TOP"><table width="100%" cellspacing="0" cellpadding="5" border="0">
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor2); ?> width="100%" valign="top"><tt><font color="<?PHP echo $ForumTableBodyFontColor2; ?>">
<?PHP echo $lAuthor;?>:&nbsp;<?PHP echo $author; ?><br>
<?PHP echo $lDate;?>:&nbsp;&nbsp;&nbsp;<?PHP echo $datestamp; ?><br><br>
<?PHP

    $qbody=$body;

    $body=str_replace("{phopen}", "", $body);
    $body=str_replace("{phclose}", "", $body);

    $body=eregi_replace("<(mailto:)([^ >\n\t]+)>", "{phopen}a href=\"\\1\\2\"{phclose}\\2{phopen}/a{phclose}", $body);
    $body=eregi_replace("<([http|news|ftp]+://[^ >\n\t]+)>", "{phopen}a href=\"\\1\"{phclose}\\1{phopen}/a{phclose}", $body);

    if($ForumAllowHTML!="Y" && substr($body, 0, 6)!="<HTML>"){
      $body=eregi_replace("<(/*[$ForumAllowHTML] *[^>]*)>", "{phopen}\\1{phclose}", $body);

      $body=eregi_replace("<(/*[$ForumAllowHTML] *[^>]*)>", "{phopen}\\1{phclose}", $body);
      $body=str_replace("<", "&lt;", $body);
      $body=str_replace(">", "&gt;", $body);
    }    

    $body=str_replace("{phopen}", "<", $body);
    $body=str_replace("{phclose}", ">", $body);

?>
<?PHP echo my_nl2br($body); ?></font></tt><p>
</td>
</tr>
</table>
</td>
</tr>
</table>
<?PHP if(!$$phflat){ ?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td valign="TOP" width=50% <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav><FONT color='<?PHP echo $ForumNavFontColor; ?>'><a href="#REPLY"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lReplyMessage; ?></font></a></font></div></td>
    <td valign="TOP" width=50% align="RIGHT" <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav><FONT color='<?PHP echo $ForumNavFontColor; ?>'>&nbsp;<a href="<?PHP echo "$read_page.$ext"; ?>?<?PHP echo $prev_thread; ?>&<?PHP echo $GetVars; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lPreviousTopic;?></font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?PHP echo "$read_page.$ext"; ?>?<?PHP echo $next_thread; ?>&<?PHP echo $GetVars; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lNextTopic;?></font></a></font></div></td>
</tr>
</table>
<p>
<?PHP }else{ ?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td valign="TOP" width="100%" align="RIGHT" <?PHP echo bgcolor($ForumTableBodyColor2); ?>><div class=nav><FONT color='<?PHP echo $ForumNavFontColor; ?>'><a href="#REPLY"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lReplyMessage; ?></font></a></font></div></td>
</tr>
</table>
<?PHP } ?>
<?PHP
      $msg_list->getrow();
    }
    $rec=$msg_body->getrow();
  }
?>
<?PHP
  if(!$$phflat){
    $msg_list->firstrow();
    if(!$ForumMultiLevel){
      include "$include_path/threads.php";
    }
    else{
      include "$include_path/multi-threads.php";
    }
  }
  unset($author);
  unset($email);
  unset($subject);
?>
<A name="REPLY">
<?PHP require "$include_path/form.php"; ?>
<?PHP } ?>
<?PHP 
  if(file_exists("$include_path/footer_$ForumTableName.php")){
    include "$include_path/footer_$ForumTableName.php";    
  }
  else{
    include "$include_path/footer.php";
  }
 ?>
