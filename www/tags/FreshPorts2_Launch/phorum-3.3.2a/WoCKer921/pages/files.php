<?php check_security(); ?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="setup">
<input type="Hidden" name="action" value="files">
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
  <td colspan="2" align="center" valign="middle" class="table-header">File/Path Settings</td>
</tr>
<tr>
  <th valign="middle">Forum URL:</th>
  <td valign="middle"><input type="Text" name="new_forum_url" value="<?php echo $forum_url; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">File Extension:</th>
  <td valign="middle"><input type="Text" name="new_ext" value="<?php echo $ext; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Forum List Page Name:</th>
  <td valign="middle"><input type="Text" name="new_forum_page" value="<?php echo $forum_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Message List Page Name:</th>
  <td valign="middle"><input type="Text" name="new_list_page" value="<?php echo $list_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Search Page Name:</th>
  <td valign="middle"><input type="Text" name="new_search_page" value="<?php echo $search_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Read Page Name:</th>
  <td valign="middle"><input type="Text" name="new_read_page" value="<?php echo $read_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Post Page Name:</th>
  <td valign="middle"><input type="Text" name="new_post_page" value="<?php echo $post_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Violation Page Name:</th>
  <td valign="middle"><input type="Text" name="new_violation_page" value="<?php echo $violation_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Down Page Name:</th>
  <td valign="middle"><input type="Text" name="new_down_page" value="<?php echo $down_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Attachment Page Name:</th>
  <td valign="middle"><input type="Text" name="new_attach_page" value="<?php echo $attach_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
</table>
<br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
