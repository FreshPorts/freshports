<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="managemenu">
<input type="Hidden" name="action" value="moderate">
<input type="Hidden" name="num" value="<?PHP echo $num; ?>">
<center>
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<td align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>Quick Approve: <?PHP echo $ForumName; ?></b></font></td>
</tr>
<tr>
<td align="center" valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Approve <INPUT CHECKED TYPE="RADIO" NAME="approved" VALUE="N"> &nbsp; | &nbsp; Disable <INPUT TYPE="RADIO" NAME="approved" VALUE="Y"></font></td>
</tr>
<td align="center" valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Ids/Threads: <input type="Text" name="id" size="10" class="TEXT"></font></td>
</tr>
</table>
<p><center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
