<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="managemenu">
<input type="Hidden" name="action" value="del">
<input type="Hidden" name="num" value="<?PHP echo $num; ?>">
<input type="Hidden" name="type" value="quick">
<center>
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<td align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>Quick Delete: <?PHP echo $ForumName; ?></b></font></td>
</tr>
<tr>
<td align="center" valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Ids/Threads: <input type="Text" name="id" size="10" class="TEXT"></font></td>
</tr>
</table>
<p><center><input type="Submit" name="submit" value="Delete" class="BUTTON"></center>
</form>
