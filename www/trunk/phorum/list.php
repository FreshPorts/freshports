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

  $cutoff = 800; // See the faq.

  if($num==0 || $ForumName==''){
    Header("Location: $forum_url?$GetVars");
    exit;
  }

  $phcollapse="phorum-collapse-$ForumTableName";
  $new_cookie="phorum-new-$ForumTableName";
  $haveread_cookie="phorum-haveread-$ForumTableName";

  if($UseCookies){
 
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
      $haveread[0]=$old_message;
    }

    if(IsSet($collapse)){
      $$phcollapse=$collapse;
      SetCookie("phorum-collapse-$ForumTableName",$collapse,time()+ 31536000);
    }
    elseif(!isset($$phcollapse)){
      $$phcollapse=$ForumCollapse;
    } 
    if(!IsSet($$haveread_cookie)) {
       SetCookie("phorum-haveread-$ForumTableName",urlencode(hexserialize($haveread)));
    }
    else{
       $$haveread_cookie="";
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

  if($DB->type=="postgresql"){
    $limit="";
    $q->query($DB, "set QUERY_LIMIT TO '$ForumDisplay'");
  }
  else{
    $limit=" limit $ForumDisplay";
  }

  if($thread==0 || $action==0){
    $sSQL = "Select max(thread) as thread from $ForumTableName where approved='Y'";
    $q->query($DB, $sSQL);
    if($q->numrows()>1){
      $maxthread=$q->field("thread", 0);
    }
    else{
      $maxthread=0;
    }
    $cutoff_thread=$maxthread-$cutoff;
    if($$phcollapse==0){
      $sSQL = "Select thread from $ForumTableName where thread > $cutoff_thread and approved='Y' order by thread desc".$limit;
    }
    else{
      $sSQL = "Select thread, count(*) as tcount, max(datestamp) as latest, max(id) as maxid from $ForumTableName where thread > $cutoff_thread and approved='Y' group by thread order by thread desc".$limit;
      echo "<!--$sSQL-->\n";
    }
  }
  else{
    if($action==1){
      $cutoff_thread=$thread+$cutoff;
      $sSQL = "Select thread from $ForumTableName where thread < $cutoff_thread and thread > $thread and approved='Y' order by thread".$limit;
      $q=new query($DB, $sSQL);
      if($rows=$q->numrows()){
        $thread = $q->field("thread",$rows-1);
      }
      $thread=$thread+1;
    }    
    $cutoff_thread=$thread-$cutoff;
    if($$phcollapse==0){
      $sSQL = "Select thread from $ForumTableName where thread < $thread and thread > $cutoff_thread and approved='Y' order by thread desc".$limit;
    }
    else{
      $sSQL = "Select thread, count(*) as tcount, max(datestamp) as latest, max(id) as maxid from $ForumTableName  where thread < $thread and thread > $cutoff_thread group by thread order by thread desc".$limit;
    }
  }
  $thread_list = new query($DB, $sSQL);
  if($DB->type=="postgresql"){
    $q->query($DB, "set QUERY_LIMIT TO '0'");
  }
  $rows = $thread_list->numrows();
    if($rows==0 && $action!=0){
      Header("Location: $list_page.$ext?f=$num$GetVars");
      exit();
  }

  $rec=$thread_list->getrow();
  if(isset($rec['thread'])){
    $max = $thread_list->field("thread", 0);
    $min = $thread_list->field("thread", $rows-1);
  }
  else{
    $max=0;
    $min=0;
  }

  if($$phcollapse==0){
    $sSQL = "Select id,parent,thread,subject,author,datestamp from $ForumTableName where thread<=$max and thread>=$min and approved='Y' order by thread desc, id asc";
  }
  else{
    $sSQL = "Select id,thread,subject,author,datestamp from $ForumTableName where thread<=$max and thread>=$min and thread=id and approved='Y' order by thread desc";
  }

  $msg_list = new query($DB, $sSQL);

  if(file_exists("$include_path/header_$ForumTableName.php")){
    include "$include_path/header_$ForumTableName.php";    
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
    $nav = "<div class=nav><FONT color='$ForumNavFontColor'>&nbsp;<a href=\"$forum_page.$ext?f=$ForumParent$GetVars\"><FONT color='$ForumNavFontColor'>".$lForumList."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$post_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;$collapse_link&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$search_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lSearch."</font></a>&nbsp;</font></div>";
  }
  else{
    $nav = "<div class=nav><FONT color='$ForumNavFontColor'>&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$post_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;$collapse_link&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$search_page.$ext?f=$num$GetVars\"><FONT color='$ForumNavFontColor'>".$lSearch."</font></a>&nbsp;</font></div>";
  }

?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="3" border="0">
<tr>
    <td width="100%" align="right" valign="bottom" nowrap <?PHP echo bgcolor($ForumNavColor); ?>><?PHP echo $nav; ?></td>
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
  <td align="right" width="40%" <?PHP echo bgcolor($ForumNavColor); ?>><div class=nav><FONT color='<?PHP echo $ForumNavFontColor; ?>'>&nbsp;<a href="<?PHP echo "$list_page.$ext"; ?>?f=<?PHP echo $num; ?>&t=<?PHP echo $max; ?>&a=1&<?PHP echo $GetVars; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lNewerMessages;?></font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?PHP echo "$list_page.$ext"; ?>?f=<?PHP echo $num; ?>&t=<?PHP echo $min; ?>&a=2&<?PHP echo $GetVars; ?>"><FONT color='<?PHP echo $ForumNavFontColor; ?>'><?PHP echo $lOlderMessages;?></font></a>&nbsp;</font></div></td>
</tr>
</table>
<?PHP
  if(file_exists("$include_path/footer_$ForumTableName.php")){
    include "$include_path/footer_$ForumTableName.php";    
  }
  else{
    include "$include_path/footer.php";
  }
?>