<?php check_security(); ?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="setup">
<input type="Hidden" name="action" value="global">
  <table border="0" cellspacing="0" cellpadding="3" class="box-table">
    <tr>
      <td colspan="2" align="center" valign="middle" class="table-header">Global Settings</td>
    </tr>
    <tr>
      <th valign="middle">Default Messages Per Page:</th>
      <td valign="middle">
        <input type="Text" name="new_DefaultDisplay" value="<?php echo $DefaultDisplay; ?>" size="10" style="width: 200px;" class="TEXT">
      </td>
    </tr>
    <tr>
      <th valign="middle">Default Email:</th>
      <td valign="middle">
        <input type="Text" name="new_DefaultEmail" value="<?php echo $DefaultEmail; ?>" size="10" style="width: 200px;" class="TEXT">
      </td>
    </tr>
    <tr>
      <th valign="middle">PhorumMail Code:</th>
      <td valign="middle">
        <input type="Text" name="new_PhorumMailCode" value="<?php echo $PhorumMailCode; ?>" size="10" style="width: 200px;" class="TEXT">
      </td>
    </tr>
    <tr>
      <th valign="middle">Cookies:</th>
      <td valign="middle">
        <select name="new_UseCookies" class=big>
          <option value="0" <?php if($UseCookies==0) echo "selected"; ?>>Do Not
          Use Cookies</option>
          <option value="1" <?php if($UseCookies==1) echo "selected"; ?>>Use Cookies</option>
        </select>
      </td>
    </tr>
    <tr>
      <th valign="middle">Sorting:</th>
      <td valign="middle">
        <select name="new_SortForums" class=big>
          <option value="0" <?php if($SortForums==0) echo "selected"; ?>>Do Not
          Sort Forums</option>
          <option value="1" <?php if($SortForums==1) echo "selected"; ?>>Sort
          Forums</option>
        </select>
      </td>
    </tr>
    <tr>
      <th valign="middle">Default Language:</th>
      <td valign="middle">
        <select name="new_default_lang">
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
    if($file==$default_lang) echo ' selected';
    echo ">$text</option>\n";
  }
  $file = next($aryLangs);
}
?>
        </select>
      </td>
    </tr>
    <tr>
      <th valign="middle">TimeZone Offset (from server)</th>
      <td valign="middle">
        <select name="new_default_timezone_offset">
<?php
for($x=12;$x>-12;$x--){
echo '          <option value="'.$x.'"'.($x==$TimezoneOffset?' selected':'').'>'.$x."</option>\n";
}
?>
        </select>
      </td>
    </tr>
  </table>
<br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
