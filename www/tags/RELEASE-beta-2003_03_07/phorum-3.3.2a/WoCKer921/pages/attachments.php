<?php check_security(); ?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="action" value="attachments">
<br />
<br />
<b>Allowing anyone to upload files to your server is risky.<br />
Security issues outside of Phorum could be used in conjunction with this to access your system.<br />
Use at your own risk.
</b><br />
<br />
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
  <td colspan="2" align="center" valign="middle" class="table-header">Attachment Settings</td>
</tr>
<tr>
  <th valign="middle">Allow Attachments:</th>
  <td valign="middle"><input type="radio" name="new_AllowAttachments" value="1"<?php echo ($AllowAttachments?' checked':''); ?>>Yes&nbsp;&nbsp;<input type="radio" name="new_AllowAttachments" value="0"<?php echo (!$AllowAttachments?' checked':''); ?>>No</td>
</tr>
<tr>
  <th valign="middle">Directory (full path):</th>
  <td valign="middle"><input type="Text" name="new_AttachmentDir" value="<?php echo $AttachmentDir; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Size Limit (eg: 2000 = 2Mb):</th>
  <td valign="middle"><input type="Text" name="new_AttachmentSizeLimit" value="<?php echo $AttachmentSizeLimit; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">File Types (eg: gif;jpg;bmp;):</th>
  <td valign="middle"><input type="Text" name="new_AttachmentFileTypes" value="<?php echo $AttachmentFileTypes; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Maximum # Attachments:</th>
  <td valign="middle"><input type="Text" name="new_MaximumNumberAttachments" value="<?php echo $MaximumNumberAttachments; ?>" size="10" style="width: 200px;" class="TEXT"></td>
</tr>
</table>
<br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
