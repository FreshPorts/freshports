<? /* Master Settings */ ?>
<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="setup">
<input type="Hidden" name="action" value="db">
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<td colspan="2" align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica"><b>Master Properties</b></font></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica"></font></td>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica"></font></td>
</tr>

<b>database server:</b><br>
<input type="Text" name="new_dbServer" value="<?PHP echo $dbServer; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>database name:</b><br>
<input type="Text" name="new_dbName" value="<?PHP echo $dbName; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>database user id:</b><br>
<input type="Text" name="new_dbUser" value="<?PHP echo $dbUser; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>database password:</b><br>
<input type="Text" name="new_dbPass" value="<?PHP echo $dbPass; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b># to display:</b><br>
<input type="Text" name="new_DefaultDisplay" value="<?PHP echo $DefaultDisplay; ?>" size="10" style="width: 120px;" class="TEXT"><br><br>
<b>default email:</b><br>
<input type="Text" name="new_DefaultEmail" value="<?PHP echo $DefaultEmail; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>use cookies:</b><br>
<input type="Checkbox" name="new_UseCookies" value="1" <?PHP if($UseCookies) echo "checked"; ?>> Check for yes<br><br>
<b>sort forums on forum list:</b><br>
<input type="Checkbox" name="new_SortForums" value="1" <?PHP if($SortForums) echo "checked"; ?>> Check for yes<br><br>
<b>forum url:</b><br>
<input type="Text" name="new_forum_url" value="<?PHP echo $forum_url; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>file extension:</b><br>
<input type="Text" name="new_ext" value="<?PHP echo $ext; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>front page name<br>(w/o extension eg. index not index.php):</b><br>
<input type="Text" name="new_forum_page" value="<?PHP echo $forum_page; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>list page name:</b><br>
<input type="Text" name="new_list_page" value="<?PHP echo $list_page; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>search page name:</b><br>
<input type="Text" name="new_search_page" value="<?PHP echo $search_page; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>read page name:</b><br>
<input type="Text" name="new_read_page" value="<?PHP echo $read_page; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>post page name:</b><br>
<input type="Text" name="new_post_page" value="<?PHP echo $post_page; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>violation page name:</b><br>
<input type="Text" name="new_violation_page" value="<?PHP echo $violation_page; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>down page name:</b><br>
<input type="Text" name="new_down_page" value="<?PHP echo $down_page; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default .lang file name:</b><br>
<input type="Text" name="new_default_lang" value="<?PHP echo $default_lang; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default table width:</b><br>
<input type="Text" name="new_default_table_width" value="<?PHP echo $default_table_width; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default table head color:</b><br>
<input type="Text" name="new_default_table_header_color" value="<?PHP echo $default_table_header_color; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default table head font color:</b><br>
<input type="Text" name="new_default_table_header_font_color" value="<?PHP echo $default_table_header_font_color; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default table body color 1:</b><br>
<input type="Text" name="new_default_table_body_color_1" value="<?PHP echo $default_table_body_color_1; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default table body font color 1:</b><br>
<input type="Text" name="new_default_table_body_font_color_1" value="<?PHP echo $default_table_body_font_color_1; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default table body color 2:</b><br>
<input type="Text" name="new_default_table_body_color_2" value="<?PHP echo $default_table_body_color_2; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default table body font color 2:</b><br>
<input type="Text" name="new_default_table_body_font_color_2" value="<?PHP echo $default_table_body_font_color_2; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default navigation body color:</b><br>
<input type="Text" name="new_default_nav_color" value="<?PHP echo $default_nav_color; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<b>default navigation font color:</b><br>
<input type="Text" name="new_default_nav_font_color" value="<?PHP echo $default_nav_font_color; ?>" size="10" style="width: 300px;" class="TEXT"><br><br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</td>
</tr>
</table>
</form>
