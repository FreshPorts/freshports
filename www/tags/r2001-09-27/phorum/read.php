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

    if(!IsSet($$new_cookie)){
      $$new_cookie='0';
    }

    $use_haveread=false;
    if(IsSet($$haveread_cookie)) {
      $arr=explode(".", $$haveread_cookie);
      $old_message=reset($arr);
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

  if($admview!=1) {
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

    if($DB->type=="sybase") {
      $limit="";
      $q->query($DB, "set rowcount 0");
    }
    elseif($DB->type=="postgresql"){
      $q->query($DB, "set QUERY_LIMIT TO '0'");
    }

    if($msg->numrows()==0){
	  	Header("Location: $list_page.$ext?f=$num$GetVars");
		  exit;
  	}

    $tres=$msg->getrow();
    $id = $tres["id"];
    $thread = $tres["thread"];
  }

  $sSQL = "Select * from $ForumTableName where thread=$thread".$limitApproved." order by id";

  $msg_list = new query($DB, $sSQL);

  $rec=$msg_list->getrow();
  $x=0;
  While(is_array($rec)){
    $headers[]=$rec;
    if($rec["id"]==$id) $loc=$x;
    $rec=$msg_list->getrow();
    $x++;
  }

  if ($$phflat) {
    if ($admview==1) {
      $sSQL = "Select * from $ForumTableName"."_bodies where thread=".$thread." ORDER BY id";
    }
    $sSQL = "SELECT $ForumTableName.id AS id, $ForumTableName.thread AS thread, body from $ForumTableName, ".$ForumTableName."_bodies WHERE $ForumTableName.approved = 'Y' AND $ForumTableName.thread = ".$thread." AND $ForumTableName.id = ".$ForumTableName."_bodies.id ORDER BY id";
//  $sSQL = "SELECT a.id AS id, a.thread AS thread, b.body AS body from ".$ForumTableName." AS a, ".$ForumTableName."_bodies AS b WHERE a.approved = 'Y' AND a.thread = ".$thread." AND a.id = b.id ORDER BY id";
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
      reset($headers);
      $row=current($headers);
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

  if(file_exists("$include_path/header_$ForumConfigSuffix.php")){
    include "$include_path/header_$ForumConfigSuffix.php";
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
    <td width="100%" align="left" <?PHP echo bgcolor($ForumNavColor); ?>><?PHP echo $nav; ?></td>
    <td nowrap align="right" <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav><FONT color='<?PHP echo $ForumNavFontColor; ?>'>&nbsp;<a href="<?PHP echo  "$read_page.$ext?$prev_link"; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lPreviousMessage;?></a></font>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?PHP echo "$read_page.$ext?$next_link"; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lNextMessage;?></font></a></div></font></td></tr>
</table>
<?PHP if ($header_rows==0 || $body_rows==0) { ?>
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
  @reset($headers);
  @reset($bodies);
  $head_row=@current($headers);
  $body_row=@current($bodies);
  while(is_array($head_row) && is_array($body_row)){
    if($head_row["id"]==$body_row["id"]){
      $rec_id=$head_row["id"];
      $subject = chop($head_row["subject"]);
      $author = chop($head_row["author"]);
      $datestamp = date_format($head_row["datestamp"]);
      $email = chop($head_row["email"]);
      $attachment = chop($head_row["attachment"]);
      $real_host=chop($head_row["host"]);
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

      $body = $body_row["body"];

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
<?PHP echo $lDate;?>:&nbsp;&nbsp;&nbsp;<?PHP echo $datestamp; ?><br>
<?PHP

    // exec read_header plugins
    reset($plugins["read_header"]);
    while(list($key,$val) = each($plugins["read_header"])) {
      $val($rec_id);
    }

    if ($uploadDir != '' AND !empty($attachment)) {
      print "$lFormAttachment:&nbsp; <A HREF=\"$forum_url/download.$ext?f=$num&file=$attachment\">$attachment</A><BR>";
    }
    echo '<br>';

    $qbody=$body;

    $body=str_replace("{phopen}", "", $body);
    $body=str_replace("{phclose}", "", $body);

    $body=eregi_replace("<(mailto:)([^ >\n\t]+)>", "{phopen}a href=\"\\1\\2\"{phclose}\\2{phopen}/a{phclose}", $body);
    $body=eregi_replace("<([http|news|ftp]+://[^ >\n\t]+)>", "{phopen}a href=\"\\1\"{phclose}\\1{phopen}/a{phclose}", $body);

    if($ForumAllowHTML!="Y" && substr($body, 0, 6)!="<HTML>"){
      $body=eregi_replace("<(/*($ForumAllowHTML) *[^>]*)>", "{phopen}\\1{phclose}", $body);
      $body=str_replace("<", "&lt;", $body);
      $body=str_replace(">", "&gt;", $body);
    }

    $body=str_replace("{phopen}", "<", $body);
    $body=str_replace("{phclose}", ">", $body);

    // exec all read plugins
    reset($plugins["read_body"]);
    while(list($key,$val) = each($plugins["read_body"])) {
      $body = $val($body);
    }

    if(empty($ForumAllowHTML) && substr($body, 0, 6)!="<HTML>"){
      $body=nl2br($body);
    }
    else{
      $body=my_nl2br($body);
    }
?>
<?PHP echo $body; ?></font></tt><p>
</td>
</tr>
</table>
</td>
</tr>
</table>
<?PHP if(!$$phflat){ ?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td valign="TOP" width=100% <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav><FONT color='<?PHP echo $ForumNavFontColor; ?>'><a href="#REPLY"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lReplyMessage; ?></font></a></font></div></td>
    <td valign="TOP" align="RIGHT" nowrap <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav><FONT color='<?PHP echo $ForumNavFontColor; ?>'>&nbsp;<a href="<?PHP echo "$read_page.$ext"; ?>?<?PHP echo $prev_thread; ?>&<?PHP echo $GetVars; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lPreviousTopic;?></font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?PHP echo "$read_page.$ext"; ?>?<?PHP echo $next_thread; ?>&<?PHP echo $GetVars; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lNextTopic;?></font></a></font></div></td>
</tr>
</table>
<p>
<?PHP }else{ ?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td valign="TOP" width="100%" align="RIGHT" <?PHP echo bgcolor($ForumTableBodyColor2); ?>><div class=nav><FONT color='<?PHP echo $ForumTableBodyFontColor1; ?>'><a href="#REPLY"><FONT color='<?PHP echo $ForumTableBodyFontColor1; ?>'><?PHP echo $lReplyMessage; ?></font></a></font></div></td>
</tr>
</table>
<?PHP } ?>
<?PHP
      $body_row=next($bodies);
    }
    $head_row=next($headers);
  }
?>
<?PHP
  if(!$$phflat){
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
  if(file_exists("$include_path/footer_$ForumConfigSuffix.php")){
    include "$include_path/footer_$ForumConfigSuffix.php";
  }
  else{
    include "$include_path/footer.php";
  }
?>
