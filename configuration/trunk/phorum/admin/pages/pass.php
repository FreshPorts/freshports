<? /* Change Password */ ?>
<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="action" value="pass">
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<td align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>Change Password</b></td>
</tr>
<tr>
<td align="left" valign="middle" bgcolor="#FFFFFF">
<font face="Arial,Helvetica">
Password:<br>
<input type="Password" name="newPassword" value="" size="10" style="width: 300px;" class="TEXT"><br><br>
Confirm:<br>
<input type="Password" name="confirm" value="" size="10" style="width: 300px;" class="TEXT"><br><br>
</font>
</td>
</tr>
</table>
<p>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
