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

  require "./common.php";
  require "$include_path/post_functions.php";
  require "$include_path/read_functions.php";

  $id=(int)initvar("i");
  $thread=(int)initvar("t");

  if($num==0 || $ForumName==''){
    Header("Location: $forum_url?$GetVars");
    exit;
  }

  // Error Checking.
  // We don't want to allow attachments if:
  //   the message was posted over 5 minutes ago.
  //   the host of the post does not match this users host.
  //   or there are already the max number of attachments attached.

  $SQL="Select $ForumTableName.thread, author, datestamp, host, subject, body from $ForumTableName, $ForumTableName"."_bodies where $ForumTableName.id=$ForumTableName"."_bodies.id and $ForumTableName.id=$id";
  $q->query($DB, $SQL);
  $row=$q->getrow();

  $noattach=false;

  list($date,$time) = explode(" ",$row["datestamp"]);
  list($year,$month,$day) = explode("-",$date);
  list($hour,$minute,$second) = explode(":",$time);

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

  $datestamp = date_format($row["datestamp"]);
  $author = chop($row["author"]);

  $SQL="Select count(*) as count from $ForumTableName"."_attachments where message_id=$id";
  $q->query($DB, $SQL);

  if($q->field("count", 0)>0){
    $count=$q->field("count", 0);
  }
  else{
    $count=0;
  }

  if( (time()-mktime($hour,$minute,$second,$month,$day,$year))>300 || $host!=trim($row["host"]) || $count>=$ForumMaxUploads ){
    $noattach=true;
  }

  if(isset($post) && !$noattach){
    // Attachment handling:
    if(is_array($HTTP_POST_FILES) && count($HTTP_POST_FILES)>0){
      // PHP4 style
      $attachments=&$HTTP_POST_FILES;
    }
    else{
      // PHP3 style
      $max=min($MaximumNumberAttachments, $ForumMaxUploads);
      for($x=0;$x<$max;$x++){
        $var="attachment_$x";
        if(isset($$var)){
          $attachments[$x]["tmp_name"]=$$var;
          $var="attachment_$x"."_name";
          $attachments[$x]["name"]=$$var;
          $var="attachment_$x"."_size";
          $attachments[$x]["size"]=$$var;
          $var="attachment_$x"."_type";
          $attachments[$x]["type"]=$$var;
        }
      }
    }

      if (@is_array($attachments)) {

        while(list($key, $arr)=each($attachments)){
          if(is_uploaded_file($arr["tmp_name"])){
            $min_size=1024*min((int)$ForumUploadSize, (int)$AttachmentSizeLimit);
            if (!ereg("^[-A-Za-z0-9_\.]+$", trim($arr["name"]))) {
              $IsError="$lInvalidFile ($arr[name])";
            }
            elseif(!empty($ForumUploadTypes) && !strstr($ForumUploadTypes, strtolower(substr($arr["name"], strrpos($arr["name"], ".")+1)))){
              $IsError=$lInvalidType.strtoupper(ereg_replace(";", " ", $ForumUploadTypes));
            }
            elseif($min_size>0  && $arr["size"]>$min_size){
              $IsError=$lInvalidSize1.$arr["name"]."<br>".$lInvalidSize2.(string)min($ForumUploadSize, $AttachmentSizeLimit)."k";
            }
          }
        }

      }

    reset($attachments);

    // Attachment handling:
    if(!empty($attachments) && is_array($attachments) && empty($IsError)){
      while(list($key, $attachment)=each($attachments)){
        if($attachment["name"])
          $IsError=add_attachment($attachment, $id);
        if($IsError) break;
      }
    }

    if(empty($IsError) && @is_array($attachments)){

      // if it is not a new message and not float to top
      // send them to the message.
      initvar("more");
      if($thread!=$id && $ForumMultiLevel!=2){
        $more = $thread+1;
        $more = "&a=2&t=$more";
      }

      Header ("Location: $forum_url/$list_page.$ext?f=$num$more$GetVars");
      exit();
    }
  }

  include phorum_get_file_name("header");

  //////////////////////////
  // START NAVIGATION     //
  //////////////////////////

    $menu=array();
    if($ActiveForums>1)
      // Forum List
      addnav($menu, $lForumList, "$forum_page.$ext?f=$ForumParent$GetVars");
    // Go To Top
    addnav($menu, $lGoToTop, "$list_page.$ext?f=$num$GetVars");
    // New Topic
    addnav($menu, $lStartTopic, "$post_page.$ext?f=$num$GetVars");
    // Search
    addnav($menu, $lSearch, "$search_page.$ext?f=$num$GetVars");
    // Log Out/Log In
    if($ForumSecurity){
      if(!empty($phorum_auth)){
        addnav($menu, $lLogOut, "login.$ext?logout=1$GetVars");
        addnav($menu, $lMyProfile, "profile.$ext?f=$f&id=$phorum_user[id]$GetVars");
      }
      else{
        addnav($menu, $lLogIn, "login.$ext$GetVars");
      }
    }

    $TopLeftNav=getnav($menu);

  //////////////////////////
  // END NAVIGATION       //
  //////////////////////////

  if(isset($IsError)){
    echo "<p><b>$IsError</b>";
  }

?>
<table cellspacing="0" cellpadding="2" border="0" width="<?php echo $ForumTableWidth; ?>">
<tr>
    <td colspan="2" <?php echo bgcolor($ForumNavColor); ?>>
      <table cellspacing="0" cellpadding="1" border="0">
        <tr>
          <td><?php echo $TopLeftNav; ?></td>
        </tr>
      </table>
    </td>
</tr>
</table>
<table class="PhorumListTable" cellspacing="0" cellpadding="2" border="0" width="<?php echo $ForumTableWidth; ?>">
<tr>
    <td class="PhorumTableHeader" colspan="2" <?php echo bgcolor($ForumTableHeaderColor); ?>><FONT  class="PhorumTableHeader" color="<?php echo $ForumTableHeaderFontColor; ?>">&nbsp;<?php echo $row["subject"]; ?></font></td>
</tr>
<tr>
    <td colspan=2 <?php echo bgcolor($ForumTableBodyColor1); ?>>
<table width="100%" cellspacing="0" cellpadding="5" border="0">
<tr><td>
<?php

    if($noattach){
        echo $lCannotAttach;
    } else {
?>
<font class="PhorumMessage" color="<?php echo $ForumTableBodyFontColor1; ?>">
<?php echo $lAuthor;?>:&nbsp;<?php echo $row["author"]; ?>&nbsp;(<?php echo $host; ?>)<br>
<?php echo $lDate;?>:&nbsp;&nbsp;&nbsp;<?php echo $datestamp; ?><br><br>
<?php echo format_body($row["body"]); ?>
<?php
    }
?>
</td></tr>
</table>
    </td>
</tr>
</table>

<?php if(!$noattach){ ?>
<form action="<?php echo "$attach_page.$ext"; ?>" method="post" enctype="multipart/form-data">
<input type="Hidden" name="t" value="<?php echo $row["thread"]; ?>">
<input type="Hidden" name="f" value="<?php echo $num; ?>">
<input type="Hidden" name="i" value="<?php echo $id; ?>">
<input type="Hidden" name="post" value="1">
<?php echo $PostVars; ?>
<table class="PhorumListTable" cellspacing="0" cellpadding="2" border="0" width="<?php echo $ForumTableWidth; ?>">
<tr>
    <td class="PhorumTableHeader" colspan="2" <?php echo bgcolor($ForumTableHeaderColor); ?>><FONT  class="PhorumTableHeader" color="<?php echo $ForumTableHeaderFontColor; ?>">&nbsp;<?php echo $lFormAttach; ?></font></td>
</tr>
<?php
  if($count<$ForumMaxUploads){
    for($x=0;$x<$ForumMaxUploads-$count;$x++){
      echo "<tr>\n";
      echo '    <td ' . bgcolor($ForumTableBodyColor1) . ' nowrap><font color="' . $ForumTableBodyFontColor1 . '">&nbsp;' . $lFormAttachment . ':</font></td>';
      echo '    <td ' . bgcolor($ForumTableBodyColor1) . ' width="100%"><input type="File" name="attachment_'.$x.'" size="30" maxlength="64"></td>';
      echo "</tr>\n";
    }
  }
  else{
    echo "<tr><td ". bgcolor($ForumTableBodyColor1) ." width=\"100%\" colspan=\"2\">$lNoMoreUploads</td></tr>\n";
  }
?>
<tr>
    <td width="100%" colspan="2" align="RIGHT" <?php echo bgcolor($ForumTableBodyColor1); ?>><input type="Submit" name="post" value=" <?php echo $lFormPost;?> ">&nbsp;<br><img src="images/trans.gif" width=3 height=3 border=0></td>
</tr>
</table>
</form>
<?php
  }

  include phorum_get_file_name("footer");
  exit();

?>