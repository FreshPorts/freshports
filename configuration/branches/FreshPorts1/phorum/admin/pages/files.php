<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="setup">
<input type="Hidden" name="action" value="files">
<table border="1" cellspacing="0" cellpadding="3">
<tr>
  <td colspan="2" align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>File/Path Settings</b></font></td>
</tr>
<?PHP
  if($forum_url==""){
    $thisdir=dirname($PHP_SELF);
    $updir=substr($thisdir, 0, strlen($thisdir)-strlen($admindir));
    $forum_url="http://$HTTP_HOST$updir";
  }
?>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Forum URL:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_forum_url" value="<?PHP echo $forum_url; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">File Extension:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_ext" value="<?PHP echo $ext; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Forum List Page Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_forum_page" value="<?PHP echo $forum_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Message List Page Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_list_page" value="<?PHP echo $list_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Search Page Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_search_page" value="<?PHP echo $search_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Read Page Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_read_page" value="<?PHP echo $read_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Post Page Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_post_page" value="<?PHP echo $post_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Violation Page Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_violation_page" value="<?PHP echo $violation_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Down Page Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_down_page" value="<?PHP echo $down_page; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
</table>
<br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>