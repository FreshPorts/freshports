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

  require "./common.php";

  $thread=$t;
  $action=$a;

  if($num==0 || $ForumName==''){
    Header("Location: $forum_url?$GetVars");
    exit;
  }

  $phcollapse="phorum-collapse-$ForumTableName";
  $new_cookie="phorum-new-$ForumTableName";
  $haveread_cookie="phorum-haveread-$ForumTableName";

  if($UseCookies){

    if ($r==1) {
      $sSQL = "Select max(id) as max_id FROM $ForumTableName WHERE approved='Y'";
      $q->query($DB, $sSQL);
      $aryRow=$q->getrow();
      if(isset($aryRow['max_id'])){
        $max_id=$aryRow['max_id'];
        $$new_cookie=$max_id;
        SetCookie($new_cookie,$$new_cookie,time()+ 31536000);
        SetCookie($haveread_cookie,$$new_cookie);	//destroy session cookie
        unset($$haveread_cookie);
      }
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

    if(IsSet($collapse)){
      $$phcollapse=$collapse;
      SetCookie("phorum-collapse-$ForumTableName",$collapse,time()+ 31536000);
    }
    elseif(!isset($$phcollapse)){
      $$phcollapse=$ForumCollapse;
    }

  }
  else{
    if(IsSet($collapse)){
      $$phcollapse=$collapse;
    }
    else{
      $$phcollapse=$ForumCollapse;
    }
  }

  if($DB->type=="sybase") {
    $limit="";
    $q->query($DB, "set rowcount $ForumDisplay");
  }
  elseif($DB->type=="postgresql"){
    $limit="";
    $q->query($DB, "set QUERY_LIMIT TO '$ForumDisplay'");
  }
  else{
    $limit=" limit $ForumDisplay";
  }

  if($thread==0 || $action==0){
    $sSQL = "Select max(thread) as thread from $ForumTableName where approved='Y'";
    $q->query($DB, $sSQL);
    if($q->numrows()>0){
      $rec=$q->getrow();
      $maxthread = isset($rec["thread"]) ? $rec["thread"] : 0;
    }
    else{
      $maxthread=0;
    }
    if ($maxthread > $cutoff) {
      $cutoff_thread=$maxthread-$cutoff;
    } else {
      $cutoff_thread = 0;
    }
    if($$phcollapse==0){
      $sSQL = "Select thread from $ForumTableName where thread > $cutoff_thread and approved='Y' order by thread desc".$limit;
    }
    else{
      $sSQL = "Select thread, count(id) as tcount, max(datestamp) as latest, max(id) as maxid from $ForumTableName where approved='Y' AND thread > $cutoff_thread group by thread order by thread desc".$limit;
    }
  }
  else{
    if($action==1){
      $cutoff_thread=$thread+$cutoff;
      $sSQL = "Select thread from $ForumTableName where approved='Y' AND thread < $cutoff_thread AND thread > $thread order by thread".$limit;
      $q=new query($DB, $sSQL);
      $rec=$q->getrow();
      if(!empty($rec["thread"])){
        $keepgoing=true;
        $x=0;
       	while (is_array($rec) && $keepgoing){
          $thread = $rec["thread"];
          $rec=$q->getrow();
          $x++;
        }
      }
      $thread=$thread+1;
    }
    if ($thread > $cutoff) {
      $cutoff_thread=$thread-$cutoff;
    } else {
      $cutoff_thread = 0;
    }
    if($$phcollapse==0){
      $sSQL = "Select thread from $ForumTableName where approved='Y' and thread < $thread and thread > $cutoff_thread order by thread desc".$limit;
    }
    else{
      $sSQL = "Select thread, COUNT(id) AS tcount, MAX(datestamp) AS latest, MAX(id) AS maxid FROM $ForumTableName WHERE approved='Y' AND thread < $thread AND thread > $cutoff_thread GROUP BY thread ORDER BY thread DESC".$limit;
    }
  }

  $thread_list = new query($DB, $sSQL);

  if($DB->type=="sybase") {
    $q->query($DB, "set rowcount 0");
  }
  elseif($DB->type=="postgresql"){
    $q->query($DB, "set QUERY_LIMIT TO '0'");
  }

  $rec=$thread_list->getrow();

  if(empty($rec["thread"]) && $action!=0){
    Header("Location: $forum_url/$list_page.$ext?f=$num$GetVars");
    exit();
  }

  if(isset($rec['thread'])){
    $max=$rec["thread"];
    $keepgoing=true;
    $x=0;
    while (is_array($rec)){
      $threads[]=$rec;
      $min=$rec["thread"];
      $rec=$thread_list->getrow();
    }
  }
  else{
    $threads="";
    $max=0;
    $min=0;
  }

  if($$phcollapse==0){
    $sSQL = "Select id,parent,thread,subject,author,datestamp from $ForumTableName where approved='Y' AND thread<=$max and thread>=$min order by thread desc, id asc";
  }
  else{
    $sSQL = "Select id,thread,subject,author,datestamp from $ForumTableName where approved='Y' AND thread = id AND thread<=$max AND thread>=$min order by thread desc";
  }

  $msg_list = new query($DB, $sSQL);

  $rec=$msg_list->getrow();
  while(is_array($rec)){
    $headers[]=$rec;
    $rec=$msg_list->getrow();
  }

  $rows=@count($headers);

  if(file_exists("$include_path/header_$ForumConfigSuffix.php")){
    include "$include_path/header_$ForumConfigSuffix.php";
  }
  else{
    include "$include_path/header.php";
  }

  if($$phcollapse==0){
    $collapse_link = "<a href=\"$list_page.$ext?f=$num&collapse=1$GetVars\"><FONT color='$ForumNavFontColor'>".$lCollapseThreads."</font></a>";
  }
  else{
    $collapse_link = "<a href=\"$list_page.$ext?f=$num&collapse=0$GetVars\"><FONT color='$ForumNavFontColor'>".$lViewThreads."</font></a>";
  }

  if($ActiveForums>1){
    $nav = "<div class=nav><FONT color='$ForumNavFontColor'>&nbsp;<a href=\"$forum_page.$ext?f=$ForumParent$GetVars\"><FONT color='$ForumNavFontColor'>".$lForumList."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$post_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;$collapse_link&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$search_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lSearch."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars&r=1\"><FONT color='$ForumNavFontColor'>".$lMarkRead."</font></a>&nbsp;</font></div>";
  }
  else{
    $nav = "<div class=nav><FONT color='$ForumNavFontColor'>&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$post_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;$collapse_link&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$search_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lSearch."</font></a>&nbsp;|&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars&r=1\"><FONT color='$ForumNavFontColor'>".$lMarkRead."</font></a>&nbsp;</font></div>";
  }


  $pagenav="";
  if($action!=0) $pagenav="<FONT color=\"$ForumNavFontColor\">&nbsp;<a href=\"$list_page.$ext?f=$num&t=$max&a=1$GetVars\"><FONT color=\"$ForumNavFontColor\">$lNewerMessages</font></a>";
  if($rows>=$ForumDisplay){
    if(!empty($pagenav)) $pagenav.="&nbsp;&nbsp;|&nbsp;&nbsp;";
    $pagenav.="<a href=\"$list_page.$ext?f=$num&t=$min&a=2$GetVars\"><FONT color=\"$ForumNavFontColor\">$lOlderMessages</font></a>&nbsp;</font>";
  }
  if(empty($pagenav)) {
    $pagenav="&nbsp;";
  }

?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
  <td width="60%" nowrap <?PHP echo bgcolor($ForumNavColor); ?>><?PHP echo $nav; ?></td>
  <td align="right" width="40%" <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav><?PHP echo $pagenav; ?></div></td>
</tr>
</table>
<?PHP
  if(!$ForumMultiLevel || $$phcollapse){
    include "$include_path/threads.php";
  }
  else{
    include "$include_path/multi-threads.php";
  }
?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
  <td width="60%" nowrap <?PHP echo bgcolor($ForumNavColor); ?>><?PHP echo $nav; ?></td>
  <td align="right" width="40%" <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav><?PHP echo $pagenav; ?></div></td>
</tr>
</table>
<?PHP
  if(file_exists("$include_path/footer_$ForumConfigSuffix.php")){
    include "$include_path/footer_$ForumConfigSuffix.php";
  }
  else{
    include "$include_path/footer.php";
  }
?>