<? /* New Forum */ ?>
<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="action" value="add">
<input type="Hidden" name="page" value="managemenu">
<input type="Hidden" name="folder" value="<? echo $folder; ?>">
<input type="Hidden" name="frompage" value="<? $folder ? $word="newfolder" : $word="newforum"; echo $word; ?>">
<table border="1" cellspacing="0" cellpadding="3">
<tr>
  <td colspan="2" align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>New <? $folder ? $word="Folder" : $word="Forum"; echo $word; ?></b></font></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Name:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="name" value="<?PHP echo $name; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Description:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><textarea name="description" cols="60" rows="3" wrap="VIRTUAL" style="width: 300px;"><?PHP echo $description; ?></textarea></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Folder:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><select name="parent">
<option value="0">Top Level</option>
<?
  $sSQL="Select id, name from forums where folder='1' order by name";
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
<? if(!$folder){ ?>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Table Name:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><font face="Arial,Helvetica"><input type="Text" name="table" value="<?PHP echo $table; ?>" size="10" class="TEXT"><br><input type="Checkbox" name="table_exists" value="1" <?PHP if($table_exists) echo "checked"; ?>> Table already exists</font></td>
</tr>
<?PHP
  if($mod_email=="")               $mod_email=$DefaultEmail;
  if($display=="")                 $display=$DefaultDisplay;
  if($table_width=="")             $table_width=$default_table_width;
  if($table_header_color=="")      $table_header_color=$default_table_header_color;
  if($table_header_font_color=="") $table_header_font_color=$default_table_header_font_color;
  if($table_body_color_1=="")      $table_body_color_1=$default_table_body_color_1;
  if($table_body_font_color_1=="") $table_body_font_color_1=$default_table_body_font_color_1;
  if($table_body_color_2=="")      $table_body_color_2=$default_table_body_color_2;
  if($table_body_font_color_2=="") $table_body_font_color_2=$default_table_body_font_color_2;
  if($nav_color=="")               $nav_color=$default_nav_color;
  if($nav_font_color=="")          $nav_font_color=$default_nav_font_color;
  if($lang=="")                    $lang=$default_lang;
?>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Moderation:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><select name="moderation" class=big>
  <option value="none" <? if($moderation=='none') echo 'selected'; ?>>None</option>
  <option value="all" <? if($moderation=='all') echo 'selected'; ?>>All Messages Before Posted</option>
  <option value="react" <? if($moderation=='react') echo 'selected'; ?>>All Messages After Posted</option>
</select></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Moderator Email:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="mod_email" value="<?PHP echo $mod_email; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Moderator Password:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="password" name="mod_pass" size="10" class="TEXT"><br><input type="password" name="mod_pass_2" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Mailing List Address:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="email_list" value="<?PHP echo $email_list; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Mailing List Return:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="email_return" value="<?PHP echo $email_return; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Duplicate Posts:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><select name="check_dup" class=big>
<option value="0" <? if($check_dup==0) echo "selected"; ?>>Do Not Check For Duplicates</option>
<option value="1" <? if($check_dup==1) echo "selected"; ?>>Check For Duplicates</option>
</select></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Messages Per Page:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="display" value="<?PHP echo $display; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Thread Type:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><select name="multi_level">
<option value="0" <? if($multi_level==0) echo "selected"; ?>>Single Level</option>
<option value="1" <? if($multi_level==1) echo "selected"; ?>>Multiple Levels</option>
</select></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Thread Display:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><select name="collapsed">
<option value="0" <? if($collapsed==0) echo "selected"; ?>>Expanded</option>
<option value="1" <? if($collapsed==1) echo "selected"; ?>>Collapsed</option>
</select></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Read Messages:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><select name="rflat" class=big>
<option value="0" <? if($rflat==0) echo "selected"; ?>>One At A Time</option>
<option value="1" <? if($rflat==1) echo "selected"; ?>>Entire Thread At Once</option>
</select></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Moderator Host:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="staff_host" value="<?PHP echo $staff_host; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Language:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><select name="lang">
<?PHP
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
  $intStartLang = strpos($file, '/') + 1;
  $intLengthLang = strpos($file, '.') - $intStartLang;
  $text=ucwords(substr($file,$intStartLang,$intLengthLang));
  echo "<option value=\"$file\"";
  if($file==$lang) echo ' selected';
  echo ">$text</option>\n";
  $file = next($aryLangs);
}
?>
</select></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">HTML:</font></td>
  <td valign="middle" bgcolor="#C0C0C0">
<table cellspacing="0" cellpadding="2" border="0">
<tr>
    <td><font face="Arial,Helvetica"><input type="checkbox" name="html_all" value="1" <? if($html_all==1) echo "checked"; ?>> Allow All</font></td>
    <td><font face="Arial,Helvetica"><input type="checkbox" name="html_style" value="1" <? if($html_style==1) echo "checked"; ?>> Bold, Italic, Underline</font></td>
</tr>
<tr>
    <td><font face="Arial,Helvetica"><input type="checkbox" name="html_font" value="1" <? if($html_font==1) echo "checked"; ?>> Fonts</font></td>
    <td><font face="Arial,Helvetica"><input type="checkbox" name="html_li" value="1" <? if($html_li==1) echo "checked"; ?>> Lists (ul,ol,li)</font></td>
</tr>
<tr>
    <td><font face="Arial,Helvetica"><input type="checkbox" name="html_img" value="1" <? if($html_img==1) echo "checked"; ?>> Images</font></td>
    <td><font face="Arial,Helvetica"><input type="checkbox" name="html_a" value="1" <? if($html_a==1) echo "checked"; ?>> Anchors (Links)</font></td>
</tr>
</table>
</td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Table Width:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="table_width" value="<?PHP echo $table_width; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Table Header Color:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="table_header_color" value="<?PHP echo $table_header_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Table Header Font Color:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="table_header_font_color" value="<?PHP echo $table_header_font_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Main Table Body Color:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="table_body_color_1" value="<?PHP echo $table_body_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Main Table Body Font Color:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="table_body_font_color_1" value="<?PHP echo $table_body_font_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Alt. Table Body Color:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="table_body_color_2" value="<?PHP echo $table_body_color_2; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Alt. Table Body Font Color:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="table_body_font_color_2" value="<?PHP echo $table_body_font_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Navigation Background Color:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="nav_color" value="<?PHP echo $nav_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Navigation Font Color:</font></td>
  <td valign="middle" bgcolor="#C0C0C0"><input type="Text" name="nav_font_color" value="<?PHP echo $nav_font_color; ?>" size="10" class="TEXT"></td>
</tr>
<? } ?>
</td>
</tr>
</table>
<p>
<center><input type="Submit" name="submit" value="Add" class="BUTTON"></center>
</form>
