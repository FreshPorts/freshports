<?php check_security(); ?>
<?php /* Change Password */ ?>
<form action="<?php echo $PHP_SELF; ?>" method="POST">
<input type="Hidden" name="action" value="pass">
<input type="Hidden" name="frompage" value="pass">
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
<td align="center" valign="middle" class="table-header">Change Password</td>
</tr>
<tr>
<td align="left" valign="middle">

Password:<br>
<input type="Password" name="newPassword" value="" size="10" style="width: 300px;" class="TEXT"><br><br>
Confirm:<br>
<input type="Password" name="confirm" value="" size="10" style="width: 300px;" class="TEXT"><br><br>

</td>
</tr>
</table>
<p>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
