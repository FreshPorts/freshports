<?php check_security(); ?>
<?php
    /* Forum Properties*/

    $SQL="Select * from $PHORUM[main_table] where id=$f";
    $q->query($DB, $SQL);
    $forum=$q->getrow();

?>
<script language="JavaScript" type="text/javascript">
  function dropforum(url, folder){
    if(folder){
      ans=window.confirm("You are about to drop this folder.  All sub folders and sub forums of this folder will be dropped also.  Do you want to continue?");
    }
    else{
      ans=window.confirm("You are about to drop this forum.  Do you want to continue?");
    }
    if(ans){
      window.location.replace(url);
    }
  }
</script>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="num" value="<?php echo $num; ?>">
<input type="Hidden" name="action" value="props">
<input type="Hidden" name="frompage" value="props">
<input type="Hidden" name="page" value="managemenu">
<input type="Hidden" name="table" value="<?php echo $forum["table_name"]; ?>">
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
  <td colspan="2" align="center" valign="middle" class="table-header">Edit <?php $folder ? $word="Folder" : $word="Forum"; echo "$word: $forum[name]"; ?></td>
</tr>
<tr>
  <th valign="middle">Name:</th>
  <td valign="middle"><input type="Text" name="name" value="<?php echo $forum["name"]; ?>" size="25" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Description:</th>
  <td valign="middle"><textarea name="description" cols="60" rows="3" wrap="VIRTUAL" style="width: 300px;"><?php echo $forum["description"]; ?></textarea></td>
</tr>
<tr>
  <th valign="middle">Config Suffix:</th>
  <td valign="middle"><input type="Text" name="config_suffix" value="<?php echo $forum["config_suffix"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Folder:</th>
  <td valign="middle"><select name="parent">
<option value="0">Top Level</option>
<?php
  $sSQL="Select id, name from $PHORUM[main_table] where folder='1' and id<>$forum[id] order by name";
  $q->query($DB, $sSQL);
  $rec=(object)$q->getrow();
  While(isset($rec->id)){
    echo "<option value=\"$rec->id\"";
    if($forum["parent"]==$rec->id) echo ' selected';
    echo ">$rec->name</option>\n";
    $rec=(object)$q->getrow();
  }
?>
</select></td>
</tr>
<tr>
  <th valign="middle">Language File:</th>
  <td valign="middle"><select name="language_file">
<?php
$aryLangs = array();
$strLangDir = "lang/";
$dirCurrent = dir($strLangDir);
while($strFile=$dirCurrent->read()) {
  if (is_file($strLangDir.$strFile)) {
    $aryLangs[] = $strLangDir.$strFile;
  }
}
$dirCurrent->close();

if (count($aryLangs) > 1) { sort ($aryLangs); }

$file = current($aryLangs);
while ($file) {
  if($file!="$strLangDir"."blank.php"){
    $intStartLang = strpos($file, '/') + 1;
    $intLengthLang = strrpos($file, '.') - $intStartLang;
    $text=ucwords(substr($file,$intStartLang,$intLengthLang));
    echo "<option value=\"$file\"";
    if($file==$forum["lang"]) echo ' selected';
    echo ">$text</option>\n";
  }
  $file = next($aryLangs);
}
?></select></td>
</tr>
<?php if(!$forum["folder"]){ ?>
<tr>
  <th valign="middle">Security:</th>
  <td valign="middle"><select name="security">
<option value="<?php echo SEC_OPTIONAL; ?>" <?php if($forum["security"]==SEC_OPTIONAL) echo "selected"; ?>>Login Is Optional</option>
<option value="<?php echo SEC_POST; ?>" <?php if($forum["security"]==SEC_POST) echo "selected"; ?>>Login To Post</option>
<option value="<?php echo SEC_ALL; ?>" <?php if($forum["security"]==SEC_ALL) echo "selected"; ?>>Login Required</option>
<option value="<?php echo SEC_NONE; ?>" <?php if($forum["security"]==SEC_NONE) echo "selected"; ?>>Public</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Email Moderator:</th>
  <td valign="middle"><select name="moderation" class=big>
  <option value="none" <?php if($forum["moderation"]=='n') echo 'selected'; ?>>No</option>
  <option value="all" <?php if($forum["moderation"]=='a') echo 'selected'; ?>>All Messages Before Posted</option>
  <option value="react" <?php if($forum["moderation"]=='r') echo 'selected'; ?>>All Messages After Posted</option>
</select></td>
</tr>
<?php } ?>
<tr>
  <td colspan="2" valign="middle" bgcolor="#000000">&nbsp;</td>
</tr>
<tr>
  <th valign="middle">Body Color:<br><font size="-2">Leave blank for default. (<?php echo $default_body_color; ?>)</th>
  <td valign="middle"><input type="Text" name="body_color" value="<?php echo $forum["body_color"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Link Color:<br><font size="-2">Leave blank for default. (<?php echo $default_body_link_color; ?>)</th>
  <td valign="middle"><input type="Text" name="body_link_color" value="<?php echo $forum["body_link_color"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Visited Link Color:<br><font size="-2">Leave blank for default. (<?php echo $default_body_vlink_color; ?>)</th>
  <td valign="middle"><input type="Text" name="body_vlink_color" value="<?php echo $forum["body_vlink_color"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Active Link Color:<br><font size="-2">Leave blank for default. (<?php echo $default_body_alink_color; ?>)</th>
  <td valign="middle"><input type="Text" name="body_alink_color" value="<?php echo $forum["body_alink_color"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Table Width:<br><font size="-2">Leave blank for default. (<?php echo $default_table_width; ?>)</th>
  <td valign="middle"><input type="Text" name="table_width" value="<?php echo $forum["table_width"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Table Header Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_header_color; ?>)</th>
  <td valign="middle"><input type="Text" name="table_header_color" value="<?php echo $forum["table_header_color"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Table Header Font Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_header_font_color; ?>)</th>
  <td valign="middle"><input type="Text" name="table_header_font_color" value="<?php echo $forum["table_header_font_color"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Main Table Body Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_body_color_1; ?>)</th>
  <td valign="middle"><input type="Text" name="table_body_color_1" value="<?php echo $forum["table_body_color_1"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Main Table Body Font Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_body_font_color_1; ?>)</th>
  <td valign="middle"><input type="Text" name="table_body_font_color_1" value="<?php echo $forum["table_body_font_color_1"]; ?>" size="10" class="TEXT"></td>
</tr>
<?php if(!$forum["Folder"]){ ?>
<tr>
  <th valign="middle">Alt. Table Body Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_body_color_2; ?>)</th>
  <td valign="middle"><input type="Text" name="table_body_color_2" value="<?php echo $forum["table_body_color_2"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Alt. Table Body Font Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_body_font_color_2; ?>)</th>
  <td valign="middle"><input type="Text" name="table_body_font_color_2" value="<?php echo $forum["table_body_font_color_2"]; ?>" size="10" class="TEXT"></td>
</tr>
<?php } ?>
<tr>
  <th valign="middle">Navigation Background Color:<br><font size="-2">Leave blank for default. (<?php echo $default_nav_color; ?>)</th>
  <td valign="middle"><input type="Text" name="nav_color" value="<?php echo $forum["nav_color"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Navigation Font Color:<br><font size="-2">Leave blank for default. (<?php echo $default_nav_font_color; ?>)</th>
  <td valign="middle"><input type="Text" name="nav_font_color" value="<?php echo $forum["nav_font_color"]; ?>" size="10" class="TEXT"></td>
</tr>

<?php if(!$forum["folder"]){ ?>
<tr>
  <td colspan="2" valign="middle" bgcolor="#000000">&nbsp;</td>
</tr>
<tr>
  <th valign="middle">Duplicate Posts:</th>
  <td valign="middle"><select name="check_dup" class=big>
<option value="0" <?php if($forum["check_dup"]==0) echo "selected"; ?>>Do Not Check For Duplicates</option>
<option value="1" <?php if($forum["check_dup"]==1) echo "selected"; ?>>Check For Duplicates</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Messages Per Page:</th>
  <td valign="middle"><input type="Text" name="display" value="<?php echo $forum["display"]; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Thread Type:</th>
  <td valign="middle"><select name="multi_level">
<option value="0" <?php if($forum["multi_level"]==0) echo "selected"; ?>>Single Level</option>
<option value="1" <?php if($forum["multi_level"]==1) echo "selected"; ?>>Multiple Levels</option>
<option value="2" <?php if($forum["multi_level"]==2) echo "selected"; ?>>Float to Top</option>
</select></td>
</tr>
<b><br>
<tr>
  <th valign="middle">Thread Display:</th>
  <td valign="middle"><select name="collapsed">
<option value="0" <?php if($forum["collapse"]==0) echo "selected"; ?>>Expanded</option>
<option value="1" <?php if($forum["collapse"]==1) echo "selected"; ?>>Collapsed</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Read Messages:</th>
  <td valign="middle"><select name="rflat" class=big>
<option value="0" <?php if($forum["flat"]==0) echo "selected"; ?>>One At A Time</option>
<option value="1" <?php if($forum["flat"]==1) echo "selected"; ?>>Entire Thread At Once</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Show Users IP/Host:</th>
  <td valign="middle"><select name="showip" class=big>
<option value="0" <?php if($forum["showip"]==0) echo "selected"; ?>>Never</option>
<option value="1" <?php if($forum["showip"]==1) echo "selected"; ?>>Always</option>
<option value="2" <?php if($forum["showip"]==2) echo "selected"; ?>>Anonymous Posts only</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Allow Email-Notification for anonymous Posters:</th>
  <td valign="middle"><select name="emailnotification" class=big>
<option value="0" <?php if($forum["emailnotification"]==0) echo "selected"; ?>>Off</option>
<option value="1" <?php if($forum["emailnotification"]==1) echo "selected"; ?>>On</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Phorum Code:</th>
  <td valign="middle"><select name="allow_html" class=big>
<option value="0" <?php if($forum["html"]==0 || $forum["allow_html"]=="") echo "selected"; ?>>Off</option>
<option value="1" <?php if($forum["html"]!=0) echo "selected"; ?>>On</option>
</select> See docs/usage.txt</td>
</tr>
<tr>
  <td colspan="2" valign="middle" bgcolor="#000000">&nbsp;</td>
</tr>
<tr>
  <th valign="middle">Allow attachments?</td>
<?php
  if($AllowAttachments){
?>
  <td valign="middle">
  <input type="radio" name="allow_uploads" value="Y" <?php if ($forum["allow_uploads"] == 'Y') echo "checked"; ?>>Yes
  <input type="radio" name="allow_uploads" value="N" <?php if ($forum["allow_uploads"] == 'N') echo "checked"; ?>>No
  </td>
<?php
  }
  else{
?>
  <td rowspan="4" valign="middle">
  <input type="hidden" name="allow_uploads" value="N">
  <input type="hidden" name="upload_size" value="">
  To use these features turn on<br>
  attachments in the <a href="?page=attachments">Phorum Setup</a>.
<?php
  }
?>
</tr>
<tr>
  <th valign="middle">Attachment Size Limit<?php if(!empty($AttachmentSizeLimit)) echo " (max: ".$AttachmentSizeLimit."k)"; ?>:</td>
<?php if($AllowAttachments){ ?>
  <td valign="middle"><input type="Text" name="upload_size" value="<?php echo $forum["upload_size"]; ?>" size="10" class="TEXT"></td>
<?php } ?>
</tr>
<tr>
  <th valign="middle">Allowed File Types:</td>
<?php if($AllowAttachments){ ?>
  <td valign="middle">
  <?php
    if(!empty($AttachmentFileTypes)){
      $types=explode(";", $AttachmentFileTypes);
      if(count($types)>3 && count($types)%4!=0){
        $types[]="";
      }
      ?><table cellspacing="0" cellpadding="2" border="0"><tr><?php
      $x=0;
      while(list($key, $type)=each($types)){
        echo "<td><font face=\"Arial,Helvetica\">";
        if(!empty($type)){
          echo "<input type=\"checkbox\" name=\"att_types[]\" value=\"$type\"";
          if(strstr($forum["upload_types"], $type)) echo " checked";
          echo "> $type</td>\n";
        }
        else{
          echo "&nbsp;";
        }
        $x++;
        if($x%4==0){
          echo "\n</tr><tr>\n";
        }
      }
      ?></tr></table><?php
    }
    else{
      ?>There are no file types listed in the<br><A HREF="?page=attachments">Phorum Setup</a>.  Therefore all will<br>be allowed.<?php
    }
  ?>
  </td>
<?php } ?>
</tr>
<tr>
  <th valign="middle">Maximum # of Attachments<?php if(!empty($MaximumNumberAttachments)) echo " (max: ".$MaximumNumberAttachments.")"; ?>:</td>
<?php if($AllowAttachments){ ?>
  <td valign="middle"><input type="Text" name="max_uploads" value="<?php echo $forum["max_uploads"]; ?>" size="10" class="TEXT"></td>
<?php } ?>
</tr>
<tr>
  <td colspan="2" valign="middle" bgcolor="#000000">&nbsp;</td>
</tr>
<tr>
  <th valign="middle">Email All Posts To:</th>
  <td valign="middle"><input type="Text" name="email_list" value="<?php echo $forum["email_list"]; ?>" size="25" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Return Email Address:</th>
  <td valign="middle"><input type="Text" name="email_return" value="<?php echo $forum["email_return"]; ?>" size="25" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Email Subject Tag:</th>
  <td valign="middle"><input type="Text" name="email_tag" value="<?php echo $forum["email_tag"]; ?>" size="25" class="TEXT"></td>
</tr>
<?php } ?>
</td>
</tr>
</table>
<p>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
