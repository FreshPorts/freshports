<?php check_security(); ?>
<?php /* New Forum */
  if($display=="")                 $display=$DefaultDisplay;
?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="action" value="add">
<input type="Hidden" name="page" value="managemenu">
<input type="Hidden" name="folder" value="<?php echo $folder; ?>">
<input type="Hidden" name="frompage" value="<?php $folder ? $word="newfolder" : $word="newforum"; echo $word; ?>">
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
  <td colspan="2" align="center" valign="middle" class="table-header">New <?php $folder ? $word="Folder" : $word="Forum"; echo $word; ?></td>
</tr>
<tr>
  <th valign="middle">Name:</th>
  <td valign="middle"><input type="Text" name="name" value="<?php echo $name; ?>" size="25" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Description:</th>
  <td valign="middle"><textarea name="description" cols="60" rows="3" wrap="VIRTUAL" style="width: 300px;"><?php echo $description; ?></textarea></td>
</tr>
<tr>
  <th valign="middle">Config Suffix:</th>
  <td valign="middle"><input type="Text" name="config_suffix" value="<?php echo $config_suffix; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Folder:</th>
  <td valign="middle"><select name="parent">
<option value="0">Top Level</option>
<?php
  $sSQL="Select id, name from ".$pho_main." where folder='1' order by name";
  $q->query($DB, $sSQL);
  $rec=(object)$q->getrow();
  While(isset($rec->id)){
    echo "<option value=\"$rec->id\"";
    if($parent==$rec->id) echo ' selected';
    echo ">$rec->name</option>\n";
    $rec=(object)$q->getrow();
  }
?>
</select></td>
</tr>
<tr>
  <th valign="middle">Language:</th>
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
    if($file==$language_file || $file==$default_lang) echo ' selected';
    echo ">$text</option>\n";
  }
  $file = next($aryLangs);
}
?>
</select></td>
</tr>
<?php if(!$folder){ ?>
<tr>
  <th valign="middle">Table Name:</th>
  <td valign="middle"><input type="Text" name="table" value="<?php echo $table; ?>" size="25" class="TEXT"><br><input type="Checkbox" name="table_exists" value="1" <?php if($table_exists) echo "checked"; ?>> Table already exists</td>
</tr>
<tr>
  <th valign="middle">Security:</th>
  <td valign="middle"><select name="security">
<option value="<?php echo SEC_OPTIONAL; ?>" <?php if(!isset($security) || $security==SEC_OPTIONAL) echo "selected"; ?>>Login Is Optional</option>
<option value="<?php echo SEC_POST; ?>" <?php if($security==SEC_POST) echo "selected"; ?>>Login To Post</option>
<option value="<?php echo SEC_ALL; ?>" <?php if($security==SEC_ALL) echo "selected"; ?>>Login Required</option>
<option value="<?php echo SEC_NONE; ?>" <?php if(isset($security) && $security==SEC_NONE) echo "selected"; ?>>Public</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Email Moderator:</th>
  <td valign="middle"><select name="moderation" class=big>
  <option value="none" <?php if($moderation=='none') echo 'selected'; ?>>No</option>
  <option value="all" <?php if($moderation=='all') echo 'selected'; ?>>All Messages Before Posted</option>
  <option value="react" <?php if($moderation=='react') echo 'selected'; ?>>All Messages After Posted</option>
</select></td>
</tr>
<?php } ?>
<tr>
  <td colspan="2" valign="middle" bgcolor="#000000"><font face="Arial,Helvetica" size="-2" color="white">&nbsp;</td>
</tr>
<tr>
  <th valign="middle">Body Color:<br><font size="-2">Leave blank for default. (<?php echo $default_body_color; ?>)</th>
  <td valign="middle"><input type="Text" name="body_color" value="<?php echo $body_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Link Color:<br><font size="-2">Leave blank for default. (<?php echo $default_body_link_color; ?>)</th>
  <td valign="middle"><input type="Text" name="body_link_color" value="<?php echo $body_link_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Visited Link Color:<br><font size="-2">Leave blank for default. (<?php echo $default_body_vlink_color; ?>)</th>
  <td valign="middle"><input type="Text" name="body_vlink_color" value="<?php echo $body_vlink_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Active Link Color:<br><font size="-2">Leave blank for default. (<?php echo $default_body_alink_color; ?>)</th>
  <td valign="middle"><input type="Text" name="body_alink_color" value="<?php echo $body_alink_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Table Width:<br><font size="-2">Leave blank for default. (<?php echo $default_table_width; ?>)</th>
  <td valign="middle"><input type="Text" name="table_width" value="<?php echo $table_width; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Table Header Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_header_color; ?>)</th>
  <td valign="middle"><input type="Text" name="table_header_color" value="<?php echo $table_header_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Table Header Font Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_header_font_color; ?>)</th>
  <td valign="middle"><input type="Text" name="table_header_font_color" value="<?php echo $table_header_font_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Main Table Body Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_body_color_1; ?>)</th>
  <td valign="middle"><input type="Text" name="table_body_color_1" value="<?php echo $table_body_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Main Table Body Font Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_body_font_color_1; ?>)</th>
  <td valign="middle"><input type="Text" name="table_body_font_color_1" value="<?php echo $table_body_font_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<?php if(!$folder){ ?>
<tr>
  <th valign="middle">Alt. Table Body Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_body_color_2; ?>)</th>
  <td valign="middle"><input type="Text" name="table_body_color_2" value="<?php echo $table_body_color_2; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Alt. Table Body Font Color:<br><font size="-2">Leave blank for default. (<?php echo $default_table_body_font_color_2; ?>)</th>
  <td valign="middle"><input type="Text" name="table_body_font_color_2" value="<?php echo $table_body_font_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<?php } ?>
<tr>
  <th valign="middle">Navigation Background Color:<br><font size="-2">Leave blank for default. (<?php echo $default_nav_color; ?>)</th>
  <td valign="middle"><input type="Text" name="nav_color" value="<?php echo $nav_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Navigation Font Color:<br><font size="-2">Leave blank for default. (<?php echo $default_nav_font_color; ?>)</th>
  <td valign="middle"><input type="Text" name="nav_font_color" value="<?php echo $nav_font_color; ?>" size="10" class="TEXT"></td>
</tr>
<?php if(!$folder){ ?>
<tr>
  <td colspan="2" valign="middle" bgcolor="#000000">&nbsp;</td>
</tr>
<tr>
  <th valign="middle">Duplicate Posts:</th>
  <td valign="middle"><select name="check_dup" class=big>
<option value="1" <?php if($check_dup==1) echo "selected"; ?>>Check For Duplicates</option>
<option value="0" <?php if(isset($check_dup) && $check_dup==0) echo "selected"; ?>>Do Not Check For Duplicates</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Messages Per Page:</th>
  <td valign="middle"><input type="Text" name="display" value="<?php echo $display; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Thread Type:</th>
  <td valign="middle"><select name="multi_level">
<option value="2" <?php if($multi_level==2) echo "selected"; ?>>Float to Top</option>
<option value="1" <?php if($multi_level==1) echo "selected"; ?>>Multiple Levels</option>
<option value="0" <?php if(isset($multi_level) && $multi_level==0) echo "selected"; ?>>Single Level</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Thread Display:</th>
  <td valign="middle"><select name="collapsed">
<option value="1" <?php if($collapsed==1) echo "selected"; ?>>Collapsed</option>
<option value="0" <?php if(isset($collapsed) && $collapsed==0) echo "selected"; ?>>Expanded</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Read Messages:</th>
  <td valign="middle"><select name="rflat" class=big>
<option value="1" <?php if($rflat==1) echo "selected"; ?>>Entire Thread At Once</option>
<option value="0" <?php if(isset($rflat) && $rflat==0) echo "selected"; ?>>One At A Time</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Show Users IP/Host:</th>
  <td valign="middle"><select name="showip" class=big>
<option value="0">Never</option>
<option value="1" selected>Always</option>
<option value="2">Anonymous Posts only</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Allow EMail-Notification for anonymous Posters:</th>
  <td valign="middle"><select name="emailnotification" class=big>
<option value="0" selected>Off</option>
<option value="1">On</option>
</select></td>
</tr>
<tr>
  <th valign="middle">Phorum Code:</th>
  <td valign="middle"><select name="allow_html" class=big>
<option value="0">Off</option>
<option value="1" selected>On</option>
</select> See docs/usage.txt</td>
</tr>
<tr>
  <td colspan="2" valign="middle" bgcolor="#000000">&nbsp;</td>
</tr>
<tr>
  <th valign="middle">Allow attachments?</th>
<?php
  if($AllowAttachments){
?>
  <td valign="middle">
  <INPUT TYPE="radio" NAME="allow_uploads" VALUE="Y">Yes
  <INPUT TYPE="radio" NAME="allow_uploads" VALUE="N" CHECKED>No
  </td>
<?php
  }
  else{
?>
  <td rowspan="4" valign="middle">
  <INPUT TYPE="hidden" NAME="allow_uploads" VALUE="N">
  <input type="hidden" name="upload_size" value="">
  To use these features turn on<br>
  attachments in the <A HREF="?page=attachments">Phorum Setup</A>.
<?php
  }
?>
</tr>
<tr>
  <th valign="middle">Attachment Size Limit<?php if(!empty($AttachmentSizeLimit)) echo " (max: ".$AttachmentSizeLimit."k)"; ?>:</th>
<?php if($AllowAttachments){ ?>
  <td valign="middle"><input type="Text" name="upload_size" value="<?php echo $upload_size; ?>" size="10" class="TEXT"></td>
<?php } ?>
</tr>
<tr>
  <th valign="middle">Allowed File Types:</th>
<?php if($AllowAttachments){ ?>
  <td valign="middle">
  <?
    if(!empty($AttachmentFileTypes)){
      $types=explode(";", $AttachmentFileTypes);
      if(count($types)>3 && count($types)%4!=0){
        $types[]="";
      }
      ?><table cellspacing="0" cellpadding="2" border="0"><tr><?
      $x=0;
      while(list($key, $type)=each($types)){
        echo "<td><font face=\"Arial,Helvetica\">";
        if(!empty($type)){
          echo "<input type=\"checkbox\" name=\"att_types[]\" value=\"$type\"";
          if($att_types[$type]==1) echo " checked";
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
      ?></tr></table><?
    }
    else{
      ?>There are no file types listed in the<br><A HREF="?page=attachments">Phorum Setup</a>.  Therefore all will<br>be allowed.<?
    }
  ?>
  </td>
<?php } ?>
</tr>
<tr>
  <th valign="middle">Maximum # of Attachments<?php if(!empty($MaximumNumberAttachments)) echo " (max: ".$MaximumNumberAttachments.")"; ?>:</th>
<?php if($AllowAttachments){ ?>
  <td valign="middle"><input type="Text" name="max_uploads" value="<?php echo $max_uploads; ?>" size="10" class="TEXT"></td>
<?php } ?>
</tr>
<tr>
  <td colspan="2" valign="middle" bgcolor="#000000">&nbsp;</td>
</tr>
<tr>
  <th valign="middle">Email All Posts To:</th>
  <td valign="middle"><input type="Text" name="email_list" value="<?php echo $email_list; ?>" size="25" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Return Email Address:</th>
  <td valign="middle"><input type="Text" name="email_return" value="<?php echo $email_return; ?>" size="25" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Email Subject Tag:</th>
  <td valign="middle"><input type="Text" name="email_tag" value="<?php echo $email_tag; ?>" size="25" class="TEXT"></td>
</tr>
<?php } ?>
</td>
</tr>
</table>
<p>
<center><input type="Submit" name="submit" value="Add" class="BUTTON"></center>
</form>
