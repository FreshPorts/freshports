<?php check_security(); ?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="setup">
<input type="Hidden" name="action" value="db">
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
  <td colspan="2" align="center" valign="middle" class="table-header">Database Settings</td>
</tr>
<tr>
  <th valign="middle">Server Name:</th>
  <td valign="middle"><input type="Text" name="new_dbServer" value="<?php echo $PHORUM["DatabaseServer"]; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Database Name:</th>
  <td valign="middle"><input type="Text" name="new_dbName" value="<?php echo $PHORUM["DatabaseName"]; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">User Name:</th>
  <td valign="middle"><input type="Text" name="new_dbUser" value="<?php echo $PHORUM["DatabaseUser"]; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
  <th valign="middle">Password:</th>
  <td valign="middle"><input type="Text" name="new_dbPass" value="<?php echo $PHORUM["DatabasePassword"]; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
</table>
<br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
<br />
<br />
<b>NOTE:  If SQL Safe Mode is in use on your server, leave the username and password emtpy.</b>
</form>