<?php check_security(); ?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="setup">
<input type="Hidden" name="action" value="html">
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
  <td colspan="2" align="center" valign="middle" class="table-header">HTML Settings</td>
</tr>
<tr>
  <th valign="middle">Default Body Color:</th>
  <td valign="middle"><input type="Text" name="new_default_body_color" value="<?php echo $default_body_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Link Color:</th>
  <td valign="middle"><input type="Text" name="new_default_body_link_color" value="<?php echo $default_body_link_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Active Link Color:</th>
  <td valign="middle"><input type="Text" name="new_default_body_alink_color" value="<?php echo $default_body_alink_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Visited Link Color:</th>
  <td valign="middle"><input type="Text" name="new_default_body_vlink_color" value="<?php echo $default_body_vlink_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Table Width:</th>
  <td valign="middle"><input type="Text" name="new_default_table_width" value="<?php echo $default_table_width; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Table Head Color:</th>
  <td valign="middle"><input type="Text" name="new_default_table_header_color" value="<?php echo $default_table_header_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Table Head Font Color:</th>
  <td valign="middle"><input type="Text" name="new_default_table_header_font_color" value="<?php echo $default_table_header_font_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Main Table Body Color:</th>
  <td valign="middle"><input type="Text" name="new_default_table_body_color_1" value="<?php echo $default_table_body_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Main Table Body Font Color:</th>
  <td valign="middle"><input type="Text" name="new_default_table_body_font_color_1" value="<?php echo $default_table_body_font_color_1; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Alt. Table Body Color:</th>
  <td valign="middle"><input type="Text" name="new_default_table_body_color_2" value="<?php echo $default_table_body_color_2; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Alt. Table Body Font Color:</th>
  <td valign="middle"><input type="Text" name="new_default_table_body_font_color_2" value="<?php echo $default_table_body_font_color_2; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Navigation Background Color:</th>
  <td valign="middle"><input type="Text" name="new_default_nav_color" value="<?php echo $default_nav_color; ?>" size="10" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Default Navigation Font Color:</th>
  <td valign="middle"><input type="Text" name="new_default_nav_font_color" value="<?php echo $default_nav_font_color; ?>" size="10" class="TEXT"></td>
</tr>
</table>
<br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
