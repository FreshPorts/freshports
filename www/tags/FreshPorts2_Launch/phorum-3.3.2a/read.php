<?php
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2000  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of either the current Phorum License (viewable at     //
//   phorum.org) or the Phorum License that was distributed with this file    //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
////////////////////////////////////////////////////////////////////////////////
// $Id: read.php,v 1.1.2.1 2002-02-16 20:47:33 dan Exp $

  $read=true;

  require "./common.php";
  require "$include_path/read_functions.php";

  $thread=(int)initvar("t");
  $action=(int)initvar("a");
  $id=(int)initvar("i");

  if(isset($v)) $v=='f' ? $flat=1 : $flat=0;

  if($num==0 || $ForumName==''){
    Header("Location: $forum_page.$ext?$GetVars");
    exit;
  }
  if(empty($id) && empty($action)){
    Header("Location: $list_page.$ext?f=$num$GetVars");
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

    if(!IsSet($$new_cookie)){
      $$new_cookie='0';
    }

    $use_haveread=false;
    if(IsSet($$haveread_cookie)) {
      $arr=explode(".", $$haveread_cookie);
      $old_message=@reset($arr);
      array_walk($arr, "explode_haveread");
      $use_haveread=true;
    }
    else{
      $old_message=$$new_cookie;
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

  if(initvar("admview")!=1) {
    $limitApproved=" and approved='Y'";
  } else {
    $limitApproved="";
  }

  if($action!=0 && ($action==1 || $action==2)){

    if($DB->type=="sybase") {
      $limit="";
      $q->query($DB, "set rowcount $ForumDisplay");
    }
    elseif($DB->type=="postgresql"){
      $limit="";
      $q->query($DB, "set QUERY_LIMIT TO 1");
    }
    else{
      $limit=" limit 1";
    }
    if ($ForumMultiLevel==2){
        $SQL = "Select modifystamp from $ForumTableName where thread=$thread $limitApproved $limit";
        $q->query($DB,$SQL);
        $thms=$q->field("modifystamp", 0);
    }
    switch($action){
      case 2:
        $cutoff_thread=$thread-$cutoff;
        if ($ForumMultiLevel==2){
            $sSQL="Select thread, id from $ForumTableName where modifystamp < $thms $limitApproved order by modifystamp desc $limit";
        } else {
            $sSQL="Select thread, id from $ForumTableName where thread<$thread and thread>$cutoff_thread and id=thread $limitApproved order by thread desc $limit";
        }
        break;
      case 1:
        $cutoff_thread=$thread+$cutoff;
        if ($ForumMultiLevel==2){
            $sSQL="select thread, id from $ForumTableName where modifystamp>$thms $limitApproved order by modifystamp $limit";
        } else {
            $sSQL="Select thread, id from $ForumTableName where thread<$cutoff_thread and thread>$thread and id=thread $limitApproved order by thread asc $limit";
        }
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

    $tres=$msg->getrow();
    Header("Location: $read_page.$ext?f=$num&i=$tres[id]&t=$tres[thread]$GetVars");
    exit();

  }

  $sSQL = "Select * from $ForumTableName where thread=$thread $limitApproved order by id";

  $msg_list = new query($DB, $sSQL);

  $rec=$msg_list->getrow();
  $x=0;
  While(is_array($rec)){
    $headers[]=$rec;
    if($rec["id"]==$id) $loc=$x;
    if($ForumSecurity!=SEC_NONE && $rec["userid"]>0) $ids[]=$rec["userid"];
    $rec=$msg_list->getrow();
    $x++;
  }

  // Get the user info.  I curse PG for not having Left Joins.
  if(@is_array($ids)){

    $SQL="select id, name, email, signature from $pho_main"."_auth where id in (".implode(",", $ids).")";
    $q->query($DB, $SQL);
    $rec=$q->getrow();
    While(is_array($rec)){
      $users[$rec["id"]]=$rec;
      $rec=$q->getrow();
    }

    $SQL="select user_id from $pho_main"."_moderators where forum_id=$f or forum_id=0 and user_id in (".implode(",", $ids).")";
    $q->query($DB, $SQL);
    $rec=$q->getrow();
    While(is_array($rec)){
      $moderators[$rec["user_id"]]=true;
      $rec=$q->getrow();
    }

  }

  if ($$phflat) {
    $sSQL = "SELECT $ForumTableName.id AS id, $ForumTableName.thread AS thread, body from $ForumTableName, ".$ForumTableName."_bodies WHERE $ForumTableName.approved = 'Y' AND $ForumTableName.thread = ".$thread." AND $ForumTableName.id = ".$ForumTableName."_bodies.id ORDER BY id";
  } else {
    $sSQL = "Select * from $ForumTableName"."_bodies where id=".$id;
  }

  $msg_body = new query($DB, $sSQL);

  $rec=$msg_body->getrow();
  While(is_array($rec)){
    $bodies[]=$rec;
    $rec=$msg_body->getrow();
  }

  $msg_body->free();

  $header_rows=count($headers);
  $body_rows=count($bodies);

  $next_thread = "f=$num&t=$thread&a=2$GetVars";
  $prev_thread = "f=$num&t=$thread&a=1$GetVars";

  if(!$$phflat){

    if($loc+1==$header_rows){
      $next_link = $next_thread;
    }
    else{
      $next_loc = $loc+1;
      $next_id = $headers[$next_loc]["id"];
      $next_link = "f=$num&i=$next_id&t=$thread$GetVars";
    }

    if($loc==0){
      $prev_link = $prev_thread;
    }
    else{
      $prev_loc = $loc-1;
      $prev_id = $headers[$prev_loc]["id"];
      $prev_link = "f=$num&i=$prev_id&t=$thread$GetVars";
    }

    if(empty($haveread[$id]) && $UseCookies && $id > $old_message){
      if(empty($$haveread_cookie)){
        $haveread[$$new_cookie] = true;
        $$haveread_cookie=$$new_cookie;
      }
      $$haveread_cookie.=".";
      $$haveread_cookie.="$id";
      $haveread[$id] = true;
      SetCookie("phorum-haveread-$ForumTableName",$$haveread_cookie,0);
    }

    $max_id=$id;
  }
  else{
    $prev_link=$prev_thread;
    $next_link=$next_thread;
    $lNextMessage=$lNextTopic;
    $lPreviousMessage=$lPreviousTopic;
    if($UseCookies){
      $madechange=false;
      @reset($headers);
      $row=@current($headers);
      while(!empty($row["id"])){
        if(empty($haveread[$row["id"]]) && $row["id"] > $old_message){
          $madechange=true;
          if(empty($$haveread_cookie)){
            $haveread[$$new_cookie] = true;
            $$haveread_cookie=$$new_cookie;
          }
          $$haveread_cookie.=".";
          $$haveread_cookie.=$row["id"];
        }
        $haveread[$row["id"]] = true;
        $max_id=$row["id"];
        $row=next($headers);
      }
      if ($madechange) {
        SetCookie($haveread_cookie,$$haveread_cookie,0);
      }
    }
  }

  if($UseCookies){
    if($$new_cookie<$max_id){
      $$new_cookie=$max_id;
      SetCookie($new_cookie,$$new_cookie,time()+ 31536000);
    }
  }

  $subject = chop($headers[$loc]["subject"]);

  $rawsub=ereg_replace("</*b>", "", $subject);
  $title = " - ".$rawsub;

  include phorum_get_file_name("header");

  $toThread = $thread + 1;

//////////////////////////
  // START NAVIGATION     //
  //////////////////////////

    $menu=array();

    if(!$$phflat)
      addnav($menu2, $lReplyMessage, "$read_page.$ext?f=$f&i=$id&t=$t$GetVars#REPLY");

    if($ActiveForums>1)
      // Forum List
      addnav($menu2, $lForumList, "$forum_page.$ext?f=$ForumParent$GetVars");
    // New Topic
    addnav($menu1, $lStartTopic, "$post_page.$ext?f=$num$GetVars");
    // Go To Top
    addnav($menu1, $lGoToTop, "$list_page.$ext?f=$num$GetVars");
    // Go To Topic
    if ($ForumMultiLevel==2) {
      // Float to Top
      addnav($menu1, $lGoToTopic, "$list_page.$ext?f=$num&t=".$headers[0]["modifystamp"]."&a=3$GetVars");
    } else {
      addnav($menu1, $lGoToTopic, "$list_page.$ext?f=$num&t=$toThread&a=3$GetVars");
    }
    if($$phflat==0){
      // Flat View
      addnav($menu2, $lReadFlat, "$read_page.$ext?f=$num&i=$id&t=$thread&v=f$GetVars");
    } else {
      // Threaded View
      addnav($menu2, $lReadThreads, "$read_page.$ext?f=$num&i=$id&t=$thread&v=t$GetVars");
    }
    // Search
    addnav($menu1, $lSearch, "$search_page.$ext?f=$num$GetVars");

    // Log Out/Log In
    if($ForumSecurity){
      if(!empty($phorum_auth)){
        addnav($menu2, $lLogOut, "login.$ext?logout=1$GetVars");
        addnav($menu2, $lMyProfile, "profile.$ext?f=$f&id=$phorum_user[id]$GetVars");
      }
      else{
        addnav($menu1, $lLogIn, "login.$ext?f=$num$GetVars");
      }
    }

    $TopLeftNav=getnav($menu1);
    $LowLeftNav=getnav($menu2);

    $menu=array();
    // Prev Thread
    addnav($menu, $lPreviousTopic, "$read_page.$ext?$prev_thread$GetVars");
    // Next Thread
    addnav($menu, $lNextTopic, "$read_page.$ext?$next_thread$GetVars");

    $ThreadNav=getnav($menu);

    $menu=array();
    // Previous Message
    addnav($menu, $lPreviousMessage, "$read_page.$ext?$prev_link$GetVars");
    // Next Message
    addnav($menu, $lNextMessage, "$read_page.$ext?$next_link$GetVars");

    $MessageNav=getnav($menu);

  //////////////////////////
  // END NAVIGATION       //
  //////////////////////////

?>
<script language="JavaScript" type="text/javascript">

function delmsg(url){
ans=window.confirm("<?php echo $lDelMessageWarning; ?>");
  if(ans){
    window.location.replace(url);
  }
}
</script>
<table width="<?php echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td width="100%" align="left" <?php echo bgcolor($ForumNavColor); ?>><?php echo $TopLeftNav; ?></td>
    <td nowrap align="right" <?php echo bgcolor($ForumNavColor); ?>><?php echo $MessageNav; ?></tr>
</table>
<?php if ($header_rows==0 || $body_rows==0) { ?>
<table class="PhorumListTable" width="<?php echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="2" border="0">
<tr>
    <td <?php echo bgcolor($ForumTableHeaderColor); ?>><FONT color="<?php echo $ForumTableHeaderFontColor; ?>">&nbsp;<?php echo $lViolationTitle; ?></font></td>
</tr>
<tr>
    <td <?php echo bgcolor($ForumTableBodyColor2); ?> valign="TOP"><table width="100%" cellspacing="0" cellpadding="5" border="0">
<tr>
    <td <?php echo bgcolor($ForumTableBodyColor2); ?> width="100%" valign="top"><font color="<?php echo $ForumTableBodyFontColor2; ?>"><?php echo $lNotFound; ?></td>
</tr>
</table>
</td>
</tr>
</table>

<?php }else{

  @reset($headers);
  @reset($bodies);
  $head_row=@current($headers);
  $body_row=@current($bodies);
  while(is_array($head_row) && is_array($body_row)){
    if($head_row["id"]==$body_row["id"]){
      $rec_id=$head_row["id"];
      $subject = chop($head_row["subject"]);
      $datestamp = date_format($head_row["datestamp"]);
      $body = $body_row["body"];
      $host="";
      $profile_link="";
      $sig="";
      $reply_url="$read_page.$ext?f=$f&i=$rec_id&t=$t$GetVars#REPLY";

      if($head_row["userid"]>0 && isset($users[$head_row["userid"]])){
        $user=$users[$head_row["userid"]];
        $author=$qauthor=$user["name"];
        $email=$user["email"];
        $host="";
        $author = "<a href=\"$forum_url/profile.$ext?f=$ForumId&id=$head_row[userid]$GetVars\">$author</a>";
        // replace sig
        $sig=$user["signature"];
      }
      else{
        $author=$qauthor=chop($head_row["author"]);
        $email = chop($head_row["email"]);
        if($email!=""){
          $author = "<a href=\"mailto:".htmlencode($email)."?subject=$rawsub\">$author</a>";
        }
        $host="";
      }
      $real_host=chop($head_row["host"]);
      if(!empty($real_host) && ($ForumShowIP==1 || ($ForumShowIP==2 && $head_row["userid"]==0) || isset($phorum_user["moderator"]))){
          $host_arr=explode(".", $real_host);
          $count=count($host_arr);

          if(empty($phorum_user["moderator"]) && $count > 1){
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
          $host="($host)";
      }

      if($head_row["id"]==$i){
          $qauthor=ereg_replace("<b>|</b>", "", $qauthor);
          $qsubject=ereg_replace("<b>|</b>", "", $subject);
      }
?>
<a name="reply_<?php echo $head_row["id"]; ?>"></a>
<table class="PhorumListTable" width="<?php echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="2" border="0">
<tr>
    <td <?php echo bgcolor($ForumTableHeaderColor); ?>><FONT class="PhorumTableHeader" color="<?php echo $ForumTableHeaderFontColor; ?>">&nbsp;<?php echo $subject; ?></font></td>
</tr>
<tr>
    <td <?php echo bgcolor($ForumTableBodyColor2); ?> valign="TOP"><table width="100%" cellspacing="0" cellpadding="5" border="0">
<tr>
    <td <?php echo bgcolor($ForumTableBodyColor2); ?> width="100%" valign="top"><font class="PhorumMessage" color="<?php echo $ForumTableBodyFontColor2; ?>">
<?php echo $lAuthor;?>:&nbsp;<?php echo $author; ?>&nbsp;<?php echo $host; ?><br>
<?php echo $lDate;?>:&nbsp;&nbsp;&nbsp;<?php echo $datestamp; ?><br>
<?php

    // exec read_header plugins
    @reset($plugins["read_header"]);
    while(list($key,$val) = each($plugins["read_header"])) {
      $val($rec_id);
    }

    if ($AllowAttachments && $ForumAllowUploads == 'Y') {
      $SQL="Select id, filename from $ForumTableName"."_attachments where message_id=$rec_id";
      $q->query($DB, $SQL);
      while($rec=$q->getrow()){
        $filename="$AttachmentDir/$ForumTableName/$rec_id"."_$rec[id]".strtolower(strrchr($rec["filename"], "."));
        $size=filesize($filename);
        if($size<1024) $size=1024;
        $size=round($size/1024)."k";
        echo "$lFormAttachment:&nbsp; <a href=\"$forum_url/download.$ext/$num,$rec_id,$rec[id]/$rec[filename]\">$rec[filename]</A> ($size)<BR>\n";
      }
    }
    echo '<br>';

    $qbody=$body;

    $body=str_replace(PHORUM_SIG_MARKER, $sig, $body);

    $body=format_body($body);
?>
<?php echo $body; ?></font><p>
</td>
</tr>
</table>
</td>
</tr>
<?php if(!$$phflat){ ?>
<?php if(!empty($phorum_user["moderator"])){ ?>
<tr>
<td valign="TOP" width="100%" align="RIGHT" <?php echo bgcolor($ForumTableBodyColor2); ?>>
<table>
<tr>
<td valign="TOP" width="100%" align="LEFT" <?php echo bgcolor($ForumTableBodyColor2); ?>>
<FONT color='<?php echo $ForumTableBodyFontColor1; ?>' class="PhorumNav"><?php echo $lModerators; ?></font>
<br><?php echo "<a href=\"$forum_url/moderator.$ext?mod=edit&f=$ForumId&i=$id&t=$thread$GetVars\">";?><FONT color='<?php echo $ForumTableBodyFontColor1; ?>' class="PhorumNav"><?php echo $lModEdit; ?></a></font>
<br><a href="javascript:delmsg('<?php echo "$forum_url/moderator.$ext?mod=delete&f=$ForumId&i=$rec_id&t=$thread$GetVars";?>')"><FONT color='<?php echo $ForumTableBodyFontColor1; ?>' class="PhorumNav"><?php echo $lModDelete; ?></a></font>
</td>
</tr>
</table>
</td>
</tr>
<?php } // if($phorum_user ...
?>
</table>
<table width="<?php echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td valign="TOP" width=100% <?php echo bgcolor($ForumNavColor); ?>><FONT color='<?php echo $ForumNavFontColor; ?>' class="PhorumNav"><?php echo $LowLeftNav; ?></font></td>
    <td valign="TOP" align="RIGHT" nowrap <?php echo bgcolor($ForumNavColor); ?>><?php echo $ThreadNav; ?></td>
</tr>
</table>
<p>
<?php }else{ ?>
<tr>
    <td valign="TOP" width="100%" align="RIGHT" <?php echo bgcolor($ForumTableBodyColor2); ?>><a href="<?php echo $reply_url; ?>"><FONT color='<?php echo $ForumTableBodyFontColor1; ?>' class="PhorumNav"><?php echo $lReplyMessage; ?></a></font><br>
<?php if(!empty($phorum_user["moderator"])){ ?>
<table>
<tr>
<td valign="TOP" width="100%" align="LEFT" <?php echo bgcolor($ForumTableBodyColor2); ?>>
<FONT color='<?php echo $ForumTableBodyFontColor1; ?>' class="PhorumNav"><?php echo $lModerators; ?></font>
<br><?php echo "<a href=\"$forum_url/moderator.$ext?mod=edit&f=$ForumId&i=$rec_id&t=$thread$GetVars\">";?><FONT color='<?php echo $ForumTableBodyFontColor1; ?>' class="PhorumNav"><?php echo $lModEdit; ?></a></font>
<br><a href="javascript:delmsg('<?php echo "$forum_url/moderator.$ext?mod=delete&f=$ForumId&i=$rec_id&t=$thread$GetVars";?>')"><FONT color='<?php echo $ForumTableBodyFontColor1; ?>' class="PhorumNav"><?php echo $lModDelete; ?></a></font>
</td>
</tr>
</table>
<?php } // if($phorum_user ...
?>
</td>
</tr>
</table>
<?php } ?>
<?php
      $body_row=next($bodies);
      if(is_array($body_row)){
?>
<table width="<?php echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="0" border="0">
<tr>
    <td width="100%"><FONT color='<?php echo $ForumTableBodyFontColor1; ?>' class="PhorumNav">&nbsp;</font></td>
</tr>
</table>
<?php
      }
    }
    $head_row=next($headers);
  }
?>
<?php
  if(!$$phflat){
    if(!$ForumMultiLevel){
      include "$include_path/threads.php";
    }
    else{
      include "$include_path/multi-threads.php";
    }
  }
  else{
?>
<table width="<?php echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td width="100%" align="left" <?php echo bgcolor($ForumNavColor); ?>><?php echo $LowLeftNav; ?></td>
    <td nowrap align="right" <?php echo bgcolor($ForumNavColor); ?>><?php echo $MessageNav; ?></tr>
</table>
<?php
    unset($TopLeftNav);
  }
  unset($author);
  unset($email);
  unset($subject);
?>
<a name="REPLY"></a>
<br /><br />
<?php
}
    require "$include_path/form.php";
    include phorum_get_file_name("footer");
?>
