<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="setup">
<input type="Hidden" name="action" value="html">
<table border="1" cellspacing="0" cellpadding="3">
<tr>
  <td colspan="2" align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>HTML Settings</b></font></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Table Width:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_table_width" value="<?PHP echo $default_table_width; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Table Head Color:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_table_header_color" value="<?PHP echo $default_table_header_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Table Head Font Color:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_table_header_font_color" value="<?PHP echo $default_table_header_font_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Main Table Body Color:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_table_body_color_1" value="<?PHP echo $default_table_body_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Main Table Body Font Color:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_table_body_font_color_1" value="<?PHP echo $default_table_body_font_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Alt. Table Body Color:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_table_body_color_2" value="<?PHP echo $default_table_body_color_2; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Alt. Table Body Font Color:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_table_body_font_color_2" value="<?PHP echo $default_table_body_font_color_2; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Navigation Background Color:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_nav_color" value="<?PHP echo $default_nav_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Default Navigation Font Color:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_default_nav_font_color" value="<?PHP echo $default_nav_font_color; ?>" size="10" class="TEXT"></td>
</tr>
</table>
<br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>